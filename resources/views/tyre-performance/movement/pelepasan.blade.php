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

      .sticky-panel {
         position: sticky;
         top: 85px;
         z-index: 10;
         transition: all 0.3s ease;
      }

      .form-section-header {
         display: flex;
         align-items: center;
         margin-bottom: 1.25rem;
         padding-bottom: 0.5rem;
         border-bottom: 1px solid #ebedef;
      }

      .form-section-title {
         font-weight: 700;
         color: #5d596c;
         margin-bottom: 0;
         display: flex;
         align-items: center;
      }

      .form-section-icon {
         width: 32px;
         height: 32px;
         background: rgba(234, 84, 85, 0.1);
         color: #ea5455;
         border-radius: 8px;
         display: flex;
         align-items: center;
         justify-content: center;
         margin-right: 12px;
         font-size: 1.2rem;
      }

      .premium-card {
         border: none;
         box-shadow: 0 0.125rem 0.25rem rgba(165, 163, 174, 0.3);
         border-radius: 0.75rem;
         transition: transform 0.2s;
      }

      .premium-card:hover {
         transform: translateY(-2px);
      }

      .removal-info-box {
         background: #fffcf0;
         border-radius: 12px;
         padding: 15px;
         border: 1px solid #ffeeba;
         border-left: 5px solid #ffc107;
      }

      .visual-layout-card {
         border-radius: 1rem;
         overflow: hidden;
         background: #fff;
         border: 1px solid #e9e9e9;
      }

      /* Fix Select2 Clipping */
      .card,
      .card-body {
         overflow: visible !important;
      }

      @media (max-width: 991.98px) {
         .sticky-panel {
            position: static !important;
         }
      }
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold mb-0 text-danger"><span class="text-muted fw-light">Transaksi /</span> Pelepasan Ban</h4>
         <a href="{{ route('tyre-movement.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kembali
         </a>
      </div>

      <form id="pelepasan_form">
         @csrf
         <input type="hidden" name="movement_type" value="Removal">

         <div class="row g-4">
            <!-- LEFT PANEL: Sticky Visual Layout -->
            <div class="col-lg-5 col-xl-4 order-2 order-lg-1">
               <div class="sticky-panel">
                  <div class="visual-layout-card shadow-sm mb-4">
                     <div
                        class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                        <h6 class="mb-0 fw-bold"><i class="ri-mouse-line me-2 text-danger"></i>Visual Axle Layout</h6>
                        <span class="badge bg-label-secondary" id="unit_code_display">-</span>
                     </div>
                     <div class="card-body p-0 d-flex flex-column align-items-center justify-content-center"
                        style="min-height: 480px; background: #fafafa;">
                        <div id="layout_container"
                           class="w-100 h-100 d-flex align-items-center justify-content-center p-4">
                           <div class="text-center text-muted p-5 w-100">
                              <i class="ri-truck-line ri-4x mb-3 d-block opacity-25"></i>
                              <p class="mb-0">Pilih Unit Kendaraan untuk memuat posisi ban.</p>
                           </div>
                        </div>
                     </div>
                     <!-- Selection Info Overlay (Danger Style for Removal) -->
                     <div id="selection_info" class="m-3 p-3 rounded-3 shadow-sm"
                        style="display: none; background: linear-gradient(135deg, #ea5455 0%, #feb1b2 100%); color: white;">
                        <div class="d-flex align-items-center">
                           <div
                              class="avatar avatar-md bg-white-transparent me-3 d-flex align-items-center justify-content-center"
                              style="background: rgba(255,255,255,0.2); border-radius: 8px;">
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
            </div>

            <!-- RIGHT PANEL: Scrollable Form Sections -->
            <div class="col-lg-7 col-xl-8 order-1 order-lg-2">
               <!-- SECTION 1: Identifikasi Unit -->
               <div class="card premium-card mb-4">
                  <div class="card-body">
                     <div class="form-section-header">
                        <div class="form-section-icon"><i class="ri-truck-line"></i></div>
                        <h5 class="form-section-title">Identifikasi Unit & Tanggal</h5>
                     </div>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                           <label class="form-label fw-bold" for="vehicle_id">Unit / Kendaraan</label>
                           <select name="vehicle_id" id="vehicle_id" class="form-select select2" required>
                              <option value="">-- Pilih Unit --</option>
                              @foreach ($kendaraans as $v)
                                 <option value="{{ $v->id }}">{{ $v->kode_kendaraan }}
                                    {{ $v->no_polisi ? '[' . $v->no_polisi . ']' : '' }}
                                 </option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-6 mb-3">
                           <label class="form-label fw-bold">Tanggal Pelepasan</label>
                           <input type="date" name="movement_date" class="form-control" value="{{ date('Y-m-d') }}"
                              required>
                        </div>
                        <div class="col-md-4 mb-3">
                           <label class="form-label fw-bold">KM Saat Lepas</label>
                           <input type="number" name="odometer" id="odometer" class="form-control"
                              placeholder="KM Odometer" required>
                           <small class="text-muted extra-small d-block mt-1">Last KM: <span id="last_odo_display"
                                 class="fw-bold">-</span></small>
                        </div>
                        <div class="col-md-4 mb-3">
                           <label class="form-label fw-bold">HM Saat Lepas</label>
                           <input type="number" name="hour_meter" id="hour_meter" class="form-control"
                              placeholder="Hour Meter" required>
                           <small class="text-muted extra-small d-block mt-1">Last HM: <span id="last_hm_display"
                                 class="fw-bold">-</span></small>
                        </div>
                        <div class="col-md-4 mb-3">
                           <label class="form-label fw-bold">Tipe Unit</label>
                           <input type="text" id="vehicle_type_display" class="form-control bg-light" readonly
                              placeholder="Auto-filled">
                        </div>
                        <div class="col-12">
                           <div
                              class="bg-light p-3 rounded border border-dashed d-flex align-items-center justify-content-between">
                              <div>
                                 <h6 class="mb-0 small fw-bold text-dark"><i class="ri-refresh-line me-1 text-warning"></i>
                                    Reset Meteran Unit?</h6>
                                 <small class="text-muted extra-small">Centang jika Odo/HM kembali ke 0 (Ganti
                                    unit/panel)</small>
                              </div>
                              <div class="form-check form-switch mb-0">
                                 <input class="form-check-input ms-0" type="checkbox" name="is_meter_reset"
                                    id="is_meter_reset" value="1" style="width: 2.5em; height: 1.25em;">
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- SECTION 2: Detail Ban Terpasang -->
               <div class="card premium-card mb-4 border-start border-danger border-5">
                  <div class="card-body">
                     <div class="form-section-header">
                        <div class="form-section-icon" style="background: rgba(234, 84, 85, 0.1); color: #ea5455;"><i
                              class="ri-list-settings-line"></i></div>
                        <h5 class="form-section-title">Detail Ban Terpasang</h5>
                     </div>

                     <div class="mb-4">
                        <label class="form-label fw-bold text-danger" for="position_id">1. Pilih Posisi Pelepasan</label>
                        <select name="position_id" id="position_id" class="form-select select2" required disabled>
                           <option value="">-- Pilih melalui visual layout atau list ini --</option>
                        </select>
                     </div>

                     <div id="current_tyre_info" class="mb-4 removal-info-box shadow-sm" style="display: none;">
                        <h6 class="mb-3 fw-bold text-muted text-uppercase small"><i class="ri-information-line me-1"></i>
                           Data Ban di Unit Ini</h6>
                        <div class="row g-3">
                           <div class="col-md-4">
                              <small class="text-muted d-block">Serial Number</small>
                              <strong id="info_sn" class="fs-5 text-dark">-</strong>
                           </div>
                           <div class="col-md-4">
                              <small class="text-muted d-block">Brand</small>
                              <span id="info_brand" class="fw-bold">-</span>
                           </div>
                           <div class="col-md-4">
                              <small class="text-muted d-block">Pattern/Size</small>
                              <span id="info_pattern_size" class="fw-bold">-</span>
                           </div>
                        </div>
                     </div>

                     <div class="row g-3">
                        <div class="col-md-6">
                           <label class="form-label fw-bold">Alasan (Failure Code)</label>
                           <select name="failure_code_id" id="failure_code_id" class="form-select select2">
                              <option value="">-- Pilih Alasan --</option>
                              @foreach ($failureCodes as $fc)
                                 <option value="{{ $fc->id }}">{{ $fc->failure_code }} - {{ $fc->failure_name }}
                                 </option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label class="form-label fw-bold">Status Akhir Ban</label>
                           <select name="target_status" id="target_status" class="form-select" required>
                              <option value="Repaired">REPAIR (Butuh Perbaikan)</option>
                              <option value="Scrap">SCRAP (Rusak Total / Afkir)</option>
                              <option value="New">STOCK (Bagus / Pindah Unit)</option>
                           </select>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- SECTION 3: Technical Inspection -->
               <div class="card premium-card mb-4">
                  <div class="card-body">
                     <div class="form-section-header">
                        <div class="form-section-icon"><i class="ri-ruler-line"></i></div>
                        <h5 class="form-section-title">Hasil Pemeriksaan Akhir</h5>
                     </div>
                     <div class="row g-3">
                        <div class="col-md-4">
                           <label class="form-label fw-bold">Sisa RTD (mm)</label>
                           <div class="input-group">
                              <input type="number" name="rtd_reading" id="rtd_reading"
                                 class="form-control border-danger" step="0.01" required>
                              <span class="input-group-text bg-danger text-white border-danger">mm</span>
                           </div>
                        </div>
                        <div class="col-md-4">
                           <label class="form-label fw-bold">Pressure (PSI)</label>
                           <div class="input-group">
                              <input type="number" name="psi_reading" class="form-control" required>
                              <span class="input-group-text">PSI</span>
                           </div>
                        </div>
                        <div class="col-md-4">
                           <label class="form-label fw-bold">Rim Size</label>
                           <input type="text" name="rim_size" class="form-control">
                        </div>
                     </div>
                  </div>
               </div>

               <!-- SECTION 4: Operation & Team -->
               <div class="card premium-card mb-4">
                  <div class="card-body">
                     <div class="form-section-header">
                        <div class="form-section-icon"><i class="ri-group-line"></i></div>
                        <h5 class="form-section-title">Administrasi & Petugas</h5>
                     </div>
                     <div class="row g-3 mb-4">
                        <div class="col-md-6">
                           <label class="form-label fw-bold">Gudang / Lokasi Tujuan</label>
                           <select name="work_location_id" id="work_location_id" class="form-select select2">
                              <option value=""></option>
                              @foreach ($locations as $loc)
                                 <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label class="form-label fw-bold">Operational Segment</label>
                           <select name="operational_segment_id" id="operational_segment_id"
                              class="form-select select2">
                              <option value=""></option>
                              @foreach ($segments as $seg)
                                 <option value="{{ $seg->id }}">{{ $seg->segment_name }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label class="form-label fw-bold">Tyreman 1</label>
                           <input type="text" name="tyreman_1" class="form-control" placeholder="Nama Petugas">
                        </div>
                        <div class="col-md-6">
                           <label class="form-label fw-bold">Tyreman 2</label>
                           <input type="text" name="tyreman_2" class="form-control" placeholder="Helper">
                        </div>
                        <div class="col-md-6">
                           <label class="form-label fw-bold">Waktu Mulai</label>
                           <input type="time" name="start_time" class="form-control">
                        </div>
                        <div class="col-md-6">
                           <label class="form-label fw-bold">Waktu Selesai</label>
                           <input type="time" name="end_time" class="form-control">
                        </div>
                     </div>

                     <div class="divider text-start fw-bold mb-3"><span class="text-muted">Lainnya</span></div>

                     <div class="mb-3">
                        <label class="form-label fw-bold">Baut Baru (Jika diganti)</label>
                        <div class="d-flex align-items-center gap-4 p-2 rounded bg-light border">
                           <div class="form-check form-switch m-0">
                              <input class="form-check-input" type="checkbox" name="new_bolts_used" id="new_bolts"
                                 value="1">
                              <label class="form-check-label" for="new_bolts">Ya</label>
                           </div>
                           <div id="bolt_qty_container" style="display: none;">
                              <div class="input-group input-group-sm">
                                 <span class="input-group-text badge bg-danger">Qty</span>
                                 <input type="number" name="new_bolts_quantity" class="form-control"
                                    style="width: 80px;">
                              </div>
                           </div>
                        </div>
                     </div>

                     <div class="mb-3">
                        <label class="form-label fw-bold">Remarks</label>
                        <select name="remarks" class="form-select select2">
                           <option value=""></option>
                           <option value="Lepas">Lepas</option>
                           <option value="Pindah">Pindah</option>
                           <option value="Scrap">Scrap</option>
                        </select>
                     </div>

                     <div class="mb-4">
                        <label class="form-label fw-bold">Catatan (Notes)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Masukkan alasan detail pelepasan..."></textarea>
                     </div>

                     <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger btn-lg shadow" id="btn_submit">
                           <i class="ri-delete-bin-line me-1"></i> Proses Pelepasan
                        </button>
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
      $(document).ready(function() {
         const vehicleSelect = $('#vehicle_id');
         const positionSelect = $('#position_id');
         const infoArea = document.getElementById('current_tyre_info');
         const layoutContainer = document.getElementById('layout_container');
         const selectionInfo = document.getElementById('selection_info');
         let assignedTyres = {};
         let suggestedSegmentId = null; // Store suggested segment from installation history

         // Initialize Select2
         $('.select2').each(function() {
            var $this = $(this);
            $this.select2({
               placeholder: $this.data('placeholder') || $this.attr('placeholder'),
               allowClear: true
            });
         });

         // Handle Baut Baru Toggle
         $('#new_bolts').on('change', function() {
            if (this.checked) {
               $('#bolt_qty_container').fadeIn();
            } else {
               $('#bolt_qty_container').fadeOut();
               $('input[name="new_bolts_quantity"]').val(0);
            }
         });

         // Suggested segment handling
         function applySuggestedSegment() {
            if (suggestedSegmentId) {
               $('#operational_segment_id').val(suggestedSegmentId).trigger('change');
            }
         }

         vehicleSelect.on('change', function() {
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

            // Fetch Vehicle Detail for auto-fill
            fetch(`{{ url('vehicle-detail') }}/${vehicleId}`)
               .then(response => response.json())
               .then(res => {
                  const data = res.vehicle;
                  $('#vehicle_type_display').val(data.jenis_kendaraan || '-');

                  // Update Last Odo & HM display
                  $('#last_odo_display').text(res.last_odometer.toLocaleString());
                  $('#last_hm_display').text(res.last_hour_meter.toLocaleString());
                  $('#odometer').attr('placeholder', 'Previous: ' + res.last_odometer);
                  $('#hour_meter').attr('placeholder', 'Previous: ' + res.last_hour_meter);

                  if (data.operational_segment_id) {
                     suggestedSegmentId = data.operational_segment_id;
                     applySuggestedSegment();

                     // If vehicle has area(location), auto-select it
                     if (data.area && !$('#work_location_id').val()) {
                        const locOption = $('#work_location_id option').filter(function() {
                           return $(this).text().trim() === data.area;
                        });
                        if (locOption.length) {
                           $('#work_location_id').val(locOption.val()).trigger('change');
                        }
                     }
                  }
               })
               .catch(err => console.error('Error fetching vehicle detail:', err));

            // Load Layout
            layoutContainer.innerHTML = '<div class="spinner-border text-primary"></div>';
            fetch(`{{ url('layout') }}/${vehicleId}`)
               .then(response => response.text())
               .then(html => {
                  layoutContainer.innerHTML = html;
                  attachLayoutEvents();
               });

            // Load Positions
            fetch(`{{ url('position-info') }}?vehicle_id=${vehicleId}&type=Removal`)
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
               node.addEventListener('click', function() {
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
         positionSelect.on('change', function() {
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

               // Auto-fill Current RTD
               if (tyre.current_tread_depth !== undefined) {
                  $('#rtd_reading').val(tyre.current_tread_depth);
               }

               infoArea.style.display = 'block';

               // --- AUTO-FILL LOGIC FROM LATEST INSTALLATION ---
               if (tyre.latest_installation) {
                  // 1. Rim Size
                  if (tyre.latest_installation.rim_size) {
                     $('input[name="rim_size"]').val(tyre.latest_installation.rim_size);
                  }

                  // 2. Work Location (Auto-fill from latest installation)
                  if (tyre.latest_installation.work_location_id) {
                     $('#work_location_id').val(tyre.latest_installation.work_location_id).trigger('change');
                  }

                  // 3. Operational Segment (Set suggestion)
                  if (tyre.latest_installation.operational_segment_id) {
                     suggestedSegmentId = tyre.latest_installation.operational_segment_id;
                     applySuggestedSegment();
                  }
               } else {
                  // Clear if no history
                  $('input[name="rim_size"]').val('');
                  $('#work_location_id').val('').trigger('change');
                  suggestedSegmentId = null;
               }
               // ------------------------------------------------
            } else {
               infoArea.style.display = 'none';
               selectionInfo.style.display = 'none';
            }
         });

         document.getElementById('pelepasan_form').addEventListener('submit', function(e) {
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

                  fetch(`{{ url('tyre-store') }}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                     })
                     .then(response => response.json().then(data => ({
                        status: response.status,
                        body: data
                     })))
                     .then(res => {
                        const data = res.body;
                        if (res.status === 200 && data.success) {
                           Swal.fire({
                              icon: 'success',
                              title: 'Berhasil!',
                              text: 'Ban berhasil dilepas dari unit',
                              timer: 2000
                           }).then(() => {
                              window.location.href = "{{ route('tyre-movement.index') }}";
                           });
                        } else {
                           Swal.fire('Gagal', data.message || 'Terjadi kesalahan sistem', 'error');
                           btn.disabled = false;
                           btn.innerHTML = '<i class="ri-delete-bin-line me-1"></i> Proses Pelepasan';
                        }
                     })
                     .catch(err => {
                        Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ri-delete-bin-line me-1"></i> Proses Pelepasan';
                     });
               }
            });
         });
      });
   </script>
@endsection
