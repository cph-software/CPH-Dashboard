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
use App\Services\VehicleReadingService;

class TyreExaminationController extends Controller
{
    /**
     * Helper to calculate lifetime difference handling potential meter resets (minus diff)
     */
    private function calculateLifetimeDiff($currentReading, $lastInstallReading)
    {
        return VehicleReadingService::calculateLifetimeDiff($currentReading, $lastInstallReading);
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

        // Fetch latest readings via centralized Service
        $readings = VehicleReadingService::getLastVehicleReadings($vehicleId);

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
            'last_odometer' => $readings['odometer'],
            'last_hour_meter' => $readings['hour_meter'],
            'company_id' => $vehicle->tyre_company_id,
            'company_name' => $vehicle->company->company_name ?? 'Unknown'
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
            'details' => 'nullable|array',
            'is_meter_reset' => 'nullable|boolean',
            'temp_id' => 'required|string',
            'exam_type' => 'nullable|in:Sales,Customer',
        ]);

        $examType = $request->input('exam_type', 'Customer');
        $approvalStatus = ($examType === 'Sales') ? 'Pending' : 'Approved';
        $user = auth()->user();

        DB::beginTransaction();
        try {
            $vehicle = MasterImportKendaraan::find($request->vehicle_id);
            $vehicleCode = $vehicle->kode_kendaraan ?? 'Unknown';
            $warnings = [];

            // --- 1. PRE-VALIDATION: DETEKSI ANOMALI ODO/HM (Human Error Check) via Service ---
            $readings = VehicleReadingService::getLastVehicleReadings($request->vehicle_id);
            $lastOdo = $readings['odometer'];

            if ($request->odometer < $lastOdo && !$request->has('is_meter_reset')) {
                $warnings[] = "Nilai ODOMETER ({$request->odometer}) LEBIH RENDAH dari catatan terakhir ({$lastOdo}). Cek kembali apakah ada penggantian speedometer atau salah input.";
            }

            if (Carbon::parse($request->examination_date)->isFuture()) {
                $warnings[] = "Tanggal Pemeriksaan tidak boleh di masa mendatang.";
            }

            if (!empty($warnings)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => "Deteksi Human Error:\n" . implode("\n", $warnings)], 422);
            }

            // --- 2. LANJUT KE PROSES PENYIMPANAN ---
            $examination = TyreExamination::create([
                'examination_date' => $request->examination_date,
                'location_id' => $request->location_id,
                'operational_segment_id' => $request->operational_segment_id,
                'vehicle_id' => $request->vehicle_id,
                'odometer' => $request->odometer,
                'hour_meter' => $request->hour_meter,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'driver_1' => $request->driver_1,
                'driver_2' => $request->driver_2,
                'tyre_man' => $request->tyre_man,
                'notes' => $request->notes,
                'exam_type' => $examType,
                'approval_status' => $approvalStatus,
                'approved_by' => ($approvalStatus === 'Approved') ? $user->id : null,
            ]);

            // Handle General Unit Photo
            $subFolder = 'examinations/' . date('Y-m');
            if ($request->hasFile('photo_unit_front')) {
                $examination->photo_unit_front = $request->file('photo_unit_front')->store($subFolder, 'public');
                $examination->save();
            }

            // Pre-process details to check if at least one tyre is actually filled
            $hasDetails = false;
            if ($request->filled('details')) {
                foreach ($request->details as $detail) {
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

                    // We also check for ajax photos later, but for now we look for measurements
                    if (!empty($detail['tyre_id']) && ($hasPsi || $hasRtd || $hasRemarks)) {
                        $hasDetails = true;
                        break;
                    }
                }
            }

            // Check if any ajax photos exist for this session
            if (!$hasDetails) {
                $hasDetails = TyreExaminationImage::where('notes', $request->temp_id)->exists();
            }

            if (!$hasDetails) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Lakukan minimal pemeriksaan 1 ban (isi PSI, RTD, Catatan, atau Foto).'], 422);
            }

            if ($request->filled('details')) {
                foreach ($request->details as $key => $detail) {
                    if (empty($detail['tyre_id'])) continue;

                    $rtds = array_filter([
                        $detail['rtd_1'] ?? null,
                        $detail['rtd_2'] ?? null,
                        $detail['rtd_3'] ?? null,
                        $detail['rtd_4'] ?? null
                    ], function ($v) {
                        return $v !== null && $v !== '';
                    });

                    $tyre = Tyre::find($detail['tyre_id']);
                    if (!$tyre) continue;

                    $hasPsi = !empty($detail['psi']);
                    $hasRtd = count($rtds) > 0;
                    $hasRemarks = !empty($detail['remarks']);
                    $hasAjaxPhoto = TyreExaminationImage::where('notes', $request->temp_id)
                        ->where('serial_number', $tyre->serial_number)
                        ->exists();

                    if (!$hasPsi && !$hasRtd && !$hasRemarks && !$hasAjaxPhoto) continue;

                    $detailModel = $examination->details()->create([
                        'position_id' => $detail['position_id'],
                        'tyre_id' => $detail['tyre_id'],
                        'psi_reading' => $detail['psi'] ?? 0,
                        'rtd_1' => $detail['rtd_1'] ?? 0,
                        'rtd_2' => $detail['rtd_2'] ?? 0,
                        'rtd_3' => $detail['rtd_3'] ?? 0,
                        'rtd_4' => $detail['rtd_4'] ?? 0,
                        'remarks' => $detail['remarks'] ?? null,
                    ]);

                    TyreExaminationImage::where('notes', $request->temp_id)
                        ->where('serial_number', $tyre->serial_number)
                        ->update(['examination_id' => $examination->id]);

                    if ($approvalStatus === 'Approved') {
                        $avgRtd = $hasRtd ? array_sum($rtds) / count($rtds) : $tyre->current_tread_depth;
                        $this->recordMovement($examination, $tyre, $detail['position_id'], $avgRtd, $detail['psi'] ?? null);
                    }
                }
            }

            DB::commit();

            setLogActivity($user->id, 'Membuat pemeriksaan ban: ' . $vehicleCode, [
                'action_type' => 'create',
                'module' => 'Examination',
                'data_after' => ['Kendaraan' => $vehicleCode, 'Tanggal' => $request->examination_date]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pemeriksaan berhasil disimpan',
                'redirect' => route('examination.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'GAGAL: ' . $e->getMessage()], 500);
        }
    }

    private function recordMovement(TyreExamination $examination, Tyre $tyre, $positionId, $avgRtd, $psiReading)
    {
        // --- Calculate Lifetime since last recorded event (Date-Aware) ---
        $lastMov = TyreMovement::where('tyre_id', $tyre->id)
            ->where('movement_date', '<=', $examination->examination_date)
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $kmDiff = 0;
        $hmDiff = 0;
        if ($lastMov) {
            $kmDiff = $this->calculateLifetimeDiff($examination->odometer, $lastMov->odometer_reading);
            $hmDiff = $this->calculateLifetimeDiff($examination->hour_meter, $lastMov->hour_meter_reading);
        }

        // Update Tyre Master
        $tyre->update([
            'current_tread_depth' => $avgRtd ?? $tyre->current_tread_depth,
            'total_lifetime_km' => ($tyre->total_lifetime_km ?? 0) + $kmDiff,
            'total_lifetime_hm' => ($tyre->total_lifetime_hm ?? 0) + $hmDiff,
        ]);

        // IMPORTANT: Record this inspection in movement history with calculated diffs
        TyreMovement::create([
            'tyre_id' => $tyre->id,
            'vehicle_id' => $examination->vehicle_id,
            'position_id' => $positionId,
            'movement_type' => 'Inspection',
            'movement_date' => $examination->examination_date,
            'odometer_reading' => $examination->odometer,
            'hour_meter_reading' => $examination->hour_meter,
            'running_km' => $kmDiff ?? 0,
            'running_hm' => $hmDiff ?? 0,
            'psi_reading' => $psiReading,
            'rtd_reading' => $avgRtd,
            'notes' => 'Pemeriksaan rutin',
            'created_by' => Auth::id(),
        ]);
    }

    public function approve($id)
    {
        $examination = TyreExamination::with(['details.tyre'])->findOrFail($id);
        $user = auth()->user();

        DB::beginTransaction();
        try {
            $examination->update([
                'approval_status' => 'Approved',
                'approved_by' => $user->id,
            ]);

            foreach ($examination->details as $detail) {
                $tyre = $detail->tyre;
                if (!$tyre) continue;

                $rtds = array_filter([$detail->rtd_1, $detail->rtd_2, $detail->rtd_3, $detail->rtd_4], function($v) {
                    return $v !== null && $v !== '';
                });

                $avgRtd = count($rtds) > 0 ? array_sum($rtds) / count($rtds) : $tyre->current_tread_depth;
                $this->recordMovement($examination, $tyre, $detail->position_id, $avgRtd, $detail->psi_reading);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pemeriksaan telah disetujui.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'GAGAL: ' . $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string']);
        $examination = TyreExamination::findOrFail($id);
        
        $examination->update([
            'approval_status' => 'Rejected',
            'reject_reason' => $request->reason,
        ]);

        return response()->json(['success' => true, 'message' => 'Pemeriksaan telah ditolak.']);
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
                'status' => $row->approval_status ?? 'Pending',
                'type' => $row->exam_type ?? 'Customer',
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
