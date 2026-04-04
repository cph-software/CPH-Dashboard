<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\Tyre;
use App\Models\TyreBrand;
use App\Models\TyreSize;
use App\Models\TyreSegment;
use App\Models\TyrePattern;
use App\Models\TyreLocation; // Import TyreLocation
use Illuminate\Http\Request;

class TyreMasterController extends Controller
{
    public function index()
    {
        // Master data: Global dropdown (no company whitelist restriction)
        $brands = TyreBrand::where('status', 'Active')->orderBy('brand_name')->get();
        $sizes = TyreSize::with('brand')->orderBy('size')->get();
        $patterns = TyrePattern::with('brand')->orderBy('name')->get();
        
        $segments = TyreSegment::with('location')->where('status', 'Active')->get();
        $locations = TyreLocation::all();
        $companies = \App\Models\TyreCompany::where('status', 'Active')->orderBy('company_name')->get();

        return view('tyre-performance.master.tyres.index', compact('brands', 'sizes', 'segments', 'patterns', 'locations', 'companies'));
    }

    /**
     * Data for Server-Side DataTables
     */
    public function data(Request $request)
    {
        $query = Tyre::with(['brand', 'size', 'pattern', 'location', 'company']);

        // Search logic
        if ($request->has('search') && $request->input('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('serial_number', 'like', "%$searchValue%")
                    ->orWhereHas('brand', function ($sub) use ($searchValue) {
                        $sub->where('brand_name', 'like', "%$searchValue%");
                    })
                    ->orWhereHas('size', function ($sub) use ($searchValue) {
                        $sub->where('size', 'like', "%$searchValue%");
                    })
                    ->orWhereHas('company', function ($sub) use ($searchValue) {
                        $sub->where('company_name', 'like', "%$searchValue%");
                    })
                    ->orWhere('status', 'like', "%$searchValue%");
            });
        }

        $totalRecords = Tyre::count();
        $filteredRecords = $query->count();

