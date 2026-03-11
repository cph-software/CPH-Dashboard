<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterImportKendaraan;
use App\Models\TyrePositionConfiguration;
use App\Models\TyreLocation;
use App\Models\TyreSegment;
use Illuminate\Http\Request;

class KendaraanController extends Controller
{
    public function index()
    {
        // Removed heavy eager loading of all vehicles
        // Data will be loaded via AJAX for the DataTable
        $configurations = TyrePositionConfiguration::where('status', 'Active')->get();
        $locations = TyreLocation::all();
        $segments = TyreSegment::all();
        return view('tyre-performance.master.kendaraan.index', compact('configurations', 'locations', 'segments'));
    }

    public function show($id)
    {
        $kendaraan = MasterImportKendaraan::with([
            'tyrePositionConfiguration',
            'segment',
            'tyres.brand',
            'tyres.size',
            'tyres.pattern',
            'tyres.currentPosition',
        ])->findOrFail($id);

        // Movement history for this vehicle
        $movements = \App\Models\TyreMovement::with(['tyre.brand', 'tyre.size', 'position'])
            ->where('vehicle_id', $id)
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        // Stats
        $installedCount = $kendaraan->tyres->count();
        $totalPositions = $kendaraan->total_tyre_position ?? 0;
        $removalCount = $movements->where('movement_type', 'Removal')->count();
        $installCount = $movements->where('movement_type', 'Installation')->count();

        return view('tyre-performance.master.kendaraan.show', compact(
            'kendaraan',
            'movements',
            'installedCount',
            'totalPositions',
            'removalCount',
            'installCount'
        ));
    }


    /**
     * Data for Server-Side DataTables
     */
    public function data(Request $request)
    {
        $query = MasterImportKendaraan::with(['tyrePositionConfiguration', 'segment']);

        // Search logic
        if ($request->has('search') && $request->input('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('kode_kendaraan', 'like', "%$searchValue%")
                    ->orWhere('jenis_kendaraan', 'like', "%$searchValue%")
                    ->orWhere('no_polisi', 'like', "%$searchValue%")
                    ->orWhere('area', 'like', "%$searchValue%");
            });
        }

        $totalRecords = MasterImportKendaraan::count();
        $filteredRecords = $query->count();

