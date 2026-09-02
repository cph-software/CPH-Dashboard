<?php

namespace App\Http\Controllers\TyrePerformance\Movement;

use App\Http\Controllers\Controller;
use App\Models\Tyre;
use App\Models\MasterImportKendaraan;
use App\Models\TyrePositionConfiguration;
use App\Models\TyrePositionDetail;
use App\Models\TyreFailureCode;
use App\Models\TyreSegment;
use App\Models\TyreMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\TyreExamination;
use App\Models\TyreCompany;
use App\Services\VehicleReadingService;

class TyreMovementController extends Controller
{
    public function setActiveCompany(Request $request)
    {
        $companyId = $request->input('tyre_company_id');
        $user = auth()->user();
        
        if ($companyId == 0 || $companyId === '0' || $companyId === 'ALL_CLIENTS') {
            if (\App\Helpers\SessionCompanyHelper::isWorkshopAdmin()) {
                $clientIds = $user->tyreCompany ? $user->tyreCompany->getAllClientIds() : [];
                if ($user->tyre_company_id) {
                    $clientIds[] = $user->tyre_company_id;
                }
                session(['active_company_id' => $clientIds]);
                return response()->json(['success' => true, 'message' => 'Global Klien (Agregat) Aktif']);
            } else {
                session()->forget('active_company_id');
                return response()->json(['success' => true, 'message' => 'Filter perusahaan dibersihkan (Global View)']);
            }
        }
        
        // Cek validitas akses untuk Workshop Admin
        if (!\App\Helpers\SessionCompanyHelper::isSuperAdmin() && !\App\Helpers\SessionCompanyHelper::isValidClient($companyId)) {
            return response()->json(['success' => false, 'message' => 'Akses ke perusahaan ini ditolak.'], 403);
        }

        $company = \App\Models\TyreCompany::findOrFail($companyId);
        session(['active_company_id' => $company->id]);
        
        return response()->json(['success' => true, 'message' => 'Filter aktif: ' . $company->company_name]);
    }

    protected function applyCompanyScope($query)
    {
        $resolvedId = \App\Helpers\SessionCompanyHelper::getActiveCompanyId();
        if ($resolvedId !== null) {
            if (is_array($resolvedId)) {
                return $query->whereIn('tyre_company_id', $resolvedId);
            }
            return $query->where('tyre_company_id', $resolvedId);
        }
        return $query;
    }

    public function index()
    {
        $query = MasterImportKendaraan::select('id', 'kode_kendaraan', 'no_polisi', 'total_tyre_position')
            ->whereNotNull('tyre_position_configuration_id')
            ->where('tyre_unit_status', 'Active')
            ->withCount(['tyres' => function ($query) {
                $query->whereNotNull('current_position_id');
            }]);
            
        $kendaraans = $this->applyCompanyScope($query)->get();
        return view('tyre-performance.movement.index', compact('kendaraans'));
    }

    protected function getLocationsAndSegments()
    {
        $locationsQuery = \App\Models\TyreLocation::query();
        $segmentsQuery = \App\Models\TyreSegment::where('status', 'Active');

        if (\App\Helpers\SessionCompanyHelper::isWorkshopAdmin() && !\App\Helpers\SessionCompanyHelper::isSuperAdmin()) {
            $activeCompany = \App\Helpers\SessionCompanyHelper::getActiveCompanyId();
            $parentCompany = auth()->user()->tyre_company_id;

            $locationsQuery->withoutGlobalScope('company')->where(function($q) use ($activeCompany, $parentCompany) {
                if (is_array($activeCompany)) {
                    $q->whereIn('tyre_company_id', $activeCompany);
                } elseif ($activeCompany) {
                    $q->where('tyre_company_id', $activeCompany);
                }
                if ($parentCompany && $parentCompany != $activeCompany) {
                    $q->orWhere('tyre_company_id', $parentCompany);
                }
            });

            $segmentsQuery->withoutGlobalScope('company')->where(function($q) use ($activeCompany, $parentCompany) {
                if (is_array($activeCompany)) {
                    $q->whereIn('tyre_company_id', $activeCompany);
                } elseif ($activeCompany) {
                    $q->where('tyre_company_id', $activeCompany);
                }
                if ($parentCompany && $parentCompany != $activeCompany) {
                    $q->orWhere('tyre_company_id', $parentCompany);
                }
            });
        } else {
            $locationsQuery = $this->applyCompanyScope($locationsQuery);
            $segmentsQuery = $this->applyCompanyScope($segmentsQuery);
        }

        return [
            'locations' => $locationsQuery->get(),
            'segments' => $segmentsQuery->get()
        ];
    }

    public function pemasangan()
    {
        $query = MasterImportKendaraan::select('id', 'kode_kendaraan', 'no_polisi', 'total_tyre_position')
            ->whereNotNull('tyre_position_configuration_id')
            ->where('tyre_unit_status', 'Active')
            ->withCount(['tyres' => function ($query) {
                $query->whereNotNull('current_position_id');
            }]);
            
        $kendaraans = $this->applyCompanyScope($query)->get();
        $availableTyres = collect();
        
        $dropdownData = $this->getLocationsAndSegments();
        $locations = $dropdownData['locations'];
        $segments = $dropdownData['segments'];
        
        return view('tyre-performance.movement.pemasangan', compact('kendaraans', 'availableTyres', 'segments', 'locations'));
    }

