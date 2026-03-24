@extends('layouts.admin')

@section('title', 'Detail Pemeriksaan Ban')

@section('page-style')
   <style>
      @media print {

         /* Hide Layout Elements */
         .layout-menu-relative,
         .layout-menu,
         .layout-navbar,
         .content-footer,
         .no-print,
         .btn-buy-now,
         .content-backdrop,
         .menu-vertical {
            display: none !important;
         }

         /* Reset Layout for Print */
         .layout-page {
            padding: 0 !important;
            margin: 0 !important;
         }

         .layout-wrapper,
         .layout-container {
            display: block !important;
         }

         .content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
         }

         .container-xxl {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
         }

         /* Card & Typography Improvements */
         .card {
            border: none !important;
            box-shadow: none !important;
            margin-bottom: 20px !important;
         }

         body {
            background-color: #fff !important;
            color: #000 !important;
            padding: 0 !important;
            margin: 0 !important;
         }

         .table-examination {
            font-size: 11px !important;
            border: 1px solid #000 !important;
         }

         .table-examination th {
            background-color: #ffd700 !important;
            color: #000 !important;
            border-bottom: 1px solid #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
         }

         .table-examination td {
            border: 1px solid #000 !important;
         }

         .bg-yellow-header {
            background-color: #ffd700 !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            border-bottom: 2px solid #000 !important;
         }

         /* Footer signatures alignment */
         .signature-box {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
         }

         @page {
            size: A4 portrait;
            margin: 1.5cm 1cm;
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
      <!-- PRINT HEADER (Only visible when printing) -->
      <div class="print-only mb-4 text-center">
         <h2 class="fw-bold mb-0">EXAMINATION FORM</h2>
         <p class="mb-0 text-muted">CPH Dashboard - Tyre Performance Module</p>
         <hr class="border-dark opacity-100">
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4 no-print">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Examination /</span> Detail #{{ $exam->id }}
         </h4>
         <div class="d-flex gap-2">
            <a href="{{ route('examination.index') }}" class="btn btn-label-secondary">
               <i class="ri ri-arrow-left-line me-1"></i> Kembali
            </a>
            <a href="{{ route('examination.export-pdf', ['id' => $exam->id, 'action' => 'stream']) }}"
               class="btn btn-primary" target="_blank">
               <i class="ri ri-printer-line me-1"></i> Cetak Form (PDF)
            </a>
            @if ($exam->approval_status === 'Pending' && auth()->user()->role_id == 1)
               <button type="button" class="btn btn-success" id="btnApprove">
                  <i class="ri ri-check-line me-1"></i> Approve
               </button>
               <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                  <i class="ri ri-close-line me-1"></i> Reject
               </button>
            @endif
         </div>
      </div>

      @if ($exam->approval_status === 'Rejected')
         <div class="alert alert-danger mb-4 shadow-sm border-2">
            <div class="d-flex align-items-center">
               <i class="ri ri-error-warning-line fs-3 me-2"></i>
               <div>
                  <h6 class="alert-heading mb-1 fw-bold">PEMERIKSAAN DITOLAK</h6>
                  <span>Alasan: <strong>{{ $exam->reject_reason }}</strong></span>
               </div>
            </div>
         </div>
      @endif

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
                     <th class="text-center" width="100">RTD #4</th>
                     <th>REMARKS</th>
                     {{-- <th class="text-center" width="80">FOTO</th> --}}
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
                        <td class="text-center">{{ $detail->rtd_4 ?: '-' }}</td>
                        <td>{{ $detail->remarks ?: '-' }}</td>
                        {{-- <td class="text-center">
                           @if ($detail->photo)
                              <a href="{{ asset('storage/' . $detail->photo) }}" target="_blank">
                                 <img src="{{ asset('storage/' . $detail->photo) }}" class="rounded shadow-sm"
                                    style="width: 40px; height: 40px; object-fit: cover;">
                              </a>
                           @else
                              -
                           @endif
                        </td> --}}
                     </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
      </div>

      @if ($exam->notes || $exam->photo_unit_front)
         <div class="card shadow-sm border-0">
            <div class="card-body">
               <div class="row g-4">
                  @if ($exam->notes)
                     <div class="{{ $exam->photo_unit_front ? 'col-md-6' : 'col-12' }}">
                        <div class="p-3 bg-light rounded h-100 border-start border-primary border-3">
                           <label class="form-label fw-bold text-primary small d-block mb-1 text-uppercase">Catatan
                              Tambahan (Notes)</label>
                           <p class="mb-0 small italic text-dark">{{ $exam->notes }}</p>
                        </div>
                     </div>
                  @endif

                  @if ($exam->photo_unit_front)
                     <div class="{{ $exam->notes ? 'col-md-6' : 'col-12' }}">
                        <div class="p-3 bg-light rounded h-100 border-start border-success border-3">
                           <label class="form-label fw-bold text-success small d-block mb-2 text-uppercase">Lampiran Foto
                              Unit</label>
                           <a href="{{ asset('storage/' . $exam->photo_unit_front) }}" target="_blank" class="d-block">
                              <img src="{{ asset('storage/' . $exam->photo_unit_front) }}"
                                 class="img-fluid rounded shadow-sm border"
                                 style="max-height: 250px; width: 100%; object-fit: cover;">
                           </a>
                           <small class="text-muted mt-2 d-block extra-small text-center italic">Klik foto untuk
                              memperbesar</small>
                        </div>
                     </div>
                  @endif
               </div>
            </div>
         </div>
      @endif
   </div>

   <!-- Reject Modal -->
   <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-sm">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Tolak Pemeriksaan</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <textarea id="rejectReason" class="form-control" placeholder="Alasan penolakan..." rows="3"></textarea>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
               <button type="button" class="btn btn-danger" id="btnConfirmReject">Reject</button>
            </div>
         </div>
      </div>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(function() {
         $('#btnApprove').on('click', function() {
            Swal.fire({
               title: 'Setujui Pemeriksaan?',
               text: "Data ban akan diupdate dan pergerakan akan dicatat.",
               icon: 'question',
               showCancelButton: true,
               confirmButtonText: 'Ya, Approve!',
               customClass: {
                  confirmButton: 'btn btn-success me-3',
                  cancelButton: 'btn btn-label-secondary'
               },
               buttonsStyling: false
            }).then((result) => {
               if (result.isConfirmed) {
                  $.post('{{ route('examination.approve', $exam->id) }}', {
                     _token: '{{ csrf_token() }}'
                  }, function(res) {
                     if (res.success) Swal.fire('Berhasil', res.message, 'success').then(() =>
                        location.reload());
                     else Swal.fire('Gagal', res.message, 'error');
                  });
               }
            });
         });

         $('#btnConfirmReject').on('click', function() {
            const reason = $('#rejectReason').val();
            if (!reason) return alert('Silakan isi alasan penolakan.');

            $.post('{{ route('examination.reject', $exam->id) }}', {
               _token: '{{ csrf_token() }}',
               reason: reason
            }, function(res) {
               if (res.success) location.reload();
               else alert(res.message);
            });
         });
      });
   </script>
@endsection