        // Ordering
        if ($request->has('order')) {
            $columnIndex = $request->input('order.0.column');
            $columnDir = $request->input('order.0.dir');

            $cols = [
                1 => 'kode_kendaraan',
                2 => 'no_polisi',
                3 => 'jenis_kendaraan',
                4 => 'area',
                5 => 'tyre_position_configuration_id',
                6 => 'total_tyre_position',
                7 => 'tyre_unit_status'
            ];

            if (isset($cols[$columnIndex])) {
                $query->orderBy($cols[$columnIndex], $columnDir);
            }
        } else {
            $query->latest();
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $kendaraans = $query->skip($start)->take($length)->get();

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalRecords),
            "recordsFiltered" => intval($filteredRecords),
            "data" => $kendaraans
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kendaraan' => 'required|string|max:255|unique:master_import_kendaraan,kode_kendaraan',
            'no_polisi' => 'required|string|max:255',
            'jenis_kendaraan' => 'nullable|string|max:255',
            'vehicle_brand' => 'nullable|string|max:255',
            'curb_weight' => 'nullable|integer|min:0',
            'payload_capacity' => 'nullable|numeric|min:0',
            'area' => 'required|string|max:255',
            'operational_segment_id' => 'nullable|exists:tyre_segments,id',
            'tipe_kendaraan' => 'nullable|string|max:255',
            'tahun_rakit' => 'nullable|string|max:4',
            'usia_kendaraan' => 'nullable|string|max:255',
            'kapasitas_silinder' => 'nullable|string|max:255',
            'no_bpkb' => 'nullable|string|max:255',
            'no_rangka' => 'nullable|string|max:255',
            'no_mesin' => 'nullable|string|max:255',
            'total_tyre_position' => 'required|integer',
            'tyre_position_configuration_id' => 'nullable|exists:tyre_position_configurations,id',
            'tyre_unit_status' => 'required|in:Active,Inactive,Maintenance',
        ]);

        $kendaraan = MasterImportKendaraan::create($request->all());
        $kendaraan->load(['tyrePositionConfiguration', 'segment']);

        setLogActivity(auth()->id(), 'Menambah kendaraan: ' . $request->kode_kendaraan . ' (' . $request->no_polisi . ')', [
            'action_type' => 'create',
            'module' => 'Vehicle Master',
            'data_after' => [
                'Kode Unit' => $kendaraan->kode_kendaraan,
                'No Polisi' => $kendaraan->no_polisi,
                'Jenis' => $kendaraan->jenis_kendaraan,
                'Area' => $kendaraan->area,
                'Konfigurasi Ban' => $kendaraan->tyrePositionConfiguration->config_name ?? '-',
                'Total Posisi' => $kendaraan->total_tyre_position,
                'Status' => $kendaraan->tyre_unit_status,
                'Operational Segment' => $kendaraan->segment->segment_name ?? '-',
            ]
        ]);

        return redirect()->back()->with('success', 'Vehicle created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_kendaraan' => 'required|string|max:255|unique:master_import_kendaraan,kode_kendaraan,' . $id,
            'no_polisi' => 'required|string|max:255',
            'jenis_kendaraan' => 'nullable|string|max:255',
            'vehicle_brand' => 'nullable|string|max:255',
            'curb_weight' => 'nullable|integer|min:0',
            'payload_capacity' => 'nullable|numeric|min:0',
            'area' => 'required|string|max:255',
            'operational_segment_id' => 'nullable|exists:tyre_segments,id',
            'tipe_kendaraan' => 'nullable|string|max:255',
            'tahun_rakit' => 'nullable|string|max:4',
            'usia_kendaraan' => 'nullable|string|max:255',
            'kapasitas_silinder' => 'nullable|string|max:255',
            'no_bpkb' => 'nullable|string|max:255',
            'no_rangka' => 'nullable|string|max:255',
            'no_mesin' => 'nullable|string|max:255',
            'total_tyre_position' => 'required|integer',
            'tyre_position_configuration_id' => 'nullable|exists:tyre_position_configurations,id',
            'tyre_unit_status' => 'required|in:Active,Inactive,Maintenance',
        ]);

        $kendaraan = MasterImportKendaraan::findOrFail($id);
        $kendaraan->load(['tyrePositionConfiguration', 'segment']);
        
        $dataBefore = [
            'Kode Unit' => $kendaraan->kode_kendaraan,
            'No Polisi' => $kendaraan->no_polisi,
            'Area' => $kendaraan->area,
            'Konfigurasi Ban' => $kendaraan->tyrePositionConfiguration->config_name ?? '-',
            'Status' => $kendaraan->tyre_unit_status,
        ];

        $kendaraan->update($request->all());
        $kendaraan->load(['tyrePositionConfiguration', 'segment']); // Reload for updated data

        setLogActivity(auth()->id(), 'Memperbarui kendaraan: ' . $request->kode_kendaraan, [
            'action_type' => 'update',
            'module' => 'Vehicle Master',
            'data_before' => $dataBefore,
            'data_after' => [
                'Kode Unit' => $kendaraan->kode_kendaraan,
                'No Polisi' => $kendaraan->no_polisi,
                'Area' => $kendaraan->area,
                'Konfigurasi Ban' => $kendaraan->tyrePositionConfiguration->config_name ?? '-',
                'Status' => $kendaraan->tyre_unit_status,
            ]
        ]);

        return redirect()->back()->with('success', 'Vehicle updated successfully');
    }

    public function destroy($id)
    {
        $kendaraan = MasterImportKendaraan::findOrFail($id);

        if ($kendaraan->tyres()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete vehicle. It is currently associated with some tyre records.');
        }

        setLogActivity(auth()->id(), 'Menghapus kendaraan: ' . $kendaraan->kode_kendaraan, [
            'action_type' => 'delete',
            'module' => 'Vehicle Master',
            'data_before' => $kendaraan->toArray()
        ]);

        $kendaraan->delete();

        return redirect()->back()->with('success', 'Vehicle deleted successfully');
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
                $vehicle = MasterImportKendaraan::find($id);
                if ($vehicle) {
                    if ($vehicle->tyres()->exists()) {
                        $skippedCount++;
                    } else {
                        $vehicle->delete();
                        $deletedCount++;
                    }
                }
            }

            setLogActivity(auth()->id(), "Bulk delete unit: $deletedCount berhasil, $skippedCount dilewati (ada ban terpasang)", [
                'action_type' => 'delete',
                'module' => 'Vehicle Master',
                'ids' => $ids
            ]);

            $msg = "$deletedCount data unit berhasil dihapus.";
            if ($skippedCount > 0) {
                $msg .= " $skippedCount data dilewati karena masih memiliki ban terpasang.";
            }

            return redirect()->back()->with($skippedCount > 0 ? 'warning' : 'success', $msg);
        }

        if ($action === 'update') {
            $data = [];
            if ($request->filled('tyre_unit_status')) $data['tyre_unit_status'] = $request->tyre_unit_status;
            if ($request->filled('area')) $data['area'] = $request->area;
            if ($request->filled('operational_segment_id')) $data['operational_segment_id'] = $request->operational_segment_id;

            if (empty($data)) {
                return redirect()->back()->with('error', 'Tidak ada field yang dipilih untuk diperbarui.');
            }

            MasterImportKendaraan::whereIn('id', $ids)->update($data);

            setLogActivity(auth()->id(), "Bulk update unit untuk " . count($ids) . " data", [
                'action_type' => 'update',
                'module' => 'Vehicle Master',
                'ids' => $ids,
                'updated_fields' => $data
            ]);

            return redirect()->back()->with('success', count($ids) . ' data unit berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Aksi tidak dikenal.');
    }
}
