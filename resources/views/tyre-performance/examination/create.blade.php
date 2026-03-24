@extends('layouts.admin')

@section('title', 'Form Pemeriksaan Ban (Examination)')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('page-style')
   <style>
      .form-header-card {
         background: #fdfae0;
         border-left: 5px solid #ffd700;
      }

      .table-examination thead {
         background: #ffd700;
         color: #000;
      }

      /* Mobile Responsive Checklist */
      @media (max-width: 767.98px) {
         .table-examination thead {
            display: none;
         }

         .table-examination,
         .table-examination tbody,
         .table-examination tr,
         .table-examination td {
            display: block;
            width: 100% !important;
         }

         .table-examination tr {
            margin-bottom: 1.5rem;
            border: 1px solid #e5e7eb !important;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
         }

         .pos-column {
            background-color: #f3f4f6 !important;
            padding: 0.75rem !important;
         }

         .info-column {
            padding: 1rem !important;
            border-bottom: 1px solid #f3f4f6 !important;
         }

         .measure-column {
            background-color: #fff !important;
         }

         .measurement-group {
            border-bottom: 1px solid #f3f4f6;
         }

         .psi-group {
            background-color: #fff9db;
         }

         .border-end-md {
            border-right: none !important;
         }
      }

      @media (min-width: 768px) {
         .border-end-md {
            border-right: 1px solid #e5e7eb !important;
         }

         .pos-column {
            width: 80px;
         }

         .info-column {
            width: 300px;
         }
      }

      .rtd-input:focus {
         background-color: #fff9db;
         border-color: #ffd700;
      }

      .empty-pos {
         opacity: 0.7;
         background-color: #fafafa;
      }
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Examination /</span> Input Baru</h4>
         <a href="{{ route('examination.index') }}" class="btn btn-label-secondary"><i
               class="ri ri-arrow-left-line me-1"></i>
            Kembali</a>
      </div>

      <form id="examination_form" enctype="multipart/form-data">
         @csrf
         <input type="hidden" name="temp_id" id="temp_id" value="{{ Str::random(16) }}">
         <!-- HEADER SECTION -->
         <div class="card mb-4 shadow-sm form-header-card">
            <div class="card-body pt-3">
               <div class="row">
                  <div class="col-md-3 mb-3">
                     <label class="form-label fw-bold small">DATE</label>
                     <input type="date" name="examination_date" class="form-control" value="{{ date('Y-m-d') }}"
                        required>
                  </div>
                  <div class="col-md-3 mb-3">
                     <label class="form-label fw-bold small">No. Pol & Unit</label>
                     <select name="vehicle_id" id="vehicle_id" class="form-select select2" required>
                        <option value="">-- Pilih Kendaraan --</option>
                        @foreach ($kendaraans as $v)
                           <option value="{{ $v->id }}">{{ $v->no_polisi }} / {{ $v->kode_kendaraan }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-3 mb-3">
                     <label class="form-label fw-bold small">KM (ODO/RETASE)</label>
                     <input type="number" name="odometer" id="odometer" class="form-control" placeholder="Pilih unit..."
                        required>
                     <small class="text-muted extra-small d-block mt-1">Last KM: <span id="last_odo_display"
                           class="fw-bold">-</span></small>
                  </div>
                  <div class="col-md-3 mb-3">
                     <div class="row">
                        <div class="col-6">
                           <label class="form-label fw-bold small">Mulai</label>
                           <input type="time" name="start_time" class="form-control" value="{{ date('H:i') }}">
                        </div>
                        <div class="col-6">
                           <label class="form-label fw-bold small">Selesai</label>
                           <input type="time" name="end_time" class="form-control">
                        </div>
                     </div>
                  </div>
                  <div class="col-md-3 mb-3">
                     <label class="form-label fw-bold small">LOCATION</label>
                     <select name="location_id" id="location_id" class="form-select select2" required>
                        <option value="">-- Pilih Lokasi --</option>
                        @foreach ($locations as $loc)
                           <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-3 mb-3">
                     <label class="form-label fw-bold small">SEGMENT</label>
                     <select name="operational_segment_id" id="operational_segment_id" class="form-select select2"
                        required>
                        <option value="">-- Pilih Segmen --</option>
                     </select>
                  </div>
                  <div class="col-md-3 mb-3">
                     <label class="form-label fw-bold small">HM (Hour Meter)</label>
                     <input type="number" name="hour_meter" id="hour_meter" class="form-control" placeholder="0">
                     <small class="text-muted extra-small d-block mt-1">Last HM: <span id="last_hm_display"
                           class="fw-bold">-</span></small>
                  </div>
                  <div class="col-md-3 mb-3">
                     <label class="form-label fw-bold small">INPUT MODE (TYPE)</label>
                     <select name="exam_type" class="form-select shadow-sm border-primary">
                        <option value="Customer">CUSTOMER (Standard)</option>
                        <option value="Sales">SALES (Pending Approval)</option>
                     </select>
                  </div>
                  <div class="col-12 mt-2 mb-3">
                     <div
                        class="bg-light p-2 rounded border border-dashed d-flex align-items-center justify-content-between px-3">
                        <div>
                           <h6 class="mb-0 small fw-bold text-dark"><i class="ri ri-refresh-line me-1 text-warning"></i>
                              Reset
                              Meteran Unit (Odo/HM)?</h6>
                           <small class="text-muted extra-small">Centang jika angka meteran kembali ke nol</small>
                        </div>
                        <div class="form-check form-switch mb-0">
                           <input class="form-check-input ms-0" type="checkbox" name="is_meter_reset" id="is_meter_reset"
                              value="1" style="width: 2.5em; height: 1.25em;">
                        </div>
                     </div>
                  </div>
                  <div class="col-md-6 mb-3">
                     <div class="row">
                        <div class="col-6">
                           <label class="form-label fw-bold small">DRIVER #1</label>
                           <input type="text" name="driver_1" class="form-control">
                        </div>
                        <div class="col-6">
                           <label class="form-label fw-bold small">DRIVER #2</label>
                           <input type="text" name="driver_2" class="form-control">
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <!-- TABLE SECTION -->
         <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
               <h5 class="mb-0"><i class="ri ri-list-check me-2"></i>Tyre Check List</h5>
               <span class="badge bg-label-info d-none d-md-inline-block">RTD 1-4: Tread Depth Measurements</span>
            </div>
            <div class="table-responsive">
               <table class="table table-bordered table-examination mb-0" id="tyre_list_table">
                  <thead>
                     <tr>
                        <th class="text-center">Pos</th>
                        <th>Informasi Ban</th>
                        <th>Pengukuran (PSI & RTD 1-4) & Keterangan</th>
                     </tr>
                  </thead>
                  <tbody id="tyre_list_body">
                     <tr>
                        <td colspan="3" class="text-center py-5 text-muted">
                           Silakan pilih unit kendaraan terlebih dahulu.
                        </td>
                     </tr>
                  </tbody>
               </table>
            </div>
         </div>

         <!-- UNIT PHOTOS SECTION -->
         <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
               <h5 class="mb-0"><i class="ri ri-camera-lens-line me-2"></i>Unit Photos (Lampiran Foto Unit)</h5>
            </div>
            <div class="card-body">
               <div class="row">
                  <div class="col-md-12 mb-3">
                     <label class="form-label fw-bold small">LAMPIRAN FOTO UNIT (DEPAN/KESELURUHAN)</label>
                     <input type="file" name="photo_unit_front" class="form-control" accept="image/*">
                     <small class="text-muted small mt-1 italic d-block">Dokumentasi kondisi fisik unit saat pemeriksaan
                        dilakukan.</small>
                  </div>
               </div>
            </div>
         </div>

         <!-- FOOTER / APPROVAL -->
         <div class="card shadow-sm mb-4">
            <div class="card-body">
               <div class="row">
                  <div class="col-md-4 mb-3">
                     <label class="form-label fw-bold">Tyre Man (Pemeriksa)</label>
                     <input type="text" name="tyre_man" class="form-control" placeholder="Nama Pemeriksa">
                  </div>
                  <div class="col-md-8">
                     <label class="form-label fw-bold">Additional Notes</label>
                     <textarea name="notes" class="form-control" rows="1"></textarea>
                  </div>
               </div>
               <hr>
               <div class="d-flex justify-content-end gap-2">
                  <button type="submit" class="btn btn-primary btn-lg px-5">
                     <i class="ri ri-save-line me-1"></i> SIMPAN PEMERIKSAAN
                  </button>
               </div>
            </div>
         </div>
      </form>
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
                         'tyre_tread' => 'Foto Telapak',
                     ];
                  @endphp
                  @foreach ($tyrePhotos as $type => $label)
                     <div class="col-md-4">
                        <div class="card border shadow-none text-center p-3">
                           <span class="small fw-bold mb-2">{{ $label }}</span>
                           <div class="preview-container mb-2" id="preview-tyre-{{ $type }}">
                              <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                 style="height: 100px;">
                                 <i class="ri ri-image-line text-muted" style="font-size: 24px;"></i>
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

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(function() {
         $('.select2').select2();

         $('#location_id').on('change', function() {
            var locationId = $(this).val();
            var $segmentSelect = $('#operational_segment_id');

            $segmentSelect.html('<option value="">-- Pilih Segmen --</option>');

            if (locationId) {
               $.ajax({
                  url: "{{ route('tyre-movement.get-segments', '') }}/" + locationId,
                  success: function(res) {
                     if (res && res.length > 0) {
                        $segmentSelect.attr('required', true);
                        res.forEach(function(segment) {
                           $segmentSelect.append(
                              '<option value="' + segment.id + '">' + segment.segment_name +
                              '</option>');
                        });
                     } else {
                        $segmentSelect.removeAttr('required');
                     }
                  }
               });
            }
         });

         $('#vehicle_id').on('change', function() {
            var vehicleId = $(this).val();
            if (!vehicleId) {
               $('#tyre_list_body').html(
                  '<tr><td colspan="3" class="text-center py-5 text-muted">Silakan pilih unit kendaraan.</td></tr>'
               );
               return;
            }

            Swal.fire({
               title: 'Memuat data ban...',
               didOpen: function() {
                  Swal.showLoading();
               },
               allowOutsideClick: false
            });

            $.ajax({
               url: "{{ route('examination.get-vehicle-tyres', '') }}/" + vehicleId,
               success: function(res) {
                  Swal.close();
                  if (res.success) {
                     $('#tyre_list_body').html(res.html);

                     // Update Last Odo & HM display
                     $('#last_odo_display').text(res.last_odometer.toLocaleString());
                     $('#last_hm_display').text(res.last_hour_meter.toLocaleString());
                     $('#odometer').attr('placeholder', 'Previous: ' + res.last_odometer);
                     $('#hour_meter').attr('placeholder', 'Previous: ' + res.last_hour_meter);

                     // Auto-select Input Mode (Type) based on company
                     const companyName = (res.company_name || '').toLowerCase();
                     if (companyName.includes('cph') || companyName.includes('catur')) {
                        $('select[name="exam_type"]').val('Sales').trigger('change');
                     } else {
                        $('select[name="exam_type"]').val('Customer').trigger('change');
                     }

                     // Add photo name display handler
                     $('.photo-input').on('change', function() {
                        const fileName = $(this).val().split('\\').pop();
                        const index = $(this).attr('id').split('_')[1];
                        if (fileName) {
                           $('#label_' + index).text('📷 ' + fileName).removeClass('d-none');
                           $(this).closest('.input-group').find('label').addClass(
                              'text-success');
                        } else {
                           $('#label_' + index).addClass('d-none');
                           $(this).closest('.input-group').find('label').removeClass(
                              'text-success');
                        }
                     });
                  }
               },
               error: function() {
                  Swal.fire('Error', 'Gagal memuat layout ban unit', 'error');
               }
            });
         });

         // --- IMAGE UPLOAD LOGIC ---
         let currentTarget = null; // { type, serial }
         const uploadedLog = {}; // Track what's uploaded: { serial: { type: url } }

         $(document).on('click', '.tyre-doc-btn', function() {
            const serial = $(this).data('serial');
            const pos = $(this).data('pos');
            $('#modalSerial').text(serial);
            $('#modalPos').text(pos);

            // Refresh previews from local log
            $('.tyre-upload-btn').each(function() {
               const type = $(this).data('type');
               refreshTyrePreview(serial, type);
            });

            $('#tyreDocsModal').modal('show');
         });

         $(document).on('click', '.tyre-upload-btn', function() {
            const type = $(this).data('type');
            const serial = $('#modalSerial').text();
            currentTarget = {
               type,
               serial
            };
            $('#imageInput').click();
         });

         $('#imageInput').on('change', function() {
            const file = this.files[0];
            if (!file || !currentTarget) return;

            const formData = new FormData();
            formData.append('image', file);
            formData.append('type', currentTarget.type);
            formData.append('serial_number', currentTarget.serial);
            formData.append('temp_id', $('#temp_id').val());
            formData.append('_token', '{{ csrf_token() }}');

            const btn = $(`.tyre-upload-btn[data-type="${currentTarget.type}"]`);
            const originalText = btn.html();
            btn.html('<span class="spinner-border spinner-border-sm" role="status"></span>').prop('disabled',
               true);

            $.ajax({
               url: '{{ route('examination.upload-image') }}',
               type: 'POST',
               data: formData,
               processData: false,
               contentType: false,
               success: function(res) {
                  btn.html(originalText).prop('disabled', false).removeClass('btn-primary').addClass(
                     'btn-success');

                  // Save to local log
                  if (!uploadedLog[currentTarget.serial]) uploadedLog[currentTarget.serial] = {};
                  uploadedLog[currentTarget.serial][currentTarget.type] = res.url;

                  // Feedback on main table: Change button to green
                  $(`.tyre-doc-btn[data-serial="${currentTarget.serial}"]`).removeClass(
                     'btn-outline-info').addClass('btn-success');

                  const previewId = `preview-tyre-${currentTarget.type}`;
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
            const existingUrl = uploadedLog[serial] ? uploadedLog[serial][type] : null;

            if (existingUrl) {
               $(`#preview-tyre-${type}`).html(
                  `<img src="${existingUrl}" class="img-fluid rounded" style="max-height: 100px;">`);
               $(`.tyre-upload-btn[data-type="${type}"]`).removeClass('btn-primary').addClass('btn-success');
            } else {
               $(`#preview-tyre-${type}`).html(`
                  <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                     <i class="ri ri-image-line text-muted" style="font-size: 24px;"></i>
                  </div>
               `);
               $(`.tyre-upload-btn[data-type="${type}"]`).removeClass('btn-success').addClass('btn-primary');
            }
         }

         $('#examination_form').on('submit', function(e) {
            e.preventDefault();
            var form = this;
            var formData = new FormData(form);

            Swal.fire({
               title: 'Simpan Data?',
               text: "Pastikan semua data RTD dan PSI sudah benar.",
               icon: 'question',
               showCancelButton: true,
               confirmButtonText: 'Ya, Simpan!',
               customClass: {
                  confirmButton: 'btn btn-primary me-3',
                  cancelButton: 'btn btn-label-secondary'
               },
               buttonsStyling: false
            }).then(function(result) {
               if (result.isConfirmed) {
                  Swal.fire({
                     title: 'Menyimpan...',
                     didOpen: function() {
                        Swal.showLoading();
                     },
                     allowOutsideClick: false
                  });

                  $.ajax({
                     url: "{{ route('examination.store') }}",
                     method: 'POST',
                     data: formData,
                     processData: false,
                     contentType: false,
                     success: function(res) {
                        if (res.success) {
                           Swal.fire({
                              icon: 'success',
                              title: 'Berhasil!',
                              text: res.message,
                              timer: 2000
                           }).then(function() {
                              window.location.href = res.redirect;
                           });
                        }
                     },
                     error: function(res) {
                        var msg = (res.responseJSON && res.responseJSON.message) ? res
                           .responseJSON.message : 'Terjadi kesalahan sistem';
                        Swal.fire('Oops!', msg, 'error');
                     }
                  });
               }
            });
         });
      });
   </script>
@endsection
