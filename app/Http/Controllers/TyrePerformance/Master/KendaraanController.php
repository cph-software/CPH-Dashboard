<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterImportKendaraan;
use App\Models\TyrePositionConfiguration;
use Illuminate\Http\Request;

class KendaraanController extends Controller
{
    public function index()
    {
        $kendaraans = MasterImportKendaraan::with('tyrePositionConfiguration')->latest()->paginate(10);
        $configurations = TyrePositionConfiguration::where('status', 'Active')->get();
        return view('tyre-performance.master.kendaraan.index', compact('kendaraans', 'configurations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kendaraan' => 'required|string|max:255|unique:master_import_kendaraan,kode_kendaraan',
            'no_polisi' => 'required|string|max:255',
            'jenis_kendaraan' => 'nullable|string|max:255',
            'area' => 'required|string|max:255',
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

        return redirect()->back()->with('success', 'Vehicle created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_kendaraan' => 'required|string|max:255|unique:master_import_kendaraan,kode_kendaraan,' . $id,
            'no_polisi' => 'required|string|max:255',
            'jenis_kendaraan' => 'nullable|string|max:255',
            'area' => 'required|string|max:255',
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
        $kendaraan->update($request->all());

        return redirect()->back()->with('success', 'Vehicle updated successfully');
    }

    public function destroy($id)
    {
        $kendaraan = MasterImportKendaraan::findOrFail($id);
        $kendaraan->delete();

        return redirect()->back()->with('success', 'Vehicle deleted successfully');
    }
}
