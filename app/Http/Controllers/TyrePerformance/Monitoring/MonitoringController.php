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
        if ($vehicle->tyre_position_configuration_id) {
            $masterPositions = TyrePositionDetail::where('configuration_id', $vehicle->tyre_position_configuration_id)
                ->orderBy('display_order')
                ->get();
        }

        // Render the visual layout partial
        $html = view('tyre-performance.movement._vehicle_layout', [
            'configuration' => $vehicle->tyrePositionConfiguration,
            'assignedTyres' => $tyres
        ])->render();
        
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

    public function storeSession(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:tyre_monitoring_vehicle,vehicle_id',
            'master_vehicle_id' => 'nullable|exists:master_import_kendaraan,id',
            'install_date' => 'required|date',
            'tyre_size' => 'required|string',
            'original_rtd' => 'required|numeric',
            'odometer_start' => 'required|integer',
        ]);

        $vehicle = TyreMonitoringVehicle::findOrFail($request->vehicle_id);
        $data = $request->all();
        if ($vehicle->master_vehicle_id) {
            $data['master_vehicle_id'] = $vehicle->master_vehicle_id;
        }

        $session = TyreMonitoringSession::create($data);
        return redirect()->route('monitoring.sessions.show', $session->session_id)->with('success', 'Monitoring Session started');
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

    public function storeCheck(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:tyre_monitoring_session,session_id',
            'serial_number' => 'required|exists:tyres,serial_number',
            'check_date' => 'required|date',
            'odometer' => 'required|integer',
            'rtd_1' => 'required|numeric',
            'rtd_2' => 'required|numeric',
            'rtd_3' => 'required|numeric',
        ]);

        $session = TyreMonitoringSession::findOrFail($request->session_id);
        $tyre = Tyre::where('serial_number', $request->serial_number)->first();
        
        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['operation_mileage'] = $request->odometer - $session->odometer_start;
            
            // Get position from installation
            $inst = TyreMonitoringInstallation::where('session_id', $session->session_id)
                ->where('serial_number', $request->serial_number)
                ->first();
            $data['position'] = $inst ? $inst->position : 0;
            $data['position_id'] = $inst ? $inst->position_id : null;

            // check_number auto increment per session
            $lastCheck = TyreMonitoringCheck::where('session_id', $session->session_id)->latest('check_number')->first();
            $data['check_number'] = $lastCheck ? $lastCheck->check_number + 1 : 1;

            $avg_rtd = ($request->rtd_1 + $request->rtd_2 + $request->rtd_3) / 3;

            TyreMonitoringCheck::create($data);

            // Calculate Lifetime Delta for Synchronization
            $kmDiff = 0;
            if ($tyre) {
                $lastMov = TyreMovement::where('tyre_id', $tyre->id)
                    ->where('movement_date', '<=', $request->check_date)
                    ->orderBy('movement_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($lastMov) {
                    $kmDiff = $this->calculateLifetimeDiff($request->odometer, $lastMov->odometer_reading);
                }

                // Record Inspection Movement (for main system history)
                TyreMovement::create([
                    'tyre_id' => $tyre->id,
                    'vehicle_id' => $session->master_vehicle_id,
                    'position_id' => $data['position_id'],
                    'movement_type' => 'Inspection',
                    'movement_date' => $request->check_date,
                    'odometer_reading' => $request->odometer,
                    'running_km' => $kmDiff,
                    'rtd_reading' => $avg_rtd,
                    'notes' => 'Monitoring Check Session #' . $session->session_id . '. Cond: ' . $request->condition,
                    'tyre_company_id' => $tyre->tyre_company_id,
                    'created_by' => \Auth::id()
                ]);

                // Update Master Tyre RTD and Cumulative Lifetime
                $tyre->update([
                    'current_tread_depth' => $avg_rtd,
                    'total_lifetime_km' => ($tyre->total_lifetime_km ?? 0) + $kmDiff
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Monitoring check recorded and Master Tyre synced');
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
