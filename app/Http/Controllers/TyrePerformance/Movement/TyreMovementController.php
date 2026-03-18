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

class TyreMovementController extends Controller
{
    public function setActiveCompany(Request $request)
    {
        $companyId = $request->input('tyre_company_id');
        
        if ($companyId == 0) {
            session()->forget('active_company_id');
            return response()->json(['success' => true, 'message' => 'Filter perusahaan dibersihkan (Global View)']);
        }
        
        $company = TyreCompany::findOrFail($companyId);
        session(['active_company_id' => $company->id]);
        
        return response()->json(['success' => true, 'message' => 'Filter aktif: ' . $company->company_name]);
    }

    public function index()
    {
        $kendaraans = MasterImportKendaraan::whereNotNull('tyre_position_configuration_id')
            ->select('id', 'kode_kendaraan', 'no_polisi')
            ->get();
        return view('tyre-performance.movement.index', compact('kendaraans'));
    }

    public function pemasangan()
    {
        $kendaraans = MasterImportKendaraan::whereNotNull('tyre_position_configuration_id')
            ->select('id', 'kode_kendaraan', 'no_polisi')
            ->get();
        // Removed eager loading of all tyres to avoid memory bloat
        // Available tyres will be fetched via AJAX search
        $availableTyres = collect();
        $locations = \App\Models\TyreLocation::all();
        $segments = \App\Models\TyreSegment::where('status', 'Active')->get();
        return view('tyre-performance.movement.pemasangan', compact('kendaraans', 'availableTyres', 'segments', 'locations'));
    }

