<?php

namespace App\Http\Controllers\TyrePerformance\Examination;

use App\Http\Controllers\Controller;
use App\Models\Tyre;
use App\Models\MasterImportKendaraan;
use App\Models\TyrePositionDetail;
use App\Models\TyreExamination;
use App\Models\TyreExaminationDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\TyreExaminationImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\TyreMovement;
use App\Models\TyreLocation;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class TyreExaminationController extends Controller
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
            // Odometer reset or replaced. Assume current reading is distance since reset.
            return (float) $currentReading;
        }

        return (float) $diff;
    }

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

        // Fetch latest readings from Movement and Examination
        $lastMovement = TyreMovement::where('vehicle_id', $vehicleId)
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $lastExamination = TyreExamination::where('vehicle_id', $vehicleId)
            ->orderBy('examination_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $lastOdo = 0;
        $lastHm = 0;

        // Logic to determine absolute latest (comparing dates if necessary)
        // For simplicity, we can just compare the two latest found
        if ($lastMovement && $lastExamination) {
            $movDate = Carbon::parse($lastMovement->movement_date);
            $examDate = Carbon::parse($lastExamination->examination_date);

            if ($movDate->gt($examDate)) {
                $lastOdo = $lastMovement->odometer_reading;
                $lastHm = $lastMovement->hour_meter_reading;
            } else {
                $lastOdo = $lastExamination->odometer;
                $lastHm = $lastExamination->hour_meter;
            }
        } elseif ($lastMovement) {
            $lastOdo = $lastMovement->odometer_reading;
            $lastHm = $lastMovement->hour_meter_reading;
        } elseif ($lastExamination) {
            $lastOdo = $lastExamination->odometer;
            $lastHm = $lastExamination->hour_meter;
        }

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
            'html' => $html,
            'last_odometer' => $lastOdo,
            'last_hour_meter' => $lastHm
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
            'is_meter_reset' => 'nullable|boolean',
            'temp_id' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $vehicle = MasterImportKendaraan::find($request->vehicle_id);
            $vehicleCode = $vehicle->kode_kendaraan ?? 'Unknown';
            $warnings = [];

            // --- 1. PRE-VALIDATION: DETEKSI ANOMALI ODO/HM (Human Error Check) ---
            // Cari data terakhir dari kedua tabel (Movement & Examination)
            $lastM = TyreMovement::where('vehicle_id', $request->vehicle_id)
                ->orderBy('movement_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $lastE = TyreExamination::where('vehicle_id', $request->vehicle_id)
                ->orderBy('examination_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $prevOdo = 0;
            $prevHm = 0;

            if ($lastM && $lastE) {
                $mDate = Carbon::parse($lastM->movement_date);
                $eDate = Carbon::parse($lastE->examination_date);
                
                if ($mDate->gt($eDate)) {
                    $prevOdo = $lastM->odometer_reading;
                    $prevHm = $lastM->hour_meter_reading;
                } elseif ($eDate->gt($mDate)) {
                    $prevOdo = $lastE->odometer;
                    $prevHm = $lastE->hour_meter;
                } else {
                    // Jika tanggal sama, bandingkan created_at atau id
                    $prevOdo = ($lastM->id > $lastE->id) ? $lastM->odometer_reading : $lastE->odometer;
                    $prevHm = ($lastM->id > $lastE->id) ? $lastM->hour_meter_reading : $lastE->hour_meter;
                }
            } elseif ($lastM) {
                $prevOdo = $lastM->odometer_reading;
                $prevHm = $lastM->hour_meter_reading;
            } elseif ($lastE) {
                $prevOdo = $lastE->odometer;
                $prevHm = $lastE->hour_meter;
            }

            // Validasi ODO/HM menurun jika TIDAK reset
            if (!$request->is_meter_reset) {
                if ($request->odometer !== null && $request->odometer < $prevOdo) {
                    $warnings[] = "KM/Odometer ({$request->odometer}) menurun dari data terakhir ({$prevOdo}). Centang 'Reset Meter' jika ini benar, atau perbaiki angka KM.";
                }
                if ($request->hour_meter !== null && $request->hour_meter < $prevHm) {
                    $warnings[] = "HM/Hour Meter ({$request->hour_meter}) menurun dari data terakhir ({$prevHm}). Centang 'Reset Meter' jika ini benar, atau perbaiki angka HM.";
                }
            }

            // 2. Future date detection
            if (Carbon::parse($request->examination_date)->isFuture()) {
                $warnings[] = "Tanggal Pemeriksaan tidak boleh di masa mendatang.";
            }

            // 3. Time anomaly
            if ($request->start_time && $request->end_time) {
                if (strtotime($request->start_time) > strtotime($request->end_time)) {
                    $warnings[] = "Waktu Mulai tidak boleh lebih besar dari Waktu Selesai.";
                }
            }

            // ABORT jika ada human error
            if (!empty($warnings)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Deteksi Human Error:\n" . implode("\n", $warnings)
                ], 422);
            }

            // --- 2. LANJUT KE PROSES PENYIMPANAN ---
            // 1. Create Header
            $exam = new TyreExamination([
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

            // Handle General Unit Photo
            $subFolder = 'examinations/' . date('Y-m');
            if ($request->hasFile('photo_unit_front')) {
                $exam->photo_unit_front = $request->file('photo_unit_front')->store($subFolder, 'public');
            }

            $exam->save();

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

                // Get Tyre first before checking its serial number!
                $tyre = Tyre::find($detail['tyre_id']);
                if (!$tyre) continue;

                // Check if any AJAX photo was uploaded for this tyre
                $hasAjaxPhoto = TyreExaminationImage::where('notes', $request->temp_id)
                    ->where('serial_number', $tyre->serial_number)
                    ->exists();

                // ONLY save if at least one field is filled (PSI, RTD, Remarks, standard Photo, or AJAX Photo)
                if (!$hasPsi && !$hasRtd && !$hasRemarks && !$hasPhoto && !$hasAjaxPhoto) {
                    continue;
                }

                // Handle standard Photo Upload
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

                // Link AJAX Uploaded images to this examination detail
                TyreExaminationImage::where('notes', $request->temp_id)
                    ->where('serial_number', $tyre->serial_number)
                    ->update(['examination_id' => $exam->id]);

                $avgRtd = $hasRtd ? array_sum($rtds) / count($rtds) : null;

                // Update current RTD of the tyre if measured
                if ($tyre) {
                    // --- Calculate Lifetime since last recorded event (Date-Aware) ---
                    $lastMov = TyreMovement::where('tyre_id', $tyre->id)
                        ->where('movement_date', '<=', $request->examination_date)
                        ->orderBy('movement_date', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();

                    $kmDiff = 0;
                    $hmDiff = 0;
                    if ($lastMov) {
                        $kmDiff = $this->calculateLifetimeDiff($request->odometer, $lastMov->odometer_reading);
                        $hmDiff = $this->calculateLifetimeDiff($request->hour_meter, $lastMov->hour_meter_reading);
                    }

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
                    }

                    // Update Tyre Master
                    $tyre->update([
                        'current_tread_depth' => $avgRtd ?? $tyre->current_tread_depth,
                        'total_lifetime_km' => ($tyre->total_lifetime_km ?? 0) + $kmDiff,
                        'total_lifetime_hm' => ($tyre->total_lifetime_hm ?? 0) + $hmDiff,
                    ]);
                }

                // IMPORTANT: Record this inspection in movement history with calculated diffs
                TyreMovement::create([
                    'tyre_id' => $detail['tyre_id'],
                    'vehicle_id' => $request->vehicle_id,
                    'position_id' => $detail['position_id'],
                    'movement_type' => 'Inspection',
                    'movement_date' => $request->examination_date,
                    'odometer_reading' => $request->odometer,
                    'hour_meter_reading' => $request->hour_meter,
                    'running_km' => $kmDiff ?? 0,
                    'running_hm' => $hmDiff ?? 0,
                    'psi_reading' => $detail['psi'] ?? null,
                    'rtd_reading' => $avgRtd,
                    'notes' => 'Pemeriksaan rutin: ' . ($detail['remarks'] ?? '-'),
                    'created_by' => Auth::id(),
                ]);
            }

            // 3. Final Check for Anomalies (Human Error)
            if (!empty($warnings)) {
                DB::rollBack();
                
                setLogActivity(Auth::id(), 'Deteksi Human Error: Pemeriksaan Ban pada unit ' . $vehicleCode, [
                    'action_type' => 'error',
                    'module' => 'Human Error',
                    'data_after' => [
                        'Kendaraan' => $vehicleCode,
                        'Pesan Error' => $warnings,
                        'Data Yang Diinput' => [
                            'Tanggal' => $request->examination_date,
                            'Odometer' => $request->odometer,
                            'Hour Meter' => $request->hour_meter,
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
                    'Kendaraan' => $vehicleCode,
                    'Tanggal' => $request->examination_date,
                    'Odometer' => $request->odometer,
                    'Hour Meter' => $request->hour_meter,
                    'Total Ban Diinspeksi' => count($request->details)
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

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
            'type' => 'required|string',
            'serial_number' => 'required|string',
            'temp_id' => 'required|string'
        ]);

        try {
            $file = $request->file('image');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('tyre-examinations/' . date('Y-m'), $fileName, 'public');

            $image = TyreExaminationImage::create([
                'serial_number' => $request->serial_number,
                'image_type' => $request->type,
                'image_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'notes' => $request->temp_id,
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
}
