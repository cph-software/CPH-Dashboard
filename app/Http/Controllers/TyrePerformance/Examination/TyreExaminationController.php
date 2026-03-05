<?php

namespace App\Http\Controllers\TyrePerformance\Examination;

use App\Http\Controllers\Controller;
use App\Models\Tyre;
use App\Models\MasterImportKendaraan;
use App\Models\TyrePositionDetail;
use App\Models\TyreExamination;
use App\Models\TyreExaminationDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\TyreMovement;
use App\Models\TyreLocation;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class TyreExaminationController extends Controller
{
    public function index()
    {
        return view('tyre-performance.examination.index');
    }

    public function create()
    {
        $kendaraans = MasterImportKendaraan::whereNotNull('tyre_position_configuration_id')
            ->select('id', 'kode_kendaraan', 'no_polisi')
            ->get();
        $locations = TyreLocation::all();
        return view('tyre-performance.examination.create', compact('kendaraans', 'locations'));
    }

    public function getVehicleTyres($vehicleId)
    {
        $vehicle = MasterImportKendaraan::with(['tyrePositionConfiguration.details'])->findOrFail($vehicleId);

        // Fetch tyres currently installed on this vehicle
        $tyres = Tyre::where('current_vehicle_id', $vehicleId)
            ->with(['brand', 'pattern', 'size'])
            ->get()
            ->keyBy('current_position_id');

        $html = view('tyre-performance.examination.partials._tyre_list', [
            'configuration' => $vehicle->tyrePositionConfiguration,
            'tyres' => $tyres
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'examination_date' => 'required|date',
            'location_id' => 'required|exists:tyre_locations,id',
            'operational_segment_id' => 'required|exists:tyre_segments,id',
            'vehicle_id' => 'required|exists:master_import_kendaraan,id',
            'odometer' => 'nullable|numeric',
            'hour_meter' => 'nullable|numeric',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'driver_1' => 'nullable|string|max:255',
            'driver_2' => 'nullable|string|max:255',
            'tyre_man' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'details' => 'required|array',
            'details.*.position_id' => 'required|exists:tyre_position_details,id',
            'details.*.tyre_id' => 'required|exists:tyres,id',
            'details.*.psi' => 'nullable|numeric',
            'details.*.rtd_1' => 'nullable|numeric',
            'details.*.rtd_2' => 'nullable|numeric',
            'details.*.rtd_3' => 'nullable|numeric',
            'details.*.rtd_4' => 'nullable|numeric',
            'details.*.remarks' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $warnings = [];
            $vehicle = MasterImportKendaraan::find($request->vehicle_id);
            $vehicleCode = $vehicle->kode_kendaraan ?? 'Unknown (' . $request->vehicle_id . ')';

            // 1. Future date detection
            if (\Carbon\Carbon::parse($request->examination_date)->isFuture()) {
                $warnings[] = "Tanggal Pemeriksaan ({$request->examination_date}) tidak boleh di masa mendatang.";
            }

            // 2. Time anomaly
            if ($request->start_time && $request->end_time) {
                if (strtotime($request->start_time) > strtotime($request->end_time)) {
                    $warnings[] = "Waktu Mulai ({$request->start_time}) tidak boleh lebih besar dari Waktu Selesai ({$request->end_time}).";
                }
            }

            // --- DETEKSI ANOMALI ODO/HM (Human Error Check) ---
            $lastMovement = TyreMovement::where('vehicle_id', $request->vehicle_id)
                ->whereIn('movement_type', ['Installation', 'Removal', 'Inspection'])
                ->orderBy('movement_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastMovement && $request->odometer) {
                if ($request->odometer < $lastMovement->odometer_reading) {
                    $warnings[] = "Odometer Unit " . $vehicleCode . " ({$request->odometer}) menurun drastis dari catatan terakhir ({$lastMovement->odometer_reading}).";
                }
            }

            if ($lastMovement && $request->hour_meter) {
                if ($request->hour_meter < $lastMovement->hour_meter_reading) {
                    $warnings[] = "Hour Meter Unit " . $vehicleCode . " ({$request->hour_meter}) menurun drastis dari catatan terakhir ({$lastMovement->hour_meter_reading}).";
                }
            }

            // 1. Create Header
            $exam = TyreExamination::create([
                'examination_date' => $request->examination_date,
                'location_id' => $request->location_id,
                'operational_segment_id' => $request->operational_segment_id,
                'odometer' => $request->odometer,
                'hour_meter' => $request->hour_meter,
                'vehicle_id' => $request->vehicle_id,
                'driver_1' => $request->driver_1,
                'driver_2' => $request->driver_2,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'tyre_man' => $request->tyre_man,
                'status' => 'Draft',
                'notes' => $request->notes,
            ]);

            // 2. Create Details, Update Tyre RTD & Create Movement History
            foreach ($request->details as $key => $detail) {
                // Skip if position doesn't have a tyre
                if (empty($detail['tyre_id']))
                    continue;

                // Calculate average RTD and check if any data is filled
                $rtds = array_filter([
                    $detail['rtd_1'] ?? null,
                    $detail['rtd_2'] ?? null,
                    $detail['rtd_3'] ?? null,
                    $detail['rtd_4'] ?? null
                ], function ($v) {
                    return $v !== null && $v !== '';
                });

                $hasPsi = !empty($detail['psi']);
                $hasRtd = count($rtds) > 0;
                $hasRemarks = !empty($detail['remarks']);
                $hasPhoto = $request->hasFile("details.{$key}.photo");

                // ONLY save if at least one field is filled
                if (!$hasPsi && !$hasRtd && !$hasRemarks && !$hasPhoto) {
                    continue;
                }

                // Handle Photo Upload
                $photoPath = null;
                if ($hasPhoto) {
                    $photoPath = $request->file("details.{$key}.photo")->store('examinations/' . date('Y-m'), 'public');
                }

                TyreExaminationDetail::create([
                    'examination_id' => $exam->id,
                    'position_id' => $detail['position_id'],
                    'tyre_id' => $detail['tyre_id'],
                    'psi_reading' => $detail['psi'] ?? null,
                    'rtd_1' => $detail['rtd_1'] ?? null,
                    'rtd_2' => $detail['rtd_2'] ?? null,
                    'rtd_3' => $detail['rtd_3'] ?? null,
                    'rtd_4' => $detail['rtd_4'] ?? null,
                    'remarks' => $detail['remarks'] ?? null,
                    'photo' => $photoPath,
                ]);

                $avgRtd = $hasRtd ? array_sum($rtds) / count($rtds) : null;

                // Update current RTD of the tyre if measured
                $tyre = Tyre::find($detail['tyre_id']);
                if ($tyre) {
                    // 3. PSI anomaly
                    if ($detail['psi'] !== null && ($detail['psi'] < 0 || $detail['psi'] > 200)) {
                        $warnings[] = "PSI Ban SN {$tyre->serial_number} ({$detail['psi']}) tidak wajar.";
                    }

                    if ($avgRtd !== null) {
                        // 4. Physical possibility check (RTD > Initial)
                        if ($tyre->initial_tread_depth > 0 && $avgRtd > $tyre->initial_tread_depth) {
                            $warnings[] = "RTD Ban SN {$tyre->serial_number} ({$avgRtd}mm) melebihi batas RTD awal/baru ({$tyre->initial_tread_depth}mm).";
                        }

                        // 5. Logical check (RTD increase)
                        if ($avgRtd > $tyre->current_tread_depth && $tyre->current_tread_depth > 0) {
                            $warnings[] = "RTD Ban SN " . $tyre->serial_number . " ({$avgRtd}mm) meningkat dari catatan sebelumnya ({$tyre->current_tread_depth}mm).";
                        }
                        
                        $tyre->update(['current_tread_depth' => $avgRtd]);
                    }
                }

                // IMPORTANT: Record this inspection in movement history
                TyreMovement::create([
                    'tyre_id' => $detail['tyre_id'],
                    'vehicle_id' => $request->vehicle_id,
                    'position_id' => $detail['position_id'],
                    'movement_type' => 'Inspection',
                    'movement_date' => $request->examination_date,
                    'odometer_reading' => $request->odometer,
                    'hour_meter_reading' => $request->hour_meter,
                    'psi_reading' => $detail['psi'] ?? null,
                    'rtd_reading' => $avgRtd,
                    'notes' => 'Pemeriksaan rutin: ' . ($detail['remarks'] ?? '-'),
                    'created_by' => Auth::id(),
                ]);
            }

            // 3. Final Check for Anomalies (Human Error)
            if (!empty($warnings)) {
                DB::rollBack();
                
                setLogActivity(Auth::id(), 'HUMAN ERROR (Data Mismatch) attempt detected during Examination: ' . implode(' | ', $warnings), [
                    'action_type' => 'error',
                    'module' => 'Human Error',
                    'data_after' => [
                        'vehicle' => $vehicleCode,
                        'warnings' => $warnings,
                        'attempted_data' => [
                            'odometer' => $request->odometer,
                            'hour_meter' => $request->hour_meter,
                        ]
                    ]
                ]);

                return response()->json([
                    'success' => false,
                    'message' => "Pemeriksaan GAGAL DISIMPAN (Deteksi Human Error):\n\n" . implode("\n", $warnings)
                ], 422);
            }

            DB::commit();

            setLogActivity(Auth::id(), 'Membuat pemeriksaan ban untuk kendaraan: ' . $vehicleCode, [
                'action_type' => 'create',
                'module' => 'Examination',
                'data_after' => [
                    'examination_id' => $exam->id,
                    'examination_date' => $request->examination_date,
                    'vehicle' => $vehicleCode,
                    'odometer' => $request->odometer,
                    'total_details' => count($request->details)
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pemeriksaan berhasil disimpan',
                'redirect' => route('examination.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function data(Request $request)
    {
        $query = TyreExamination::with(['vehicle']);

        // DataTables logic similar to Movement
        $totalRecords = TyreExamination::count();

        if ($request->has('search') && $request->input('search.value')) {
            $searchValue = $request->input('search.value');
            $query->whereHas('vehicle', function ($q) use ($searchValue) {
                $q->where('kode_kendaraan', 'like', "%$searchValue%");
            });
        }

        $filteredRecords = $query->count();
        $query->orderBy('examination_date', 'desc')->orderBy('created_at', 'desc');

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $exams = $query->skip($start)->take($length)->get();

        $data = $exams->map(function ($row) {
            return [
                'id' => $row->id,
                'date' => Carbon::parse($row->examination_date)->format('d/m/Y'),
                'vehicle' => $row->vehicle->kode_kendaraan ?? '-',
                'odometer' => number_format($row->odometer, 0),
                'tyre_man' => $row->tyre_man ?? '-',
                'status' => $row->status,
                'action' => '<a href="' . route('examination.show', $row->id) . '" class="btn btn-sm btn-info"><i class="ri-eye-line"></i> Detail</a>'
            ];
        });

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalRecords),
            "recordsFiltered" => intval($filteredRecords),
            "data" => $data
        ]);
    }

    public function show($id)
    {
        $exam = TyreExamination::with([
            'vehicle',
            'location',
            'segment',
            'details.position',
            'details.tyre.brand',
            'details.tyre.pattern',
            'details.tyre.size'
        ])->findOrFail($id);
        return view('tyre-performance.examination.show', compact('exam'));
    }

    public function exportPdf(Request $request, $id)
    {
        $exam = TyreExamination::with([
            'vehicle',
            'location',
            'segment',
            'details.position',
            'details.tyre.brand',
            'details.tyre.pattern',
            'details.tyre.size'
        ])->findOrFail($id);

        if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = Pdf::loadView('tyre-performance.examination.pdf', compact('exam'))
                ->setPaper('a5', 'landscape');

            $filename = 'Examination-Form-' . $exam->vehicle->kode_kendaraan . '-' . $exam->examination_date . '.pdf';

            if ($request->action == 'stream') {
                return $pdf->stream($filename);
            }

            return $pdf->download($filename);
        }

        return view('tyre-performance.examination.pdf', compact('exam'));
    }
}
