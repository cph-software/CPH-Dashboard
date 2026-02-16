@extends('layouts.admin')

@section('title', 'Form Pelepasan Ban')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('page-style')
   <style>
      .select2-container {
         width: 100% !important;
      }
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Transaksi /</span> Pelepasan Ban</h4>

      <form id="pelepasan_form">
         @csrf
         <input type="hidden" name="movement_type" value="Removal">

         <!-- Top Row: Identifikasi Unit -->
         <div class="card mb-4 shadow-sm">
            <div class="card-header bg-transparent border-bottom">
               <h5 class="mb-0 fw-bold text-danger"><i class="ri-truck-line me-2"></i>Identifikasi Unit & Waktu (Pelepasan)
               </h5>
            </div>
            <div class="card-body pt-3">
               <div class="row">
                  <div class="col-md-4 mb-3">
                     <label class="form-label fw-bold font-size-13" for="vehicle_id">Pilih Unit / Kendaraan</label>
                     <select name="vehicle_id" id="vehicle_id" class="form-select select2" data-placeholder="Pilih Unit..."
                        required>
                        <option value="">-- Pilih Unit --</option>
                        @foreach ($kendaraans as $v)
                           <option value="{{ $v->id }}">{{ $v->kode_kendaraan }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-3 mb-3">
                     <label class="form-label fw-bold font-size-13">Tanggal Pelepasan</label>
                     <input type="date" name="movement_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                  </div>
                  <div class="col-md-3 mb-3">
                     <label class="form-label fw-bold font-size-13">KM Saat Lepas</label>
                     <input type="number" name="odometer" class="form-control" placeholder="Odometer">
                  </div>
                  <div class="col-md-2 mb-3">
                     <label class="form-label fw-bold font-size-13">HM Saat Lepas</label>
                     <input type="number" name="hour_meter" class="form-control" placeholder="Hour Meter">
                  </div>
                  <div class="col-md-4 mb-3">
                     <label class="form-label fw-bold font-size-13">Vehicle Type</label>
                     <input type="text" id="vehicle_type_display" class="form-control bg-light" readonly
                        placeholder="Auto-filled">
                  </div>
                  <div class="col-md-8 pt-2">
                     <div class="d-flex align-items-center justify-content-end h-100">
                        <span class="badge bg-label-danger text-uppercase px-3 py-2">Removal</span>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <div class="row">
            <!-- Left Column: Layout Ban (Visual) -->
            <div class="col-xl-6 col-lg-8">
               <div class="card mb-4 shadow-sm" style="min-height: 450px;">
                  <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                     <h6 class="mb-0 fw-bold"><i class="ri-layout-grid-line me-2"></i>Visual Layout Ban</h6>
                     <span class="badge bg-label-secondary" id="unit_code_display">-</span>
                  </div>
                  <div class="card-body d-flex flex-column align-items-center justify-content-center p-0">
                     <div id="layout_container" class="w-100 h-100 d-flex align-items-center justify-content-center p-4">
                        <div class="text-center text-muted p-5 w-100">
                           <i class="ri-truck-line ri-4x mb-3 d-block opacity-25"></i>
                           <p class="mb-0">Pilih Unit di atas untuk memuat layout ban.</p>
                        </div>
                     </div>
                  </div>
                  {{-- Quick Selection Info --}}
                  <div id="selection_info" class="mx-3 mb-3 p-3 rounded-3 shadow-sm"
                     style="display: none; background: linear-gradient(135deg, #ea5455 0%, #feb1b2 100%); color: white;">
                     <div class="d-flex align-items-center">
                        <div class="avatar avatar-md bg-white-transparent me-3">
                           <i class="ri-focus-3-line text-white ri-xl"></i>
                        </div>
                        <div>
                           <h6 class="mb-0 text-white" id="info_pos_name">Posisi -</h6>
                           <small class="text-white-50" id="info_pos_code">CODE</small>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Right Column: Removal Details -->
            <div class="col-xl-6 col-lg-4">
               <div class="card mb-4 shadow-sm">
                  <div class="card-header bg-transparent border-bottom">
                     <h6 class="mb-0 fw-bold"><i class="ri-list-settings-line me-2"></i>Detail Pelepasan</h6>
                  </div>
                  <div class="card-body pt-3">
                     <!-- Position selection sync -->
                     <div class="mb-3">
                        <label class="form-label fw-bold font-size-13" for="position_id">Posisi Ban</label>
                        <select name="position_id" id="position_id" class="form-select select2"
                           data-placeholder="Pilih Posisi..." required disabled>
                           <option value="">-- Pilih Posisi --</option>
                        </select>
                     </div>

                     <div id="current_tyre_info" class="mb-3 p-3 rounded-3 border-start border-danger border-5 shadow-sm"
                        style="display: none; background: #fffcf0;">
                        <h6 class="mb-2 fw-bold text-muted text-uppercase small">Detail Ban Terpasang</h6>
                        <div class="mb-1">
                           <small class="text-muted d-block small">Serial Number</small>
                           <strong id="info_sn" class="text-dark">-</strong>
                        </div>
                        <div class="row g-2">
                           <div class="col-6">
                              <small class="text-muted d-block small">Brand</small>
                              <span id="info_brand" class="fw-bold fs-tiny">-</span>
                           </div>
                           <div class="col-6">
                              <small class="text-muted d-block small">Pattern/Size</small>
                              <span id="info_pattern_size" class="fw-bold fs-tiny text-truncate d-block">-</span>
                           </div>
                        </div>
                     </div>

                     <div class="row g-2 mb-3">
                        <div class="col-6">
                           <label class="form-label fw-bold font-size-13">Alasan (Failure Code)</label>
                           <select name="failure_code_id" id="failure_code_id" class="form-select select2"
                              data-placeholder="Pilih Alasan...">
                              <option value="">-- Pilih Alasan --</option>
                              @foreach ($failureCodes as $fc)
                                 <option value="{{ $fc->id }}">{{ $fc->failure_code }} - {{ $fc->failure_name }}
                                 </option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-6">
                           <label class="form-label fw-bold font-size-13">Status Akhir Ban</label>
                           <select name="target_status" id="target_status" class="form-select" required>
                              <option value="Repaired">REPAIR (Butuh Perbaikan)</option>
                              <option value="Scrap">SCRAP (Rusak Total / Afkir)</option>
                              <option value="New">STOCK (Bagus / Pindah Unit)</option>
                           </select>
                        </div>
                     </div>

                     <div class="row g-2 mb-3">
                        <div class="col-6">
                           <label class="form-label fw-bold font-size-13">Lokasi Pengerjaan</label>
                           <select name="work_location_id" id="work_location_id" class="form-select select2"
                              data-placeholder="Pilih Lokasi...">
                              <option value=""></option>
                              @foreach ($locations as $loc)
                                 <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-6">
                           <label class="form-label fw-bold font-size-13">Operational Segment</label>
                           <select name="operational_segment_id" id="operational_segment_id" class="form-select select2"
                              data-placeholder="Pilih Segmen..." disabled>
                              <option value=""></option>
                           </select>
                        </div>
                     </div>

                     <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Penggunaan Baut Baru (Jika Ada)</label>
                        <div class="d-flex align-items-center gap-3">
                           <div class="form-check form-switch">
                              <input class="form-check-input" type="checkbox" name="new_bolts_used" id="new_bolts"
                                 value="1">
                              <label class="form-check-label" for="new_bolts">Ya</label>
                           </div>
                           <div id="bolt_qty_container" style="display: none;">
                              <div class="input-group input-group-sm">
                                 <span class="input-group-text bg-primary text-white border-primary">Jumlah Baut
                                    Baru</span>
                                 <input type="number" name="new_bolts_quantity" class="form-control border-danger"
                                    placeholder="Qty" style="width: 80px;">
                              </div>
                           </div>
                        </div>
                     </div>

                     <div class="row g-2 mb-3">
                        <div class="col-6">
                           <label class="form-label fw-bold font-size-13">Tyreman 1</label>
                           <input type="text" name="tyreman_1" class="form-control" placeholder="Nama">
                        </div>
                        <div class="col-6">
                           <label class="form-label fw-bold font-size-13">Tyreman 2 (Helper)</label>
                           <input type="text" name="tyreman_2" class="form-control" placeholder="Nama">
                        </div>
                     </div>

                     <div class="row g-2 mb-3">
                        <div class="col-4">
                           <label class="form-label fw-bold font-size-13 small">Pressure (PSI)</label>
                           <input type="number" name="psi_reading" class="form-control" placeholder="PSI">
                        </div>
                        <div class="col-4">
                           <label class="form-label fw-bold font-size-13 small">RTD (mm)</label>
                           <input type="number" name="rtd_reading" class="form-control" placeholder="RTD" step="0.01">
                        </div>
                        <div class="col-4">
                           <label class="form-label fw-bold font-size-13 small">Rim Size</label>
                           <input type="text" name="rim_size" class="form-control" placeholder="Size">
                        </div>
                     </div>

                     <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Waktu Pengerjaan (Start - End)</label>
                        <div class="row g-2">
                           <div class="col-6">
                              <input type="time" name="start_time" class="form-control small">
                              <small class="text-muted fs-tiny">Jam Mulai</small>
                           </div>
                           <div class="col-6">
                              <input type="time" name="end_time" class="form-control small">
                              <small class="text-muted fs-tiny">Jam Selesai</small>
                           </div>
                        </div>
                     </div>

                     <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Remarks (Keterangan Dropdown)</label>
                        <select name="remarks" class="form-select select2" data-placeholder="Pilih Keterangan...">
                           <option value=""></option>
                           <option value="Pasang">Pasang</option>
                           <option value="Pindah">Pindah</option>
                           <option value="Lepas">Lepas</option>
                           <option value="Tergores">Tergores</option>
                           <option value="Kembung">Kembung</option>
                           <option value="Pecah">Pecah</option>
                           <option value="Sobek">Sobek</option>
                           <option value="Tertusuk">Tertusuk</option>
                           <option value="Telapak Lepas">Telapak Lepas</option>
                        </select>
                     </div>

                     <div class="mb-4">
                        <label class="form-label fw-bold font-size-13">Keterangan Tambahan (Notes)</label>
                        <textarea name="notes" class="form-control" rows="3"
                           placeholder="Masukkan catatan tambahan jika ada..."></textarea>
                     </div>

                     <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-danger shadow" id="btn_submit">
                           <i class="ri-delete-bin-line me-1"></i> Proses Pelepasan
                        </button>
                        <a href="{{ route('tyre-movement.index') }}"
                           class="btn btn-outline-secondary btn-sm text-center">Batal</a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </form>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function () {
         const vehicleSelect = $('#vehicle_id');
         const positionSelect = $('#position_id');
         const infoArea = document.getElementById('current_tyre_info');
         const layoutContainer = document.getElementById('layout_container');
         const selectionInfo = document.getElementById('selection_info');
         let assignedTyres = {};
         let suggestedSegmentId = null; // Store suggested segment from installation history

         // Initialize Select2
         $('.select2').each(function () {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
               placeholder: $this.data('placeholder'),
               dropdownParent: $(this).parent(),
               allowClear: true
            });
         });

         // Handle Baut Baru Toggle
         $('#new_bolts').on('change', function () {
            if (this.checked) {
               $('#bolt_qty_container').fadeIn();
            } else {
               $('#bolt_qty_container').fadeOut();
               $('input[name="new_bolts_quantity"]').val(0);
            }
         });

         // Handle Location -> Segment Filtering
         $('#work_location_id').on('change', function () {
            const locId = $(this).val();
            const segmentSelect = $('#operational_segment_id');

            segmentSelect.empty().append('<option value=""></option>');
            if (!locId) {
               segmentSelect.prop('disabled', true).trigger('change');
               return;
            }

            segmentSelect.prop('disabled', false);
            fetch(`{{ url('tyre_performance/segments') }}/${locId}`)
               .then(response => response.json())
               .then(data => {
                  data.forEach(seg => {
                     segmentSelect.append(`<option value="${seg.id}">${seg.segment_name}</option>`);
                  });

                  // Auto-select segment if suggested via tyre history
                  if (suggestedSegmentId) {
                     segmentSelect.val(suggestedSegmentId);
                     // Verify if selection worked (it might not be in this location list)
                     if (!segmentSelect.val()) {
                        // Reset if not found in this location
                        suggestedSegmentId = null;
                     }
                  }

                  segmentSelect.trigger('change');
               });
         });

         vehicleSelect.on('change', function () {
            const vehicleId = $(this).val();
            const text = $(this).find('option:selected').text();
            document.getElementById('unit_code_display').textContent = vehicleId ? text : '-';
            infoArea.style.display = 'none';

            if (!vehicleId) {
               layoutContainer.innerHTML =
                  '<div class="text-center text-muted p-5 bg-white rounded-4 shadow-sm border w-100"><i class="ri-truck-line ri-4x mb-3 d-block opacity-25"></i><p class="mb-0">Pilih Kendaraan untuk memuat layout ban.</p></div>';
               positionSelect.empty().append('<option value="">-- Pilih Posisi --</option>').prop('disabled',
                  true);
               selectionInfo.style.display = 'none';
               $('#vehicle_type_display').val('');
               return;
            }

            // Fetch Vehicle Detail
            fetch(`{{ url('tyre_performance/vehicle-detail') }}/${vehicleId}`)
               .then(response => response.json())
               .then(data => {
                  $('#vehicle_type_display').val(data.jenis_kendaraan || '-');
               });

            // Load Layout
            layoutContainer.innerHTML = '<div class="spinner-border text-primary"></div>';
            fetch(`{{ url('tyre_performance/layout') }}/${vehicleId}`)
               .then(response => response.text())
               .then(html => {
                  layoutContainer.innerHTML = html;
                  attachLayoutEvents();
               });

            // Load Positions
            fetch(`{{ url('tyre_performance/position-info') }}?vehicle_id=${vehicleId}&type=Removal`)
               .then(response => response.json())
               .then(data => {
                  assignedTyres = data.assignedTyres;
                  positionSelect.empty().append('<option value="">-- Pilih Posisi --</option>');
                  data.positions.forEach(pos => {
                     const tyre = assignedTyres[pos.id];
                     positionSelect.append(
                        `<option value="${pos.id}">${pos.position_code} - ${pos.position_name} (${tyre.serial_number})</option>`
                     );
                  });
                  positionSelect.prop('disabled', false);

                  // Auto-select position if provided in URL
                  if (typeof pendingPositionId !== 'undefined' && pendingPositionId) {
                     positionSelect.val(pendingPositionId).trigger('change');
                     pendingPositionId = null; // Reset
                  } else {
                     positionSelect.trigger('change');
                  }
               });
         });

         // Check URL Params for Auto-fill
         const urlParams = new URLSearchParams(window.location.search);
         const preVehicleId = urlParams.get('vehicle_id');
         let pendingPositionId = urlParams.get('position_id');

         if (preVehicleId) {
            if (vehicleSelect.find("option[value='" + preVehicleId + "']").length) {
               vehicleSelect.val(preVehicleId).trigger('change');
            }
         }

         // Sync visual click to dropdown
         function attachLayoutEvents() {
            const nodes = document.querySelectorAll('.m-tyre-node');
            nodes.forEach(node => {
               node.addEventListener('click', function () {
                  const posId = this.getAttribute('data-position-id');
                  if (!this.classList.contains('filled')) {
                     Swal.fire('Informasi',
                        'Posisi ini kosong. Gunakan Form Pemasangan jika ingin memasang ban.',
                        'info');
                     return;
                  }

                  positionSelect.val(posId).trigger('change');
               });
            });
         }

         // Sync dropdown to visual
         positionSelect.on('change', function () {
            const posId = $(this).val();
            const nodes = document.querySelectorAll('.m-tyre-node');

            nodes.forEach(n => n.classList.remove('selected'));

            if (posId && assignedTyres[posId]) {
               const tyre = assignedTyres[posId];
               const targetNode = document.querySelector(`.m-tyre-node[data-position-id="${posId}"]`);

               if (targetNode) {
                  targetNode.classList.add('selected');
                  // Show Info
                  document.getElementById('info_pos_name').textContent = targetNode.getAttribute('data-name');
                  document.getElementById('info_pos_code').textContent = targetNode.getAttribute('data-code');
                  selectionInfo.style.display = 'block';
               }

               // Update current tyre display
               document.getElementById('info_sn').textContent = tyre.serial_number;
               document.getElementById('info_brand').textContent = tyre.brand ? tyre.brand.brand_name : '-';
               document.getElementById('info_pattern_size').textContent =
                  `${tyre.pattern ? tyre.pattern.name : '-'} / ${tyre.size ? tyre.size.size : '-'}`;
               infoArea.style.display = 'block';

               // --- AUTO-FILL LOGIC FROM LATEST INSTALLATION ---
               if (tyre.latest_installation) {
                  // 1. Rim Size
                  if (tyre.latest_installation.rim_size) {
                     $('input[name="rim_size"]').val(tyre.latest_installation.rim_size);
                  }

                  // 2. Operational Segment (Set suggestion for location change)
                  if (tyre.latest_installation.operational_segment_id) {
                     suggestedSegmentId = tyre.latest_installation.operational_segment_id;

                     // If location is already selected and segments loaded, try to select immediately
                     const currentSegmentVal = $('#operational_segment_id').val();
                     if (!currentSegmentVal && $('#operational_segment_id option').length > 1) {
                        $('#operational_segment_id').val(suggestedSegmentId).trigger('change');
                     }
                  }
               } else {
                  // Clear if no history
                  $('input[name="rim_size"]').val('');
                  suggestedSegmentId = null;
               }
               // ------------------------------------------------
            } else {
               infoArea.style.display = 'none';
               selectionInfo.style.display = 'none';
            }
         });

         document.getElementById('pelepasan_form').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const posId = positionSelect.val();
            const targetNode = document.querySelector(`.m-tyre-node[data-position-id="${posId}"]`);

            if (!posId || !targetNode) {
               Swal.fire('Peringatan', 'Silakan pilih posisi pelepasan terlebih dahulu.', 'warning');
               return;
            }

            Swal.fire({
               title: 'Konfirmasi Pelepasan',
               text: 'Apakah Anda yakin ingin melepas ban ini dari unit?',
               icon: 'warning',
               showCancelButton: true,
               confirmButtonText: 'Ya, Lepas!',
               customClass: {
                  confirmButton: 'btn btn-danger me-3',
                  cancelButton: 'btn btn-outline-secondary'
               },
               buttonsStyling: false
            }).then((result) => {
               if (result.isConfirmed) {
                  const btn = document.getElementById('btn_submit');
                  btn.disabled = true;
                  btn.innerHTML =
                     '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

                  fetch(`{{ url('tyre_performance/store') }}`, {
                     method: 'POST',
                     body: formData,
                     headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                     }
                  })
                     .then(response => response.json())
                     .then(data => {
                        if (data.success) {
                           Swal.fire({
                              icon: 'success',
                              title: 'Berhasil!',
                              text: 'Ban berhasil dilepas dari unit',
                              timer: 2000
                           }).then(() => {
                              window.location.href = "{{ route('tyre-movement.index') }}";
                           });
                        } else {
                           Swal.fire('Gagal', data.message, 'error');
                           btn.disabled = false;
                           btn.innerHTML = '<i class="ri-delete-bin-line me-1"></i> Proses Pelepasan';
                        }
                     });
               }
            });
         });
      });
   </script>
@endsection