    public function searchTyres(Request $request)
    {
        $search = $request->input('q');

        $query = Tyre::whereIn('status', ['New', 'Repaired'])
            ->whereNull('current_vehicle_id');

        if ($search) {
            $query->where('serial_number', 'like', "%$search%");
        }

        $tyres = $query->with(['brand', 'size', 'pattern', 'latestInstallation'])
            ->limit(20)
            ->get();

        $results = $tyres->map(function ($tyre) {
            return [
                'id' => $tyre->id,
                'text' => $tyre->serial_number,
                'brand' => $tyre->brand->brand_name ?? '-',
                'pattern' => $tyre->pattern->name ?? '-',
                'size' => $tyre->size->size ?? '-',
                'sn' => $tyre->serial_number,
                'otd' => $tyre->initial_tread_depth,
                'rtd' => $tyre->current_tread_depth,
                'location_id' => $tyre->work_location_id,
                'status' => $tyre->status,
                'latest_rim_size' => $tyre->latestInstallation->rim_size ?? null,
                'latest_segment_id' => $tyre->latestInstallation->operational_segment_id ?? null,
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function pelepasan()
    {
        $kendaraans = MasterImportKendaraan::whereNotNull('tyre_position_configuration_id')
            ->whereHas('tyres') // Only vehicles with tyres
            ->select('id', 'kode_kendaraan', 'no_polisi')
            ->get();
        $failureCodes = TyreFailureCode::where('status', 'Active')->select('id', 'failure_name', 'failure_code')->get();
        $locations = \App\Models\TyreLocation::all();
        $segments = \App\Models\TyreSegment::where('status', 'Active')->get();
        return view('tyre-performance.movement.pelepasan', compact('kendaraans', 'failureCodes', 'segments', 'locations'));
    }

    public function rotasi()
    {
        $kendaraans = MasterImportKendaraan::whereNotNull('tyre_position_configuration_id')
            ->whereHas('tyres')
            ->select('id', 'kode_kendaraan', 'no_polisi')
            ->get();
        $locations = \App\Models\TyreLocation::all();
        $segments = \App\Models\TyreSegment::where('status', 'Active')->get();
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
        $vehicle = MasterImportKendaraan::with('segment')->findOrFail($id);

        // Fetch latest readings from Movement and Examination
        $lastMovement = TyreMovement::where('vehicle_id', $id)
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $lastExamination = TyreExamination::where('vehicle_id', $id)
            ->orderBy('examination_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $lastOdo = 0;
        $lastHm = 0;

        if ($lastMovement && $lastExamination) {
            $movDate = Carbon::parse($lastMovement->movement_date);
            $examDate = Carbon::parse($lastExamination->examination_date);

            if ($movDate->gt($examDate)) {
                $lastOdo = $lastMovement->odometer_reading;
                $lastHm = $lastMovement->hour_meter_reading;
            } else {
                $lastOdo = $lastExamination->odometer;
                $lastHm = $lastExamination->hour_meter;
            }
        } elseif ($lastMovement) {
            $lastOdo = $lastMovement->odometer_reading;
            $lastHm = $lastMovement->hour_meter_reading;
        } elseif ($lastExamination) {
            $lastOdo = $lastExamination->odometer;
            $lastHm = $lastExamination->hour_meter;
        }

        return response()->json([
            'vehicle' => $vehicle,
            'last_odometer' => $lastOdo,
            'last_hour_meter' => $lastHm
        ]);
    }

    public function getPositionInfo(Request $request)
    {
        $vehicleId = $request->vehicle_id;
        // Check if we need available tyres specifically for a position
        // The frontend sends vehicle_id and position_id for the quick form

        if ($request->has('position_id')) {
            // For Quick Form Installation: We need list of available tyres
            $availableTyres = Tyre::whereIn('status', ['New', 'Repaired'])
                ->with(['brand', 'size'])
                ->get();

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
            $tyre = Tyre::with(['brand', 'size', 'pattern', 'segment', 'location'])->find($tyreId);
        } elseif ($positionId && $vehicleId) {
            $tyre = Tyre::with(['brand', 'size', 'pattern', 'segment', 'location'])
                ->where('current_vehicle_id', $vehicleId)
                ->where('current_position_id', $positionId)
                ->first();
        }

        if (!$tyre) {
            return response()->json(['success' => false, 'message' => 'Ban tidak ditemukan di posisi ini.'], 404);
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

        return response()->json([
            'success' => true,
            'tyre' => [
                'id' => $tyre->id,
                'serial_number' => $tyre->serial_number,
                'status' => $tyre->status,
                'brand' => $tyre->brand->brand_name ?? '-',
                'size' => $tyre->size->size ?? '-',
                'pattern' => $tyre->pattern->name ?? '-',
                'segment' => $tyre->segment->segment_name ?? '-',
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
        if (!$currentReading || !$lastInstallReading)
            return 0;

        $diff = $currentReading - $lastInstallReading;

        if ($diff < 0) {
            // Odometer reset or replaced. 
            // Logic: Assume the current reading is the distance covered since reset.
            return (float) $currentReading;
        }

        return (float) $diff;
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
            'work_location_id' => 'nullable|exists:tyre_locations,id',
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

            // --- DETEKSI ANOMALI ODO/HM (Human Error Check) ---
            $lastVehicleMov = TyreMovement::where('vehicle_id', $request->vehicle_id)
                ->whereIn('movement_type', ['Installation', 'Removal', 'Inspection', 'Rotation'])
                ->orderBy('movement_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$request->is_meter_reset) {
                if ($lastVehicleMov && $request->odometer) {
                    if ($request->odometer < $lastVehicleMov->odometer_reading) {
                        $warnings[] = "Odometer Unit " . $vehicleCode . " ({$request->odometer}) menurun drastis dari catatan terakhir ({$lastVehicleMov->odometer_reading}). Hilangkan centang 'Reset Meter' jika ini adalah kesalahan ketik.";
                    }
                }

                if ($lastVehicleMov && $request->hour_meter) {
                    if ($request->hour_meter < $lastVehicleMov->hour_meter_reading) {
                        $warnings[] = "Hour Meter Unit " . $vehicleCode . " ({$request->hour_meter}) menurun drastis dari catatan terakhir ({$lastVehicleMov->hour_meter_reading}). Hilangkan centang 'Reset Meter' jika ini adalah kesalahan ketik.";
                    }
                }
            }

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
                        'current_km' => $request->odometer,
                        'current_hm' => $request->hour_meter,
                    ]);

                    $targetTyre->update([
                        'current_position_id' => $request->position_id,
                        'total_lifetime_km' => ($targetTyre->total_lifetime_km ?? 0) + $kmDiffTgt,
                        'total_lifetime_hm' => ($targetTyre->total_lifetime_hm ?? 0) + $hmDiffTgt,
                        'current_tread_depth' => $request->target_rtd_reading ?? $targetTyre->current_tread_depth,
                        'current_km' => $request->odometer,
                        'current_hm' => $request->hour_meter,
                    ]);

                    // 4. Update Position Details
                    $position->update(['tyre_id' => $targetTyre->id]);
                    $targetPosition->update(['tyre_id' => $sourceTyre->id]);

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
                        'current_km' => $request->odometer,
                        'current_hm' => $request->hour_meter,
                    ]);

                    // 3. Update Position Details
                    $position->update(['tyre_id' => null]);
                    $targetPosition->update(['tyre_id' => $sourceTyre->id]);
                }
                
                $tyre = $sourceTyre; // For potential use in logging below

            } elseif ($request->movement_type === 'Installation') {
                $tyre = Tyre::findOrFail($request->tyre_id);
                $oldLocationId = $tyre->work_location_id; // Store old location before update

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
                if ($position->tyre_id) {
                    $isReplacement = true;

                    $oldTyre = Tyre::find($position->tyre_id);
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
                        $oldTyre->update([
                            'current_vehicle_id' => null,
                            'current_position_id' => null,
                            'status' => 'Repaired',
                            'work_location_id' => $request->work_location_id, // Masuk ke gudang pengerjaan
                            'total_lifetime_km' => ($oldTyre->total_lifetime_km ?? 0) + $kmDiff,
                            'total_lifetime_hm' => ($oldTyre->total_lifetime_hm ?? 0) + $hmDiff,
                            'current_km' => $request->odometer,
                            'current_hm' => $request->hour_meter,
                        ]);

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

                $tyre->update([
                    'current_vehicle_id' => $request->vehicle_id,
                    'current_position_id' => $request->position_id,
                    'status' => 'Installed',
                    'current_tread_depth' => $request->rtd_reading ?? $tyre->current_tread_depth,
                    'current_km' => $request->odometer,
                    'current_hm' => $request->hour_meter,
                ]);

                // 2. Update Position Detail (Secondary sync)
                $position->update(['tyre_id' => $tyre->id]);

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

                $tyre->update([
                    'current_vehicle_id' => null,
                    'current_position_id' => null,
                    'status' => $request->target_status ?? 'Repaired',
                    'work_location_id' => $request->work_location_id, // Update lokasi fisik ban
                    'total_lifetime_km' => ($tyre->total_lifetime_km ?? 0) + $kmDiff,
                    'total_lifetime_hm' => ($tyre->total_lifetime_hm ?? 0) + $hmDiff,
                    'current_tread_depth' => $request->rtd_reading ?? $tyre->current_tread_depth
                ]);

                // 3. Increase stock at new location (tyre entering warehouse)
                if ($request->work_location_id) {
                    DB::table('tyre_locations')
                        ->where('id', $request->work_location_id)
                        ->increment('current_stock');
                }

                // 4. Clear Position Detail
                $position->update(['tyre_id' => null]);
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

    public function apiHistory(Request $request)
    {
        $query = TyreMovement::with(['tyre', 'vehicle', 'position', 'failureCode']);

        $totalRecords = TyreMovement::count();

        // Search logic
        if ($request->has('search') && $request->input('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('movement_type', 'like', "%$searchValue%")
                    ->orWhereHas('tyre', function ($sub) use ($searchValue) {
                        $sub->where('serial_number', 'like', "%$searchValue%");
                    })
                    ->orWhereHas('vehicle', function ($sub) use ($searchValue) {
                        $sub->where('kode_kendaraan', 'like', "%$searchValue%");
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

            return [
                'id' => $row->id,
                'movement_date' => \Carbon\Carbon::parse($row->movement_date)->format('d/m/Y'),
                'movement_type' => $row->movement_type,
                'movement_type_display' => $row->movement_type === 'Installation' ? 'Pasang' : ($row->movement_type === 'Removal' ? 'Lepas' : ($row->movement_type === 'Rotation' ? 'Rotasi' : 'Inspeksi')),
                'install_condition' => $row->install_condition,
                'is_replacement' => $row->is_replacement,
                'tyre_sn' => $row->tyre->serial_number ?? '-',
                'vehicle_code' => $row->vehicle->kode_kendaraan ?? '-',
                'position_name' => $row->position ? $row->position->position_code . ' - ' . $row->position->position_name : '-',
                'failure_info' => $failureInfo,
                'action' => (auth()->user()->tyre_company_id) 
                    ? '<button type="button" class="btn btn-sm btn-danger" onclick="rollbackMovement(' . $row->id . ')"><i class="icon-base ri ri-history-line"></i> Rollback</button>'
                    : '<span class="text-muted small">No Action</span>'
            ];
        });

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalRecords),
            "recordsFiltered" => intval($filteredRecords),
            "data" => $data
        ]);
    }

    public function rollback($id)
    {
        DB::beginTransaction();
        try {
            $movement = TyreMovement::findOrFail($id);
            $tyre = Tyre::find($movement->tyre_id);

            if (!$tyre) {
                throw new \Exception('Data ban tidak ditemukan.');
            }

            $position = TyrePositionDetail::find($movement->position_id);

            if ($movement->movement_type === 'Installation') {
                // LOGIC: Undo Installation (Remove from vehicle → Return to stock)

                // 1. Reset Tyre Status to "New" or available state
                $tyre->update([
                    'current_vehicle_id' => null,
                    'current_position_id' => null,
                    'status' => 'New',
                    // Return tracking to the location where the installation happened
                    'work_location_id' => $movement->work_location_id
                ]);

                // 2. Increment Stock at that location (assuming it goes back to inventory)
                if ($movement->work_location_id) {
                    DB::table('tyre_locations')->where('id', $movement->work_location_id)->increment('current_stock');
                }

                // 3. Free the position (if it still exists)
                if ($position) {
                    $position->update(['tyre_id' => null]);
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
                if ($oldPos->tyre_id && $oldPos->tyre_id != $tyre->id) {
                    // Check if the occupier is a sibling rotation (Swap case)
                    $sibling = TyreMovement::where('vehicle_id', $movement->vehicle_id)
                        ->where('movement_date', $movement->movement_date)
                        ->where('odometer_reading', $movement->odometer_reading)
                        ->where('movement_type', 'Rotation')
                        ->where('tyre_id', $oldPos->tyre_id)
                        ->where('id', '!=', $movement->id)
                        ->first();
                    
                    if (!$sibling) {
                        $occupier = Tyre::find($oldPos->tyre_id);
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
                    $position->update(['tyre_id' => null]);
                }

                // 3. Occupy old position
                $oldPos->update(['tyre_id' => $tyre->id]);

            } else {
                // Removal
                // ... (existing Removal logic)
                if (!$position) {
                    throw new \Exception("Posisi ban (ID: {$movement->position_id}) tidak ditemukan dalam database. Tidak dapat mengembalikan ban ke posisi yang sudah dihapus.");
                }

                // Check if position is currently occupied by another tyre
                if ($position->tyre_id && $position->tyre_id != $tyre->id) {
                    // Check if the tyre occupying it is actually valid
                    $occupier = Tyre::find($position->tyre_id);
                    if ($occupier) {
                        throw new \Exception("Posisi ini sekarang sedang diisi oleh ban lain (SN: {$occupier->serial_number}). Rollback dibatalkan untuk mencegah konflik.");
                    }
                }

                // 1. Put Tyre back on Vehicle
                $tyre->update([
                    'current_vehicle_id' => $movement->vehicle_id,
                    'current_position_id' => $movement->position_id,
                    'status' => 'Installed',
                    'total_lifetime_km' => max(0, ($tyre->total_lifetime_km ?? 0) - ($movement->running_km ?? 0)),
                    'total_lifetime_hm' => max(0, ($tyre->total_lifetime_hm ?? 0) - ($movement->running_hm ?? 0)),
                ]);

                // 2. Decrement Stock at the warehouse location (it's leaving the warehouse to go back on vehicle)
                if ($movement->work_location_id) {
                    DB::table('tyre_locations')->where('id', $movement->work_location_id)->decrement('current_stock');
                }

                // 3. Occupy the position
                $position->update(['tyre_id' => $tyre->id]);
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
