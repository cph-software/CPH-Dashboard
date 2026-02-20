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
use App\Models\TyreLocation;
use Carbon\Carbon;

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
            'vehicle_id' => 'required|exists:master_import_kendaraan,id',
            'odometer' => 'nullable|numeric',
            'hour_meter' => 'nullable|numeric',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'details' => 'required|array',
            'details.*.position_id' => 'required|exists:tyre_position_details,id',
            'details.*.tyre_id' => 'required|exists:tyres,id',
        ]);

        DB::beginTransaction();
        try {
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

            // 2. Create Details & Update Tyre RTD
            foreach ($request->details as $detail) {
                TyreExaminationDetail::create([
                    'examination_id' => $exam->id,
                    'position_id' => $detail['position_id'],
                    'tyre_id' => $detail['tyre_id'],
                    'psi_reading' => $detail['psi'] ?? null,
                    'rtd_1' => $detail['rtd_1'] ?? null,
                    'rtd_2' => $detail['rtd_2'] ?? null,
                    'rtd_3' => $detail['rtd_3'] ?? null,
                    'remarks' => $detail['remarks'] ?? null,
                ]);

                // Update current RTD of the tyre to the average or most recent reading
                $tyre = Tyre::find($detail['tyre_id']);
                if ($tyre) {
                    $avgRtd = null;
                    $rtds = array_filter([$detail['rtd_1'], $detail['rtd_2'], $detail['rtd_3']]);
                    if (count($rtds) > 0) {
                        $avgRtd = array_sum($rtds) / count($rtds);
                    }

                    if ($avgRtd !== null) {
                        $tyre->update(['current_tread_depth' => $avgRtd]);
                    }
                }
            }

            DB::commit();

            $vehicle = MasterImportKendaraan::find($request->vehicle_id);
            setLogActivity(Auth::id(), 'Membuat pemeriksaan ban untuk kendaraan: ' . ($vehicle->kode_kendaraan ?? $request->vehicle_id), [
                'action_type' => 'create',
                'module' => 'Examination',
                'data_after' => [
                    'examination_id' => $exam->id,
                    'examination_date' => $request->examination_date,
                    'vehicle' => $vehicle->kode_kendaraan ?? $request->vehicle_id,
                    'odometer' => $request->odometer,
                    'total_details' => count($request->details),
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

        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tyre-performance.examination.pdf', compact('exam'))
                ->setPaper('a4', 'portrait');
            
            $filename = 'Examination-Form-' . $exam->vehicle->kode_kendaraan . '-' . $exam->examination_date . '.pdf';
            
            if ($request->action == 'stream') {
                return $pdf->stream($filename);
            }
            
            return $pdf->download($filename);
        }

        return view('tyre-performance.examination.pdf', compact('exam'));
    }
}
