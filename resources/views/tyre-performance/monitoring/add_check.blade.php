@extends('layouts.admin')

@section('title', 'Add Periodic Check')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <style>
      .table-responsive {
         overflow-x: auto;
         -webkit-overflow-scrolling: touch;
         margin-bottom: 1rem;
         border: 1px solid #e5e7eb;
         border-radius: 8px;
         background: #fff;
      }

      #check-table {
         min-width: 1400px;
         table-layout: fixed;
      }

      #check-table th,
      #check-table td {
         padding: 12px 10px !important;
         vertical-align: middle;
      }

      .rtd-input {
         height: 45px !important;
         font-size: 1.1rem !important;
         font-weight: 700 !important;
         text-align: center;
         color: #1a202c;
         border: 2px solid #cbd5e1 !important;
         border-radius: 6px;
      }

      .rtd-input:focus {
         border-color: #7367f0 !important;
         outline: none;
      }

      #check-table thead th {
         background-color: #233446 !important;
         color: #fff !important;
         text-transform: uppercase;
         font-size: 0.75rem;
         text-align: center;
      }

      .tyre-info-box {
         background: #f8fafc;
         padding: 8px 12px;
         border-radius: 6px;
         border-left: 4px solid #00cfe8;
      }
   </style>
@endsection

@section('content')
   <div class="row">
      <div class="col-12">
         <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
               <h5 class="mb-0 fw-bold"><i class="ri ri-search-eye-line me-2"></i>Periodic Check #{{ $checkCount + 1 }}</h5>
               <a href="{{ route('monitoring.vehicle.show', $vehicle->vehicle_id) }}" class="btn btn-label-secondary">
                  <i class="ri ri-arrow-left-line me-1"></i> Kembali
               </a>
            </div>
            <div class="card-body">
               <form action="{{ route('monitoring.check.store') }}" method="POST">
                  @csrf
                  <input type="hidden" name="session_id" value="{{ $session->session_id }}">
                  <input type="hidden" name="check_number" value="{{ $checkCount + 1 }}">
                  <input type="hidden" name="temp_id" id="temp_id" value="{{ Str::random(16) }}">

                  <div class="row g-4 mb-4 bg-light p-3 rounded shadow-sm">
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Tanggal Pengecekan</label>
                        <input type="date" name="check_date"
                           class="form-control form-control-lg @error('check_date') is-invalid @enderror" required
                           value="{{ old('check_date', date('Y-m-d')) }}">
                        @error('check_date')
                           <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Odometer Check (KM)</label>
                        <input type="number" name="odometer"
                           class="form-control form-control-lg @error('odometer') is-invalid @enderror" required
                           placeholder="KM" value="{{ old('odometer') }}">
                        @error('odometer')
                           <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Hour Meter Check (HM)</label>
                        <input type="number" name="hour_meter"
                           class="form-control form-control-lg @error('hour_meter') is-invalid @enderror" placeholder="HM"
                           value="{{ old('hour_meter') }}">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Driver Name</label>
                        <input type="text" name="driver_name"
                           class="form-control form-control-lg @error('driver_name') is-invalid @enderror" required
                           placeholder="Nama Driver" value="{{ old('driver_name', $vehicle->driver_name) }}">
                        @error('driver_name')
                           <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" name="phone_number"
                           class="form-control form-control-lg @error('phone_number') is-invalid @enderror"
                           placeholder="081xxx" value="{{ old('phone_number', $vehicle->phone_number) }}">
                        @error('phone_number')
                           <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Recommended PSI (Global)</label>
                        <input type="number" name="retase"
                           class="form-control form-control-lg @error('retase') is-invalid @enderror" required
                           placeholder="PSI" value="{{ old('retase', $session->retase) }}">
                        @error('retase')
                           <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Operation Mileage (KM)</label>
                        <input type="number" name="operation_mileage" class="form-control form-control-lg"
                           placeholder="KM" readonly id="computed_milage">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold">Load Payload (Ton)</label>
                        <input type="text" name="load" class="form-control form-control-lg"
                           value="{{ $vehicle->load_capacity }}">
                     </div>
                  </div>

                  <div class="card bg-lighter mb-4 border-0 shadow-none">
                     <div class="card-body">
                        <h6 class="fw-bold mb-3"><i class="ri ri-camera-line me-2"></i>General Documentation (Fleet &
                           Condition)</h6>
                        <div class="row g-3">
                           @php
                              $generalPhotos = [
                                  'fleet' => 'Foto Fleet',
                                  'vehicle' => 'Foto Kendaraan',
                                  'map' => 'Foto Rute (Map)',
                                  'odometer_km' => 'Foto KM',
                                  'hm' => 'Foto HM',
                              ];
                           @endphp
                           @foreach ($generalPhotos as $type => $label)
                              <div class="col-6 col-md-2">
                                 <div class="d-flex flex-column align-items-center p-2 border rounded bg-white">
                                    <span class="small mb-2 text-center">{{ $label }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary upload-btn"
                                       data-type="{{ $type }}">
                                       <i class="ri ri-upload-2-line"></i>
                                    </button>
                                    <div class="preview-container mt-2" id="preview-{{ $type }}"></div>
                                 </div>
                              </div>
                           @endforeach
                        </div>
                     </div>
                  </div>

                  <div class="alert alert-info py-2 mb-3">
                     <i class="ri ri-information-line me-1"></i> Masukkan data RTD terbaru untuk semua ban yang terpasang
                     pada kendaraan ini.
                  </div>

                  <div class="table-responsive">
                     <table class="table table-bordered align-middle" id="check-table">
                        <thead>
                           <tr>
                              <th style="width: 80px;">Pos</th>
                              <th style="width: 280px;">Informasi Ban</th>
                              <th style="width: 150px;">Psi (Rec/Act)</th>
                              <th style="width: 100px;">RTD 1</th>
                              <th style="width: 100px;">RTD 2</th>
                              <th style="width: 100px;">RTD 3</th>
                              <th style="width: 100px;">RTD 4</th>
                              <th style="width: 80px;">Docs</th>
                              <th style="width: 180px;">Kondisi</th>
                              <th>Rekomendasi & Catatan</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach ($installedTyres as $idx => $tyre)
                              @php
                                 $posDetail = $masterPositions->firstWhere('id', $tyre->current_position_id);
                                 $serial = $tyre->serial_number;
                              @endphp
                              <tr>
                                 <td class="text-center fw-bold bg-light" style="font-size: 1.1rem;">
                                    {{ $posDetail->position_code ?? '-' }}
                                    <input type="hidden" name="checks[{{ $serial }}][position]"
                                       value="{{ $posDetail->position_code ?? '-' }}">
                                    <input type="hidden" name="checks[{{ $serial }}][position_id]"
                                       value="{{ $tyre->current_position_id }}">
                                 </td>
                                 <td>
                                    <div class="tyre-info-box">
                                       <div class="fw-bold text-primary">{{ $tyre->serial_number }}</div>
                                       <small class="text-muted d-block">{{ $tyre->brand->brand_name ?? '-' }} ·
                                          {{ $tyre->size->size ?? '-' }}</small>
                                       <input type="hidden" name="checks[{{ $serial }}][serial_number]"
                                          value="{{ $tyre->serial_number }}">
                                       <input type="hidden" name="checks[{{ $serial }}][tyre_id]"
                                          value="{{ $tyre->id }}">
                                    </div>
                                 </td>
                                 <td>
                                    <div class="input-group input-group-sm mb-1">
                                       <span class="input-group-text">Rec</span>
                                       <input type="number" name="checks[{{ $serial }}][psi_recommended]"
                                          class="form-control" value="{{ $session->retase }}">
                                    </div>
                                    <div class="input-group input-group-sm">
                                       <span class="input-group-text text-primary fw-bold">Act</span>
                                       <input type="number" name="checks[{{ $serial }}][psi_actual]"
                                          class="form-control border-primary">
                                    </div>
                                 </td>
                                 <td><input type="number" name="checks[{{ $serial }}][rtd_1]"
                                       class="form-control rtd-input" step="0.1"
                                       value="{{ $tyre->current_tread_depth }}"></td>
                                 <td><input type="number" name="checks[{{ $serial }}][rtd_2]"
                                       class="form-control rtd-input" step="0.1"
                                       value="{{ $tyre->current_tread_depth }}"></td>
                                 <td><input type="number" name="checks[{{ $serial }}][rtd_3]"
                                       class="form-control rtd-input" step="0.1"
                                       value="{{ $tyre->current_tread_depth }}"></td>
                                 <td><input type="number" name="checks[{{ $serial }}][rtd_4]"
                                       class="form-control rtd-input" step="0.1"
                                       value="{{ $tyre->current_tread_depth }}"></td>
                                 <td class="text-center">
                                    <button type="button" class="btn btn-icon btn-outline-info tyre-doc-btn"
                                       data-serial="{{ $serial }}"
                                       data-pos="{{ $posDetail->position_code ?? '-' }}">
                                       <i class="ri ri-camera-switch-line"></i>
                                    </button>
                                 </td>
                                 <td>
                                    <select name="checks[{{ $serial }}][condition]" class="form-select fw-bold">
                                       <option value="ok" class="text-success">OK</option>
                                       <option value="warning" class="text-warning">Warning</option>
                                       <option value="critical" class="text-danger">Critical</option>
                                    </select>
                                 </td>
                                 <td>
                                    <input type="text" name="checks[{{ $serial }}][recommendation]"
                                       class="form-control form-control-sm mb-1" placeholder="Rekomendasi...">
                                    <input type="text" name="checks[{{ $serial }}][notes]"
                                       class="form-control form-control-sm" placeholder="Catatan...">
                                 </td>
                              </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>

                  <div class="mt-4 text-end">
                     <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="ri ri-save-line me-1"></i> Submit Periodic Check
                     </button>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>

   <!-- Modal Documentation Per Tyre -->
   <div class="modal fade" id="tyreDocsModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Dokumentasi Ban - Posisi <span id="modalPos"></span> (<span
                     id="modalSerial"></span>)</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <div class="row g-3">
                  @php
                     $tyrePhotos = [
                         'tyre_serial' => 'Serial Number',
                         'tyre_psi' => 'PSI Measurement',
                         'tyre_rtd_1' => 'RTD point 1',
                         'tyre_rtd_2' => 'RTD point 2',
                         'tyre_rtd_3' => 'RTD point 3',
                         'tyre_rtd_4' => 'RTD point 4',
                     ];
                  @endphp
                  @foreach ($tyrePhotos as $type => $label)
                     <div class="col-md-4">
                        <div class="card border shadow-none text-center p-3">
                           <span class="small fw-bold mb-2">{{ $label }}</span>
                           <div class="preview-container mb-2" id="preview-tyre-{{ $type }}">
                              <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                 style="height: 100px;">
                                 <i class="ri ri-image-line ri-24px text-muted"></i>
                              </div>
                           </div>
                           <button type="button" class="btn btn-sm btn-primary tyre-upload-btn"
                              data-type="{{ $type }}">
                              <i class="ri ri-camera-line me-1"></i> Capture
                           </button>
                        </div>
                     </div>
                  @endforeach
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Selesai</button>
            </div>
         </div>
      </div>
   </div>

   <!-- Hidden Input File for triggering -->
   <input type="file" id="imageInput" accept="image/*" style="display: none;">
