<?php

namespace App\Http\Controllers\TyrePerformance\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\Tyre;
use App\Models\TyreMonitoringVehicle;
use App\Models\TyreMonitoringSession;
use App\Models\TyreMonitoringInstallation;
use App\Models\TyreMonitoringCheck;
use App\Models\TyreMonitoringRemoval;
use App\Models\TyreMovement;
use App\Models\MasterImportKendaraan;
use App\Models\TyrePositionDetail;
use App\Models\TyreBrand;
use App\Models\TyrePattern;
use App\Models\TyreSize;
use App\Models\TyreLocation;
use App\Models\TyrePositionConfiguration;
use App\Exports\Monitoring\SessionExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class MonitoringController extends Controller
{
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

    public function index()
    {
        $vehicles = TyreMonitoringVehicle::withCount(['sessions' => function($q) {
            $q->where('status', 'active');
        }])->latest()->get();

        $masterVehicles = MasterImportKendaraan::select('id', 'no_polisi', 'kode_kendaraan', 'payload_capacity', 'total_tyre_position')->get();
        
        return view('tyre-performance.monitoring.index', compact('vehicles', 'masterVehicles'));
    }

    public function getMasterVehicleDetails($id)
    {
        $vehicle = MasterImportKendaraan::with(['tyrePositionConfiguration.details'])->findOrFail($id);
        
        // Fetch tyres currently installed on this vehicle
        $tyres = Tyre::where('current_vehicle_id', $id)
            ->with(['brand', 'pattern', 'size'])
            ->get()
            ->keyBy('current_position_id');

        $masterPositions = [];
        $html = '';
        
        if ($vehicle->tyre_position_configuration_id && $vehicle->tyrePositionConfiguration) {
            $masterPositions = TyrePositionDetail::where('configuration_id', $vehicle->tyre_position_configuration_id)
                ->orderBy('display_order')
                ->get();

            // Render the visual layout partial
            $html = view('tyre-performance.movement._vehicle_layout', [
                'configuration' => $vehicle->tyrePositionConfiguration,
                'assignedTyres' => $tyres
            ])->render();
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'payload_capacity' => $vehicle->payload_capacity,
                'total_tyre_position' => $vehicle->total_tyre_position,
                'configuration' => $vehicle->tyrePositionConfiguration,
                'positions' => $masterPositions,
                'tyres' => $tyres,
                'layout_html' => $html
            ]
        ]);
    }

    public function destroyVehicle($id)
    {
        $vehicle = TyreMonitoringVehicle::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Delete related sessions, installations, and checks if necessary
            // Or just rely on database level cascade if set up.
            // For safety, let's just delete the vehicle.
            $vehicle->delete();
            
            DB::commit();
            return redirect()->back()->with('success', 'Monitoring Vehicle and its history deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error deleting vehicle: ' . $e->getMessage());
        }
    }

    public function showVehicle($id)
    {
        $vehicle = TyreMonitoringVehicle::findOrFail($id);
        $sessions = TyreMonitoringSession::where('vehicle_id', $id)->withCount('checks')->latest()->get();
        
        $masterPositions = [];
        $assignedTyres = collect();
        $configuration = null;

        if ($vehicle->master_vehicle_id) {
            $master = MasterImportKendaraan::find($vehicle->master_vehicle_id);
            if ($master && $master->tyre_position_configuration_id) {
                $configuration = TyrePositionConfiguration::with('details')->find($master->tyre_position_configuration_id);
                $masterPositions = TyrePositionDetail::where('configuration_id', $master->tyre_position_configuration_id)
                    ->orderBy('display_order')
                    ->get();
                
                $assignedTyres = Tyre::where('current_vehicle_id', $vehicle->master_vehicle_id)
                    ->with(['brand', 'pattern', 'size'])
                    ->get()
                    ->keyBy('current_position_id');
            }
        }

        $brands = TyreBrand::orderBy('brand_name')->get();
        $patterns = TyrePattern::orderBy('name')->get();
        $sizes = TyreSize::orderBy('size')->get();

        return view('tyre-performance.monitoring.vehicle_sessions', compact(
            'vehicle', 
            'sessions', 
            'masterPositions', 
            'assignedTyres', 
            'configuration',
            'brands',
            'patterns',
            'sizes'
        ));
    }

    public function showSession($id)
    {
        $session = TyreMonitoringSession::with(['vehicle.sessions', 'installations.positionDetail', 'checks', 'removal'])->findOrFail($id);

        $masterPositions = [];
        $assignedTyres = collect();

        if ($session->master_vehicle_id) {
            $vehicle = MasterImportKendaraan::find($session->master_vehicle_id);
            if ($vehicle && $vehicle->tyre_position_configuration_id) {
                $masterPositions = TyrePositionDetail::where('configuration_id', $vehicle->tyre_position_configuration_id)
                    ->orderBy('display_order')
                    ->get();

                // Fetch tyres currently installed on this master vehicle
                $assignedTyres = Tyre::where('current_vehicle_id', $session->master_vehicle_id)
                    ->with(['brand', 'pattern', 'size'])
                    ->get()
                    ->keyBy('current_position_id');
            }
        }

        $locations = TyreLocation::all();
        $brands = TyreBrand::orderBy('brand_name')->get();
        $patterns = TyrePattern::orderBy('name')->get();
        $sizes = TyreSize::orderBy('size')->get();

        // Get available tyres for installation dropdown
        $availableTyres = Tyre::whereNull('current_vehicle_id')
            ->select('id', 'serial_number')
            ->orderBy('serial_number')
            ->get();

        return view('tyre-performance.monitoring.session_detail', compact(
            'session',
            'masterPositions',
            'locations',
            'brands',
            'patterns',
            'sizes',
            'assignedTyres',
            'availableTyres'
        ));
    }

    public function storeVehicle(Request $request)
    {
        $request->validate([
            'fleet_name' => 'required|string',
            'vehicle_number' => 'required|string',
            'driver_name' => 'required|string',
            'tire_positions' => 'required|integer|min:1',
            'master_vehicle_id' => 'nullable|exists:master_import_kendaraan,id',
        ]);

        TyreMonitoringVehicle::create($request->all());
        return redirect()->back()->with('success', 'Monitoring Vehicle created and linked to Master Data');
    }

    public function updateVehicle(Request $request, $id)
    {
        $request->validate([
            'fleet_name' => 'required|string',
            'vehicle_number' => 'required|string',
            'driver_name' => 'required|string',
            'tire_positions' => 'required|integer|min:1',
            'master_vehicle_id' => 'nullable|exists:master_import_kendaraan,id',
        ]);

        $vehicle = TyreMonitoringVehicle::findOrFail($id);
        $vehicle->fill($request->all());
        $vehicle->save();
        
        return redirect()->back()->with('success', 'Monitoring Vehicle updated');
    }

    public function createSession($vehicle_id)
    {
        $vehicle = TyreMonitoringVehicle::findOrFail($vehicle_id);
        
        $masterPositions = [];
        $assignedTyres = [];
        if ($vehicle->master_vehicle_id) {
            $masterVehicle = MasterImportKendaraan::with(['tyrePositionConfiguration.details'])->find($vehicle->master_vehicle_id);
            if ($masterVehicle && $masterVehicle->tyrePositionConfiguration) {
                $masterPositions = $masterVehicle->tyrePositionConfiguration->details()
                    ->orderBy('display_order')
                    ->get();
                
                $assignedTyres = Tyre::where('current_vehicle_id', $vehicle->master_vehicle_id)
                    ->with(['brand', 'pattern', 'size'])
                    ->get()
                    ->keyBy('current_position_id');
            }
        }

        $brands = TyreBrand::orderBy('brand_name')->get();
        $patterns = TyrePattern::orderBy('name')->get();
        $sizes = TyreSize::orderBy('size')->get();

        return view('tyre-performance.monitoring.create_session', compact(
            'vehicle',
            'masterPositions',
            'assignedTyres',
            'brands',
            'patterns',
            'sizes'
        ));
    }

    public function storeSession(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:tyre_monitoring_vehicle,vehicle_id',
            'install_date' => 'required|date',
            'tyre_size' => 'required|string',
            'original_rtd' => 'required|numeric',
            'odometer_start' => 'required|integer',
        ]);

        $vehicle = TyreMonitoringVehicle::findOrFail($request->vehicle_id);
        $data = $request->except('checks');
        
        DB::beginTransaction();
        try {
            $session = TyreMonitoringSession::create($data);

            if ($request->has('checks')) {
                foreach ($request->checks as $key => $c) {
                    // Skip if no tyre Sn or Pos ID
                    if (!isset($c['serial_number']) && !isset($c['position_id'])) continue;
                    
                    $serial = $c['serial_number'] ?? null;
                    $tyreId = $c['tyre_id'] ?? null;
                    $posId = $c['position_id'];
                    
                    $posDetail = TyrePositionDetail::find($posId);
                    $posName = $posDetail ? $posDetail->position_code : "Pos $posId";

                    $r1 = (float) ($c['rtd_1'] ?? $request->original_rtd);
                    $r2 = (float) ($c['rtd_2'] ?? $request->original_rtd);
                    $r3 = (float) ($c['rtd_3'] ?? $request->original_rtd);
                    $r4 = (float) ($c['rtd_4'] ?? $request->original_rtd);
                    $avgRtd = ($r1 + $r2 + $r3 + $r4) / 4;

                    // Analytics for Check 1 (Initial = 0 KM operation)
                    $wornPct = ($request->original_rtd > 0) ? (($request->original_rtd - $avgRtd) / $request->original_rtd * 100) : 0;

                    // 1. Create Installation Record
                    TyreMonitoringInstallation::create([
                        'session_id' => $session->session_id,
                        'tyre_id' => $tyreId,
                        'serial_number' => $serial,
                        'install_date' => $request->install_date,
                        'odometer' => $request->odometer_start,
                        'position' => $posName,
                        'position_id' => $posId,
                        'brand' => $c['brand'] ?? '-',
                        'pattern' => $c['pattern'] ?? '-',
                        'size' => $request->tyre_size,
                        'original_rtd' => $request->original_rtd,
                        'rtd_1' => $r1,
                        'rtd_2' => $r2,
                        'rtd_3' => $r3,
                        'rtd_4' => $r4,
                        'avg_rtd' => $avgRtd,
                        'inf_press_recommended' => $c['inf_press_recommended'] ?? null,
                        'inf_press_actual' => $c['inf_press_actual'] ?? null,
                        'date_assembly' => $c['date_assembly'] ?? null,
                        'date_inspection' => $c['date_inspection'] ?? $request->install_date,
                        'notes' => $c['notes'] ?? 'Bulk Installation',
                    ]);

                    // 2. Initial Check (Cek 1) if serial exists
                    if ($serial) {
                        TyreMonitoringCheck::create([
                            'session_id' => $session->session_id,
                            'check_number' => 1,
                            'serial_number' => $serial,
                            'tyre_id' => $tyreId,
                            'position' => $posName,
                            'position_id' => $posId,
                            'check_date' => $request->install_date,
                            'odometer' => $request->odometer_start,
                            'operation_mileage' => 0,
                            'inf_press_recommended' => $c['inf_press_recommended'] ?? null,
                            'inf_press_actual' => $c['inf_press_actual'] ?? null,
                            'date_assembly' => $c['date_assembly'] ?? null,
                            'date_inspection' => $c['date_inspection'] ?? $request->install_date,
                            'rtd_1' => $r1,
                            'rtd_2' => $r2,
                            'rtd_3' => $r3,
                            'rtd_4' => $r4,
                            'worn_percentage' => $wornPct,
                            'km_per_mm' => 0,
                            'projected_life_km' => 0,
                            'condition' => 'ok',
                            'notes' => 'Cek 1 (Start Session)',
                        ]);

                        // Sync Master Tyre
                        if ($tyreId) {
                            $tyre = Tyre::find($tyreId);
                            if ($tyre) {
                                $tyre->current_tread_depth = $avgRtd;
                                $tyre->last_inspection_date = $request->install_date;
                                $tyre->save();

                                TyreMovement::create([
                                    'tyre_id' => $tyre->id,
                                    'vehicle_id' => $vehicle->master_vehicle_id,
                                    'movement_date' => $request->install_date,
                                    'movement_type' => 'Inspection',
                                    'odometer' => $request->odometer_start,
                                    'position_id' => $posId,
                                    'tread_depth' => $avgRtd,
                                    'remark' => "Monitoring Sesi Start - Cek #1",
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('monitoring.sessions.show', $session->session_id)
                ->with('success', 'Monitoring Session started and Bulk Installation completed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function storeInstallation(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:tyre_monitoring_session,session_id',
            'position' => 'nullable|integer',
            'position_id' => 'required_without:position|exists:tyre_position_details,id',
            'serial_number' => 'required|string',
            'rtd_1' => 'required|numeric',
            'rtd_2' => 'required|numeric',
            'rtd_3' => 'required|numeric',
        ]);

        $session = TyreMonitoringSession::findOrFail($request->session_id);
        
        // Find or Create Tyre
        $tyre = Tyre::where('serial_number', $request->serial_number)->first();
        
        if (!$tyre) {
            // Create new tyre record if it doesn't exist
            $tyre = Tyre::create([
                'serial_number' => $request->serial_number,
                'tyre_brand_id' => $request->tyre_brand_id,
                'tyre_pattern_id' => $request->tyre_pattern_id,
                'tyre_size_id' => $request->tyre_size_id,
                'status' => 'Installed',
                'original_tread_depth' => ($request->rtd_1 + $request->rtd_2 + $request->rtd_3) / 3,
                'current_tread_depth' => ($request->rtd_1 + $request->rtd_2 + $request->rtd_3) / 3,
                'tyre_company_id' => auth()->user()->tyre_company_id ?? 1, // Default or session company
            ]);
        }
        
        $tyre->load(['brand', 'size', 'pattern']);

        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['tyre_id'] = $tyre->id;
            $data['brand'] = $tyre->brand->brand_name ?? '-';
            $data['size'] = $tyre->size->size ?? '-';
            $data['pattern'] = $tyre->pattern->name ?? '-';
            $data['avg_rtd'] = ($request->rtd_1 + $request->rtd_2 + $request->rtd_3) / 3;
            $data['install_date'] = $session->install_date;

            // If master position is used, map the numeric position for legacy support
            if ($request->position_id) {
                $posDetail = TyrePositionDetail::find($request->position_id);
                $data['position'] = $posDetail->display_order;
            }

            TyreMonitoringInstallation::create($data);

            // Sync with Master Tyre
            $tyre->update([
                'status' => 'Installed',
                'current_vehicle_id' => $session->master_vehicle_id,
                'current_position_id' => $request->position_id,
                'current_tread_depth' => $data['avg_rtd']
            ]);

            // Record Movement
            TyreMovement::create([
                'tyre_id' => $tyre->id,
                'vehicle_id' => $session->master_vehicle_id,
                'position_id' => $request->position_id,
                'movement_type' => 'Installation',
                'movement_date' => $session->install_date,
                'odometer_reading' => $request->odometer ?? $session->odometer_start,
                'rtd_reading' => $data['avg_rtd'],
                'work_location_id' => $tyre->work_location_id,
                'notes' => 'Monitoring Installation Session #' . $session->session_id,
                'tyre_company_id' => $tyre->tyre_company_id,
                'created_by' => \Auth::id()
            ]);

            // Sync with Position Detail (Secondary sync)
            if ($request->position_id) {
                TyrePositionDetail::where('id', $request->position_id)->update(['tyre_id' => $tyre->id]);
            }

            // Sync stock if applicable
            if ($tyre->wasRecentlyCreated == false && $tyre->work_location_id) {
                DB::table('tyre_locations')
                    ->where('id', $tyre->work_location_id)
                    ->decrement('current_stock');
            }

            DB::commit();
            return redirect()->back()->with('success', 'Tyre installation recorded and synced with master data');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function storeBatchCheck(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:tyre_monitoring_session,session_id',
            'check_date' => 'required|date',
            'odometer' => 'required|integer',
            'checks' => 'required|array',
        ]);

        $session = TyreMonitoringSession::findOrFail($request->session_id);
        $lastCheck = TyreMonitoringCheck::where('session_id', $session->session_id)->max('check_number');
        $newCheckNumber = ($lastCheck ?? 0) + 1;

        DB::beginTransaction();
        try {
            foreach ($request->checks as $serial => $c) {
                // Skip if no data
                if (empty($c['rtd_1']) && empty($c['rtd_2']) && empty($c['rtd_3']) && empty($c['rtd_4']) && empty($c['psi_actual'])) continue;

                $inst = TyreMonitoringInstallation::where('session_id', $session->session_id)
                    ->where('serial_number', $serial)
                    ->first();
                
                $origRtd = $inst->original_rtd ?? $session->original_rtd ?? 1;
                
                $r1 = (float)($c['rtd_1'] ?? 0);
                $r2 = (float)($c['rtd_2'] ?? 0);
                $r3 = (float)($c['rtd_3'] ?? 0);
                $r4 = (float)($c['rtd_4'] ?? 0);
                $avgRtd = ($r1 + $r2 + $r3 + $r4) / 4;

                // Analytics
                $opMileage = $this->calculateLifetimeDiff($request->odometer, $session->odometer_start);
                $lossRtd = $origRtd - $avgRtd;
                $wornPct = ($origRtd > 0) ? ($lossRtd / $origRtd * 100) : 0;
                $kmPerMm = ($lossRtd > 0) ? ($opMileage / $lossRtd) : 0;
                $projLife = $kmPerMm * ($origRtd - 3);

                $checkData = [
                    'session_id' => $session->session_id,
                    'check_number' => $newCheckNumber,
                    'check_date' => $request->check_date,
                    'odometer' => $request->odometer,
                    'operation_mileage' => $opMileage,
                    'position' => $inst ? $inst->position : '?',
                    'position_id' => $inst ? $inst->position_id : null,
                    'serial_number' => $serial,
                    'inf_press_recommended' => $c['psi_recommended'] ?? null,
                    'inf_press_actual' => $c['psi_actual'] ?? null,
                    'date_assembly' => $c['date_assembly'] ?? null,
                    'date_inspection' => $request->check_date,
                    'rtd_1' => $r1,
                    'rtd_2' => $r2,
                    'rtd_3' => $r3,
                    'rtd_4' => $r4,
                    'worn_percentage' => $wornPct,
                    'km_per_mm' => $kmPerMm,
                    'projected_life_km' => $projLife,
                    'condition' => $c['condition'] ?? 'ok',
                    'notes' => $c['notes'] ?? null,
                ];

                TyreMonitoringCheck::create($checkData);

                // Sync with Master Tyre and Movement
                $tyre = Tyre::where('serial_number', $serial)->first();
                if ($tyre) {
                    $lastMov = TyreMovement::where('tyre_id', $tyre->id)
                        ->where('movement_date', '<=', $request->check_date)
                        ->orderBy('movement_date', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();

                    $kmDiff = 0;
                    if ($lastMov) {
                        $kmDiff = $this->calculateLifetimeDiff($request->odometer, $lastMov->odometer_reading);
                    }

                    TyreMovement::create([
                        'tyre_id' => $tyre->id,
                        'vehicle_id' => $session->master_vehicle_id,
                        'position_id' => $inst ? $inst->position_id : null,
                        'movement_type' => 'Inspection',
                        'movement_date' => $request->check_date,
                        'odometer_reading' => $request->odometer,
                        'running_km' => $kmDiff,
                        'rtd_reading' => $avgRtd,
                        'notes' => "Periodic Check #{$newCheckNumber} (Session #{$session->session_id})",
                        'tyre_company_id' => $tyre->tyre_company_id,
                        'created_by' => \Auth::id()
                    ]);

                    $tyre->update([
                        'current_tread_depth' => $avgRtd,
                        'total_lifetime_km' => ($tyre->total_lifetime_km ?? 0) + $kmDiff
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', "Periodic Check #{$newCheckNumber} recorded successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function storeRemoval(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:tyre_monitoring_session,session_id',
            'serial_number' => 'required|exists:tyre_monitoring_installation,serial_number',
            'removal_date' => 'required|date',
            'odometer' => 'required|integer',
            'final_rtd' => 'required|numeric',
            'work_location_id' => 'nullable|exists:tyre_locations,id',
            'target_status' => 'nullable|in:New,Repaired,Scrap'
        ]);

        $session = TyreMonitoringSession::findOrFail($request->session_id);
        
        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['total_mileage'] = $request->odometer - $session->odometer_start;
            
            // Get position from installation
            $inst = TyreMonitoringInstallation::where('session_id', $session->session_id)
                ->where('serial_number', $request->serial_number)
                ->first();
            $data['position'] = $inst ? $inst->position : 0;
            $data['position_id'] = $inst ? $inst->position_id : null;

            TyreMonitoringRemoval::create($data);

            // Update main tyre status
            $tyre = Tyre::where('serial_number', $request->serial_number)->first();
            if ($tyre) {
                // Determine new status
                $status = $request->target_status;
                if (!$status) {
                    if (stripos($request->tyre_condition_after, 'scrap') !== false || stripos($request->removal_reason, 'worn') !== false) {
                        $status = 'Scrap';
                    } else {
                        $status = 'Repaired';
                    }
                }

                // Calculate Lifetime Delta (Sync with main system logic)
                $lastMov = TyreMovement::where('tyre_id', $tyre->id)
                    ->where('movement_date', '<=', $request->removal_date)
                    ->orderBy('movement_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                $kmDiff = 0;
                if ($lastMov) {
                    $kmDiff = $this->calculateLifetimeDiff($request->odometer, $lastMov->odometer_reading);
                }

                $tyre->update([
                    'status' => $status,
                    'current_vehicle_id' => null,
                    'current_position_id' => null,
                    'work_location_id' => $request->work_location_id,
                    'current_tread_depth' => $request->final_rtd,
                    'total_lifetime_km' => ($tyre->total_lifetime_km ?? 0) + $kmDiff
                ]);

                // Sync with Position Detail (Clear the position)
                if ($data['position_id']) {
                    TyrePositionDetail::where('id', $data['position_id'])->update(['tyre_id' => null]);
                }

                // Sync stock (Increment at destination location)
                if ($request->work_location_id) {
                    DB::table('tyre_locations')->where('id', $request->work_location_id)->increment('current_stock');
                }

                // Record Movement
                TyreMovement::create([
                    'tyre_id' => $tyre->id,
                    'vehicle_id' => $session->master_vehicle_id,
                    'position_id' => $data['position_id'],
                    'movement_type' => 'Removal',
                    'movement_date' => $request->removal_date,
                    'odometer_reading' => $request->odometer,
                    'running_km' => $kmDiff,
                    'rtd_reading' => $request->final_rtd,
                    'target_status' => $status,
                    'work_location_id' => $request->work_location_id,
                    'notes' => 'Monitoring Removal Session #' . $session->session_id . '. Reason: ' . $request->removal_reason,
                    'tyre_company_id' => $tyre->tyre_company_id,
                    'created_by' => \Auth::id()
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Tyre removal recorded and Master Data synced');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function updateSession(Request $request, $id)
    {
        $session = TyreMonitoringSession::findOrFail($id);
        $request->validate([
            'status' => 'required|in:active,completed',
            'notes' => 'nullable|string'
        ]);

        $session->update($request->only(['status', 'notes']));
        return redirect()->back()->with('success', 'Session status updated to ' . $request->status);
    }

    public function destroySession($id)
    {
        $session = TyreMonitoringSession::findOrFail($id);
        
        // Check if has installations/checks
        if ($session->installations()->exists() || $session->checks()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete session with existing records. Delete checks/installations first.');
        }

        $session->delete();
        return redirect()->back()->with('success', 'Monitoring Session deleted');
    }

    public function getTyreBySerial(Request $request)
    {
        $serialNumber = $request->serial_number;
        $tyre = Tyre::where('serial_number', $serialNumber)
            ->with(['brand', 'size', 'pattern'])
            ->first();

        if (!$tyre) {
            return response()->json(['success' => false, 'message' => 'Tyre not found']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'brand' => $tyre->brand->brand_name ?? '-',
                'size' => $tyre->size->size ?? '-',
                'pattern' => $tyre->pattern->name ?? '-',
                'otd' => $tyre->initial_tread_depth ?? 0,
                'rtd' => $tyre->current_tread_depth ?? 0,
            ]
        ]);
    }

    public function export($sessionId)
    {
        $session = TyreMonitoringSession::findOrFail($sessionId);
        $fileName = 'Monitoring_Session_' . $session->session_id . '_' . $session->install_date . '.xlsx';
        
        return Excel::download(new SessionExport($sessionId), $fileName);
    }
}
