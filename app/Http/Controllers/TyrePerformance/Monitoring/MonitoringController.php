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
use App\Exports\Monitoring\SessionExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TyreLocation;
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

        $masterVehicles = MasterImportKendaraan::select('id', 'no_polisi', 'kode_kendaraan')->get();
        
        return view('tyre-performance.monitoring.index', compact('vehicles', 'masterVehicles'));
    }

    public function showVehicle($id)
    {
        $vehicle = TyreMonitoringVehicle::findOrFail($id);
        $sessions = TyreMonitoringSession::where('vehicle_id', $id)->withCount('checks')->latest()->get();
        
        return view('tyre-performance.monitoring.vehicle_sessions', compact('vehicle', 'sessions'));
    }

    public function showSession($id)
    {
        $session = TyreMonitoringSession::with(['vehicle.sessions', 'installations.positionDetail', 'checks', 'removal'])->findOrFail($id);
        
        $masterPositions = [];
        if ($session->master_vehicle_id) {
            $vehicle = MasterImportKendaraan::find($session->master_vehicle_id);
            if ($vehicle && $vehicle->tyre_position_configuration_id) {
                $masterPositions = TyrePositionDetail::where('configuration_id', $vehicle->tyre_position_configuration_id)
                    ->orderBy('display_order')
                    ->get();
            }
        }
        $locations = TyreLocation::all();
        
        return view('tyre-performance.monitoring.session_detail', compact('session', 'masterPositions', 'locations'));
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
            'position' => 'required|integer',
            'position_id' => 'nullable|exists:tyre_position_details,id',
            'serial_number' => 'required|exists:tyres,serial_number',
            'rtd_1' => 'required|numeric',
            'rtd_2' => 'required|numeric',
            'rtd_3' => 'required|numeric',
        ]);

        $session = TyreMonitoringSession::findOrFail($request->session_id);
        $tyre = Tyre::where('serial_number', $request->serial_number)->with(['brand', 'size', 'pattern'])->first();
        
        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['tyre_id'] = $tyre->id;
            $data['brand'] = $tyre->brand->brand_name ?? '-';
            $data['size'] = $tyre->size->size ?? '-';
            $data['pattern'] = $tyre->pattern->name ?? '-';
            $data['avg_rtd'] = ($request->rtd_1 + $request->rtd_2 + $request->rtd_3) / 3;
            $data['install_date'] = $session->install_date;

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
                'odometer_reading' => $session->odometer_start,
                'rtd_reading' => $data['avg_rtd'],
                'work_location_id' => $tyre->work_location_id,
                'notes' => 'Monitoring Installation Session #' . $session->session_id,
                'tyre_company_id' => $tyre->tyre_company_id,
                'created_by' => \Auth::id()
            ]);

            // Sync with Position Detail (Secondary sync for vehicle layout)
            if ($request->position_id) {
                TyrePositionDetail::where('id', $request->position_id)->update(['tyre_id' => $tyre->id]);
            }

            // Sync stock (Decrement from current tyre location)
            if ($tyre->work_location_id) {
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
