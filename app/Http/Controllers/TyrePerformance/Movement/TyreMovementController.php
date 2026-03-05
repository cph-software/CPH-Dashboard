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
            'is_meter_reset' => 'nullable|boolean',
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
                ->whereIn('movement_type', ['Installation', 'Removal', 'Inspection'])
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

            $position = TyrePositionDetail::findOrFail($request->position_id);

            if ($request->movement_type === 'Installation') {
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
                    'install_condition' => $actualCondition,
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
                    'new_bolts_used' => $request->has('new_bolts_used'),
                    'new_bolts_quantity' => $request->new_bolts_quantity,
                    'rim_size' => $request->rim_size,
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
                    'created_by' => Auth::id()
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

                $actionLabel = $request->movement_type === 'Installation' ? 'Pemasangan' : 'Pelepasan';
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

            setLogActivity(Auth::id(), ($request->movement_type === 'Installation' ? 'Pemasangan' : 'Pelepasan') . ' ban pada kendaraan ' . $vehicleCode, [
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
                'movement_type_display' => $row->movement_type === 'Installation' ? 'Pasang' : ($row->movement_type === 'Removal' ? 'Lepas' : 'Inspeksi'),
                'install_condition' => $row->install_condition,
                'is_replacement' => $row->is_replacement,
                'tyre_sn' => $row->tyre->serial_number ?? '-',
                'vehicle_code' => $row->vehicle->kode_kendaraan ?? '-',
                'position_name' => $row->position ? $row->position->position_code . ' - ' . $row->position->position_name : '-',
                'failure_info' => $failureInfo,
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