@endsection

@section('page-script')
   <script>
      $(function() {
         const odometerStart = {{ $session->odometer_start }};
         const globalRetase = $('input[name="retase"]');
         const odometerInput = $('input[name="odometer"]');
         const mileageInput = $('#computed_milage');

         // Auto-calculate Operation Mileage
         odometerInput.on('input', function() {
            const val = parseInt($(this).val()) || 0;
            if (val < odometerStart) {
               $(this).addClass('is-invalid');
               mileageInput.val(0);
            } else {
               $(this).removeClass('is-invalid');
               const diff = val - odometerStart;
               mileageInput.val(diff);
            }
         });

         // Sync Global PSI to all individual Rec PSI
         globalRetase.on('input', function() {
            const val = $(this).val();
            $('input[name^="checks"][name$="[psi_recommended]"]').val(val);
         });

         // Initial calculation if odometer has value
         if (odometerInput.val()) odometerInput.trigger('input');
         // Initial sync for PSI
         if (globalRetase.val()) globalRetase.trigger('input');

         // --- IMAGE UPLOAD LOGIC ---
         let currentTarget = null; // { type, serial }

         $('.upload-btn, .tyre-upload-btn').on('click', function() {
            const type = $(this).data('type');
            const serial = $(this).closest('#tyreDocsModal').length ? $('#modalSerial').text() : null;
            currentTarget = {
               type,
               serial
            };
            $('#imageInput').click();
         });

         $('.tyre-doc-btn').on('click', function() {
            const serial = $(this).data('serial');
            const pos = $(this).data('pos');
            $('#modalSerial').text(serial);
            $('#modalPos').text(pos);

            // Clean/Refresh previews for this serial
            $('.tyre-upload-btn').each(function() {
               const type = $(this).data('type');
               refreshTyrePreview(serial, type);
            });

            $('#tyreDocsModal').modal('show');
         });

         $('#imageInput').on('change', function() {
            const file = this.files[0];
            if (!file || !currentTarget) return;

            const formData = new FormData();
            formData.append('image', file);
            formData.append('type', currentTarget.type);
            formData.append('session_id', '{{ $session->session_id }}');
            formData.append('serial_number', currentTarget.serial || '');
            formData.append('temp_id', $('#temp_id').val());
            formData.append('_token', '{{ csrf_token() }}');

            const btn = currentTarget.serial ?
               $(`.tyre-upload-btn[data-type="${currentTarget.type}"]`) :
               $(`.upload-btn[data-type="${currentTarget.type}"]`);

            const originalText = btn.html();
            btn.html('<span class="spinner-border spinner-border-sm" role="status"></span>').prop('disabled',
               true);

            $.ajax({
               url: '{{ route('monitoring.upload-image') }}',
               type: 'POST',
               data: formData,
               processData: false,
               contentType: false,
               success: function(res) {
                  btn.html(originalText).prop('disabled', false).removeClass('btn-outline-primary')
                     .addClass('btn-success');
                  const previewId = currentTarget.serial ? `preview-tyre-${currentTarget.type}` :
                     `preview-${currentTarget.type}`;
                  $(`#${previewId}`).html(
                     `<img src="${res.url}" class="img-fluid rounded" style="max-height: 100px;">`);
               },
               error: function(err) {
                  btn.html(originalText).prop('disabled', false);
                  alert('Gagal upload gambar: ' + (err.responseJSON?.message || 'Error server'));
               }
            });
         });

         function refreshTyrePreview(serial, type) {
            // This would ideally fetch existing images if re-opening modal
            // For now, we'll just show placeholders unless it was just uploaded in this session
            $(`#preview-tyre-${type}`).html(`
               <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                  <i class="ri ri-image-line ri-24px text-muted"></i>
               </div>
            `);
            $(`.tyre-upload-btn[data-type="${type}"]`).removeClass('btn-success').addClass('btn-primary');
         }
      });
   </script>
@endsection
