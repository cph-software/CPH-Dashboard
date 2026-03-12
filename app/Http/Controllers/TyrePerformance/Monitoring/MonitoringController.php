<?php

namespace App\Http\Controllers\TyrePerformance\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\Tyre;
use App\Models\TyreMonitoringVehicle;
use App\Models\TyreMonitoringSession;
use App\Models\TyreMonitoringInstallation;
use App\Models\TyreMonitoringCheck;
use App\Models\TyreMonitoringRemoval;
use App\Exports\Monitoring\SessionExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class MonitoringController extends Controller
{
    public function index()
    {
        $vehicles = TyreMonitoringVehicle::withCount(['sessions' => function($q) {
            $q->where('status', 'active');
        }])->latest()->get();
        
        return view('tyre-performance.monitoring.index', compact('vehicles'));
    }

    public function showVehicle($id)
    {
        $vehicle = TyreMonitoringVehicle::findOrFail($id);
        $sessions = TyreMonitoringSession::where('vehicle_id', $id)->withCount('checks')->latest()->get();
        
        return view('tyre-performance.monitoring.vehicle_sessions', compact('vehicle', 'sessions'));
    }

    public function showSession($id)
    {
        $session = TyreMonitoringSession::with(['vehicle', 'installations', 'checks', 'removal'])->findOrFail($id);
        
        return view('tyre-performance.monitoring.session_detail', compact('session'));
    }

    public function storeVehicle(Request $request)
    {
        $request->validate([
            'fleet_name' => 'required|string',
            'vehicle_number' => 'required|string',
            'driver_name' => 'required|string',
            'tire_positions' => 'required|integer|min:1',
        ]);

        TyreMonitoringVehicle::create($request->all());
        return redirect()->back()->with('success', 'Monitoring Vehicle created');
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

        $session = TyreMonitoringSession::create($request->all());
        return redirect()->route('monitoring.sessions.show', $session->session_id)->with('success', 'Monitoring Session started');
    }

    public function storeInstallation(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:tyre_monitoring_session,session_id',
            'position' => 'required|integer',
            'serial_number' => 'required|exists:tyres,serial_number',
            'rtd_1' => 'required|numeric',
            'rtd_2' => 'required|numeric',
            'rtd_3' => 'required|numeric',
        ]);

        $tyre = Tyre::where('serial_number', $request->serial_number)->with(['brand', 'size', 'pattern'])->first();
        
        $data = $request->all();
        $data['brand'] = $tyre->brand->brand_name ?? '-';
        $data['size'] = $tyre->size->size ?? '-';
        $data['pattern'] = $tyre->pattern->name ?? '-';
        $data['avg_rtd'] = ($request->rtd_1 + $request->rtd_2 + $request->rtd_3) / 3;
        $data['install_date'] = Carbon::now()->toDateString(); // Or from session

        TyreMonitoringInstallation::create($data);
        return redirect()->back()->with('success', 'Tyre installation recorded');
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
        
        $data = $request->all();
        $data['operation_mileage'] = $request->odometer - $session->odometer_start;
        // check_number auto increment per session
        $lastCheck = TyreMonitoringCheck::where('session_id', $session->session_id)->latest('check_number')->first();
        $data['check_number'] = $lastCheck ? $lastCheck->check_number + 1 : 1;

        TyreMonitoringCheck::create($data);
        return redirect()->back()->with('success', 'Monitoring check recorded');
    }

    public function storeRemoval(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:tyre_monitoring_session,session_id',
            'serial_number' => 'required|exists:tyre_monitoring_installation,serial_number',
            'removal_date' => 'required|date',
            'odometer' => 'required|integer',
            'final_rtd' => 'required|numeric',
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

            TyreMonitoringRemoval::create($data);

            // Update main tyre status
            $tyre = Tyre::where('serial_number', $request->serial_number)->first();
            if ($tyre) {
                // If removal condition is "Scrapped" or alike, update accordingly
                $status = 'New'; // Default back to stock if not specified
                if (stripos($request->tyre_condition_after, 'scrap') !== false || stripos($request->removal_reason, 'worn') !== false) {
                    $status = 'Scrap';
                } else {
                    $status = 'Repaired'; // Assume needs repair/check if removed from monitoring
                }
                $tyre->update(['status' => $status]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Tyre removal recorded');
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

    public function export($sessionId)
    {
        $session = TyreMonitoringSession::findOrFail($sessionId);
        $fileName = 'Monitoring_Session_' . $session->session_id . '_' . $session->install_date . '.xlsx';
        
        return Excel::download(new SessionExport($sessionId), $fileName);
    }
}
