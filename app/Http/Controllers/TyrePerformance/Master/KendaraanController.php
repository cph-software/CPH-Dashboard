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
    /**
     * Data for Server-Side DataTables
     */
    public function data(Request $request)
    {
        $query = MasterImportKendaraan::with(['tyrePositionConfiguration', 'segment', 'company'])
            ->withCount('tyres');

        // Search logic
        if ($request->has('search') && $request->input('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('kode_kendaraan', 'like', "%$searchValue%")
                    ->orWhere('jenis_kendaraan', 'like', "%$searchValue%")
                    ->orWhere('no_polisi', 'like', "%$searchValue%")
                    ->orWhereHas('company', function ($sub) use ($searchValue) {
                        $sub->where('company_name', 'like', "%$searchValue%");
                    })
                    ->orWhere('area', 'like', "%$searchValue%");
            });
        }

        $totalRecords = MasterImportKendaraan::count();
        $filteredRecords = $query->count();

        // Ordering
        if ($request->has('order')) {
            $columnIndex = $request->input('order.0.column');
            $columnDir = $request->input('order.0.dir');

            $isAdmin = (auth()->check() && auth()->user()->role_id == 1);

            if ($isAdmin) {
                $cols = [
                    1 => 'kode_kendaraan',
                    2 => 'tyre_company_id',
                    3 => 'no_polisi',
                    4 => 'jenis_kendaraan',
                    5 => 'area',
                    6 => 'operational_segment_id',
                    7 => 'tyre_position_configuration_id',
                    8 => 'total_tyre_position',
                    9 => 'measurement_unit',
                    10 => 'tyre_unit_status'
                ];
            } else {
                $cols = [
                    1 => 'kode_kendaraan',
                    2 => 'no_polisi',
                    3 => 'jenis_kendaraan',
                    4 => 'area',
                    5 => 'operational_segment_id',
                    6 => 'tyre_position_configuration_id',
                    7 => 'total_tyre_position',
                    8 => 'measurement_unit',
                    9 => 'tyre_unit_status'
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
        $kendaraans = $query->skip($start)->take($length)->get();

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalRecords),
            "recordsFiltered" => intval($filteredRecords),
            "data" => $kendaraans
        ]);
    }
    public function index()
    {
        $user = auth()->user();
        $activeCompanyId = \App\Helpers\SessionCompanyHelper::getActiveCompanyId();

        $locQuery = TyreLocation::query();
        $segQuery = TyreSegment::with('location')->where('status', 'Active');

        if ($user->role_id != 1 && $activeCompanyId && !is_array($activeCompanyId)) {
            $locQuery->where(function($q) use ($activeCompanyId) {
                $q->whereNull('tyre_company_id')->orWhere('tyre_company_id', $activeCompanyId);
            });
            $segQuery->where(function($q) use ($activeCompanyId) {
                $q->whereNull('tyre_company_id')->orWhere('tyre_company_id', $activeCompanyId);
            });
        }
        $locations = $locQuery->get();
        $segments = $segQuery->get();
        $configurations = TyrePositionConfiguration::where('status', 'Active')->get();
        $companies = \App\Helpers\SessionCompanyHelper::getAccessibleCompanies();

        return view('tyre-performance.master.kendaraan.index', compact('configurations', 'locations', 'segments', 'companies'));
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

        // Integrated Monitoring Data — Match by no_polisi to TyreMonitoringVehicle
        $monitoringVehicle = \App\Models\TyreMonitoringVehicle::where('vehicle_number', $kendaraan->no_polisi)
            ->with(['sessions' => function($q) {
                $q->orderBy('install_date', 'desc');
            }])
            ->first();

        return view('tyre-performance.master.kendaraan.show', compact(
            'kendaraan',
            'movements',
            'installedCount',
            'totalPositions',
            'removalCount',
            'installCount',
            'monitoringVehicle'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kendaraan' => 'nullable|string|max:255',
            'no_polisi' => 'required|string|max:255',
            'vehicle_brand' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'tyre_position_configuration_id' => 'required|exists:tyre_position_configurations,id',
            'total_tyre_position' => 'nullable|integer',
            'measurement_unit' => 'required|in:KM,HM',
            'tyre_unit_status' => 'required|in:Active,Inactive,Maintenance',
            'tyre_company_id' => 'nullable',
            'jenis_kendaraan' => 'nullable|string|max:255',
            'curb_weight' => 'nullable|integer|min:0',
            'payload_capacity' => 'nullable|numeric|min:0',
            'operational_segment_id' => 'nullable',
            'tipe_kendaraan' => 'nullable|string|max:255',
        ]);

        $data = $request->all();
        $user = auth()->user();

        // Determine target company ID
        $activeCompanyId = \App\Helpers\SessionCompanyHelper::getActiveCompanyId();
        $targetCompanyId = null;

        if ($user->role_id == 1) {
            $targetCompanyId = $data['tyre_company_id'] ?? (!is_array($activeCompanyId) && $activeCompanyId ? $activeCompanyId : 1);
        } elseif (\App\Helpers\SessionCompanyHelper::isWorkshopAdmin()) {
            if (!empty($data['tyre_company_id']) && \App\Helpers\SessionCompanyHelper::isValidClient($data['tyre_company_id'])) {
                $targetCompanyId = $data['tyre_company_id'];
            } elseif (!is_array($activeCompanyId) && $activeCompanyId) {
                $targetCompanyId = $activeCompanyId;
            } else {
                $targetCompanyId = $user->tyre_company_id;
            }
        } else {
            $targetCompanyId = $user->tyre_company_id;
        }
        $data['tyre_company_id'] = $targetCompanyId;

        // Auto-generate unit code if empty or check uniqueness if provided
        if (empty($data['kode_kendaraan'])) {
            $cleanNoPol = strtoupper(str_replace([' ', '-', '.'], '', $request->no_polisi));
            $candidateCode = 'UNIT-' . $cleanNoPol;
            $exists = MasterImportKendaraan::withoutGlobalScopes()->where('kode_kendaraan', $candidateCode)->exists();
            $data['kode_kendaraan'] = $exists ? $candidateCode . '-' . rand(100, 999) : $candidateCode;
        } else {
            $cleanInputCode = trim($data['kode_kendaraan']);
            $exists = MasterImportKendaraan::withoutGlobalScopes()->where('kode_kendaraan', $cleanInputCode)->exists();
            if ($exists) {
                return redirect()->back()->withInput()->with('error', 'Kode unit "' . $cleanInputCode . '" sudah digunakan. Silakan gunakan kode lain atau kosongkan agar dibuat otomatis.');
            }
            $data['kode_kendaraan'] = $cleanInputCode;
        }

        // Auto-fill total_tyre_position from configuration if empty
        if (empty($data['total_tyre_position']) && !empty($data['tyre_position_configuration_id'])) {
            $config = TyrePositionConfiguration::find($data['tyre_position_configuration_id']);
            $data['total_tyre_position'] = $config ? $config->total_positions : 10;
        }

        // 1. Resolve Location / Area (create if new string)
        $locationId = null;
        if (!empty($data['area']) && $targetCompanyId && !is_array($targetCompanyId)) {
            $locName = trim($data['area']);
            $location = TyreLocation::firstOrCreate(
                [
                    'location_name' => $locName,
                    'tyre_company_id' => $targetCompanyId
                ],
                [
                    'location_type' => 'Warehouse',
                    'capacity' => 100,
                    'current_stock' => 0,
                ]
            );
            $locationId = $location->id;
            $data['area'] = $locName;
        }

        // 2. Resolve Segment (create if new string)
        if (!empty($data['operational_segment_id']) && $targetCompanyId && !is_array($targetCompanyId)) {
            $seg = is_numeric($data['operational_segment_id']) ? TyreSegment::find($data['operational_segment_id']) : null;
            if (!$seg) {
                $segName = trim($data['operational_segment_id']);
                $seg = TyreSegment::firstOrCreate(
                    [
                        'segment_name' => $segName,
                        'tyre_company_id' => $targetCompanyId
                    ],
                    [
                        'segment_id' => 'SEG-' . strtoupper(\Illuminate\Support\Str::slug(substr($segName, 0, 5))) . '-' . rand(100, 999),
                        'tyre_location_id' => $locationId,
                        'terrain_type' => 'Standard',
                        'status' => 'Active'
                    ]
                );
            }
            $data['operational_segment_id'] = $seg->id;
        }

        $data['jenis_kendaraan'] = $data['jenis_kendaraan'] ?? 'Dump Truck';
        $data['vehicle_brand'] = $data['vehicle_brand'] ?? '-';
        $data['tipe_kendaraan'] = $data['tipe_kendaraan'] ?? '-';
        $data['curb_weight'] = $data['curb_weight'] ?? 0;
        $data['payload_capacity'] = $data['payload_capacity'] ?? 0;

        $kendaraan = MasterImportKendaraan::create($data);
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
                'Satuan' => $kendaraan->measurement_unit,
            ]
        ]);

        return redirect()->back()->with('success', 'Vehicle created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_kendaraan' => 'nullable|string|max:255',
            'no_polisi' => 'required|string|max:255',
            'vehicle_brand' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'tyre_position_configuration_id' => 'required|exists:tyre_position_configurations,id',
            'total_tyre_position' => 'nullable|integer',
            'measurement_unit' => 'required|in:KM,HM',
            'tyre_unit_status' => 'required|in:Active,Inactive,Maintenance',
            'tyre_company_id' => 'nullable',
            'jenis_kendaraan' => 'nullable|string|max:255',
            'curb_weight' => 'nullable|integer|min:0',
            'payload_capacity' => 'nullable|numeric|min:0',
            'operational_segment_id' => 'nullable',
            'tipe_kendaraan' => 'nullable|string|max:255',
        ]);

        $kendaraan = MasterImportKendaraan::findOrFail($id);
        $data = $request->all();
        $user = auth()->user();

        // Determine target company ID
        $activeCompanyId = \App\Helpers\SessionCompanyHelper::getActiveCompanyId();
        $targetCompanyId = null;

        if ($user->role_id == 1) {
            $targetCompanyId = $data['tyre_company_id'] ?? $kendaraan->tyre_company_id ?? (!is_array($activeCompanyId) && $activeCompanyId ? $activeCompanyId : 1);
        } elseif (\App\Helpers\SessionCompanyHelper::isWorkshopAdmin()) {
            if (!empty($data['tyre_company_id']) && \App\Helpers\SessionCompanyHelper::isValidClient($data['tyre_company_id'])) {
                $targetCompanyId = $data['tyre_company_id'];
            } else {
                $targetCompanyId = $kendaraan->tyre_company_id ?? (!is_array($activeCompanyId) && $activeCompanyId ? $activeCompanyId : $user->tyre_company_id);
            }
        } else {
            $targetCompanyId = $kendaraan->tyre_company_id ?? $user->tyre_company_id;
        }
        $data['tyre_company_id'] = $targetCompanyId;

        // Auto-generate unit code if empty or check uniqueness if provided
        if (empty($data['kode_kendaraan'])) {
            $cleanNoPol = strtoupper(str_replace([' ', '-', '.'], '', $request->no_polisi));
            $candidateCode = 'UNIT-' . $cleanNoPol;
            $exists = MasterImportKendaraan::withoutGlobalScopes()->where('kode_kendaraan', $candidateCode)->where('id', '!=', $id)->exists();
            $data['kode_kendaraan'] = $exists ? $candidateCode . '-' . rand(100, 999) : $candidateCode;
        } else {
            $cleanInputCode = trim($data['kode_kendaraan']);
            $exists = MasterImportKendaraan::withoutGlobalScopes()->where('kode_kendaraan', $cleanInputCode)->where('id', '!=', $id)->exists();
            if ($exists) {
                return redirect()->back()->withInput()->with('error', 'Kode unit "' . $cleanInputCode . '" sudah digunakan oleh kendaraan lain.');
            }
            $data['kode_kendaraan'] = $cleanInputCode;
        }

        // Auto-fill total_tyre_position from configuration if empty
        if (empty($data['total_tyre_position']) && !empty($data['tyre_position_configuration_id'])) {
            $config = TyrePositionConfiguration::find($data['tyre_position_configuration_id']);
            $data['total_tyre_position'] = $config ? $config->total_positions : 10;
        }

        // 1. Resolve Location / Area (create if new string)
        $locationId = null;
        if (!empty($data['area']) && $targetCompanyId && !is_array($targetCompanyId)) {
            $locName = trim($data['area']);
            $location = TyreLocation::firstOrCreate(
                [
                    'location_name' => $locName,
                    'tyre_company_id' => $targetCompanyId
                ],
                [
                    'location_type' => 'Warehouse',
                    'capacity' => 100,
                    'current_stock' => 0,
                ]
            );
            $locationId = $location->id;
            $data['area'] = $locName;
        }

        // 2. Resolve Segment (create if new string)
        if (!empty($data['operational_segment_id']) && $targetCompanyId && !is_array($targetCompanyId)) {
            $seg = is_numeric($data['operational_segment_id']) ? TyreSegment::find($data['operational_segment_id']) : null;
            if (!$seg) {
                $segName = trim($data['operational_segment_id']);
                $seg = TyreSegment::firstOrCreate(
                    [
                        'segment_name' => $segName,
                        'tyre_company_id' => $targetCompanyId
                    ],
                    [
                        'segment_id' => 'SEG-' . strtoupper(\Illuminate\Support\Str::slug(substr($segName, 0, 5))) . '-' . rand(100, 999),
                        'tyre_location_id' => $locationId,
                        'terrain_type' => 'Standard',
                        'status' => 'Active'
                    ]
                );
            }
            $data['operational_segment_id'] = $seg->id;
        }

        $dataBefore = [
            'Kode Unit' => $kendaraan->kode_kendaraan,
            'No Polisi' => $kendaraan->no_polisi,
            'Area' => $kendaraan->area,
            'Konfigurasi Ban' => $kendaraan->tyrePositionConfiguration->config_name ?? '-',
            'Status' => $kendaraan->tyre_unit_status,
            'Satuan' => $kendaraan->measurement_unit,
        ];

        $kendaraan->update($data);
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
                'Satuan' => $kendaraan->measurement_unit,
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

        $kendaraan->update(['deleted_by' => auth()->id()]);
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
