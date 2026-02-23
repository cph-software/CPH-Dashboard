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
                0 => 'kode_kendaraan',
                1 => 'no_polisi',
                2 => 'jenis_kendaraan',
                3 => 'area',
                4 => 'tyre_position_configuration_id',
                5 => 'total_tyre_position',
                6 => 'tyre_unit_status'
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

        MasterImportKendaraan::create($request->all());

        setLogActivity(auth()->id(), 'Menambah kendaraan: ' . $request->kode_kendaraan . ' (' . $request->no_polisi . ')', [
            'action_type' => 'create',
            'module' => 'Vehicle Master',
            'data_after' => $request->all()
        ]);

        return redirect()->back()->with('success', 'Vehicle created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_kendaraan' => 'required|string|max:255|unique:master_import_kendaraan,kode_kendaraan,' . $id,
            'no_polisi' => 'required|string|max:255',
            'jenis_kendaraan' => 'nullable|string|max:255',
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
        $dataBefore = $kendaraan->toArray();
        $kendaraan->update($request->all());

        setLogActivity(auth()->id(), 'Memperbarui kendaraan: ' . $request->kode_kendaraan, [
            'action_type' => 'update',
            'module' => 'Vehicle Master',
            'data_before' => $dataBefore,
            'data_after' => $request->all()
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
}
