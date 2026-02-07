<?php

namespace App\Http\Controllers\TyrePerformance\Movement;

use App\Http\Controllers\Controller;
use App\Models\Tyre;
use App\Models\MasterImportKendaraan;
use App\Models\TyrePositionConfiguration;
use App\Models\TyrePositionDetail;
use App\Models\TyreMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TyreMovementController extends Controller
{
    public function index()
    {
        $kendaraans = MasterImportKendaraan::whereNotNull('tyre_position_configuration_id')->get();
        return view('tyre-performance.movement.index', compact('kendaraans'));
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

    public function getPositionInfo(Request $request)
    {
        $positionId = $request->position_id;
        $vehicleId = $request->vehicle_id;
        
        $availableTyres = Tyre::whereNull('current_vehicle_id')
            ->whereIn('status', ['New', 'Repaired'])
            ->with(['brand', 'size'])
            ->get();

        return response()->json([
            'availableTyres' => $availableTyres
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
        ]);

        DB::beginTransaction();
        try {
            if ($request->movement_type === 'Installation') {
                $tyre = Tyre::findOrFail($request->tyre_id);
                
                // 1. Update Tyre status & location
                $tyre->update([
                    'current_vehicle_id' => $request->vehicle_id,
                    'current_position_id' => $request->position_id,
                    'status' => 'Installed'
                ]);

                // 2. Log Movement
                TyreMovement::create([
                    'tyre_id' => $tyre->id,
                    'vehicle_id' => $request->vehicle_id,
                    'position_id' => $request->position_id,
                    'movement_type' => 'Installation',
                    'movement_date' => $request->movement_date,
                    'odometer_reading' => $request->odometer,
                    'notes' => $request->notes,
                    'created_by' => Auth::id()
                ]);
            } else {
                // Removal
                $tyre = Tyre::where('current_vehicle_id', $request->vehicle_id)
                    ->where('current_position_id', $request->position_id)
                    ->firstOrFail();

                // 1. Log Movement FIRST (while it still has context)
                TyreMovement::create([
                    'tyre_id' => $tyre->id,
                    'vehicle_id' => $request->vehicle_id,
                    'position_id' => $request->position_id,
                    'movement_type' => 'Removal',
                    'movement_date' => $request->movement_date,
                    'odometer_reading' => $request->odometer,
                    'notes' => $request->notes,
                    'created_by' => Auth::id()
                ]);

                // 2. Update Tyre status & location
                $tyre->update([
                    'current_vehicle_id' => null,
                    'current_position_id' => null,
                    'status' => $request->target_status ?? 'Repaired' // e.g. Scrap, Repaired, New
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaksi berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