        // Ordering
        if ($request->has('order')) {
            $columnIndex = $request->input('order.0.column');
            $columnDir = $request->input('order.0.dir');

            $isAdmin = (auth()->check() && auth()->user()->role_id == 1);

            if ($isAdmin) {
                // Map column index to DB field for Admin (with company column)
                $cols = [
                    1 => 'serial_number',
                    2 => 'tyre_company_id',
                    3 => 'tyre_brand_id',
                    4 => 'tyre_size_id',
                    5 => 'segment_name',
                    6 => 'is_in_warehouse',
                    7 => 'status'
                ];
            } else {
                // Map column index to DB field for Normal User (without company column)
                $cols = [
                    1 => 'serial_number',
                    2 => 'tyre_brand_id',
                    3 => 'tyre_size_id',
                    4 => 'segment_name',
                    5 => 'is_in_warehouse',
                    6 => 'status'
                ];
            }

            if (isset($cols[$columnIndex])) {
                $query->orderBy($cols[$columnIndex], $columnDir);
            }
        } else {
            $query->latest();
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $tyres = $query->skip($start)->take($length)->get();

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalRecords),
            "recordsFiltered" => intval($filteredRecords),
            "data" => $tyres
        ]);
    }

    public function show($id)
    {
        $tyre = Tyre::with(['brand', 'size', 'pattern', 'location', 'currentVehicle', 'currentPosition', 'movements.vehicle', 'movements.position'])
            ->findOrFail($id);

        return view('tyre-performance.master.tyres.show', compact('tyre'));
    }

    public function edit($id)
    {
        $tyre = Tyre::findOrFail($id);

        // Master data: Global dropdown (no company whitelist restriction)
        $brands = TyreBrand::where('status', 'Active')->orderBy('brand_name')->get();
        $sizes = TyreSize::with('brand')->orderBy('size')->get();
        $patterns = TyrePattern::with('brand')->orderBy('name')->get();
        
        $segments = TyreSegment::where('status', 'Active')->get();
        $locations = TyreLocation::all();

        return view('tyre-performance.master.tyres.edit', compact('tyre', 'brands', 'sizes', 'segments', 'patterns', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string|max:255|unique:tyres',
            'custom_serial_number' => 'nullable|string|max:255|unique:tyres',
            'tyre_brand_id' => 'required', // Can be ID or String for Admin
            'tyre_size_id' => 'required',
            'tyre_pattern_id' => 'nullable',
            'segment_name' => 'nullable|string|max:255',
            'is_in_warehouse' => 'nullable|boolean',
            'status' => 'required|in:New,Installed,Scrap,Repaired,Retread',
            'price' => 'nullable|numeric|min:0',
            'original_tread_depth' => 'nullable|numeric|min:0',
            'ply_rating' => 'nullable|string|max:50',
            'retread_count' => 'nullable|integer|min:0',
            'tyre_company_id' => auth()->user()->role_id == 1 ? 'required|exists:tyre_companies,id' : 'nullable',
        ]);

        $data = $request->all();
        $user = auth()->user();

        // Handle Admin "Type Manual" Logic
        if ($user->role_id == 1) {
            // Brand
            if (!is_numeric($request->tyre_brand_id)) {
                $brand = TyreBrand::firstOrCreate(['brand_name' => strtoupper($request->tyre_brand_id)], ['status' => 'Active']);
                $data['tyre_brand_id'] = $brand->id;
            }
            // Size
            if (!is_numeric($request->tyre_size_id)) {
                $size = TyreSize::firstOrCreate(['size' => strtoupper($request->tyre_size_id), 'tyre_brand_id' => $data['tyre_brand_id']]);
                $data['tyre_size_id'] = $size->id;
            }
            // Pattern
            if ($request->filled('tyre_pattern_id') && !is_numeric($request->tyre_pattern_id)) {
                $pattern = TyrePattern::firstOrCreate(['name' => strtoupper($request->tyre_pattern_id), 'tyre_brand_id' => $data['tyre_brand_id']]);
                $data['tyre_pattern_id'] = $pattern->id;
            }
        }

        $tyre = Tyre::create($data);
        $tyre->load(['brand', 'size', 'pattern', 'location']);

        setLogActivity(auth()->id(), 'Menambah ban baru: ' . $request->serial_number, [
            'action_type' => 'create',
            'module' => 'Master Tyre',
            'data_after' => [
                'Serial Number' => $tyre->serial_number,
                'Brand' => $tyre->brand->brand_name ?? '-',
                'Size' => $tyre->size->size ?? '-',
                'Segment' => $tyre->segment_name ?? '-',
                'Work Location' => $tyre->location->location_name ?? '-',
                'Status' => $tyre->status,
                'Price' => $tyre->price,
                'Initial Tread Depth' => $tyre->initial_tread_depth ?? '-',
            ]
        ]);

        return redirect()->back()->with('success', 'Tyre created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'serial_number' => 'required|string|max:255|unique:tyres,serial_number,' . $id,
            'custom_serial_number' => 'nullable|string|max:255|unique:tyres,custom_serial_number,' . $id,
            'tyre_brand_id' => 'required',
            'tyre_size_id' => 'required',
            'tyre_pattern_id' => 'nullable',
            'segment_name' => 'nullable|string|max:255',
            'is_in_warehouse' => 'nullable|boolean',
            'status' => 'required|in:New,Installed,Scrap,Repaired,Retread',
            'price' => 'nullable|numeric|min:0',
            'original_tread_depth' => 'nullable|numeric|min:0',
            'ply_rating' => 'nullable|string|max:50',
            'retread_count' => 'nullable|integer|min:0',
            'tyre_company_id' => auth()->user()->role_id == 1 ? 'required|exists:tyre_companies,id' : 'nullable',
        ]);

        $tyre = Tyre::findOrFail($id);
        $data = $request->all();
        $user = auth()->user();

        // Handle Admin "Type Manual" Logic
        if ($user->role_id == 1) {
            if (!is_numeric($request->tyre_brand_id)) {
                $brand = TyreBrand::firstOrCreate(['brand_name' => strtoupper($request->tyre_brand_id)], ['status' => 'Active']);
                $data['tyre_brand_id'] = $brand->id;
            }
            if (!is_numeric($request->tyre_size_id)) {
                $size = TyreSize::firstOrCreate(['size' => strtoupper($request->tyre_size_id), 'tyre_brand_id' => $data['tyre_brand_id']]);
                $data['tyre_size_id'] = $size->id;
            }
            if ($request->filled('tyre_pattern_id') && !is_numeric($request->tyre_pattern_id)) {
                $pattern = TyrePattern::firstOrCreate(['name' => strtoupper($request->tyre_pattern_id), 'tyre_brand_id' => $data['tyre_brand_id']]);
                $data['tyre_pattern_id'] = $pattern->id;
            }
        }

        $dataBefore = $tyre->toArray();
        $tyre->update($data);
        $tyre->load(['brand', 'size', 'pattern', 'location']);

        setLogActivity(auth()->id(), 'Memperbarui ban: ' . $request->serial_number, [
            'action_type' => 'update',
            'module' => 'Master Tyre',
            'data_before' => $dataBefore,
            'data_after' => [
                'Serial Number' => $tyre->serial_number,
                'Brand' => $tyre->brand->brand_name ?? '-',
                'Size' => $tyre->size->size ?? '-',
                'Segment' => $tyre->segment_name ?? '-',
                'Work Location' => $tyre->location->location_name ?? '-',
                'Status' => $tyre->status,
            ]
        ]);

        return redirect()->back()->with('success', 'Tyre updated successfully');
    }

    public function destroy($id)
    {
        $tyre = Tyre::findOrFail($id);

        if ($tyre->movements()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete tyre. it has movement history records.');
        }

        setLogActivity(auth()->id(), 'Menghapus ban: ' . $tyre->serial_number, [
            'action_type' => 'delete',
            'module' => 'Master Tyre',
            'data_before' => $tyre->toArray()
        ]);

        $tyre->delete();

        return redirect()->back()->with('success', 'Tyre deleted successfully');
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids');
        $action = $request->input('action');

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        if ($action === 'delete') {
            $deletedCount = 0;
            $skippedCount = 0;

            foreach ($ids as $id) {
                $tyre = Tyre::find($id);
                if ($tyre) {
                    if ($tyre->movements()->exists()) {
                        $skippedCount++;
                    } else {
                        $tyre->delete();
                        $deletedCount++;
                    }
                }
            }

            setLogActivity(auth()->id(), "Bulk delete ban: $deletedCount berhasil, $skippedCount dilewati (ada riwayat)", [
                'action_type' => 'delete',
                'module' => 'Master Tyre',
                'ids' => $ids
            ]);

            $msg = "$deletedCount data ban berhasil dihapus.";
            if ($skippedCount > 0) {
                $msg .= " $skippedCount data dilewati karena memiliki riwayat pergerakan.";
            }

            return redirect()->back()->with($skippedCount > 0 ? 'warning' : 'success', $msg);
        }

        if ($action === 'update') {
            $data = [];
            if ($request->filled('status')) $data['status'] = $request->status;
            if ($request->filled('current_location_id')) $data['current_location_id'] = $request->current_location_id;
            if ($request->filled('segment_name')) $data['segment_name'] = $request->segment_name;
            if ($request->filled('retread_count')) $data['retread_count'] = $request->retread_count;

            if (empty($data)) {
                return redirect()->back()->with('error', 'Tidak ada field yang dipilih untuk diperbarui.');
            }

            Tyre::whereIn('id', $ids)->update($data);

            setLogActivity(auth()->id(), "Bulk update ban untuk " . count($ids) . " data", [
                'action_type' => 'update',
                'module' => 'Master Tyre',
                'ids' => $ids,
                'updated_fields' => $data
            ]);

            return redirect()->back()->with('success', count($ids) . ' data ban berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Aksi tidak dikenal.');
    }
}
