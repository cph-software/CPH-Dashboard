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

class TyreMovementController extends Controller
{
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
            ->select('id', 'kode_kendaraan')
            ->get();
        // Removed eager loading of all tyres to avoid memory bloat
        // Available tyres will be fetched via AJAX search
        $availableTyres = collect(); 
        $segments = TyreSegment::where('status', 'Active')->select('id', 'segment_name')->get();
        return view('tyre-performance.movement.pemasangan', compact('kendaraans', 'availableTyres', 'segments'));
    }

    public function searchTyres(Request $request)
    {
        $search = $request->input('q');
        
        $tyres = Tyre::whereIn('status', ['New', 'Repaired'])
            ->whereNull('current_vehicle_id')
            ->when($search, function($query) use ($search) {
                $query->where('serial_number', 'like', "%$search%");
            })
            ->with(['brand', 'size', 'pattern'])
            ->limit(20)
            ->get();

        $results = $tyres->map(function($tyre) {
            return [
                'id' => $tyre->id,
                'text' => $tyre->serial_number,
                'brand' => $tyre->brand->brand_name ?? '-',
                'pattern' => $tyre->pattern->name ?? '-',
                'size' => $tyre->size->size ?? '-',
                'sn' => $tyre->serial_number
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function pelepasan()
    {
        $kendaraans = MasterImportKendaraan::whereNotNull('tyre_position_configuration_id')
            ->whereHas('tyres') // Only vehicles with tyres
            ->select('id', 'kode_kendaraan')
            ->get();
        $failureCodes = TyreFailureCode::where('status', 'Active')->select('id', 'name', 'code')->get();
        $segments = TyreSegment::where('status', 'Active')->select('id', 'segment_name')->get();
        return view('tyre-performance.movement.pelepasan', compact('kendaraans', 'failureCodes', 'segments'));
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
        $vehicle = MasterImportKendaraan::findOrFail($id);
        return response()->json($vehicle);
    }

    public function getPositionInfo(Request $request)
    {
        $vehicleId = $request->vehicle_id;
        $type = $request->type ?? 'Installation';
        
        $vehicle = MasterImportKendaraan::select('id', 'tyre_position_configuration_id')->findOrFail($vehicleId);
        $configId = $vehicle->tyre_position_configuration_id;
        
        if ($type === 'Installation') {
            // Get positions that DON'T have a tyre assigned
            $occupiedPositionIds = Tyre::where('current_vehicle_id', $vehicleId)
                ->whereNotNull('current_position_id')
                ->pluck('current_position_id')
                ->toArray();
                
            $positions = TyrePositionDetail::where('configuration_id', $configId)
                ->whereNotIn('id', $occupiedPositionIds)
                ->get();
        } else {
            // Removal: Get tyres currently on this vehicle
            $tyresOnVehicle = Tyre::where('current_vehicle_id', $vehicleId)
                ->whereNotNull('current_position_id')
                ->with(['brand:id,brand_name', 'size:id,size', 'pattern:id,name'])
                ->get(['id', 'serial_number', 'tyre_brand_id', 'tyre_size_id', 'tyre_pattern_id', 'current_position_id']);
                
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

    public function store(Request $request)
    {
        $request->validate([
            'movement_type' => 'required|in:Installation,Removal',
            'vehicle_id' => 'required|exists:master_import_kendaraan,id',
            'position_id' => 'required|exists:tyre_position_details,id',
            'tyre_id' => 'required_if:movement_type,Installation|exists:tyres,id',
            'movement_date' => 'required|date',
            'odometer' => 'nullable|numeric',
            'hour_meter' => 'nullable|numeric',
            'operational_segment_id' => 'nullable|exists:tyre_segments,id',
            'psi_reading' => 'nullable|numeric',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'failure_code_id' => 'nullable|exists:tyre_failure_codes,id',
        ]);

        DB::beginTransaction();
        try {
            $position = TyrePositionDetail::findOrFail($request->position_id);

            if ($request->movement_type === 'Installation') {
                $tyre = Tyre::findOrFail($request->tyre_id);
                
                // 1. Update Tyre status & location
                $tyre->update([
                    'current_vehicle_id' => $request->vehicle_id,
                    'current_position_id' => $request->position_id,
                    'status' => 'Installed'
                ]);

                // 2. Update Position Detail (Secondary sync)
                $position->update(['tyre_id' => $tyre->id]);

                // 3. Log Movement
                TyreMovement::create([
                    'tyre_id' => $tyre->id,
                    'vehicle_id' => $request->vehicle_id,
                    'position_id' => $request->position_id,
                    'operational_segment_id' => $request->operational_segment_id,
                    'work_location' => $request->work_location,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'tyreman_1' => $request->tyreman_1,
                    'tyreman_2' => $request->tyreman_2,
                    'psi_reading' => $request->psi_reading,
                    'new_bolts_used' => $request->has('new_bolts_used'),
                    'rim_size' => $request->rim_size,
                    'movement_type' => 'Installation',
                    'movement_date' => $request->movement_date,
                    'odometer_reading' => $request->odometer,
                    'hour_meter_reading' => $request->hour_meter,
                    'notes' => $request->notes,
                    'created_by' => Auth::id()
                ]);
            } else {
                // Removal
                $tyre = Tyre::where('current_vehicle_id', $request->vehicle_id)
                    ->where('current_position_id', $request->position_id)
                    ->firstOrFail();

                // 1. Log Movement
                TyreMovement::create([
                    'tyre_id' => $tyre->id,
                    'vehicle_id' => $request->vehicle_id,
                    'position_id' => $request->position_id,
                    'operational_segment_id' => $request->operational_segment_id,
                    'work_location' => $request->work_location,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'tyreman_1' => $request->tyreman_1,
                    'tyreman_2' => $request->tyreman_2,
                    'psi_reading' => $request->psi_reading,
                    'new_bolts_used' => $request->has('new_bolts_used'),
                    'rim_size' => $request->rim_size,
                    'movement_type' => 'Removal',
                    'target_status' => $request->target_status,
                    'failure_code_id' => $request->failure_code_id,
                    'movement_date' => $request->movement_date,
                    'odometer_reading' => $request->odometer,
                    'hour_meter_reading' => $request->hour_meter,
                    'notes' => $request->notes,
                    'created_by' => Auth::id()
                ]);

                // 2. Update Tyre status & location
                $tyre->update([
                    'current_vehicle_id' => null,
                    'current_position_id' => null,
                    'status' => $request->target_status ?? 'Repaired'
                ]);

                // 3. Clear Position Detail
                $position->update(['tyre_id' => null]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaksi berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
