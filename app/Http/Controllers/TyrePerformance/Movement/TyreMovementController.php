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
        $locations = \App\Models\TyreLocation::all();
        $segments = collect(); // Will be loaded via AJAX based on location
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

        $tyres = $query->with(['brand', 'size', 'pattern'])
            ->limit(20)
            ->get();

        $results = $tyres->map(function ($tyre) {
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
        $failureCodes = TyreFailureCode::where('status', 'Active')->select('id', 'failure_name', 'failure_code')->get();
        $locations = \App\Models\TyreLocation::all();
        $segments = collect();
        return view('tyre-performance.movement.pelepasan', compact('kendaraans', 'failureCodes', 'segments', 'locations'));
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
        $vehicle = MasterImportKendaraan::findOrFail($id);
        return response()->json($vehicle);
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
                ->with(['brand:id,brand_name', 'size:id,size', 'pattern:id,name'])
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
            'work_location_id' => 'nullable|exists:tyre_locations,id',
            'psi_reading' => 'nullable|numeric',
            'rtd_reading' => 'nullable|numeric',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'failure_code_id' => 'nullable|exists:tyre_failure_codes,id',
            'install_condition' => 'nullable|in:New,Spare,Repair',
            'new_bolts_quantity' => 'nullable|integer',
            'remarks' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $position = TyrePositionDetail::findOrFail($request->position_id);

            if ($request->movement_type === 'Installation') {
                $tyre = Tyre::findOrFail($request->tyre_id);
                $oldLocationId = $tyre->work_location_id; // Store old location before update

                // --- HANDLE REPLACEMENT (If position is already occupied) ---
                $isReplacement = false;
                if ($position->tyre_id) {
                    $isReplacement = true;

                    $oldTyre = Tyre::find($position->tyre_id);
                    if ($oldTyre) {
                        // 1. Calculate Lifetime for Old Tyre
                        $lastOldInstall = TyreMovement::where('tyre_id', $oldTyre->id)
                            ->where('movement_type', 'Installation')
                            ->orderBy('movement_date', 'desc')
                            ->orderBy('id', 'desc')
                            ->first();

                        $kmDiff = 0;
                        $hmDiff = 0;
                        if ($lastOldInstall) {
                            if ($request->odometer && $lastOldInstall->odometer_reading) {
                                $diff = $request->odometer - $lastOldInstall->odometer_reading;
                                if ($diff > 0)
                                    $kmDiff = $diff;
                            }
                            if ($request->hour_meter && $lastOldInstall->hour_meter_reading) {
                                $diff = $request->hour_meter - $lastOldInstall->hour_meter_reading;
                                if ($diff > 0)
                                    $hmDiff = $diff;
                            }
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
                $tyre->update([
                    'current_vehicle_id' => $request->vehicle_id,
                    'current_position_id' => $request->position_id,
                    'status' => 'Installed',
                    // Optional: Update RTD if provided during install (e.g. used tyre)
                    'current_tread_depth' => $request->rtd_reading ?? $tyre->current_tread_depth
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
                    'install_condition' => $request->install_condition,
                    'is_replacement' => $isReplacement,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'tyreman_1' => $request->tyreman_1,
                    'tyreman_2' => $request->tyreman_2,
                    'psi_reading' => $request->psi_reading,
                    'rtd_reading' => $request->rtd_reading,
                    'new_bolts_used' => $request->has('new_bolts_used'),
                    'new_bolts_quantity' => $request->new_bolts_quantity,
                    'rim_size' => $request->rim_size,
                    'movement_type' => 'Installation',
                    'movement_date' => $request->movement_date,
                    'odometer_reading' => $request->odometer,
                    'hour_meter_reading' => $request->hour_meter,
                    'remarks' => $request->remarks,
                    'notes' => $request->notes,
                    'created_by' => Auth::id()
                ]);
            } else {
                // Removal
                $tyre = Tyre::where('current_vehicle_id', $request->vehicle_id)
                    ->where('current_position_id', $request->position_id)
                    ->firstOrFail();

                // --- Calculate Lifetime (KM & HM) ---
                $lastInstallation = TyreMovement::where('tyre_id', $tyre->id)
                    ->where('movement_type', 'Installation')
                    ->orderBy('movement_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                $kmDiff = 0;
                $hmDiff = 0;

                if ($lastInstallation) {
                    // Calculate KM Difference
                    if ($request->odometer && $lastInstallation->odometer_reading) {
                        $diff = $request->odometer - $lastInstallation->odometer_reading;
                        if ($diff > 0)
                            $kmDiff = $diff;
                    }

                    // Calculate HM Difference
                    if ($request->hour_meter && $lastInstallation->hour_meter_reading) {
                        $diff = $request->hour_meter - $lastInstallation->hour_meter_reading;
                        if ($diff > 0)
                            $hmDiff = $diff;
                    }
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
                    'new_bolts_used' => $request->has('new_bolts_used'),
                    'new_bolts_quantity' => $request->new_bolts_quantity,
                    'rim_size' => $request->rim_size,
                    'movement_type' => 'Removal',
                    'target_status' => $request->target_status,
                    'failure_code_id' => $request->failure_code_id,
                    'movement_date' => $request->movement_date,
                    'odometer_reading' => $request->odometer,
                    'hour_meter_reading' => $request->hour_meter,
                    'remarks' => $request->remarks,
                    'notes' => $request->notes,
                    'created_by' => Auth::id()
                ]);

                // 2. Update Tyre status, location, Total Lifetime AND RTD
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

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaksi berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiHistory(Request $request)
    {
        $query = TyreMovement::with(['tyre', 'vehicle', 'position']);

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
            return [
                'id' => $row->id,
                'movement_date' => \Carbon\Carbon::parse($row->movement_date)->format('d/m/Y'),
                'movement_type' => $row->movement_type,
                'install_condition' => $row->install_condition,
                'is_replacement' => $row->is_replacement,
                'tyre_sn' => $row->tyre->serial_number ?? '-',
                'vehicle_code' => $row->vehicle->kode_kendaraan ?? '-',
                'position_name' => $row->position ? $row->position->position_code . ' - ' . $row->position->position_name : '-',
                'action' => '<button type="button" class="btn btn-sm btn-danger" onclick="rollbackMovement(' . $row->id . ')"><i class="icon-base ri ri-history-line"></i> Rollback</button>'
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
            } else {
                // LOGIC: Undo Removal (Return to vehicle)

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
                    'status' => 'Installed'
                ]);

                // 2. Occupy the position
                $position->update(['tyre_id' => $tyre->id]);
            }

            // Delete the log
            $movement->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaksi berhasil di-rollback.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal Rollback: ' . $e->getMessage()], 500);
        }
    }
}
