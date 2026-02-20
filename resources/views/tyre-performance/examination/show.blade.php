@extends('layouts.admin')

@section('title', 'Detail Pemeriksaan Ban')

@section('page-style')
   <style>
      .print-header {
         display: none;
      }

      @media print {
         .no-print {
            display: none;
         }

         .print-header {
            display: block;
         }

         .card {
            border: none !important;
            box-shadow: none !important;
         }

         .container-xxl {
            max-width: 100% !important;
            padding: 0 !important;
         }

         .table-examination {
            font-size: 10px;
         }

         body {
            padding: 0;
            margin: 0;
         }
      }

      .bg-yellow-header {
         background: #ffd700 !important;
         color: #000;
      }
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4 no-print">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Examination /</span> Detail #{{ $exam->id }}
         </h4>
         <div class="d-flex gap-2">
            <a href="{{ route('examination.index') }}" class="btn btn-label-secondary">
               <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
            <div class="btn-group">
               <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  <i class="ri-printer-line me-1"></i> Cetak / Export
               </button>
               <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                  <li>
                     <a class="dropdown-item py-2" href="javascript:void(0);" onclick="window.print()">
                        <i class="ri-printer-line me-2 text-primary"></i> Cetak Langsung (Browser)
                     </a>
                  </li>
                  <li>
                     <a class="dropdown-item py-2" href="{{ route('examination.export-pdf', $exam->id) }}" target="_blank">
                        <i class="ri-file-pdf-line me-2 text-danger"></i> Unduh File PDF (Official)
                     </a>
                  </li>
                  <li>
                     <hr class="dropdown-divider">
                  </li>
                  <li>
                     <small class="dropdown-header text-muted pb-0">Format Lainnya</small>
                     <a class="dropdown-item py-2 disabled" href="javascript:void(0);">
                        <i class="ri-file-excel-line me-2 text-success"></i> Export Excel (Coming Soon)
                     </a>
                  </li>
               </ul>
            </div>
         </div>
      </div>

      <!-- HEADER INFO -->
      <div class="card mb-4">
         <div class="card-header bg-yellow-header">
            <h5 class="mb-0 fw-bold">EXAMINATION FORM</h5>
         </div>
         <div class="card-body pt-4">
            <div class="row text-uppercase small">
               <div class="col-md-3 border-end">
                  <p class="text-muted mb-1">DATE</p>
                  <h6 class="fw-bold">{{ \Carbon\Carbon::parse($exam->examination_date)->format('d F Y') }}</h6>
                  <div class="row mt-3">
                     <div class="col-6">
                        <p class="text-muted mb-1">LOCATION</p>
                        <h6 class="fw-bold">{{ $exam->location->location_name ?? '-' }}</h6>
                     </div>
                     <div class="col-6">
                        <p class="text-muted mb-1">SEGMENT</p>
                        <h6 class="fw-bold">{{ $exam->segment->segment_name ?? '-' }}</h6>
                     </div>
                  </div>
               </div>
               <div class="col-md-3 border-end">
                  <p class="text-muted mb-1">KM (ODO/RETASE)</p>
                  <h6 class="fw-bold">{{ number_format($exam->odometer, 0) }}</h6>
                  <p class="text-muted mb-1 mt-3">HM (HOUR METER)</p>
                  <h6 class="fw-bold">{{ number_format($exam->hour_meter, 0) }}</h6>
               </div>
               <div class="col-md-3 border-end">
                  <p class="text-muted mb-1">No. Pol & Unit</p>
                  <h6 class="fw-bold">{{ $exam->vehicle->no_polisi }} / {{ $exam->vehicle->kode_kendaraan }}</h6>
                  <div class="row mt-3">
                     <div class="col-6">
                        <p class="text-muted mb-1">JAM MULAI</p>
                        <h6 class="fw-bold">{{ $exam->start_time ? substr($exam->start_time, 0, 5) : '-' }}</h6>
                     </div>
                     <div class="col-6">
                        <p class="text-muted mb-1">JAM SELESAI</p>
                        <h6 class="fw-bold">{{ $exam->end_time ? substr($exam->end_time, 0, 5) : '-' }}</h6>
                     </div>
                  </div>
               </div>
               <div class="col-md-3">
                  <p class="text-muted mb-1">DRIVER #1</p>
                  <h6 class="fw-bold">{{ $exam->driver_1 ?: '-' }}</h6>
                  <p class="text-muted mb-1 mt-3">DRIVER #2</p>
                  <h6 class="fw-bold">{{ $exam->driver_2 ?: '-' }}</h6>
               </div>
            </div>
         </div>
      </div>

      <!-- DETAILS TABLE -->
      <div class="card mb-4">
         <div class="table-responsive">
            <table class="table table-bordered table-sm table-examination mb-0">
               <thead class="bg-yellow-header">
                  <tr>
                     <th class="text-center">Pos</th>
                     <th>BRAND</th>
                     <th>PATTERN</th>
                     <th>SIZE / PR</th>
                     <th>SERIAL NUMBER</th>
                     <th class="text-center" width="120">PSI</th>
                     <th class="text-center" width="100">RTD #1</th>
                     <th class="text-center" width="100">RTD #2</th>
                     <th class="text-center" width="100">RTD #3</th>
                     <th>REMARKS</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($exam->details as $detail)
                     @php $tyre = $detail->tyre; @endphp
                     <tr>
                        <td class="text-center fw-bold bg-light">{{ $detail->position->position_code }}</td>
                        <td>{{ $tyre->brand->brand_name ?? '-' }}</td>
                        <td>{{ $tyre->pattern->name ?? '-' }}</td>
                        <td>{{ $tyre->size->size ?? '-' }}
                           {{ $tyre->size->ply_rating ? '/ ' . $tyre->size->ply_rating . ' PR' : '' }}
                        </td>
                        <td class="fw-bold">{{ $tyre->serial_number ?? '-' }}</td>
                        <td class="text-center">{{ $detail->psi_reading ?: '-' }}</td>
                        <td class="text-center">{{ $detail->rtd_1 ?: '-' }}</td>
                        <td class="text-center">{{ $detail->rtd_2 ?: '-' }}</td>
                        <td class="text-center">{{ $detail->rtd_3 ?: '-' }}</td>
                        <td>{{ $detail->remarks ?: '-' }}</td>
                     </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
      </div>

      <!-- FOOTER / SIGNATURES -->
      <div class="card">
         <div class="card-body">
            <div class="row text-center mt-3">
               <div class="col border-end">
                  <p class="small text-muted mb-4">Tyre Man</p>
                  <div style="height: 60px;"></div>
                  <h6 class="mb-0 fw-bold">{{ $exam->tyre_man ?: '....................' }}</h6>
               </div>
               <div class="col border-end">
                  <p class="small text-muted mb-4">Ka. Kendaraan</p>
                  <div style="height: 60px;"></div>
                  <h6 class="mb-0 fw-bold">....................</h6>
               </div>
               <div class="col border-end">
                  <p class="small text-muted mb-4">Logistics</p>
                  <div style="height: 60px;"></div>
                  <h6 class="mb-0 fw-bold">....................</h6>
               </div>
               <div class="col border-end">
                  <p class="small text-muted mb-4">Verified by</p>
                  <div style="height: 60px;"></div>
                  <h6 class="mb-0 fw-bold">....................</h6>
               </div>
               <div class="col">
                  <p class="small text-muted mb-4">Plant Manager</p>
                  <div style="height: 60px;"></div>
                  <h6 class="mb-0 fw-bold">....................</h6>
               </div>
            </div>
            @if ($exam->notes)
               <div class="mt-4 p-3 bg-light rounded shadow-sm border-start border-primary border-3">
                  <small class="text-muted d-block fw-bold mb-1">NOTES:</small>
                  <p class="mb-0 small italic">{{ $exam->notes }}</p>
               </div>
            @endif
         </div>
      </div>
   </div>
@endsection
