<?php

namespace App\Http\Controllers\TyrePerformance;

use App\Http\Controllers\Controller;
use App\Models\Tyre;
use App\Models\MasterImportKendaraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TrashController extends Controller
{
    /**
     * Tier 1: Company Trash — SPV/MGR melihat data terhapus milik perusahaannya
     */
    public function index()
    {
        return view('tyre-performance.trash.index');
    }

    /**
     * API DataTable — data terhapus (Tier 1)
     * BYPASS global company scope, lakukan filtering company secara manual
     */
    public function data(Request $request)
    {
        $type = $request->input('type', 'tyres');
        $user = Auth::user();

        if ($type === 'tyres') {
            $query = Tyre::allCompanies()->onlyTrashed()
                ->whereNull('permanent_deleted_at')
                ->with(['brand', 'size', 'company']);
        } else {
            $query = MasterImportKendaraan::allCompanies()->onlyTrashed()
                ->whereNull('permanent_deleted_at')
                ->with(['tyrePositionConfiguration', 'company']);
        }

        // Isolasi perusahaan: non-admin hanya lihat milik sendiri
        if ($user->role_id != 1 && $user->tyre_company_id) {
            $query->where('tyre_company_id', $user->tyre_company_id);
        }

        // Search
        if ($request->has('search') && $request->input('search.value')) {
            $search = $request->input('search.value');
            if ($type === 'tyres') {
                $query->where(function ($q) use ($search) {
                    $q->where('serial_number', 'like', "%$search%")
                      ->orWhereHas('brand', fn($sub) => $sub->where('brand_name', 'like', "%$search%"));
                });
            } else {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_kendaraan', 'like', "%$search%")
                      ->orWhere('no_polisi', 'like', "%$search%");
                });
            }
        }

        $totalRecords = $query->count();
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        
        $data = $query->orderBy('deleted_at', 'desc')
            ->skip($start)->take($length)->get()
            ->map(function ($item) use ($type) {
                $deletedAt = Carbon::parse($item->deleted_at);
                $expiresAt = $deletedAt->copy()->addDays(3);
                $hoursLeft = now()->diffInHours($expiresAt, false);

                return [
                    'id' => $item->id,
                    'type' => $type,
                    'name' => $type === 'tyres' 
                        ? $item->serial_number 
                        : $item->kode_kendaraan . ' [' . ($item->no_polisi ?? '-') . ']',
                    'detail' => $type === 'tyres'
                        ? ($item->brand->brand_name ?? '-') . ' / ' . ($item->size->size ?? '-')
                        : ($item->jenis_kendaraan ?? '-'),
                    'company' => $item->company->company_name ?? '-',
                    'deleted_at' => $deletedAt->format('d/m/Y H:i'),
                    'hours_left' => max(0, $hoursLeft),
                    'expired' => $hoursLeft <= 0,
                    'status' => $item->status ?? ($item->tyre_unit_status ?? '-'),
                ];
            });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data,
        ]);
    }

    /**
     * Restore — Kembalikan data dari Tier 1 ke data aktif
     */
    public function restore(Request $request, $type, $id)
    {
        $model = $this->resolveModel($type, $id);
        
        if (!$model) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        if (!$this->canAccess($model)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $model->restore();

        $label = $type === 'tyres' ? $model->serial_number : $model->kode_kendaraan;
        setLogActivity(Auth::id(), "Memulihkan data dari Trash: $label", [
            'action_type' => 'restore',
            'module' => 'Backup & Restore',
            'data_after' => ['type' => $type, 'id' => $id, 'name' => $label]
        ]);

        return response()->json(['success' => true, 'message' => "Data \"$label\" berhasil dipulihkan."]);
    }

    /**
     * Force Delete — Pindahkan ke Tier 2 (set permanent_deleted_at)
     * Data hilang dari tampilan SPV/MGR, masuk ke Super Admin Trash
     * TIDAK melakukan hard delete!
     */
    public function forceDelete(Request $request, $type, $id)
    {
        $model = $this->resolveModel($type, $id);
        
        if (!$model) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        if (!$this->canAccess($model)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        // Pindahkan ke Tier 2 — Direct DB update, bypass semua Eloquent scope/event
        \DB::table($model->getTable())
            ->where('id', $model->id)
            ->update(['permanent_deleted_at' => now()]);

        $label = $type === 'tyres' ? $model->serial_number : $model->kode_kendaraan;
        setLogActivity(Auth::id(), "Menghapus permanen (Tier 2): $label", [
            'action_type' => 'force_delete',
            'module' => 'Backup & Restore',
            'data_before' => ['type' => $type, 'id' => $id, 'name' => $label]
        ]);

        return response()->json(['success' => true, 'message' => "Data \"$label\" telah dihapus permanen dari tampilan Anda."]);
    }

    // =====================================================================
    // TIER 2: Super Admin Trash
    // =====================================================================

    /**
     * Tier 2: Super Admin — Lihat semua data yang sudah di-force-delete
     */
    public function adminTrash()
    {
        if (Auth::user()->role_id != 1) {
            abort(403);
        }

        $companies = \App\Models\TyreCompany::where('status', 'Active')->orderBy('company_name')->get();
        return view('tyre-performance.trash.admin', compact('companies'));
    }

    /**
     * API DataTable — Tier 2 data (bypass company scope sepenuhnya)
     */
    public function adminData(Request $request)
    {
        if (Auth::user()->role_id != 1) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $type = $request->input('type', 'tyres');
        $companyFilter = $request->input('company_id');

        if ($type === 'tyres') {
            $query = Tyre::allCompanies()->onlyTrashed()
                ->whereNotNull('permanent_deleted_at')
                ->with(['brand', 'size', 'company']);
        } else {
            $query = MasterImportKendaraan::allCompanies()->onlyTrashed()
                ->whereNotNull('permanent_deleted_at')
                ->with(['tyrePositionConfiguration', 'company']);
        }

        if ($companyFilter) {
            $query->where('tyre_company_id', $companyFilter);
        }

        // Search
        if ($request->has('search') && $request->input('search.value')) {
            $search = $request->input('search.value');
            if ($type === 'tyres') {
                $query->where(function ($q) use ($search) {
                    $q->where('serial_number', 'like', "%$search%");
                });
            } else {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_kendaraan', 'like', "%$search%")
                      ->orWhere('no_polisi', 'like', "%$search%");
                });
            }
        }

        $totalRecords = $query->count();
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $data = $query->orderBy('permanent_deleted_at', 'desc')
            ->skip($start)->take($length)->get()
            ->map(function ($item) use ($type) {
                $permDeletedAt = Carbon::parse($item->permanent_deleted_at);
                $purgeAt = $permDeletedAt->copy()->addDays(3);
                $hoursLeft = now()->diffInHours($purgeAt, false);

                return [
                    'id' => $item->id,
                    'type' => $type,
                    'name' => $type === 'tyres'
                        ? $item->serial_number
                        : $item->kode_kendaraan . ' [' . ($item->no_polisi ?? '-') . ']',
                    'detail' => $type === 'tyres'
                        ? ($item->brand->brand_name ?? '-') . ' / ' . ($item->size->size ?? '-')
                        : ($item->jenis_kendaraan ?? '-'),
                    'company' => $item->company->company_name ?? '-',
                    'deleted_at' => Carbon::parse($item->deleted_at)->format('d/m/Y H:i'),
                    'permanent_deleted_at' => $permDeletedAt->format('d/m/Y H:i'),
                    'hours_left' => max(0, $hoursLeft),
                    'status' => $item->status ?? ($item->tyre_unit_status ?? '-'),
                ];
            });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data,
        ]);
    }

    /**
     * Super Admin: Restore dari Tier 2 kembali ke data aktif
     */
    public function adminRestore(Request $request, $type, $id)
    {
        if (Auth::user()->role_id != 1) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $model = $this->resolveModel($type, $id, true);
        
        if (!$model) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        // Direct DB update to clear permanent_deleted_at
        \DB::table($model->getTable())
            ->where('id', $model->id)
            ->update(['permanent_deleted_at' => null]);
        $model->restore();

        $label = $type === 'tyres' ? $model->serial_number : $model->kode_kendaraan;
        setLogActivity(Auth::id(), "Super Admin memulihkan data dari Tier 2: $label", [
            'action_type' => 'admin_restore',
            'module' => 'Backup & Restore',
            'data_after' => ['type' => $type, 'id' => $id, 'name' => $label]
        ]);

        return response()->json(['success' => true, 'message' => "Data \"$label\" berhasil dipulihkan ke sistem."]);
    }

    /**
     * Super Admin: Hard Delete — benar-benar hapus dari database
     */
    public function adminPurge(Request $request, $type, $id)
    {
        if (Auth::user()->role_id != 1) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $model = $this->resolveModel($type, $id, true);
        
        if (!$model) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        $label = $type === 'tyres' ? $model->serial_number : $model->kode_kendaraan;
        
        setLogActivity(Auth::id(), "Super Admin menghapus permanen (Hard Delete): $label", [
            'action_type' => 'hard_delete',
            'module' => 'Backup & Restore',
            'data_before' => $model->toArray()
        ]);

        $model->forceDelete();

        return response()->json(['success' => true, 'message' => "Data \"$label\" telah dihapus permanen dari database."]);
    }

    // =====================================================================
    // BULK ACTIONS (TIER 1 & TIER 2)
    // =====================================================================

    public function bulkRestore(Request $request)
    {
        $ids = $request->input('ids', []);
        $type = $request->input('type', 'tyres');

        if (empty($ids)) return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.']);

        $count = 0;
        foreach ($ids as $id) {
            $model = $this->resolveModel($type, $id);
            if ($model && $this->canAccess($model)) {
                $model->restore();
                $count++;
            }
        }

        setLogActivity(Auth::id(), "Memulihkan $count data secara massal dari Trash", [
            'action_type' => 'bulk_restore',
            'module' => 'Backup & Restore',
            'data_after' => ['type' => $type, 'count' => $count]
        ]);

        return response()->json(['success' => true, 'message' => "$count data berhasil dipulihkan."]);
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        $type = $request->input('type', 'tyres');

        if (empty($ids)) return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.']);

        $count = 0;
        foreach ($ids as $id) {
            $model = $this->resolveModel($type, $id);
            if ($model && $this->canAccess($model)) {
                \DB::table($model->getTable())
                    ->where('id', $model->id)
                    ->update(['permanent_deleted_at' => now()]);
                $count++;
            }
        }

        setLogActivity(Auth::id(), "Menghapus permanen (Tier 2) $count data secara massal", [
            'action_type' => 'bulk_force_delete',
            'module' => 'Backup & Restore',
            'data_before' => ['type' => $type, 'count' => $count]
        ]);

        return response()->json(['success' => true, 'message' => "$count data telah dihapus permanen dari tampilan Anda."]);
    }

    public function adminBulkRestore(Request $request)
    {
        if (Auth::user()->role_id != 1) return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);

        $ids = $request->input('ids', []);
        $type = $request->input('type', 'tyres');

        if (empty($ids)) return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.']);

        $count = 0;
        foreach ($ids as $id) {
            $model = $this->resolveModel($type, $id, true);
            if ($model) {
                \DB::table($model->getTable())
                    ->where('id', $model->id)
                    ->update(['permanent_deleted_at' => null]);
                $model->restore();
                $count++;
            }
        }

        setLogActivity(Auth::id(), "Super Admin memulihkan $count data secara massal dari Tier 2", [
            'action_type' => 'admin_bulk_restore',
            'module' => 'Backup & Restore',
            'data_after' => ['type' => $type, 'count' => $count]
        ]);

        return response()->json(['success' => true, 'message' => "$count data berhasil dipulihkan ke sistem."]);
    }

    public function adminBulkPurge(Request $request)
    {
        if (Auth::user()->role_id != 1) return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);

        $ids = $request->input('ids', []);
        $type = $request->input('type', 'tyres');

        if (empty($ids)) return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.']);

        $count = 0;
        foreach ($ids as $id) {
            $model = $this->resolveModel($type, $id, true);
            if ($model) {
                $model->forceDelete();
                $count++;
            }
        }

        setLogActivity(Auth::id(), "Super Admin menghapus permanen (Hard Delete) $count data secara massal", [
            'action_type' => 'admin_bulk_purge',
            'module' => 'Backup & Restore',
            'data_before' => ['type' => $type, 'count' => $count]
        ]);

        return response()->json(['success' => true, 'message' => "$count data telah dihapus permanen dari database."]);
    }

    // =====================================================================
    // HELPERS
    // =====================================================================

    /**
     * Resolve model — BYPASS global company scope agar data trash
     * tidak terfilter oleh session active_company_id
     */
    private function resolveModel($type, $id, $includePermanent = false)
    {
        $query = $type === 'tyres'
            ? Tyre::allCompanies()->onlyTrashed()
            : MasterImportKendaraan::allCompanies()->onlyTrashed();

        if (!$includePermanent) {
            $query->whereNull('permanent_deleted_at');
        }

        return $query->find($id);
    }

    private function canAccess($model)
    {
        $user = Auth::user();
        if ($user->role_id == 1) return true;
        
        return $model->tyre_company_id == $user->tyre_company_id;
    }
}
