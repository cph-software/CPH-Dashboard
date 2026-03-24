<?php

namespace App\Http\Controllers\TyrePerformance\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\Tyre;
use App\Models\TyreMonitoringVehicle;
use App\Models\TyreMonitoringImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use DB;

class MonitoringController extends Controller
{
    /**
     * Helper to calculate lifetime difference handling potential meter resets (minus diff)
     */
    private function calculateLifetimeDiff($currentReading, $lastInstallReading)
    {
        if ($currentReading === null || $lastInstallReading === null)
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
        $sessions = TyreMonitoringSession::where('vehicle_id', $id)
            ->withCount(['checks', 'installations', 'removal'])
            ->latest()
            ->get();
        
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

        // Master data: Global dropdown (no company whitelist restriction)
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
        $session = TyreMonitoringSession::with(['vehicle', 'installations.positionDetail', 'checks', 'removal'])->findOrFail($id);

        $masterPositions = [];
        $assignedTyres = collect();

        if ($session->master_vehicle_id) {
            $vehicle = MasterImportKendaraan::find($session->master_vehicle_id);
            if ($vehicle && $vehicle->tyre_position_configuration_id) {
                $masterPositions = TyrePositionDetail::where('configuration_id', $vehicle->tyre_position_configuration_id)
                    ->orderBy('display_order')
                    ->get();

                // Get tyres that were part of this session's installations
                $assignedTyres = $session->installations->mapWithKeys(function($inst) {
                    // Try to attach the tyre model for UI compatibility, but prioritize session fields
                    $t = $inst->tyre ?: (object)[
                        'serial_number' => $inst->serial_number,
                        'brand' => (object)['brand_name' => $inst->brand],
                        'pattern' => (object)['name' => $inst->pattern],
                        'size' => (object)['size' => $inst->size],
                        'current_tread_depth' => $inst->avg_rtd // Fallback
                    ];
                    return [$inst->position_id => $t];
                });
            }
        }

        return view('tyre-performance.monitoring.session_detail', compact(
            'session',
            'masterPositions',
            'assignedTyres'
        ));
    }

    public function storeVehicle(Request $request)
    {
        $request->validate([
            'fleet_name' => 'required|string',
            'vehicle_number' => 'required|string',
            'driver_name' => 'required|string',
            'tire_positions' => 'required|integer|min:1',
            'is_trail' => 'nullable|boolean',
            'master_vehicle_id' => 'nullable|exists:master_import_kendaraan,id',
        ]);

        $data = $request->all();
        $data['is_trail'] = $request->has('is_trail');

        TyreMonitoringVehicle::create($data);
        return redirect()->back()->with('success', 'Monitoring Vehicle created and linked to Master Data');
    }

    public function updateVehicle(Request $request, $id)
    {
        $request->validate([
            'fleet_name' => 'required|string',
            'vehicle_number' => 'required|string',
            'driver_name' => 'required|string',
            'tire_positions' => 'required|integer|min:1',
            'is_trail' => 'nullable|boolean',
            'master_vehicle_id' => 'nullable|exists:master_import_kendaraan,id',
        ]);

        $vehicle = TyreMonitoringVehicle::findOrFail($id);
        $data = $request->all();
        $data['is_trail'] = $request->has('is_trail');
        
        $vehicle->update($data);
        
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

        // Master data: Global dropdown (no company whitelist restriction)
        $brands = TyreBrand::orderBy('brand_name')->get();
        $patterns = TyrePattern::with('brand')->orderBy('name')->get();
        $sizes = TyreSize::orderBy('size')->get();

        $availableTyres = \App\Models\Tyre::whereNull('current_vehicle_id')
            ->with(['brand', 'size', 'pattern', 'monitoringChecks' => function($q) {
                $q->latest();
            }])
            ->orderBy('serial_number')
            ->get();

        // Get latest vehicle readings
        $latestMovement = TyreMovement::where('vehicle_id', $vehicle->master_vehicle_id)
            ->latest('movement_date')
            ->latest('id')
            ->first();
        
        $currentKM = $latestMovement->odometer_reading ?? 0;
        $currentHM = $latestMovement->hour_meter_reading ?? 0;

        return view('tyre-performance.monitoring.create_session', compact(
            'vehicle',
            'masterPositions',
            'assignedTyres',
            'brands',
            'patterns',
            'sizes',
            'availableTyres',
            'currentKM',
            'currentHM'
        ));
    }