    public function searchTyres(Request $request)
    {
        $search = $request->input('q');

        $query = Tyre::whereIn('status', ['New', 'Repaired'])
            ->where('is_in_warehouse', true)
            ->where('is_repairing', false);
            
        // Cross-Company Install Logic: Active Company + Parent/Workshop Company Stock
        $activeCompany = \App\Helpers\SessionCompanyHelper::getActiveCompanyId();
        $userCompany = auth()->user()->tyre_company_id;
        
        $relatedCompanyIds = [];
        if ($userCompany) {
            $relatedCompanyIds[] = $userCompany;
        }
        if ($activeCompany && !is_array($activeCompany)) {
            $relatedCompanyIds[] = $activeCompany;
            $comp = \App\Models\TyreCompany::find($activeCompany);
            if ($comp && $comp->parent_company_id) {
                $relatedCompanyIds[] = $comp->parent_company_id;
            }
        } elseif (is_array($activeCompany)) {
            $relatedCompanyIds = array_merge($relatedCompanyIds, $activeCompany);
        }
        $relatedCompanyIds = array_unique(array_filter($relatedCompanyIds));

        if (!empty($relatedCompanyIds) && !\App\Helpers\SessionCompanyHelper::isSuperAdmin()) {
            $query->withoutGlobalScope('company')->whereIn('tyre_company_id', $relatedCompanyIds);
        } else {
            $query = $this->applyCompanyScope($query);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('serial_number', 'like', "%$search%")
                  ->orWhereHas('brand', function($qBrand) use ($search) {
                      $qBrand->where('brand_name', 'like', "%$search%");
                  })
                  ->orWhereHas('size', function($qSize) use ($search) {
                      $qSize->where('size', 'like', "%$search%");
                  })
                  ->orWhereHas('pattern', function($qPattern) use ($search) {
                      $qPattern->where('name', 'like', "%$search%");
                  });
            });
        }

        $tyres = $query->with(['brand', 'size', 'pattern', 'company', 'latestInstallation'])
            ->limit(30)
            ->get();

        $results = $tyres->map(function ($tyre) {
            $companyLabel = $tyre->company ? ' [' . $tyre->company->company_name . ']' : '';
            return [
                'id' => $tyre->id,
                'text' => $tyre->serial_number . $companyLabel . ' (' . ($tyre->brand->brand_name ?? '-') . ' - ' . ($tyre->size->size ?? '-') . ')',
                'company_name' => $tyre->company->company_name ?? '-',
                'brand' => $tyre->brand->brand_name ?? '-',
                'pattern' => $tyre->pattern->name ?? '-',
                'size' => $tyre->size->size ?? '-',
                'sn' => $tyre->serial_number,
                'otd' => $tyre->initial_tread_depth,
                'rtd' => $tyre->current_tread_depth,
                'location_id' => $tyre->current_location_id,
                'status' => $tyre->status,
                'latest_rim_size' => $tyre->latestInstallation->rim_size ?? null,
                'latest_segment_id' => $tyre->latestInstallation->operational_segment_id ?? null,
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function pelepasan()
    {
        $query = MasterImportKendaraan::select('id', 'kode_kendaraan', 'no_polisi', 'total_tyre_position')
            ->whereNotNull('tyre_position_configuration_id')
            ->where('tyre_unit_status', 'Active')
            ->whereHas('tyres', function ($query) {
                $query->whereNotNull('current_position_id');
            })
            ->withCount(['tyres' => function ($query) {
                $query->whereNotNull('current_position_id');
            }]);
            
        $kendaraans = $this->applyCompanyScope($query)->get();

        $activeCompanyId = \App\Helpers\SessionCompanyHelper::getActiveCompanyId();
        $failureCodes = TyreFailureCode::where('status', 'Active')
            ->where(function ($query) use ($activeCompanyId) {
                $query->whereNull('tyre_company_id');
                if ($activeCompanyId) {
                    if (is_array($activeCompanyId)) {
                        $query->orWhereIn('tyre_company_id', $activeCompanyId);
                    } else {
                        $query->orWhere('tyre_company_id', $activeCompanyId);
                    }
                }
            })
            ->get();
        
        $dropdownData = $this->getLocationsAndSegments();
        $locations = $dropdownData['locations'];
        $segments = $dropdownData['segments'];
        
        return view('tyre-performance.movement.pelepasan', compact('kendaraans', 'failureCodes', 'segments', 'locations'));
    }

    public function rotasi()
    {
        $query = MasterImportKendaraan::select('id', 'kode_kendaraan', 'no_polisi', 'total_tyre_position')
            ->whereNotNull('tyre_position_configuration_id')
            ->where('tyre_unit_status', 'Active')
            ->whereHas('tyres', function ($query) {
                $query->whereNotNull('current_position_id');
            })
            ->withCount(['tyres' => function ($query) {
                $query->whereNotNull('current_position_id');
            }]);
            
        $kendaraans = $this->applyCompanyScope($query)->get();
        
        $dropdownData = $this->getLocationsAndSegments();
        $locations = $dropdownData['locations'];
        $segments = $dropdownData['segments'];
        
        return view('tyre-performance.movement.rotasi', compact('kendaraans', 'segments', 'locations'));
    }

    public function getSegmentsByLocation($locationId)
    {
        $segments = TyreSegment::where('tyre_location_id', $locationId)
            ->where('status', 'Active')
            ->get();
        return response()->json($segments);
    }

    public function getVehicleLayout($id)
    {
        $vehicle = MasterImportKendaraan::with('tyrePositionConfiguration.details')->findOrFail($id);

        // Fetch all tyres currently on this vehicle
        $assignedTyres = Tyre::where('current_vehicle_id', $id)
            ->whereNotNull('current_position_id')
            ->with(['brand', 'size', 'pattern'])
            ->get()
            ->keyBy('current_position_id');

        return view('tyre-performance.movement._vehicle_layout', [
            'vehicle' => $vehicle,
            'configuration' => $vehicle->tyrePositionConfiguration,
            'assignedTyres' => $assignedTyres
        ]);
    }

    public function getVehicleDetail($id)
    {
        $vehicle = MasterImportKendaraan::with(['segment', 'company:id,measurement_mode'])->findOrFail($id);

        // Fetch latest readings via centralized Service
        $readings = VehicleReadingService::getLastVehicleReadings($id);

        return response()->json([
            'vehicle' => $vehicle,
            'last_odometer' => $readings['odometer'],
            'last_hour_meter' => $readings['hour_meter']
        ]);
    }

    public function getPositionInfo(Request $request)
    {
        $vehicleId = $request->vehicle_id;
        // Check if we need available tyres specifically for a position
        // The frontend sends vehicle_id and position_id for the quick form

        if ($request->has('position_id')) {
            // For Quick Form Installation: We need list of available tyres
            $query = Tyre::whereIn('status', ['New', 'Repaired'])
                    ->where('is_in_warehouse', true)
                    ->where('is_repairing', false);
                    
            $activeCompany = \App\Helpers\SessionCompanyHelper::getActiveCompanyId();
            $userCompany = auth()->user()->tyre_company_id;
            
            $relatedCompanyIds = [];
            if ($userCompany) {
                $relatedCompanyIds[] = $userCompany;
            }
            if ($activeCompany && !is_array($activeCompany)) {
                $relatedCompanyIds[] = $activeCompany;
                $comp = \App\Models\TyreCompany::find($activeCompany);
                if ($comp && $comp->parent_company_id) {
                    $relatedCompanyIds[] = $comp->parent_company_id;
                }
            } elseif (is_array($activeCompany)) {
                $relatedCompanyIds = array_merge($relatedCompanyIds, $activeCompany);
            }
            $relatedCompanyIds = array_unique(array_filter($relatedCompanyIds));

            if (!empty($relatedCompanyIds) && !\App\Helpers\SessionCompanyHelper::isSuperAdmin()) {
                $query->withoutGlobalScope('company')->whereIn('tyre_company_id', $relatedCompanyIds);
            } else {
                $query = $this->applyCompanyScope($query);
            }
            
            $availableTyres = $query->with(['brand', 'size', 'company'])->limit(50)->get();

            return response()->json([
                'availableTyres' => $availableTyres
            ]);
        }

        // ... (rest of existing logic for full page forms if needed, but the quick form uses the above)
        $type = $request->type ?? 'Installation';

        $vehicle = MasterImportKendaraan::select('id', 'tyre_position_configuration_id')->findOrFail($vehicleId);
        $configId = $vehicle->tyre_position_configuration_id;

        if ($type === 'Installation') {
            $positions = TyrePositionDetail::where('configuration_id', $configId)
                ->get();
        } else {
            $tyresOnVehicle = Tyre::where('current_vehicle_id', $vehicleId)
                ->whereNotNull('current_position_id')
                ->with(['brand:id,brand_name', 'size:id,size', 'pattern:id,name', 'latestInstallation'])
                ->get();

            $positionIds = $tyresOnVehicle->pluck('current_position_id');
            $positions = TyrePositionDetail::whereIn('id', $positionIds)->get();

            return response()->json([
                'positions' => $positions,
                'assignedTyres' => $tyresOnVehicle->keyBy('current_position_id')
            ]);
        }

        return response()->json([
            'positions' => $positions
        ]);
    }

    public function getWarehouseStock(Request $request)
    {
        $query = Tyre::with(['brand:id,brand_name', 'size:id,size', 'pattern:id,name'])
            ->whereIn('status', ['New', 'Repaired'])
            ->where('is_in_warehouse', true)
            ->where('is_repairing', false)
            ->whereNull('current_vehicle_id');
            
        $query = $this->applyCompanyScope($query);
            
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('serial_number', 'like', '%' . $search . '%')
                  ->orWhereHas('brand', function($qBrand) use ($search) {
                      $qBrand->where('brand_name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('size', function($qSize) use ($search) {
                      $qSize->where('size', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('pattern', function($qPattern) use ($search) {
                      $qPattern->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $tyres = $query->limit(100)->get();

        return response()->json([
            'success' => true,
            'data' => $tyres
        ]);
    }

    /**
     * API: Get Tyre Detail for preview modal on Movement History page
     */
    public function tyreDetail(Request $request)
    {
        $tyreId = $request->tyre_id;
        $positionId = $request->position_id;
        $vehicleId = $request->vehicle_id;

        // Find tyre either by ID or by position+vehicle
        if ($tyreId) {
            $tyre = Tyre::with(['brand', 'size', 'pattern', 'location'])->find($tyreId);
        } elseif ($positionId && $vehicleId) {
            $tyre = Tyre::with(['brand', 'size', 'pattern', 'location'])
                ->where('current_vehicle_id', $vehicleId)
                ->where('current_position_id', $positionId)
                ->first();
        }

        if (!$tyre) {
            return response()->json(['success' => false, 'message' => 'Ban tidak ditemukan di posisi ini.'], 404);
        }

        // Company scope validation
        $user = auth()->user();
        $isInternal = ($user && ($user->role_id == 1 || $user->tyre_company_id == 1));
        if (!$isInternal && $user && $tyre->tyre_company_id != $user->tyre_company_id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Ban bukan milik perusahaan Anda.'], 403);
        }

        // Get movement history (last 10)
        $movements = TyreMovement::where('tyre_id', $tyre->id)
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($m) {
                $typeLabels = [
                    'Installation' => 'Pasang',
                    'Removal' => 'Lepas',
                    'Rotation' => 'Rotasi',
                    'Inspection' => 'Inspeksi',
                ];
                return [
                    'id' => $m->id,
                    'date' => Carbon::parse($m->movement_date)->format('d/m/Y'),
                    'type' => $typeLabels[$m->movement_type] ?? $m->movement_type,
                    'type_raw' => $m->movement_type,
                    'odo' => $m->odometer_reading,
                    'hm' => $m->hour_meter_reading,
                    'running_km' => $m->running_km ?? 0,
                    'running_hm' => $m->running_hm ?? 0,
                    'rtd' => $m->rtd_reading,
                    'psi' => $m->psi_reading,
                    'notes' => $m->notes,
                ];
            });

        // Get installation info
        $installMov = TyreMovement::where('tyre_id', $tyre->id)
            ->where('movement_type', 'Installation')
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $installDate = $installMov ? Carbon::parse($installMov->movement_date)->format('d/m/Y') : null;
        $installOdo = $installMov ? $installMov->odometer_reading : null;
        $installHm = $installMov ? $installMov->hour_meter_reading : null;

        // Days since installation
        $daysSinceInstall = $installMov
            ? Carbon::parse($installMov->movement_date)->diffInDays(Carbon::now())
            : null;

        // Total movements count
        $totalMovements = TyreMovement::where('tyre_id', $tyre->id)->count();

        // RTD wear percentage
        $rtdWearPct = null;
        if ($tyre->initial_tread_depth && $tyre->initial_tread_depth > 0 && $tyre->current_tread_depth !== null) {
            $rtdWearPct = round((1 - ($tyre->current_tread_depth / $tyre->initial_tread_depth)) * 100, 1);
        }

        $measurementMode = 'BOTH';
        if (auth()->check() && auth()->user()->tyreCompany) {
            $measurementMode = auth()->user()->tyreCompany->measurement_mode ?? 'BOTH';
        }

        return response()->json([
            'success' => true,
            'measurement_mode' => $measurementMode,
            'tyre' => [
                'id' => $tyre->id,
                'serial_number' => $tyre->serial_number,
                'status' => $tyre->status,
                'brand' => $tyre->brand->brand_name ?? '-',
                'size' => $tyre->size->size ?? '-',
                'pattern' => $tyre->pattern->name ?? '-',
                'segment' => $tyre->segment_name ?? '-',
                'location' => $tyre->location->location_name ?? '-',
                'price' => $tyre->price,
                'initial_rtd' => $tyre->initial_tread_depth,
                'current_rtd' => $tyre->current_tread_depth,
                'rtd_wear_pct' => $rtdWearPct,
                'retread_count' => $tyre->retread_count ?? 0,
                'total_lifetime_km' => $tyre->total_lifetime_km ?? 0,
                'total_lifetime_hm' => $tyre->total_lifetime_hm ?? 0,
                'install_date' => $installDate,
                'install_odo' => $installOdo,
                'install_hm' => $installHm,
                'days_since_install' => $daysSinceInstall,
                'total_movements' => $totalMovements,
            ],
            'movements' => $movements,
        ]);
    }

    /**
     * Helper to calculate lifetime difference handling potential meter resets (minus diff)
     */
    private function calculateLifetimeDiff($currentReading, $lastInstallReading)
    {
        return VehicleReadingService::calculateLifetimeDiff($currentReading, $lastInstallReading);
    }

    public function store(Request $request)
    {
        $request->validate([
            'movement_type' => 'required|in:Installation,Removal,Rotation',
            'vehicle_id' => 'required|exists:master_import_kendaraan,id',
            'position_id' => 'required|exists:tyre_position_details,id',
            'target_position_id' => 'required_if:movement_type,Rotation|exists:tyre_position_details,id',
            'tyre_id' => 'required_if:movement_type,Installation|exists:tyres,id',
            'movement_date' => 'required|date',
            'odometer' => 'nullable|numeric',
            'hour_meter' => 'nullable|numeric',
            'operational_segment_id' => 'nullable|exists:tyre_segments,id',
            'work_location_id' => $request->input('movement_type') === 'Removal' ? 'required|exists:tyre_locations,id' : 'nullable|exists:tyre_locations,id',
            'psi_reading' => 'nullable|numeric',
            'target_psi_reading' => 'nullable|numeric',
            'rtd_reading' => 'nullable|numeric',
            'rtd_1' => 'nullable|numeric',
            'rtd_2' => 'nullable|numeric',
            'rtd_3' => 'nullable|numeric',
            'rtd_4' => 'nullable|numeric',
            'target_rtd_reading' => 'nullable|numeric',
            'target_rtd_1' => 'nullable|numeric',
            'target_rtd_2' => 'nullable|numeric',
            'target_rtd_3' => 'nullable|numeric',
            'target_rtd_4' => 'nullable|numeric',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'failure_code_id' => 'nullable|exists:tyre_failure_codes,id',
            'install_condition' => 'nullable|in:New,Spare,Repair',
            'new_bolts_quantity' => 'nullable|integer',
            'remarks' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_meter_reset' => 'nullable|boolean',
            'photo' => 'nullable|image|max:5120',
            'photo_target' => 'nullable|image|max:5120',
        ], [
            'work_location_id.required' => 'Gudang / Lokasi Tujuan wajib diisi saat melakukan pelepasan ban.',
            'work_location_id.exists' => 'Gudang / Lokasi Tujuan tidak valid.'
        ]);

        DB::beginTransaction();
        try {
            $warnings = [];
            $vehicle = MasterImportKendaraan::find($request->vehicle_id);
            $vehicleCode = $vehicle->kode_kendaraan ?? 'Unknown (' . $request->vehicle_id . ')';

            // 1. Future date detection
            if (\Carbon\Carbon::parse($request->movement_date)->isFuture()) {
                $warnings[] = "Tanggal Transaksi ({$request->movement_date}) tidak boleh di masa mendatang.";
            }

            // 2. Pressure anomaly
            if ($request->psi_reading !== null && ($request->psi_reading < 0 || $request->psi_reading > 200)) {
                $warnings[] = "Tekanan PSI ({$request->psi_reading}) tidak wajar (Standard: 0 - 200 PSI).";
            }

            // 3. Time anomaly
            if ($request->start_time && $request->end_time) {
                if (strtotime($request->start_time) > strtotime($request->end_time)) {
                    $warnings[] = "Waktu Mulai ({$request->start_time}) tidak boleh lebih besar dari Waktu Selesai ({$request->end_time}).";
                }
            }

            // --- DETEKSI ANOMALI ODO/HM (Human Error Check) via Service ---
            $odoWarnings = VehicleReadingService::detectOdoAnomalies(
                $request->vehicle_id, $vehicleCode,
                $request->odometer, $request->hour_meter,
                $request->is_meter_reset
            );
            $warnings = array_merge($warnings, $odoWarnings);

            // --- HANDLE PHOTO UPLOADS ---
            $photoPath = null;
            $photoTargetPath = null;

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $photoPath = $file->store('movements/' . $request->vehicle_id . '/' . date('Y-m'), 'public');
            }

            if ($request->hasFile('photo_target')) {
                $file = $request->file('photo_target');
                $photoTargetPath = $file->store('movements/' . $request->vehicle_id . '/' . date('Y-m'), 'public');
            }

            $position = TyrePositionDetail::findOrFail($request->position_id);

            if ($request->movement_type === 'Rotation') {
                $targetPosition = TyrePositionDetail::findOrFail($request->target_position_id);
                
                // Get Source Tyre
                $sourceTyre = Tyre::where('current_vehicle_id', $request->vehicle_id)
                    ->where('current_position_id', $request->position_id)
                    ->first();
                
                if (!$sourceTyre) {
                    throw new \Exception("Ban pada posisi sumber tidak ditemukan.");
                }

                // RTD Anomaly Detection (Source Tyre)
                if ($request->rtd_reading !== null && $sourceTyre->current_tread_depth > 0) {
                    if ($request->rtd_reading > $sourceTyre->current_tread_depth) {
                        $warnings[] = "RTD Ban A ({$sourceTyre->serial_number}) meningkat dari ({$sourceTyre->current_tread_depth}mm) ke ({$request->rtd_reading}mm).";
                    }
                }
                
                // Calculate lifetime for Source Tyre
                $lastMovSrc = TyreMovement::where('tyre_id', $sourceTyre->id)
                    ->where('movement_date', '<=', $request->movement_date)
                    ->orderBy('movement_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
                
                $kmDiffSrc = 0; $hmDiffSrc = 0;
                if ($lastMovSrc) {
                    $kmDiffSrc = $this->calculateLifetimeDiff($request->odometer, $lastMovSrc->odometer_reading);
                    $hmDiffSrc = $this->calculateLifetimeDiff($request->hour_meter, $lastMovSrc->hour_meter_reading);
                }

                // Check if Target Position is occupied
                $targetTyre = Tyre::where('current_vehicle_id', $request->vehicle_id)
                    ->where('current_position_id', $request->target_position_id)
                    ->first();
                
                if ($targetTyre) {
                    // RTD Anomaly Detection (Target Tyre)
                    if ($request->target_rtd_reading !== null && $targetTyre->current_tread_depth > 0) {
                        if ($request->target_rtd_reading > $targetTyre->current_tread_depth) {
                            $warnings[] = "RTD Ban B ({$targetTyre->serial_number}) meningkat dari ({$targetTyre->current_tread_depth}mm) ke ({$request->target_rtd_reading}mm).";
                        }
                    }

                    // SWAP LOGIC
                    $lastMovTgt = TyreMovement::where('tyre_id', $targetTyre->id)
                        ->where('movement_date', '<=', $request->movement_date)
                        ->orderBy('movement_date', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    
                    $kmDiffTgt = 0; $hmDiffTgt = 0;
                    if ($lastMovTgt) {
                        $kmDiffTgt = $this->calculateLifetimeDiff($request->odometer, $lastMovTgt->odometer_reading);
                        $hmDiffTgt = $this->calculateLifetimeDiff($request->hour_meter, $lastMovTgt->hour_meter_reading);
                    }

                    // 1. Log Rotation for Source Tyre (moving to Target Position)
                    TyreMovement::create([
                        'tyre_id' => $sourceTyre->id,
                        'vehicle_id' => $request->vehicle_id,
                        'position_id' => $request->target_position_id,
                        'movement_type' => 'Rotation',
                        'movement_date' => $request->movement_date,
                        'odometer_reading' => $request->odometer,
                        'hour_meter_reading' => $request->hour_meter,
                        'running_km' => $kmDiffSrc,
                        'running_hm' => $hmDiffSrc,
                        'psi_reading' => $request->psi_reading,
                        'rtd_reading' => $request->rtd_reading,
                        'rtd_1' => $request->rtd_1,
                        'rtd_2' => $request->rtd_2,
                        'rtd_3' => $request->rtd_3,
                        'rtd_4' => $request->rtd_4,
                        'work_location_id' => $request->work_location_id,
                        'operational_segment_id' => $request->operational_segment_id,
                        'tyreman_1' => $request->tyreman_1,
                        'tyreman_2' => $request->tyreman_2,
                        'start_time' => $request->start_time,
                        'end_time' => $request->end_time,
                        'notes' => 'Rotation Swap ke ' . $targetPosition->position_code . ' (Asal dari ' . $position->position_code . '). ' . ($request->notes ?? ''),
                        'created_by' => Auth::id(),
                        'photo' => $photoPath,
                    ]);

                    // 2. Log Rotation for Target Tyre (moving to Source Position)
                    TyreMovement::create([
                        'tyre_id' => $targetTyre->id,
                        'vehicle_id' => $request->vehicle_id,
                        'position_id' => $request->position_id,
                        'movement_type' => 'Rotation',
                        'movement_date' => $request->movement_date,
                        'odometer_reading' => $request->odometer,
                        'hour_meter_reading' => $request->hour_meter,
                        'running_km' => $kmDiffTgt,
                        'running_hm' => $hmDiffTgt,
                        'psi_reading' => $request->target_psi_reading,
                        'rtd_reading' => $request->target_rtd_reading,
                        'rtd_1' => $request->target_rtd_1,
                        'rtd_2' => $request->target_rtd_2,
                        'rtd_3' => $request->target_rtd_3,
                        'rtd_4' => $request->target_rtd_4,
                        'work_location_id' => $request->work_location_id,
                        'operational_segment_id' => $request->operational_segment_id,
                        'tyreman_1' => $request->tyreman_1,
                        'tyreman_2' => $request->tyreman_2,
                        'start_time' => $request->start_time,
                        'end_time' => $request->end_time,
                        'notes' => 'Rotation Swap ke ' . $position->position_code . ' (Asal dari ' . $targetPosition->position_code . ').',
                        'created_by' => Auth::id(),
                        'photo' => $photoTargetPath,
                    ]);

                    // 3. Update Master Tyres
                    $sourceTyre->update([
                        'current_position_id' => $request->target_position_id,
                        'total_lifetime_km' => ($sourceTyre->total_lifetime_km ?? 0) + $kmDiffSrc,
                        'total_lifetime_hm' => ($sourceTyre->total_lifetime_hm ?? 0) + $hmDiffSrc,
                        'current_tread_depth' => $request->rtd_reading ?? $sourceTyre->current_tread_depth,
                        'current_km' => $request->odometer ?? 0,
                        'current_hm' => $request->hour_meter ?? 0,
                    ]);

                    $targetTyre->update([
                        'current_position_id' => $request->position_id,
                        'total_lifetime_km' => ($targetTyre->total_lifetime_km ?? 0) + $kmDiffTgt,
                        'total_lifetime_hm' => ($targetTyre->total_lifetime_hm ?? 0) + $hmDiffTgt,
                        'current_tread_depth' => $request->target_rtd_reading ?? $targetTyre->current_tread_depth,
                        'current_km' => $request->odometer ?? 0,
                        'current_hm' => $request->hour_meter ?? 0,
                    ]);

                    // 4. Update Position Details

                } else {
                    // MOVE LOGIC (Target is empty)
                    // 1. Log Rotation
                    TyreMovement::create([
                        'tyre_id' => $sourceTyre->id,
                        'vehicle_id' => $request->vehicle_id,
                        'position_id' => $request->target_position_id,
                        'movement_type' => 'Rotation',
                        'movement_date' => $request->movement_date,
                        'odometer_reading' => $request->odometer,
                        'hour_meter_reading' => $request->hour_meter,
                        'running_km' => $kmDiffSrc,
                        'running_hm' => $hmDiffSrc,
                        'psi_reading' => $request->psi_reading,
                        'rtd_reading' => $request->rtd_reading,
                        'rtd_1' => $request->rtd_1,
                        'rtd_2' => $request->rtd_2,
                        'rtd_3' => $request->rtd_3,
                        'rtd_4' => $request->rtd_4,
                        'work_location_id' => $request->work_location_id,
                        'operational_segment_id' => $request->operational_segment_id,
                        'tyreman_1' => $request->tyreman_1,
                        'tyreman_2' => $request->tyreman_2,
                        'start_time' => $request->start_time,
                        'end_time' => $request->end_time,
                        'notes' => 'Rotation Pindah ke ' . $targetPosition->position_code . '. ' . ($request->notes ?? ''),
                        'created_by' => Auth::id(),
                        'photo' => $photoPath,
                    ]);

                    // 2. Update Master Tyre
                    $sourceTyre->update([
                        'current_position_id' => $request->target_position_id,
                        'total_lifetime_km' => ($sourceTyre->total_lifetime_km ?? 0) + $kmDiffSrc,
                        'total_lifetime_hm' => ($sourceTyre->total_lifetime_hm ?? 0) + $hmDiffSrc,
                        'current_tread_depth' => $request->rtd_reading ?? $sourceTyre->current_tread_depth,
                        'current_km' => $request->odometer ?? 0,
                        'current_hm' => $request->hour_meter ?? 0,
                    ]);

                    // 3. Update Position Details
                }
                
                $tyre = $sourceTyre; // For potential use in logging below

            } elseif ($request->movement_type === 'Installation') {
                $tyre = Tyre::findOrFail($request->tyre_id);
                $oldLocationId = $tyre->current_location_id; // Store old location before update

                // Determine actual condition from master status for new tyre
                $actualCondition = 'Repair';
                if ($tyre->status === 'New') $actualCondition = 'New';

                // 4. Physical possibility check (RTD > Initial)
                if ($request->rtd_reading !== null && $tyre->initial_tread_depth > 0) {
                    if ($request->rtd_reading > $tyre->initial_tread_depth) {
                        $warnings[] = "RTD Ban ({$request->rtd_reading}mm) tidak mungkin lebih besar dari RTD Awal/Baru ({$tyre->initial_tread_depth}mm).";
                    }
                }

                // 5. Status mismatch (Installing a tyre that is already installed elsewhere or scrap)
                if ($tyre->status === 'Installed' && $tyre->current_vehicle_id != $request->vehicle_id) {
                    $otherVehicle = MasterImportKendaraan::find($tyre->current_vehicle_id);
                    $vName = $otherVehicle->kode_kendaraan ?? 'Unit lain';
                    $warnings[] = "Ban SN {$tyre->serial_number} terdeteksi masih terpasang di unit {$vName}. Silakan lakukan pelepasan terlebih dahulu.";
                }

                if ($tyre->status === 'Scrap') {
                    $warnings[] = "Ban SN {$tyre->serial_number} sudah berstatus SCRAP dan tidak boleh dipasang kembali.";
                }

                // --- HANDLE REPLACEMENT (If position is already occupied) ---
                $isReplacement = false;
                $oldTyre = Tyre::where('current_vehicle_id', $request->vehicle_id)->where('current_position_id', $position->id)->first();
                if ($oldTyre) {
                    $isReplacement = true;
                    if ($oldTyre) {
                        // 1. Calculate Lifetime for Old Tyre since its last recorded event
                        $lastOldMov = TyreMovement::where('tyre_id', $oldTyre->id)
                            ->where('movement_date', '<=', $request->movement_date)
                            ->orderBy('movement_date', 'desc')
                            ->orderBy('id', 'desc')
                            ->first();

                        $kmDiff = 0;
                        $hmDiff = 0;
                        if ($lastOldMov) {
                            $kmDiff = $this->calculateLifetimeDiff($request->odometer, $lastOldMov->odometer_reading);
                            $hmDiff = $this->calculateLifetimeDiff($request->hour_meter, $lastOldMov->hour_meter_reading);
                        }

                        // 2. Create Removal Log for Old Tyre (Auto-replace)
                        TyreMovement::create([
                            'tyre_id' => $oldTyre->id,
                            'vehicle_id' => $request->vehicle_id,
                            'position_id' => $request->position_id,
                            'movement_type' => 'Removal',
                            'movement_date' => $request->movement_date,
                            'odometer_reading' => $request->odometer,
                            'hour_meter_reading' => $request->hour_meter,
                            'running_km' => $kmDiff,
                            'running_hm' => $hmDiff,
                            'notes' => 'Auto-Removal during Replacement (SN: ' . $tyre->serial_number . ')',
                            'created_by' => Auth::id()
                        ]);

                        // 3. Update Old Tyre status to 'Repaired' (Default for auto-removal)
                        $updateDataOld = [
                            'current_vehicle_id' => null,
                            'current_position_id' => null,
                            'is_in_warehouse' => true,
                            'is_repairing' => true,
                            'current_location_id' => $request->work_location_id,
                            'status' => 'Repaired',
                            'total_lifetime_km' => ($oldTyre->total_lifetime_km ?? 0) + $kmDiff,
                            'total_lifetime_hm' => ($oldTyre->total_lifetime_hm ?? 0) + $hmDiff,
                            'current_km' => $request->odometer ?? 0,
                            'current_hm' => $request->hour_meter ?? 0,
                        ];
                        if (\App\Helpers\SessionCompanyHelper::isWorkshopAdmin() && !\App\Helpers\SessionCompanyHelper::isSuperAdmin()) {
                            $updateDataOld['tyre_company_id'] = auth()->user()->tyre_company_id;
                        }
                        $oldTyre->update($updateDataOld);

                        // 4. Increase stock at working location (Old tyre enters warehouse)
                        if ($request->work_location_id) {
                            DB::table('tyre_locations')
                                ->where('id', $request->work_location_id)
                                ->increment('current_stock');
                        }
                    }
                }
                // -------------------------------------------------------------

                // 1. Update New Tyre status & location
                if ($request->rtd_reading && $tyre->current_tread_depth > 0) {
                    if ($request->rtd_reading > $tyre->current_tread_depth) {
                        $warnings[] = "RTD Ban Pasang SN " . $tyre->serial_number . " ({$request->rtd_reading}mm) lebih tinggi dari catatan stok ({$tyre->current_tread_depth}mm).";
                    }
                }

                $updateDataNew = [
                    'current_vehicle_id' => $request->vehicle_id,
                    'current_position_id' => $request->position_id,
                    'is_in_warehouse' => false,
                    'current_location_id' => null,
                    'status' => 'Installed',
                    'current_tread_depth' => $request->rtd_reading ?? $tyre->current_tread_depth,
                    'current_km' => $request->odometer ?? 0,
                    'current_hm' => $request->hour_meter ?? 0,
                ];
                if (\App\Helpers\SessionCompanyHelper::isWorkshopAdmin() && !\App\Helpers\SessionCompanyHelper::isSuperAdmin()) {
                    $updateDataNew['tyre_company_id'] = $vehicle->tyre_company_id;
                }
                $tyre->update($updateDataNew);

                // 2. Update Position Detail (Secondary sync)

                // 3. Decrease stock at old location (tyre leaving warehouse)
                if ($oldLocationId) {
                    DB::table('tyre_locations')
                        ->where('id', $oldLocationId)
                        ->decrement('current_stock');
                }

                // 4. Log Movement
                TyreMovement::create([
                    'tyre_id' => $tyre->id,
                    'vehicle_id' => $request->vehicle_id,
                    'position_id' => $request->position_id,
                    'operational_segment_id' => $request->operational_segment_id,
                    'work_location_id' => $request->work_location_id,
                    'install_condition' => $actualCondition,
                    'is_replacement' => $isReplacement,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'tyreman_1' => $request->tyreman_1,
                    'tyreman_2' => $request->tyreman_2,
                    'psi_reading' => $request->psi_reading,
                    'rtd_reading' => $request->rtd_reading,
                    'rtd_1' => $request->rtd_1,
                    'rtd_2' => $request->rtd_2,
                    'rtd_3' => $request->rtd_3,
                    'rtd_4' => $request->rtd_4,
                    'new_bolts_used' => $request->has('new_bolts_used'),
                    'new_bolts_quantity' => $request->new_bolts_quantity,
                    'movement_type' => 'Installation',
                    'movement_date' => $request->movement_date,
                    'odometer_reading' => $request->odometer,
                    'hour_meter_reading' => $request->hour_meter,
                    'remarks' => $request->remarks,
                    'notes' => $request->notes,
                    'created_by' => Auth::id(),
                    'photo' => $photoPath,
                ]);
            } else {
                // Removal
                $tyre = Tyre::where('current_vehicle_id', $request->vehicle_id)
                    ->where('current_position_id', $request->position_id)
                    ->first();

                if (!$tyre) {
                    $posInfo = $position->position_code . " - " . $position->position_name;
                    $warnings[] = "Posisi {$posInfo} sudah kosong atau ban tidak terdeteksi terpasang pada unit {$vehicleCode} di posisi tersebut.";
                    
                    // IMPORTANT: Rollback first so setLogActivity can commit to DB
                    DB::rollBack();

                    setLogActivity(Auth::id(), 'Deteksi Human Error: Pelepasan Ban pada unit ' . $vehicleCode, [
                        'action_type' => 'error',
                        'module' => 'Human Error',
                        'data_after' => [
                            'Kendaraan' => $vehicleCode,
                            'Posisi' => $posInfo,
                            'Pesan Error' => $warnings,
                            'Tipe Transaksi' => 'Removal'
                        ]
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => "Transaksi GAGAL DISIMPAN (Data Mismatch):\n\n" . implode("\n", $warnings)
                    ], 422);
                }

                // --- Calculate Lifetime (KM & HM) ---
                // Calculate Lifetime since last recorded event (could be install or inspection)
                $lastMov = TyreMovement::where('tyre_id', $tyre->id)
                    ->where('movement_date', '<=', $request->movement_date)
                    ->orderBy('movement_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                $kmDiff = 0;
                $hmDiff = 0;

                if ($lastMov) {
                    $kmDiff = $this->calculateLifetimeDiff($request->odometer, $lastMov->odometer_reading);
                    $hmDiff = $this->calculateLifetimeDiff($request->hour_meter, $lastMov->hour_meter_reading);
                }
                // ------------------------------------

                // 1. Log Movement
                TyreMovement::create([
                    'tyre_id' => $tyre->id,
                    'vehicle_id' => $request->vehicle_id,
                    'position_id' => $request->position_id,
                    'operational_segment_id' => $request->operational_segment_id,
                    'work_location_id' => $request->work_location_id,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'tyreman_1' => $request->tyreman_1,
                    'tyreman_2' => $request->tyreman_2,
                    'psi_reading' => $request->psi_reading,
                    'rtd_reading' => $request->rtd_reading,
                    'rtd_1' => $request->rtd_1,
                    'rtd_2' => $request->rtd_2,
                    'rtd_3' => $request->rtd_3,
                    'rtd_4' => $request->rtd_4,
                    'new_bolts_used' => $request->has('new_bolts_used'),
                    'new_bolts_quantity' => $request->new_bolts_quantity,
                    'movement_type' => 'Removal',
                    'target_status' => $request->target_status,
                    'failure_code_id' => $request->failure_code_id,
                    'movement_date' => $request->movement_date,
                    'odometer_reading' => $request->odometer,
                    'hour_meter_reading' => $request->hour_meter,
                    'running_km' => $kmDiff,
                    'running_hm' => $hmDiff,
                    'remarks' => $request->remarks,
                    'notes' => $request->notes,
                    'created_by' => Auth::id(),
                    'photo' => $photoPath,
                ]);

                // Fetch failure code for descriptive logging
                $failureCodeModel = null;
                if ($request->failure_code_id) {
                    $failureCodeModel = TyreFailureCode::find($request->failure_code_id);
                }

                // 2. Update Tyre status, location, Total Lifetime AND RTD
                if ($request->rtd_reading && $tyre->current_tread_depth > 0) {
                    if ($request->rtd_reading > $tyre->current_tread_depth) {
                        $warnings[] = "RTD Ban Lepas SN " . $tyre->serial_number . " ({$request->rtd_reading}mm) meningkat dari catatan sebelumnya ({$tyre->current_tread_depth}mm).";
                    }
                }

                $finalStatus = $request->target_status ?? 'Repaired';
                $resolvedCompanyId = null;
                if (!empty($request->work_location_id)) {
                    $location = \App\Models\TyreLocation::withoutGlobalScope('company')->find($request->work_location_id);
                    if ($location) {
                        $resolvedCompanyId = $location->tyre_company_id;
                    }
                }
                
                if (!$resolvedCompanyId && \App\Helpers\SessionCompanyHelper::isWorkshopAdmin() && !\App\Helpers\SessionCompanyHelper::isSuperAdmin()) {
                    $resolvedCompanyId = auth()->user()->tyre_company_id;
                }

                $updateDataRem = [
                    'current_vehicle_id' => null,
                    'current_position_id' => null,
                    'is_in_warehouse' => true,
                    'is_repairing' => ($finalStatus === 'Repaired'),
                    'current_location_id' => $request->work_location_id,
                    'status' => $finalStatus,
                    'total_lifetime_km' => ($tyre->total_lifetime_km ?? 0) + $kmDiff,
                    'total_lifetime_hm' => ($tyre->total_lifetime_hm ?? 0) + $hmDiff,
                    'current_tread_depth' => $request->rtd_reading ?? $tyre->current_tread_depth,
                    'current_km' => $request->odometer ?? 0,
                    'current_hm' => $request->hour_meter ?? 0,
                ];
                
                if ($resolvedCompanyId) {
                    $updateDataRem['tyre_company_id'] = $resolvedCompanyId;
                }
                $tyre->update($updateDataRem);

                // 3. Increase stock at new location (tyre entering warehouse), UNLESS SCRAP
                if ($request->work_location_id && $finalStatus !== 'Scrap') {
                    DB::table('tyre_locations')
                        ->where('id', $request->work_location_id)
                        ->increment('current_stock');
                }

                // 4. Clear Position Detail
            }

            if (!empty($warnings)) {
                DB::rollBack();

                $actionLabels = ['Installation' => 'Pemasangan', 'Rotation' => 'Rotasi', 'Removal' => 'Pelepasan'];
                $actionLabel = $actionLabels[$request->movement_type] ?? $request->movement_type;
                setLogActivity(Auth::id(), 'Deteksi Human Error: ' . $actionLabel . ' Ban pada unit ' . $vehicleCode, [
                    'action_type' => 'error',
                    'module' => 'Human Error',
                    'data_after' => [
                        'Kendaraan' => $vehicleCode,
                        'Pesan Error' => $warnings,
                        'Tipe Transaksi' => $request->movement_type,
                        'Data Yang Diinput' => [
                            'Odometer' => $request->odometer,
                            'Hour Meter' => $request->hour_meter,
                            'Posisi' => $position->position_code . ' - ' . $position->position_name,
                            'Serial Number' => $tyre->serial_number ?? ($request->tyre_id ?? '-'),
                            'Kondisi Pasang' => $actualCondition ?? null,
                            'Catatan' => $request->notes
                        ]
                    ]
                ]);

                return response()->json([
                    'success' => false,
                    'message' => "Transaksi GAGAL DISIMPAN (Deteksi Human Error):\n\n" . implode("\n", $warnings)
                ], 422);
            }

            DB::commit();

            // Auto-sync with Tyre Monitoring
            try {
                if ($request->movement_type === 'Installation' && isset($tyre)) {
                    \App\Services\TyreMonitoringSyncService::syncInstallation(
                        $request->vehicle_id,
                        $request->position_id,
                        $tyre->id,
                        $request->all()
                    );
                } elseif ($request->movement_type === 'Removal' && isset($tyre)) {
                    \App\Services\TyreMonitoringSyncService::syncRemoval(
                        $request->vehicle_id,
                        $request->position_id,
                        $tyre->id,
                        $request->all()
                    );
                } elseif ($request->movement_type === 'Rotation' && isset($sourceTyre)) {
                    \App\Services\TyreMonitoringSyncService::syncRotation(
                        $request->vehicle_id,
                        $request->position_id,
                        $request->target_position_id,
                        $sourceTyre->id,
                        isset($targetTyre) && $targetTyre ? $targetTyre->id : null,
                        $request->all()
                    );
                }
            } catch (\Exception $syncEx) {
                \Illuminate\Support\Facades\Log::error("Tyre Movement -> Monitoring Auto-Sync Error: " . $syncEx->getMessage());
            }

            // [FIX-SYNC-1] Clear dashboard cache so data reflects immediately
            \Illuminate\Support\Facades\Cache::flush();

            $successLabels = ['Installation' => 'Pemasangan', 'Rotation' => 'Rotasi', 'Removal' => 'Pelepasan'];
            $successLabel = $successLabels[$request->movement_type] ?? $request->movement_type;
            setLogActivity(Auth::id(), $successLabel . ' ban pada kendaraan ' . $vehicleCode, [
                'action_type' => 'create',
                'module' => 'Tyre Movement',
                'data_after' => [
                    'Tipe Transaksi' => $request->movement_type,
                    'Kendaraan' => $vehicleCode,
                    'Posisi' => $position->position_code . ' - ' . $position->position_name,
                    'Serial Number' => $tyre->serial_number ?? '-',
                    'Odometer' => $request->odometer,
                    'Hour Meter' => $request->hour_meter,
                    'Kondisi Pasang' => $actualCondition ?? '-',
                    'Status Akhir' => $request->target_status ?? '-',
                    'Kode Kerusakan' => isset($failureCodeModel) ? ($failureCodeModel->failure_code . ' - ' . $failureCodeModel->failure_name) : '-',
                    'User Notes' => $request->notes ?? '-'
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required',
            'movement_date' => 'required|date',
            'odometer' => 'nullable|numeric',
            'hour_meter' => 'nullable|numeric',
            'movements' => 'required|string', // JSON string from frontend
        ]);

        $user = Auth::user();
        $companyId = \App\Helpers\SessionCompanyHelper::getActiveCompanyId();
        // For write operations, resolve to a single company ID
        if (is_array($companyId)) $companyId = $companyId[0] ?? $user->tyre_company_id;
        $movements = json_decode($request->movements, true);
        if (!is_array($movements) || empty($movements)) {
            return response()->json(['success' => false, 'message' => 'Data pergerakan ban kosong atau tidak valid.'], 422);
        }

        $hasRemoval = false;
        if (is_array($movements)) {
            foreach ($movements as $mov) {
                if (isset($mov['type']) && $mov['type'] === 'Removal') {
                    $hasRemoval = true;
                    break;
                }
            }
        }

        $request->validate([
            'work_location_id' => $hasRemoval ? 'required|exists:tyre_locations,id' : 'nullable|exists:tyre_locations,id',
        ], [
            'work_location_id.required' => 'Gudang / Lokasi Tujuan wajib diisi saat melakukan pelepasan ban.',
            'work_location_id.exists' => 'Gudang / Lokasi Tujuan tidak valid.'
        ]);

        DB::beginTransaction();
        try {
            // Pessimistic Locking
            $vehicle = MasterImportKendaraan::where('id', $request->vehicle_id)->lockForUpdate()->firstOrFail();
            $vehicleCode = $vehicle->kode_kendaraan;
            
            // Check vehicle access:
            if (!\App\Helpers\SessionCompanyHelper::isSuperAdmin()) {
                if (\App\Helpers\SessionCompanyHelper::isWorkshopAdmin()) {
                    $allowedCompanyIds = auth()->user()->tyreCompany ? auth()->user()->tyreCompany->getAllClientIds() : [];
                    $allowedCompanyIds[] = auth()->user()->tyre_company_id;
                    if (!in_array($vehicle->tyre_company_id, $allowedCompanyIds)) {
                        throw new \Exception('Akses Ditolak: Kendaraan bukan milik perusahaan Anda atau klien binaan Anda.');
                    }
                } else {
                    $activeCompanyId = \App\Helpers\SessionCompanyHelper::getActiveCompanyId();
                    if ($activeCompanyId && !is_array($activeCompanyId)) {
                        if ($vehicle->tyre_company_id != $activeCompanyId) {
                            throw new \Exception('Akses Ditolak: Kendaraan bukan milik perusahaan Anda.');
                        }
                    } elseif ($vehicle->tyre_company_id != auth()->user()->tyre_company_id) {
                        throw new \Exception('Akses Ditolak: Kendaraan bukan milik perusahaan Anda.');
                    }
                }
            }

            $warnings = [];

            // --- DETEKSI ANOMALI ODO/HM ---
            $odoWarnings = \App\Services\VehicleReadingService::detectOdoAnomalies(
                $request->vehicle_id, $vehicleCode,
                $request->odometer, $request->hour_meter,
                $request->has('is_meter_reset') ? true : false
            );
            $warnings = array_merge($warnings, $odoWarnings);

            $allPositions = TyrePositionDetail::where('configuration_id', $vehicle->tyre_position_configuration_id)->get()->keyBy('id');

            // --- VALIDASI MULTI-TENANT PADA BAN ---
            $tyreIds = [];
            foreach ($movements as $mov) {
                if (!empty($mov['tyre_id'])) $tyreIds[] = $mov['tyre_id'];
                if (!empty($mov['target_tyre_id'])) $tyreIds[] = $mov['target_tyre_id'];
            }
            if (!empty($tyreIds)) {
                $checkCompanyId = $companyId;
                if ($checkCompanyId) {
                    if (\App\Helpers\SessionCompanyHelper::isWorkshopAdmin() && !\App\Helpers\SessionCompanyHelper::isSuperAdmin()) {
                        $allowedCompanyIds = auth()->user()->tyreCompany->getAllClientIds();
                        $allowedCompanyIds[] = auth()->user()->tyre_company_id;
                        
                        $tyresCount = Tyre::withoutGlobalScope('company')
                            ->whereIn('id', $tyreIds)
                            ->whereIn('tyre_company_id', $allowedCompanyIds)
                            ->count();
                    } else {
                        $tyresCount = Tyre::whereIn('id', $tyreIds)->where('tyre_company_id', $checkCompanyId)->count();
                    }
                    
                    $uniqueTyreIds = count(array_unique($tyreIds));
                    if ($tyresCount !== $uniqueTyreIds) {
                        throw new \Exception("Akses Ditolak: Satu atau lebih Ban yang dipilih tidak berada dalam lingkup perusahaan Anda.");
                    }
                }
            }

            foreach ($movements as $index => $mov) {
                $type = $mov['type'];
                $position = $allPositions->get($mov['position_id']);
                
                if (!$position) {
                    throw new \Exception("Posisi ban tidak valid pada transaksi baris " . ($index + 1));
                }

                // Handle Photo
                $photoPath = null;
                $photoKey = 'move_photo_' . $index;
                if ($request->hasFile($photoKey)) {
                    $photoPath = $request->file($photoKey)->store('movements/' . $vehicle->id . '/' . date('Y-m'), 'public');
                }

                if ($type === 'Installation') {
                    // Cek jika posisi sudah ada bannya (Fitur Auto-Replace)
                    $isReplacement = false;
                    $oldTyre = Tyre::where('current_vehicle_id', $request->vehicle_id)->where('current_position_id', $mov['position_id'])->first();
                    if ($oldTyre) {
                        $isReplacement = true;
                        if ($oldTyre) {
                            // Catat pelepasan ban lama
                            $oldTyre->update([
                                'current_vehicle_id' => null,
                                'current_position_id' => null,
                                'is_in_warehouse' => true,
                                'status' => 'Repaired', 
                                'is_repairing' => true,
                            ]);

                            TyreMovement::create([
                                'tyre_id' => $oldTyre->id,
                                'vehicle_id' => $request->vehicle_id,
                                'position_id' => $mov['position_id'],
                                'operational_segment_id' => $request->operational_segment_id ?? null,
                                'work_location_id' => $request->work_location_id ?? null,
                                'start_time' => $request->start_time ?? null,
                                'movement_type' => 'Removal',
                                'movement_date' => $request->movement_date,
                                'odometer_reading' => $request->odometer,
                                'hour_meter_reading' => $request->hour_meter,
                                'created_by' => Auth::id(),
                                'notes' => 'Auto-removed during replacement.'
                            ]);
                        }
                    }

                    $tyre = Tyre::findOrFail($mov['tyre_id']);
                    $oldLocationId = $tyre->current_location_id;

                    $actualCondition = 'Repair';
                    if ($tyre->status === 'New') $actualCondition = 'New';

                    // Physical check
                    if (isset($mov['rtd']) && $mov['rtd'] !== '' && $tyre->initial_tread_depth > 0) {
                        if ($mov['rtd'] > $tyre->initial_tread_depth) {
                            $warnings[] = "RTD Ban ({$mov['rtd']}mm) lebih besar dari RTD Awal ({$tyre->initial_tread_depth}mm).";
                        }
                    }

                    if ($tyre->status === 'Installed' && $tyre->current_vehicle_id != $request->vehicle_id) {
                        $warnings[] = "Ban SN {$tyre->serial_number} masih terpasang di unit lain.";
                    }
                    if ($tyre->status === 'Scrap') {
                        $warnings[] = "Ban SN {$tyre->serial_number} sudah SCRAP.";
                    }

                    // (Kode Handle Replacement dihapus karena pemasangan sekarang mewajibkan node kosong dan menggunakan Drag-to-Correct / Pelepasan eksplisit)

                    $updateData = [
                        'current_vehicle_id' => $request->vehicle_id,
                        'current_position_id' => $mov['position_id'],
                        'is_in_warehouse' => false,
                        'current_location_id' => null,
                        'status' => 'Installed',
                        'current_tread_depth' => isset($mov['rtd']) && $mov['rtd'] !== '' ? $mov['rtd'] : $tyre->current_tread_depth,
                        'current_km' => $request->odometer ?? 0,
                        'current_hm' => $request->hour_meter ?? 0,
                    ];

                    if (!empty($mov['serial_number'])) {
                        $newSn = strtoupper(trim($mov['serial_number']));
                        if ($newSn !== $tyre->serial_number) {
                            $exists = Tyre::withoutGlobalScopes()
                                ->where('serial_number', $newSn)
                                ->where('id', '!=', $tyre->id)
                                ->whereNull('deleted_at')
                                ->exists();
                            if ($exists) {
                                throw new \Exception("Nomor Seri Ban '{$newSn}' sudah terdaftar pada ban lain di database.");
                            }
                            $updateData['serial_number'] = $newSn;
                        }
                    }

                    if (\App\Helpers\SessionCompanyHelper::isWorkshopAdmin() && !\App\Helpers\SessionCompanyHelper::isSuperAdmin()) {
                        $updateData['tyre_company_id'] = $vehicle->tyre_company_id;
                    }
                    $tyre->update($updateData);


                    if ($oldLocationId) {
                        DB::table('tyre_locations')->where('id', $oldLocationId)->decrement('current_stock');
                    }

                    TyreMovement::create([
                        'tyre_id' => $tyre->id,
                        'vehicle_id' => $request->vehicle_id,
                        'position_id' => $mov['position_id'],
                        'operational_segment_id' => $request->operational_segment_id ?? null,
                        'work_location_id' => $request->work_location_id ?? null,
                        'start_time' => $request->start_time ?? null,
                        'install_condition' => $actualCondition,
                        'is_replacement' => $isReplacement,
                        'tyreman_1' => $request->tyreman_1 ?? null,
                        'tyreman_2' => $request->tyreman_2 ?? null,
                        'psi_reading' => $mov['psi'] ?? null,
                        'rtd_reading' => isset($mov['rtd']) && $mov['rtd'] !== '' ? $mov['rtd'] : null,
                        'movement_type' => 'Installation',
                        'movement_date' => $request->movement_date,
                        'odometer_reading' => $request->odometer,
                        'hour_meter_reading' => $request->hour_meter,
                        'remarks' => $mov['remarks'] ?? null,
                        'notes' => $mov['notes'] ?? null,
                        'created_by' => Auth::id(),
                        'photo' => $photoPath,
                    ]);

                } elseif ($type === 'Removal') {
                    $tyre = Tyre::where('current_vehicle_id', $request->vehicle_id)
                        ->where('current_position_id', $mov['position_id'])
                        ->first();

                    if (!$tyre) {
                        throw new \Exception("Gagal: Posisi {$position->position_code} sudah kosong atau ban telah dilepas oleh user lain. Harap muat ulang layout.");
                    }

                    $lastMov = TyreMovement::where('tyre_id', $tyre->id)
                        ->where('movement_date', '<=', $request->movement_date)
                        ->orderBy('movement_date', 'desc')->orderBy('id', 'desc')->first();

                    $kmDiff = 0; $hmDiff = 0;
                    if ($lastMov) {
                        $kmDiff = $this->calculateLifetimeDiff($request->odometer, $lastMov->odometer_reading);
                        $hmDiff = $this->calculateLifetimeDiff($request->hour_meter, $lastMov->hour_meter_reading);
                    }

                    $finalStatus = $mov['target_status'] ?? 'Repaired';

                    TyreMovement::create([
                        'tyre_id' => $tyre->id,
                        'vehicle_id' => $request->vehicle_id,
                        'position_id' => $mov['position_id'],
                        'operational_segment_id' => $request->operational_segment_id ?? null,
                        'work_location_id' => $request->work_location_id ?? null,
                        'start_time' => $request->start_time ?? null,
                        'tyreman_1' => $request->tyreman_1 ?? null,
                        'tyreman_2' => $request->tyreman_2 ?? null,
                        'psi_reading' => $mov['psi'] ?? null,
                        'rtd_reading' => isset($mov['rtd']) && $mov['rtd'] !== '' ? $mov['rtd'] : null,
                        'movement_type' => 'Removal',
                        'target_status' => $finalStatus,
                        'failure_code_id' => $mov['failure_code_id'] ?? null,
                        'movement_date' => $request->movement_date,
                        'odometer_reading' => $request->odometer,
                        'hour_meter_reading' => $request->hour_meter,
                        'running_km' => $kmDiff,
                        'running_hm' => $hmDiff,
                        'remarks' => $mov['remarks'] ?? null,
                        'notes' => $mov['notes'] ?? null,
                        'created_by' => Auth::id(),
                        'photo' => $photoPath,
                    ]);

                    $resolvedCompanyId = null;
                    if (!empty($request->work_location_id)) {
                        $location = \App\Models\TyreLocation::withoutGlobalScope('company')->find($request->work_location_id);
                        if ($location) {
                            $resolvedCompanyId = $location->tyre_company_id;
                        }
                    }
                    
                    if (!$resolvedCompanyId && \App\Helpers\SessionCompanyHelper::isWorkshopAdmin() && !\App\Helpers\SessionCompanyHelper::isSuperAdmin()) {
                        $resolvedCompanyId = auth()->user()->tyre_company_id;
                    }

                    $updateDataRem = [
                        'current_vehicle_id' => null,
                        'current_position_id' => null,
                        'is_in_warehouse' => true,
                        'current_location_id' => (!empty($request->work_location_id)) ? $request->work_location_id : null,
                        'status' => $finalStatus,
                        'is_repairing' => ($finalStatus === 'Repaired') ? true : false,
                        'total_lifetime_km' => ($tyre->total_lifetime_km ?? 0) + $kmDiff,
                        'total_lifetime_hm' => ($tyre->total_lifetime_hm ?? 0) + $hmDiff,
                        'current_tread_depth' => isset($mov['rtd']) && $mov['rtd'] !== '' ? $mov['rtd'] : $tyre->current_tread_depth,
                        'current_km' => $request->odometer ?? 0,
                        'current_hm' => $request->hour_meter ?? 0,
                    ];
                    
                    if ($resolvedCompanyId) {
                        $updateDataRem['tyre_company_id'] = $resolvedCompanyId;
                    }
                    
                    $tyre->update($updateDataRem);

                    if (!empty($request->work_location_id) && $finalStatus !== 'Scrap') {
                        DB::table('tyre_locations')->where('id', $request->work_location_id)->increment('current_stock');
                    }


                } elseif ($type === 'Rotation') {
                    $targetPosition = $allPositions->get($mov['target_position_id']);
                    if (!$targetPosition) {
                        throw new \Exception("Posisi target rotasi tidak valid.");
                    }

                    $sourceTyre = Tyre::where('current_vehicle_id', $request->vehicle_id)
                        ->where('current_position_id', $mov['position_id'])
                        ->first();
                    
                    if (!$sourceTyre) {
                        throw new \Exception("Gagal: Ban sumber rotasi (Posisi {$position->position_code}) tidak ditemukan atau telah dilepas oleh user lain.");
                    }

                    $lastMovSrc = TyreMovement::where('tyre_id', $sourceTyre->id)
                        ->where('movement_date', '<=', $request->movement_date)
                        ->orderBy('movement_date', 'desc')->orderBy('id', 'desc')->first();
                    $kmDiffSrc = 0; $hmDiffSrc = 0;
                    if ($lastMovSrc) {
                        $kmDiffSrc = $this->calculateLifetimeDiff($request->odometer, $lastMovSrc->odometer_reading);
                        $hmDiffSrc = $this->calculateLifetimeDiff($request->hour_meter, $lastMovSrc->hour_meter_reading);
                    }

                    $targetTyre = Tyre::where('current_vehicle_id', $request->vehicle_id)
                        ->where('current_position_id', $mov['target_position_id'])
                        ->first();

                    if ($targetTyre) {
                        // SWAP
                        $photoTargetKey = 'move_photo_target_' . $index;
                        $photoTargetPath = null;
                        if ($request->hasFile($photoTargetKey)) {
                            $photoTargetPath = $request->file($photoTargetKey)->store('movements/' . $vehicle->id . '/' . date('Y-m'), 'public');
                        }

                        $lastMovTgt = TyreMovement::where('tyre_id', $targetTyre->id)
                            ->where('movement_date', '<=', $request->movement_date)
                            ->orderBy('movement_date', 'desc')->orderBy('id', 'desc')->first();
                        $kmDiffTgt = 0; $hmDiffTgt = 0;
                        if ($lastMovTgt) {
                            $kmDiffTgt = $this->calculateLifetimeDiff($request->odometer, $lastMovTgt->odometer_reading);
                            $hmDiffTgt = $this->calculateLifetimeDiff($request->hour_meter, $lastMovTgt->hour_meter_reading);
                        }

                        TyreMovement::create([
                            'tyre_id' => $sourceTyre->id,
                            'vehicle_id' => $request->vehicle_id,
                            'position_id' => $mov['target_position_id'],
                            'movement_type' => 'Rotation',
                            'movement_date' => $request->movement_date,
                            'odometer_reading' => $request->odometer,
                            'hour_meter_reading' => $request->hour_meter,
                            'running_km' => $kmDiffSrc,
                            'running_hm' => $hmDiffSrc,
                            'psi_reading' => $mov['psi'] ?? null,
                            'rtd_reading' => isset($mov['rtd']) && $mov['rtd'] !== '' ? $mov['rtd'] : null,
                            'work_location_id' => $request->work_location_id ?? null,
                            'start_time' => $request->start_time ?? null,
                            'operational_segment_id' => $request->operational_segment_id ?? null,
                            'tyreman_1' => $request->tyreman_1 ?? null,
                            'tyreman_2' => $request->tyreman_2 ?? null,
                            'notes' => 'Rotation Swap ke ' . $targetPosition->position_code . '. ' . ($mov['notes'] ?? ''),
                            'created_by' => Auth::id(),
                            'photo' => $photoPath,
                        ]);

                        TyreMovement::create([
                            'tyre_id' => $targetTyre->id,
                            'vehicle_id' => $request->vehicle_id,
                            'position_id' => $mov['position_id'],
                            'movement_type' => 'Rotation',
                            'movement_date' => $request->movement_date,
                            'odometer_reading' => $request->odometer,
                            'hour_meter_reading' => $request->hour_meter,
                            'running_km' => $kmDiffTgt,
                            'running_hm' => $hmDiffTgt,
                            'psi_reading' => $mov['target_psi'] ?? null,
                            'rtd_reading' => isset($mov['target_rtd']) && $mov['target_rtd'] !== '' ? $mov['target_rtd'] : null,
                            'work_location_id' => $request->work_location_id ?? null,
                            'start_time' => $request->start_time ?? null,
                            'operational_segment_id' => $request->operational_segment_id ?? null,
                            'tyreman_1' => $request->tyreman_1 ?? null,
                            'tyreman_2' => $request->tyreman_2 ?? null,
                            'notes' => 'Rotation Swap ke ' . $position->position_code . '.',
                            'created_by' => Auth::id(),
                            'photo' => $photoTargetPath,
                        ]);

                        $sourceTyre->update([
                            'current_position_id' => $mov['target_position_id'],
                            'total_lifetime_km' => ($sourceTyre->total_lifetime_km ?? 0) + $kmDiffSrc,
                            'total_lifetime_hm' => ($sourceTyre->total_lifetime_hm ?? 0) + $hmDiffSrc,
                            'current_tread_depth' => isset($mov['rtd']) && $mov['rtd'] !== '' ? $mov['rtd'] : $sourceTyre->current_tread_depth,
                            'current_km' => $request->odometer ?? 0,
                            'current_hm' => $request->hour_meter ?? 0,
                        ]);

                        $targetTyre->update([
                            'current_position_id' => $mov['position_id'],
                            'total_lifetime_km' => ($targetTyre->total_lifetime_km ?? 0) + $kmDiffTgt,
                            'total_lifetime_hm' => ($targetTyre->total_lifetime_hm ?? 0) + $hmDiffTgt,
                            'current_tread_depth' => isset($mov['target_rtd']) && $mov['target_rtd'] !== '' ? $mov['target_rtd'] : $targetTyre->current_tread_depth,
                            'current_km' => $request->odometer ?? 0,
                            'current_hm' => $request->hour_meter ?? 0,
                        ]);


                    } else {
                        // MOVE
                        TyreMovement::create([
                            'tyre_id' => $sourceTyre->id,
                            'vehicle_id' => $request->vehicle_id,
                            'position_id' => $mov['target_position_id'],
                            'movement_type' => 'Rotation',
                            'movement_date' => $request->movement_date,
                            'odometer_reading' => $request->odometer,
                            'hour_meter_reading' => $request->hour_meter,
                            'running_km' => $kmDiffSrc,
                            'running_hm' => $hmDiffSrc,
                            'psi_reading' => $mov['psi'] ?? null,
                            'rtd_reading' => isset($mov['rtd']) && $mov['rtd'] !== '' ? $mov['rtd'] : null,
                            'work_location_id' => $request->work_location_id ?? null,
                            'start_time' => $request->start_time ?? null,
                            'operational_segment_id' => $request->operational_segment_id ?? null,
                            'tyreman_1' => $request->tyreman_1 ?? null,
                            'tyreman_2' => $request->tyreman_2 ?? null,
                            'notes' => 'Rotation Pindah ke ' . $targetPosition->position_code . '. ' . ($mov['notes'] ?? ''),
                            'created_by' => Auth::id(),
                            'photo' => $photoPath,
                        ]);

                        $sourceTyre->update([
                            'current_position_id' => $mov['target_position_id'],
                            'total_lifetime_km' => ($sourceTyre->total_lifetime_km ?? 0) + $kmDiffSrc,
                            'total_lifetime_hm' => ($sourceTyre->total_lifetime_hm ?? 0) + $hmDiffSrc,
                            'current_tread_depth' => isset($mov['rtd']) && $mov['rtd'] !== '' ? $mov['rtd'] : $sourceTyre->current_tread_depth,
                            'current_km' => $request->odometer ?? 0,
                            'current_hm' => $request->hour_meter ?? 0,
                        ]);

                    }
                }
            }

            if (!empty($warnings)) {
                DB::rollBack();
                setLogActivity(Auth::id(), 'Deteksi Human Error: Bulk Transaksi Ban unit ' . $vehicleCode, [
                    'action_type' => 'error',
                    'module' => 'Human Error',
                    'data_after' => ['Kendaraan' => $vehicleCode, 'Pesan Error' => $warnings]
                ]);
                return response()->json([
                    'success' => false,
                    'message' => "Transaksi GAGAL DISIMPAN:\n\n" . implode("\n", $warnings)
                ], 422);
            }

            DB::commit();

            // Auto-sync all bulk movements with Tyre Monitoring
            try {
                foreach ($movements as $mov) {
                    $mType = $mov['type'] ?? null;
                    $pId = $mov['position_id'] ?? null;
                    $tId = $mov['tyre_id'] ?? null;

                    if ($mType === 'Installation' && $tId && $pId) {
                        \App\Services\TyreMonitoringSyncService::syncInstallation(
                            $request->vehicle_id,
                            $pId,
                            $tId,
                            array_merge($request->all(), $mov, [
                                'movement_date' => $request->movement_date,
                                'odometer_reading' => $request->odometer,
                                'hour_meter_reading' => $request->hour_meter
                            ])
                        );
                    } elseif ($mType === 'Removal' && $pId) {
                        $remTyre = Tyre::withoutGlobalScopes()->where('id', $tId)->orWhere(function($q) use ($request, $pId) {
                            $q->where('current_vehicle_id', $request->vehicle_id)->where('current_position_id', $pId);
                        })->first();
                        if ($remTyre) {
                            \App\Services\TyreMonitoringSyncService::syncRemoval(
                                $request->vehicle_id,
                                $pId,
                                $remTyre->id,
                                array_merge($request->all(), $mov)
                            );
                        }
                    } elseif ($mType === 'Rotation' && $pId && !empty($mov['target_position_id'])) {
                        $srcT = Tyre::withoutGlobalScopes()->where('id', $tId)->first();
                        $tgtT = !empty($mov['target_tyre_id']) ? Tyre::withoutGlobalScopes()->find($mov['target_tyre_id']) : null;
                        if ($srcT) {
                            \App\Services\TyreMonitoringSyncService::syncRotation(
                                $request->vehicle_id,
                                $pId,
                                $mov['target_position_id'],
                                $srcT->id,
                                $tgtT ? $tgtT->id : null,
                                array_merge($request->all(), $mov)
                            );
                        }
                    }
                }
            } catch (\Exception $bulkSyncEx) {
                \Illuminate\Support\Facades\Log::error("Tyre Movement Bulk -> Monitoring Auto-Sync Error: " . $bulkSyncEx->getMessage());
            }

            \Illuminate\Support\Facades\Cache::flush();

            setLogActivity(Auth::id(), 'Bulk Transaksi Ban pada unit ' . $vehicleCode, [
                'action_type' => 'create',
                'module' => 'Tyre Movement',
                'data_after' => ['Total Proses' => count($movements), 'Kendaraan' => $vehicleCode]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Semua transaksi massal berhasil disimpan.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiHistory(Request $request)
    {
        $query = TyreMovement::with([
            'tyre.company',
            'tyre.brand',
            'vehicle.company',
            'position',
            'failureCode',
            'workLocation',
            'segment'
        ]);

        // Company scope: filter movements by vehicle's company or active company
        $resolvedCompanyId = \App\Helpers\SessionCompanyHelper::getActiveCompanyId();
        if ($resolvedCompanyId !== null) {
            if (is_array($resolvedCompanyId)) {
                $query->whereHas('vehicle', fn($q) => $q->whereIn('tyre_company_id', $resolvedCompanyId));
            } else {
                $query->whereHas('vehicle', fn($q) => $q->where('tyre_company_id', $resolvedCompanyId));
            }
        }

        $totalRecords = (clone $query)->count();

        // Search logic
        if ($request->has('search') && $request->input('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('movement_type', 'like', "%$searchValue%")
                    ->orWhereHas('tyre', function ($sub) use ($searchValue) {
                        $sub->where('serial_number', 'like', "%$searchValue%")
                            ->orWhere('custom_serial_number', 'like', "%$searchValue%");
                    })
                    ->orWhereHas('vehicle', function ($sub) use ($searchValue) {
                        $sub->where('kode_kendaraan', 'like', "%$searchValue%")
                            ->orWhere('no_polisi', 'like', "%$searchValue%");
                    })
                    ->orWhereHas('workLocation', function ($sub) use ($searchValue) {
                        $sub->where('location_name', 'like', "%$searchValue%");
                    });
            });
        }

        $filteredRecords = $query->count();

        // Ordering
        $query->orderBy('created_at', 'desc');

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $movements = $query->skip($start)->take($length)->get();

        $data = $movements->map(function ($row) {
            $failureInfo = '-';
            if ($row->failureCode) {
                $failureInfo = $row->failureCode->display_name ?: ($row->failureCode->failure_code . ' - ' . $row->failureCode->failure_name);
            }

            // 1. Vehicle info + Company
            $vehicleCompany = $row->vehicle && $row->vehicle->company ? $row->vehicle->company->company_name : null;
            $vehCode = $row->vehicle ? $row->vehicle->kode_kendaraan : '-';
            if ($row->vehicle && $row->vehicle->no_polisi) {
                $vehCode .= ' <span class="text-muted small">[' . e($row->vehicle->no_polisi) . ']</span>';
            }
            if ($vehicleCompany) {
                $vehCode .= '<br><span class="badge bg-label-info text-dark mt-1" style="font-size:0.7rem;"><i class="ri-building-line me-1"></i>' . e($vehicleCompany) . '</span>';
            }

            // 2. Tyre SN + Custom Code + Stock Owner Company
            $tyreCompany = $row->tyre && $row->tyre->company ? $row->tyre->company->company_name : null;
            $tyreSn = $row->tyre ? '<strong>' . e($row->tyre->serial_number) . '</strong>' : '-';
            if ($row->tyre && $row->tyre->custom_serial_number) {
                $tyreSn .= '<br><span class="badge bg-label-secondary font-monospace" style="font-size:0.68rem;"><i class="ri-hashtag me-1"></i>' . e($row->tyre->custom_serial_number) . '</span>';
            }
            if ($tyreCompany) {
                $isParent = ($row->tyre->company && $row->tyre->company->parent_company_id === null && $row->tyre->company->children()->count() > 0);
                $compBadgeClass = $isParent ? 'bg-label-primary' : 'bg-label-dark';
                $tyreSn .= '<br><span class="badge ' . $compBadgeClass . ' mt-1" style="font-size:0.68rem;"><i class="ri-store-2-line me-1"></i>Stok: ' . e($tyreCompany) . '</span>';
            }

            // 3. Work Location + Segment
            $locName = $row->workLocation ? '<strong>' . e($row->workLocation->location_name) . '</strong>' : '-';
            if ($row->segment) {
                $locName .= '<br><small class="text-muted"><i class="ri-road-map-line me-1"></i>' . e($row->segment->segment_name) . '</small>';
            }

            return [
                'id' => $row->id,
                'movement_date' => \Carbon\Carbon::parse($row->movement_date)->format('d/m/Y'),
                'movement_type' => $row->movement_type,
                'movement_type_display' => $row->movement_type === 'Installation' ? 'Pasang' : ($row->movement_type === 'Removal' ? 'Lepas' : ($row->movement_type === 'Rotation' ? 'Rotasi' : 'Inspeksi')),
                'install_condition' => $row->install_condition,
                'is_replacement' => $row->is_replacement,
                'tyre_sn' => $tyreSn,
                'vehicle_code' => $vehCode,
                'position_name' => $row->position ? $row->position->position_code . ' - ' . $row->position->position_name : '-',
                'start_time' => $row->start_time ?? '-',
                'tyreman' => $row->tyreman_1 ?? '-',
                'work_location' => $locName,
                'failure_info' => $failureInfo,
                'action' => '<button type="button" class="btn btn-sm btn-info me-1" onclick="viewMovementDetail(' . $row->id . ')" title="Lihat Detail & Foto"><i class="icon-base ri ri-eye-line"></i> Detail</button>' . 
                    ((!auth()->user()->tyre_company_id || auth()->user()->tyre_company_id == 1 || auth()->user()->role_id == 1 || \App\Helpers\SessionCompanyHelper::isWorkshopAdmin()) 
                    ? '<button type="button" class="btn btn-sm btn-danger" onclick="rollbackMovement(' . $row->id . ')" title="Rollback Transaksi"><i class="icon-base ri ri-history-line"></i> Rollback</button>'
                    : '')
            ];
        });

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalRecords),
            "recordsFiltered" => intval($filteredRecords),
            "data" => $data
        ]);
    }

    public function show($id)
    {
        try {
            $movement = TyreMovement::with([
                'tyre.brand', 'tyre.pattern', 'tyre.size', 'tyre.company',
                'vehicle.company',
                'position', 'failureCode', 'workLocation', 'segment'
            ])->findOrFail($id);
            $html = view('tyre-performance.movement.partials._movement_detail', compact('movement'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            \Log::error('Movement Detail Error: ' . $e->getMessage() . ' on line ' . $e->getLine() . ' in ' . $e->getFile());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rollback($id)
    {
        DB::beginTransaction();
        try {
            $movement = TyreMovement::findOrFail($id);

            // Company scope validation
            $user = auth()->user();
            $isInternal = ($user && ($user->role_id == 1 || $user->tyre_company_id == 1));
            if (!$isInternal) {
                $vehicle = MasterImportKendaraan::withoutGlobalScope('company')->find($movement->vehicle_id);
                $vehicleCompanyId = $vehicle ? $vehicle->tyre_company_id : null;
                
                $isWorkshopAdmin = \App\Helpers\SessionCompanyHelper::isWorkshopAdmin();
                $hasAccess = ($vehicleCompanyId == $user->tyre_company_id) 
                    || ($isWorkshopAdmin && \App\Helpers\SessionCompanyHelper::isValidClient($vehicleCompanyId));
                
                if (!$hasAccess) {
                    throw new \Exception('Akses Ditolak: Anda tidak memiliki izin untuk rollback transaksi perusahaan lain.');
                }
            }

            $tyre = Tyre::find($movement->tyre_id);

            if (!$tyre) {
                throw new \Exception('Data ban tidak ditemukan.');
            }

            $position = TyrePositionDetail::find($movement->position_id);

            if ($movement->movement_type === 'Installation') {
                // LOGIC: Undo Installation (Remove from vehicle → Return to stock)

                // Determine original status before installation from install_condition field
                // install_condition: 'New' → status was 'New', 'Repair' → status was 'Repaired'
                $originalStatus = 'New'; // Safe default
                if ($movement->install_condition === 'Repair') {
                    $originalStatus = 'Repaired';
                } elseif ($movement->install_condition === 'New') {
                    $originalStatus = 'New';
                }

                // 1. Reset Tyre Status to original pre-installation state
                $tyre->update([
                    'current_vehicle_id' => null,
                    'current_position_id' => null,
                    'is_in_warehouse' => true,
                    'current_location_id' => $movement->work_location_id,
                    'status' => $originalStatus,
                ]);

                // 2. Increment Stock at that location (assuming it goes back to inventory)
                if ($movement->work_location_id) {
                    DB::table('tyre_locations')->where('id', $movement->work_location_id)->increment('current_stock');
                }

                // 3. Free the position (if it still exists)
                if ($position) {
                }
            } elseif ($movement->movement_type === 'Rotation') {
                // LOGIC: Undo Rotation
                
                // Find previous movement for this tyre to know former position
                $prevMov = TyreMovement::where('tyre_id', $movement->tyre_id)
                    ->where('id', '<', $movement->id)
                    ->orderBy('id', 'desc')
                    ->first();
                
                if (!$prevMov || $prevMov->vehicle_id != $movement->vehicle_id) {
                    throw new \Exception("Gagal mendeteksi posisi asal ban. Riwayat sebelumnya mencatat ban tidak berada pada unit ini.");
                }

                $oldPosId = $prevMov->position_id;
                $oldPos = TyrePositionDetail::find($oldPosId);

                if (!$oldPos) {
                    throw new \Exception("Posisi asal ban sudah tidak valid atau dihapus.");
                }

                // Check for occupation at old position
                $occupier = Tyre::where('current_vehicle_id', $movement->vehicle_id)->where('current_position_id', $oldPos->id)->first();
                if ($occupier && $occupier->id != $tyre->id) {
                    // Check if the occupier is a sibling rotation (Swap case)
                    $sibling = TyreMovement::where('vehicle_id', $movement->vehicle_id)
                        ->where('movement_date', $movement->movement_date)
                        ->where('odometer_reading', $movement->odometer_reading)
                        ->where('movement_type', 'Rotation')
                        ->where('tyre_id', $occupier->id)
                        ->where('id', '!=', $movement->id)
                        ->first();
                    
                    if (!$sibling) {
                        throw new \Exception("Posisi asal ban ({$oldPos->position_code}) sekarang sedang diisi oleh ban lain (SN: " . ($occupier->serial_number ?? '?') . "). Rollback dibatalkan.");
                    }
                    
                    // It's a SWAP. We should probably rollback the sibling too.
                    // But to avoid recursion/complexity, let's just swap them back in one go if this is the source log.
                }

                // 1. Return tyre to old position
                $tyre->update([
                    'current_position_id' => $oldPosId,
                    'total_lifetime_km' => max(0, ($tyre->total_lifetime_km ?? 0) - ($movement->running_km ?? 0)),
                    'total_lifetime_hm' => max(0, ($tyre->total_lifetime_hm ?? 0) - ($movement->running_hm ?? 0)),
                ]);

                // 2. Clear current position
                if ($position) {
                }

                // 3. Occupy old position

            } else {
                // Removal
                // ... (existing Removal logic)
                if (!$position) {
                    throw new \Exception("Posisi ban (ID: {$movement->position_id}) tidak ditemukan dalam database. Tidak dapat mengembalikan ban ke posisi yang sudah dihapus.");
                }

                // Check if position is currently occupied by another tyre
                $occupier = Tyre::where('current_vehicle_id', $movement->vehicle_id)->where('current_position_id', $position->id)->first();
                if ($occupier && $occupier->id != $tyre->id) {
                    throw new \Exception("Posisi ini sekarang sedang diisi oleh ban lain (SN: {$occupier->serial_number}). Rollback dibatalkan untuk mencegah konflik.");
                }

                // 1. Put Tyre back on Vehicle
                $tyre->update([
                    'current_vehicle_id' => $movement->vehicle_id,
                    'current_position_id' => $movement->position_id,
                    'is_in_warehouse' => false,
                    'current_location_id' => null,
                    'status' => 'Installed',
                    'total_lifetime_km' => max(0, ($tyre->total_lifetime_km ?? 0) - ($movement->running_km ?? 0)),
                    'total_lifetime_hm' => max(0, ($tyre->total_lifetime_hm ?? 0) - ($movement->running_hm ?? 0)),
                ]);

                // 2. Decrement Stock at the warehouse location (it's leaving the warehouse to go back on vehicle)
                if ($movement->work_location_id && $movement->target_status !== 'Scrap') {
                    DB::table('tyre_locations')->where('id', $movement->work_location_id)->decrement('current_stock');
                }

                // 3. Occupy the position
            }

            // Delete the log
            $movement->delete();

            DB::commit();

            setLogActivity(Auth::id(), 'Rollback ' . $movement->movement_type . ' ban SN: ' . ($tyre->serial_number ?? '-'), [
                'action_type' => 'delete',
                'module' => 'Tyre Movement',
                'data_before' => [
                    'movement_type' => $movement->movement_type,
                    'tyre_sn' => $tyre->serial_number ?? '-',
                    'movement_date' => $movement->movement_date,
                ]
            ]);

            return response()->json(['success' => true, 'message' => 'Transaksi berhasil di-rollback.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal Rollback: ' . $e->getMessage()], 500);
        }
    }
}