    public function createCheck($session_id)
    {
        $session = TyreMonitoringSession::with(['vehicle', 'installations.positionDetail'])->findOrFail($session_id);
        $vehicle = TyreMonitoringVehicle::findOrFail($session->vehicle_id);
        $checkCount = TyreMonitoringCheck::where('session_id', $session_id)->distinct('check_number')->count();

        $masterPositions = [];
        $assignedTyres = collect();

        if ($session->master_vehicle_id) {
            $masterVehicle = MasterImportKendaraan::with(['tyrePositionConfiguration.details'])->find($session->master_vehicle_id);
            if ($masterVehicle && $masterVehicle->tyrePositionConfiguration) {
                $masterPositions = $masterVehicle->tyrePositionConfiguration->details()
                    ->orderBy('display_order')
                    ->get();
                
                $assignedTyres = Tyre::where('current_vehicle_id', $session->master_vehicle_id)
                    ->with(['brand', 'pattern', 'size'])
                    ->get()
                    ->keyBy('current_position_id');
            }
        }

        // Master data: Global dropdown (no company whitelist restriction)
        $brands = TyreBrand::orderBy('brand_name')->get();
        $patterns = TyrePattern::orderBy('name')->get();
        $sizes = TyreSize::orderBy('size')->get();
        
        // Only check tyres that were installed for this session
        $installedTyres = Tyre::whereIn('serial_number', $session->installations->pluck('serial_number'))
            ->where('current_vehicle_id', $session->master_vehicle_id)
            ->with(['brand', 'size', 'pattern'])
            ->get();

        // Attach last check data to each tyre for historical RTD points
        foreach ($installedTyres as $tyre) {
            $tyre->last_check = TyreMonitoringCheck::where('session_id', $session_id)
                ->where('serial_number', $tyre->serial_number)
                ->orderBy('check_number', 'desc')
                ->first();
        }
        
        // Get latest global readings
        $lastCheck = TyreMonitoringCheck::where('session_id', $session_id)
            ->orderBy('check_number', 'desc')
            ->first();
        
        $currentKM = $lastCheck ? $lastCheck->odometer_reading : ($session->odometer_start ?? 0);
        $currentHM = $lastCheck ? $lastCheck->hm_reading : ($session->hm_start ?? 0);

        return view('tyre-performance.monitoring.add_check', compact(
            'session',
            'vehicle',
            'checkCount',
            'masterPositions',
            'assignedTyres',
            'brands',
            'patterns',
            'sizes',
            'installedTyres',
            'currentKM',
            'currentHM'
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
            'hm_start' => 'nullable|integer',
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

                    // Fetch tyre info for snapshot
                    $tyreSnapshot = null;
                    if ($tyreId) {
                        $tyreSnapshot = Tyre::with(['brand', 'pattern', 'size'])->find($tyreId);
                    }

                    $r1 = (float) ($c['rtd_1'] ?? $request->original_rtd);
                    $r2 = (float) ($c['rtd_2'] ?? $request->original_rtd);
                    $r3 = (float) ($c['rtd_3'] ?? $request->original_rtd);
                    $r4 = (float) ($c['rtd_4'] ?? $request->original_rtd);
                    $rtdCount = ($r4 > 0) ? 4 : 3;
                    $avgRtd = ($r1 + $r2 + $r3 + $r4) / $rtdCount;

                    // Analytics for Check 1 (Initial = 0 KM operation)
                    $wornPct = ($request->original_rtd > 0) ? (($request->original_rtd - $avgRtd) / $request->original_rtd * 100) : 0;

                    // 1. Create Installation Record
                    TyreMonitoringInstallation::create([
                        'session_id' => $session->session_id,
                        'tyre_id' => $tyreId,
                        'serial_number' => $serial,
                        'install_date' => $request->install_date,
                        'odometer_reading' => $request->odometer_start,
                        'hm_reading' => $request->hm_start,
                        'position' => $posName,
                        'position_id' => $posId,
                        'brand' => $tyreSnapshot ? ($tyreSnapshot->brand->brand_name ?? '-') : ($c['brand'] ?? '-'),
                        'pattern' => $tyreSnapshot ? ($tyreSnapshot->pattern->name ?? '-') : ($c['pattern'] ?? '-'),
                        'size' => $tyreSnapshot ? ($tyreSnapshot->size->size ?? '-') : ($request->tyre_size ?? '-'),
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
                            'odometer_reading' => $request->odometer_start,
                            'hm_reading' => $request->hm_start,
                            'operation_mileage' => 0,
                            'operation_hm' => 0,
                            'inf_press_recommended' => $c['inf_press_recommended'] ?? null,
                            'inf_press_actual' => $c['inf_press_actual'] ?? null,
                            'date_assembly' => $c['date_assembly'] ?? null,
                            'date_inspection' => $request->install_date,
                            'rtd_1' => $r1,
                            'rtd_2' => $r2,
                            'rtd_3' => $r3,
                            'rtd_4' => $r4,
                            'worn_percentage' => $wornPct,
                            'km_per_mm' => 0,
                            'projected_life_km' => 0,
                            'condition' => $c['condition'] ?? 'ok',
                            'recommendation' => $c['recommendation'] ?? null,
                            'notes' => $c['notes'] ?? 'Cek 1 (Start Session)',
                        ]);

                        // Sync Master Tyre
                        if ($tyreId) {
                            $tyre = Tyre::find($tyreId);
                            if ($tyre) {
                                // Determine if this is a new installation or just a check for existing tyre
                                $isNewInstallation = ($tyre->current_vehicle_id != $vehicle->master_vehicle_id);

                                // 1. Update Master Tyre Record
                                $tyre->current_tread_depth = $avgRtd;
                                $tyre->last_inspection_date = $request->install_date;
                                
                                if ($isNewInstallation) {
                                    $tyre->update([
                                        'status' => 'Installed',
                                        'current_vehicle_id' => $vehicle->master_vehicle_id,
                                        'current_position_id' => $posId,
                                        'current_tread_depth' => $avgRtd,
                                        'last_inspection_date' => $request->install_date,
                                        'last_hm_reading' => $request->hm_start,
                                    ]);
                                } else {
                                    $tyre->save(); // Save changes if not a new installation
                                }

                                // 2. Record Movement Log
                                TyreMovement::create([
                                    'tyre_id' => $tyre->id,
                                    'vehicle_id' => $vehicle->master_vehicle_id,
                                    'movement_date' => $request->install_date,
                                    'movement_type' => $isNewInstallation ? 'Installation' : 'Inspection',
                                    'odometer_reading' => $request->odometer_start,
                                    'hour_meter_reading' => $request->hm_start,
                                    'position_id' => $posId,
                                    'rtd_reading' => $avgRtd,
                                    'notes' => $isNewInstallation ? "Monitoring Sesi Start - New Installation" : "Monitoring Sesi Start - Periodic Check #1",
                                    'tyre_company_id' => $tyre->tyre_company_id,
                                    'created_by' => \Auth::id()
                                ]);

                                // 3. Sync Stock if it's a new installation from a location
                                if ($isNewInstallation && $tyre->work_location_id) {
                                    DB::table('tyre_locations')
                                        ->where('id', $tyre->work_location_id)
                                        ->decrement('current_stock');
                                }

                                // 4. Sync Position Detail
                                if ($posId) {
                                    TyrePositionDetail::where('id', $posId)->update(['tyre_id' => $tyre->id]);
                                }
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

             $data['odometer_reading'] = $request->odometer ?? $session->odometer_start;
            $data['hm_reading'] = $request->hour_meter ?? $session->hm_start;
            
            TyreMonitoringInstallation::create($data);

            // Sync with Master Tyre
            $tyre->update([
                'status' => 'Installed',
                'current_vehicle_id' => $session->master_vehicle_id,
                'current_position_id' => $request->position_id,
                'current_tread_depth' => $data['avg_rtd'],
                'last_inspection_date' => $session->install_date,
                'last_hm_reading' => $data['hm_reading']
            ]);

            // Record Movement
            TyreMovement::create([
                'tyre_id' => $tyre->id,
                'vehicle_id' => $session->master_vehicle_id,
                'position_id' => $request->position_id,
                'movement_type' => 'Installation',
                'movement_date' => $session->install_date,
                'odometer_reading' => $data['odometer_reading'],
                'hour_meter_reading' => $data['hm_reading'],
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

            // Link pending images to this installation event (using check_id as NULL, session_id + serial)
            TyreMonitoringImage::where('session_id', $session->session_id)
                ->where('serial_number', $request->serial_number)
                ->whereNull('check_id')
                ->update(['uploaded_by' => auth()->id()]);

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
            'odometer' => 'required|numeric|min:0',
            'hour_meter' => 'nullable|numeric|min:0',
            'driver_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'retase' => 'required|numeric',
            'checks' => 'required|array',
            'temp_id' => 'nullable|string', // For image linking
        ], [
            'odometer.required' => 'Odometer harus diisi.',
            'driver_name.required' => 'Nama driver harus diisi.',
        ]);

        $session = TyreMonitoringSession::findOrFail($request->session_id);
        
        // Human error prevention: Odometer check vs Session Start
        if ($request->odometer < $session->odometer_start) {
            return redirect()->back()->with('error', "Odometer Input ({$request->odometer}) tidak boleh lebih kecil dari Odometer Awal Sesi ({$session->odometer_start}).")->withInput();
        }

        // Human error prevention: Odometer check vs Last Check
        $lastCheckRecord = TyreMonitoringCheck::where('session_id', $session->session_id)
            ->orderBy('check_number', 'desc')
            ->first();
        
        if ($lastCheckRecord && $request->odometer < $lastCheckRecord->odometer_reading) {
            return redirect()->back()->with('error', "Odometer Input ({$request->odometer}) tidak boleh lebih kecil dari Odometer Check sebelumnya ({$lastCheckRecord->odometer_reading}).")->withInput();
        }

        if ($request->hour_meter && $session->hm_start && $request->hour_meter < $session->hm_start) {
            return redirect()->back()->with('error', "Hour Meter Input ({$request->hour_meter}) tidak boleh lebih kecil dari HM Awal Sesi ({$session->hm_start}).")->withInput();
        }

        $newCheckNumber = ($lastCheckRecord->check_number ?? 0) + 1;
        $user = auth()->user();
        $isAdmin = ($user->role_id == 1);

        // Validation Rule: 1 Axle = Must check all wheels in that axle (if it's a dual axle)
        $checksForAxles = [];
        foreach ($request->checks as $serial => $c) {
            // Check if this tyre actually has data
            if (empty($c['rtd_1']) && empty($c['rtd_2']) && empty($c['rtd_3']) && empty($c['rtd_4']) && empty($c['psi_actual'])) continue;

            $inst = TyreMonitoringInstallation::where('session_id', $session->session_id)
                ->where('serial_number', $serial)
                ->first();
            
            if ($inst && $inst->position_id) {
                $posDetail = TyrePositionDetail::find($inst->position_id);
                if ($posDetail) {
                    $axleKey = $posDetail->axle_type . '_' . $posDetail->axle_number;
                    $checksForAxles[$axleKey][] = $serial;
                }
            }
        }

        // Validate each axle involved
        foreach ($checksForAxles as $axleKey => $checkedSerials) {
            [$axleType, $axleNumber] = explode('_', $axleKey);
            // Get all positions in this axle for this vehicle's configuration
            $totalPositionsInAxle = TyrePositionDetail::where('configuration_id', $session->vehicle->tyre_position_configuration_id)
                ->where('axle_type', $axleType)
                ->where('axle_number', $axleNumber)
                ->count();
            
            if (count($checkedSerials) < $totalPositionsInAxle) {
                return redirect()->back()->with('error', "Sumbat/Axle {$axleType} #{$axleNumber} memiliki {$totalPositionsInAxle} ban, tetapi Anda hanya mengisi " . count($checkedSerials) . " ban. Harap isi semua ban dalam satu sumbu.")
                    ->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // Calculate HM Delta if applicable
            $opHm = $request->hour_meter ? ($request->hour_meter - ($session->hm_start ?? $request->hour_meter)) : 0;

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

                // Validation: RTD cannot be greater than original
                if ($r1 > $origRtd) return redirect()->back()->with('error', "RTD 1 ({$r1}) untuk ban {$serial} tidak boleh lebih besar dari RTD Original ({$origRtd}).")->withInput();
                if ($r2 > $origRtd) return redirect()->back()->with('error', "RTD 2 ({$r2}) untuk ban {$serial} tidak boleh lebih besar dari RTD Original ({$origRtd}).")->withInput();
                if ($r3 > $origRtd) return redirect()->back()->with('error', "RTD 3 ({$r3}) untuk ban {$serial} tidak boleh lebih besar dari RTD Original ({$origRtd}).")->withInput();
                if ($r4 > $origRtd) return redirect()->back()->with('error', "RTD 4 ({$r4}) untuk ban {$serial} tidak boleh lebih besar dari RTD Original ({$origRtd}).")->withInput();

                $rtdCount = $r4 > 0 ? 4 : 3;
                $avgRtd = ($r1 + $r2 + $r3 + $r4) / $rtdCount;

                // Analytics
                $opMileage = $this->calculateLifetimeDiff($request->odometer, $session->odometer_start);
                $lossRtd = $origRtd - $avgRtd;
                $wornPct = ($origRtd > 0) ? ($lossRtd / $origRtd * 100) : 0;
                
                // Only calculate performance if wear >= 0.1mm to avoid unrealistic numbers
                if ($lossRtd >= 0.1) {
                    $kmPerMm = $opMileage / $lossRtd;
                    $projLife = $kmPerMm * ($origRtd - 3);
                } else {
                    $kmPerMm = 0;
                    $projLife = 0;
                }

                $checkData = [
                    'session_id' => $session->session_id,
                    'check_number' => $newCheckNumber,
                    'check_date' => $request->check_date,
                    'odometer_reading' => $request->odometer,
                    'hm_reading' => $request->hour_meter,
                    'operation_mileage' => $opMileage,
                    'operation_hm' => $opHm,
                    'driver_name' => $request->driver_name,
                    'phone_number' => $request->phone_number,
                    'position' => $inst ? $inst->position : '?',
                    'position_id' => $inst ? $inst->position_id : null,
                    'serial_number' => $serial,
                    'inf_press_recommended' => $c['psi_recommended'] ?? $request->retase,
                    'inf_press_actual' => $c['psi_actual'] ?? null,
                    'date_assembly' => $c['date_assembly'] ?? ($inst->date_assembly ?? null),
                    'date_inspection' => $request->check_date,
                    'rtd_1' => $r1,
                    'rtd_2' => $r2,
                    'rtd_3' => $r3,
                    'rtd_4' => $r4,
                    'worn_percentage' => $wornPct,
                    'km_per_mm' => $kmPerMm,
                    'projected_life_km' => $projLife,
                    'condition' => $this->determineCondition($wornPct, $c['psi_actual'], $request->retase),
                    'recommendation' => $c['recommendation'] ?? null,
                    'notes' => $c['notes'] ?? null,
                    'is_sales_input' => $request->has('is_sales_input'),
                    'approval_status' => $isAdmin ? 'Approved' : 'Pending',
                    'approved_by' => $isAdmin ? $user->id : null,
                ];

                $check = TyreMonitoringCheck::create($checkData);

                // Link images to this check
                TyreMonitoringImage::where('session_id', $session->session_id)
                    ->where(function($q) use ($serial, $request) {
                        $q->where('serial_number', $serial)
                          ->orWhere(function($sub) use ($request) {
                              $sub->whereNull('serial_number')
                                  ->where('notes', $request->temp_id); // Using notes as temp session identifier
                          });
                    })
                    ->whereNull('check_id')
                    ->update(['check_id' => $check->check_id, 'uploaded_by' => auth()->id()]);

                // Sync with Master Tyre and Movement
                $tyre = Tyre::where('serial_number', $serial)->first();
                if ($tyre) {
                    $lastMov = TyreMovement::where('tyre_id', $tyre->id)
                        ->where('movement_date', '<=', $request->check_date)
                        ->orderBy('movement_date', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();

                    $kmDiff = 0;
                    $hmDiff = 0;
                    if ($lastMov) {
                        $kmDiff = $this->calculateLifetimeDiff($request->odometer, $lastMov->odometer_reading);
                        if ($request->hour_meter && $lastMov->hour_meter_reading) {
                            $hmDiff = $request->hour_meter - $lastMov->hour_meter_reading;
                        }
                    }

                    if ($isAdmin) {
                        TyreMovement::create([
                            'tyre_id' => $tyre->id,
                            'vehicle_id' => $session->master_vehicle_id,
                            'position_id' => $inst ? $inst->position_id : null,
                            'movement_type' => 'Inspection',
                            'movement_date' => $request->check_date,
                            'odometer_reading' => $request->odometer,
                            'hour_meter_reading' => $request->hour_meter,
                            'running_km' => $kmDiff,
                            'running_hm' => $hmDiff,
                            'rtd_reading' => $avgRtd,
                            'notes' => "Periodic Check #{$newCheckNumber} (Session #{$session->session_id}) - ADMIN",
                            'tyre_company_id' => $tyre->tyre_company_id,
                            'created_by' => \Auth::id()
                        ]);

                        $tyre->update([
                            'current_tread_depth' => $avgRtd,
                            'total_lifetime_km' => ($tyre->total_lifetime_km ?? 0) + $kmDiff,
                            'total_lifetime_hm' => ($tyre->total_lifetime_hm ?? 0) + ($hmDiff > 0 ? $hmDiff : 0),
                            'last_inspection_date' => $request->check_date,
                            'last_hm_reading' => $request->hour_meter
                        ]);
                    }
                }
            }

            // Update session global retase
            $session->update(['retase' => $request->retase]);

            DB::commit();
            return redirect()->route('monitoring.vehicle.show', $session->vehicle_id)
                ->with('success', "Periodic Check #{$newCheckNumber} recorded successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
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

            $data['odometer_reading'] = $request->odometer;
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
                    'total_lifetime_km' => ($tyre->total_lifetime_km ?? 0) + $kmDiff,
                    'last_inspection_date' => $request->removal_date
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

    public function exportPdf(Request $request, $sessionId)
    {
        $session = TyreMonitoringSession::with([
            'vehicle', 
            'installations.tyre.brand', 
            'installations.tyre.size', 
            'installations.tyre.pattern',
            'checks', 
            'masterVehicle'
        ])->findOrFail($sessionId);

        $checkNumber = $request->check_number ?? $session->checks->max('check_number');
        if (!$checkNumber) {
            return redirect()->back()->with('error', 'Tidak ada data pemeriksaan untuk sesi ini.');
        }

        $checks = TyreMonitoringCheck::where('session_id', $sessionId)
            ->where('check_number', $checkNumber)
            ->with(['tyre.brand', 'tyre.size', 'tyre.pattern'])
            ->get();

        $checkIds = $checks->pluck('check_id')->toArray();

        $images = TyreMonitoringImage::where('session_id', $sessionId)
            ->whereIn('check_id', $checkIds)
            ->get()
            ->groupBy('serial_number');

        // General images are linked to one of the check_ids
        $generalImages = TyreMonitoringImage::where('session_id', $sessionId)
            ->whereIn('check_id', $checkIds)
            ->whereNull('serial_number')
            ->get()
            ->keyBy('image_type');

        $data = [
            'session' => $session,
            'vehicle' => $session->vehicle,
            'masterVehicle' => $session->masterVehicle,
            'checks' => $checks,
            'checkNumber' => $checkNumber,
            'images' => $images,
            'generalImages' => $generalImages,
            'date' => date('d M Y'),
        ];

        $pdf = Pdf::loadView('tyre-performance.monitoring.report_pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->stream('Monitoring_Report_Vehicle_' . $session->vehicle->vehicle_number . '.pdf');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5MB max
            'type' => 'required|string',
            'session_id' => 'required',
            'serial_number' => 'nullable|string',
            'temp_id' => 'nullable|string'
        ]);

        try {
            $file = $request->file('image');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('tyre-monitoring/' . $request->session_id, $fileName, 'public');

            $image = TyreMonitoringImage::create([
                'session_id' => $request->session_id,
                'serial_number' => $request->serial_number,
                'image_type' => $request->type,
                'image_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'notes' => $request->temp_id, // Store temp_id to link later if check_id is not yet known
                'uploaded_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'image_id' => $image->image_id,
                'url' => asset('storage/' . $path)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approve(Request $request, $sessionId, $checkNumber)
    {
        $checks = TyreMonitoringCheck::where('session_id', $sessionId)
            ->where('check_number', $checkNumber)
            ->get();

        foreach ($checks as $c) {
            $c->update([
                'approval_status' => 'Approved',
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);

            // Sync with Tyre Master if not already synced (e.g. from User input)
            $tyre = Tyre::where('serial_number', $c->serial_number)->first();
            if ($tyre) {
                // Calculate average of RTD 1-4
                $rtds = array_filter([$c->rtd_1, $c->rtd_2, $c->rtd_3, $c->rtd_4], function($v) { return $v > 0; });
                $avgRtd = count($rtds) > 0 ? array_sum($rtds) / count($rtds) : $tyre->current_tread_depth;

                $tyre->update([
                    'current_tread_depth' => $avgRtd,
                    'total_lifetime_km' => ($tyre->total_lifetime_km ?? 0) + ($c->operation_mileage ?? 0)
                ]);
            }
        }

        return redirect()->back()->with('success', 'Pemeriksaan #' . $checkNumber . ' telah disetujui.');
    }

    public function reject(Request $request, $sessionId, $checkNumber)
    {
        $request->validate(['reason' => 'required|string']);

        TyreMonitoringCheck::where('session_id', $sessionId)
            ->where('check_number', $checkNumber)
            ->update([
                'approval_status' => 'Rejected',
                'reject_reason' => $request->reason
            ]);

        return redirect()->back()->with('success', 'Pemeriksaan #' . $checkNumber . ' telah ditolak.');
    }

    private function determineCondition($wornPct, $psiActual, $psiRec)
    {
        // 1. Check Air Pressure (Most critical)
        if ($psiActual && $psiRec) {
            $diffPct = abs(($psiActual - $psiRec) / $psiRec * 100);
            if ($diffPct > 20) return 'critical'; // More than 20% diff is dangerous
            if ($diffPct > 10) return 'warning';
        }

        // 2. Check Wear Percentage
        if ($wornPct >= 85) return 'critical'; // Time to scrap
        if ($wornPct >= 70) return 'warning';  // Monitor closely

        return 'ok';
    }
}
