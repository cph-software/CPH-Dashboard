@extends('layouts.admin')

@section('title', 'Form Rotasi Ban')

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
         background: rgba(115, 103, 240, 0.1);
         color: #7367f0;
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

      .rotation-info-box {
         background: #f8f7ff;
         border-radius: 12px;
         padding: 15px;
         border: 1px solid #dcd7ff;
         border-left: 5px solid #7367f0;
      }

      .visual-layout-card {
         border-radius: 1rem;
         overflow: hidden;
         background: #fff;
         border: 1px solid #e9e9e9;
      }

      .m-tyre-node.selected-source {
         border: 3px solid #7367f0 !important;
         box-shadow: 0 0 15px rgba(115, 103, 240, 0.5);
         transform: scale(1.1);
      }

      .m-tyre-node.selected-target {
         border: 3px solid #28c76f !important;
         box-shadow: 0 0 15px rgba(40, 199, 111, 0.5);
         transform: scale(1.1);
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
         <h4 class="fw-bold mb-0 text-primary"><span class="text-muted fw-light">Transaksi /</span> Rotasi Ban</h4>
         <a href="{{ route('tyre-movement.index') }}" class="btn btn-outline-secondary">
            <i class="ri ri-arrow-left-line me-1"></i> Kembali
         </a>
      </div>

      <form id="rotasi_form" enctype="multipart/form-data">
         @csrf
         <input type="hidden" name="movement_type" value="Rotation">

         <div class="row g-4">
            <!-- LEFT PANEL: Sticky Visual Layout -->
            <div class="col-lg-5 col-xl-4 order-2 order-lg-1">
               <div class="sticky-panel">
                  <div class="visual-layout-card shadow-sm mb-4">
                     <div
                        class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                        <h6 class="mb-0 fw-bold"><i class="ri ri-mouse-line me-2 text-primary"></i>Visual Axle Layout</h6>
                        <span class="badge bg-label-secondary" id="unit_code_display">-</span>
                     </div>
                     <div class="card-body p-0 d-flex flex-column align-items-center justify-content-center"
                        style="min-height: 480px; background: #fafafa;">
                        <div id="layout_container"
                           class="w-100 h-100 d-flex align-items-center justify-content-center p-4">
                           <div class="text-center text-muted p-5 w-100">
                              <i class="ri ri-truck-line ri-4x mb-3 d-block opacity-25"></i>
                              <p class="mb-0">Pilih Unit Kendaraan untuk memuat posisi ban.</p>
                           </div>
                        </div>
                     </div>

                     <div id="selection_status" class="m-3 p-3 rounded-3 shadow-sm bg-light border text-center">
                        <p class="mb-0 small fw-bold text-muted" id="status_text">PILIH UNIT TERLEBIH DAHULU</p>
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
                        <div class="form-section-icon"><i class="ri ri-steering-line"></i></div>
                        <h5 class="form-section-title">Identifikasi Unit & Tanggal</h5>
                     </div>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                           <label class="form-label fw-bold" for="vehicle_id">Unit / Kendaraan</label>
                           <select name="vehicle_id" id="vehicle_id" class="form-select select2" required>
                              <option value="">-- Pilih Unit --</option>
                              @foreach ($kendaraans as $v)
                                 <option value="{{ $v->id }}">{{ $v->kode_kendaraan }}
                                    {{ $v->no_polisi ? '[' . $v->no_polisi . ']' : '' }} - {{ $v->tyre_capacity_label }}
                                 </option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-6 mb-3">
                           <label class="form-label fw-bold">Tanggal Rotasi</label>
                           <input type="date" name="movement_date" class="form-control" value="{{ date('Y-m-d') }}"
                              required>
                        </div>
                        <div class="col-md-4 mb-3">
                           <label class="form-label fw-bold">KM Saat Rotasi</label>
                           <input type="number" name="odometer" id="odometer" class="form-control"
                              placeholder="KM Odometer" required>
                           <small class="text-muted extra-small d-block mt-1">Last KM: <span id="last_odo_display"
                                 class="fw-bold">-</span></small>
                        </div>
                        <div class="col-md-4 mb-3">
                           <label class="form-label fw-bold">HM Saat Rotasi</label>
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
                        <div class="col-md-12 mb-3">
                           <div class="d-flex align-items-center p-2 rounded bg-label-warning border border-warning">
                              <div class="form-check form-switch m-0">
                                 <input class="form-check-input" type="checkbox" name="is_meter_reset" id="is_meter_reset"
                                    value="1" style="width: 2.5em; height: 1.25em;">
                                 <label class="form-check-label fw-bold mb-0 ms-2" for="is_meter_reset">
                                    Unit Habis Reset Meter (Odo/HM Kembali ke 0)
                                 </label>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-6 mb-3">
                           <label class="form-label fw-bold">Workshop / Lokasi Kerja</label>
                           <select name="work_location_id" id="work_location_id" class="form-select select2" required>
                              <option value="">-- Pilih Lokasi --</option>
                              @foreach ($locations as $loc)
                                 <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-6 mb-3">
                           <label class="form-label fw-bold">Operational Segment</label>
                           <select name="operational_segment_id" id="operational_segment_id" class="form-select select2"
                              required>
                              <option value="">-- Pilih Segment --</option>
                              @foreach ($segments as $seg)
                                 <option value="{{ $seg->id }}">{{ $seg->segment_name }}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- SECTION 2: Pemilihan Posisi -->
               <div class="card premium-card mb-4 border-start border-primary border-5">
                  <div class="card-body">
                     <div class="form-section-header">
                        <div class="form-section-icon" style="background: rgba(115, 103, 240, 0.1); color: #7367f0;"><i
                              class="ri ri-arrow-left-right-line"></i></div>
                        <h5 class="form-section-title">Konfigurasi Rotasi</h5>
                     </div>

                     <div class="row g-3 mb-4">
                        <div class="col-md-6">
                           <label class="form-label fw-bold text-primary">1. Konfigurasi Sumber (Ban A)</label>
                           <select name="position_id" id="position_id" class="form-select select2" required disabled>
                              <option value="">-- Pilih Posisi A --</option>
                           </select>
                           <div id="source_tyre_info" class="mt-2 p-2 rounded bg-light border small"
                              style="display:none;">
                              SN: <strong id="src_sn">-</strong> | <span id="src_brand">-</span>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <label class="form-label fw-bold text-success">2. Konfigurasi Tujuan (Ke Posisi B)</label>
                           <select name="target_position_id" id="target_position_id" class="form-select select2"
                              required disabled>
                              <option value="">-- Pilih Posisi B --</option>
                           </select>
                           <div id="target_tyre_info" class="mt-2 p-2 rounded bg-light border small"
                              style="display:none;">
                              <span id="target_status_text" class="text-muted">Kosong (Hanya Pindah)</span>
                              <div id="target_tyre_detail" style="display:none;">
                                 SN: <strong id="tgt_sn">-</strong> | <span id="tgt_brand">-</span>
                                 <div class="badge bg-label-warning d-block mt-1">SWAP (Tukar Posisi)</div>
                              </div>
                           </div>
                        </div>
                     </div>

                     <div id="rotation_summary" class="rotation-info-box shadow-sm mb-3" style="display: none;">
                        <div class="d-flex align-items-center">
                           <div class="flex-shrink-0 text-center" style="width: 80px;">
                              <div class="p-2 bg-white rounded border border-primary mb-1">
                                 <strong id="sum_src_code">-</strong>
                              </div>
                              <small class="text-muted">Dari</small>
                           </div>
                           <div class="flex-grow-1 text-center px-3">
                              <i class="ri-arrow-right-line ri-2x text-primary"></i>
                              <div><span id="sum_type_badge" class="badge bg-label-primary">MOVE</span></div>
                           </div>
                           <div class="flex-shrink-0 text-center" style="width: 80px;">
                              <div class="p-2 bg-white rounded border border-success mb-1">
                                 <strong id="sum_tgt_code">-</strong>
                              </div>
                              <small class="text-muted">Ke</small>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- SECTION 3: Technical Inspection (RTD for both if swap) -->
               <div class="card premium-card mb-4" id="inspection_section" style="display:none;">
                  <div class="card-body">
                     <div class="form-section-header">
                        <div class="form-section-icon"><i class="ri ri-ruler-line"></i></div>
                        <h5 class="form-section-title">Hasil Pemeriksaan</h5>
                     </div>

                     <!-- RTD/PSI for source tyre -->
                     <div class="p-3 border rounded mb-3 bg-light">
                        <h6 class="fw-bold mb-3">Ban A (SN: <span id="rtd_sn_a">-</span>)</h6>
                        <div class="row g-3">
                           <div class="col-md-12">
                              <label class="form-label fw-bold">Remaining Tread Depth (4 Titik)</label>
                              <div class="row g-2">
                                 <div class="col-3">
                                    <input type="number" name="rtd_1"
                                       class="form-control form-control-sm rtd-input-a" step="0.01" placeholder="P1">
                                 </div>
                                 <div class="col-3">
                                    <input type="number" name="rtd_2"
                                       class="form-control form-control-sm rtd-input-a" step="0.01" placeholder="P2">
                                 </div>
                                 <div class="col-3">
                                    <input type="number" name="rtd_3"
                                       class="form-control form-control-sm rtd-input-a" step="0.01" placeholder="P3">
                                 </div>
                                 <div class="col-3">
                                    <input type="number" name="rtd_4"
                                       class="form-control form-control-sm rtd-input-a" step="0.01" placeholder="P4">
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-6 mt-3">
                              <label class="form-label fw-bold">Sisa RTD (Avg)</label>
                              <input type="number" name="rtd_reading" id="rtd_reading" class="form-control bg-light"
                                 step="0.01" required readonly title="Averaged from 4 points">
                           </div>
                           <div class="col-md-6 mt-3">
                              <label class="form-label fw-bold">Pressure (PSI)</label>
                              <input type="number" name="psi_reading" id="psi_reading" class="form-control" required>
                           </div>
                        </div>
                     </div>

                     <!-- RTD/PSI for target tyre (only if swap) -->
                     <div id="swap_inspection" class="p-3 border rounded mb-3 bg-light border-warning"
                        style="display:none;">
                        <h6 class="fw-bold mb-3 text-warning">Ban B (SN: <span id="rtd_sn_b">-</span>)</h6>
                        <div class="row g-3">
                           <div class="col-md-12">
                              <label class="form-label fw-bold">Remaining Tread Depth (4 Titik)</label>
                              <div class="row g-2">
                                 <div class="col-3">
                                    <input type="number" name="target_rtd_1"
                                       class="form-control form-control-sm rtd-input-b" step="0.01" placeholder="P1">
                                 </div>
                                 <div class="col-3">
                                    <input type="number" name="target_rtd_2"
                                       class="form-control form-control-sm rtd-input-b" step="0.01" placeholder="P2">
                                 </div>
                                 <div class="col-3">
                                    <input type="number" name="target_rtd_3"
                                       class="form-control form-control-sm rtd-input-b" step="0.01" placeholder="P3">
                                 </div>
                                 <div class="col-3">
                                    <input type="number" name="target_rtd_4"
                                       class="form-control form-control-sm rtd-input-b" step="0.01" placeholder="P4">
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-6 mt-3">
                              <label class="form-label fw-bold">Sisa RTD (Avg)</label>
                              <input type="number" name="target_rtd_reading" id="target_rtd_reading"
                                 class="form-control bg-light" step="0.01" readonly title="Averaged from 4 points">
                           </div>
                           <div class="col-md-6 mt-3">
                              <label class="form-label fw-bold">Pressure (PSI)</label>
                              <input type="number" name="target_psi_reading" id="target_psi_reading"
                                 class="form-control">
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- SECTION 4: Team -->
               <div class="card premium-card mb-4">
                  <div class="card-body">
                     <div class="form-section-header">
                        <div class="form-section-icon"><i class="ri ri-group-line"></i></div>
                        <h5 class="form-section-title">Petugas & Catatan</h5>
                     </div>
                     <div class="row g-3 mb-4">
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

                        <div class="col-md-6">
                           <label class="form-label fw-bold"><i class="ri ri-camera-line me-1"></i>Foto Instalasi Ban
                              A</label>
                           <div class="p-2 border rounded bg-light">
                              <input type="file" name="photo" class="form-control form-control-sm"
                                 accept="image/*">
                           </div>
                        </div>
                        <div class="col-md-6" id="photo_target_container">
                           <label class="form-label fw-bold"><i class="ri ri-camera-line me-1"></i>Foto Instalasi Ban B
                              (Swap)</label>
                           <div class="p-2 border rounded bg-light">
                              <input type="file" name="photo_target" class="form-control form-control-sm"
                                 accept="image/*">
                           </div>
                        </div>
                        <div class="col-md-12">
                           <label class="form-label fw-bold">Catatan (Notes)</label>
                           <textarea name="notes" class="form-control" rows="2" placeholder="Masukkan alasan rotasi..."></textarea>

                           <div class="d-grid gap-2">
                              <button type="submit" class="btn btn-primary btn-lg shadow" id="btn_submit">
                                 <i class="ri ri-arrow-left-right-line me-1"></i> Eksekusi Rotasi Ban
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
         const sourceSelect = $('#position_id');
         const targetSelect = $('#target_position_id');
         const layoutContainer = document.getElementById('layout_container');
         const statusText = document.getElementById('status_text');
         let assignedTyres = {};
         let selectionStep = 1; // 1: Select Source, 2: Select Target

         // Initialize Select2
         $('.select2').each(function() {
            var $this = $(this);
            $this.select2({
               placeholder: $this.data('placeholder') || $this.attr('placeholder'),
               allowClear: true
            });
         });

         vehicleSelect.on('change', function() {
            const vehicleId = $(this).val();
            const text = $(this).find('option:selected').text();
            document.getElementById('unit_code_display').textContent = vehicleId ? text : '-';

            resetSelection();

            if (!vehicleId) {
               layoutContainer.innerHTML =
                  '<div class="text-center text-muted p-5 bg-white rounded-4 shadow-sm border w-100"><i class="ri ri-truck-line ri-4x mb-3 d-block opacity-25"></i><p class="mb-0">Pilih Kendaraan untuk memuat posisi ban.</p></div>';
               statusText.textContent = "PILIH UNIT TERLEBIH DAHULU";
               $('#vehicle_type_display').val('');
               return;
            }

            statusText.textContent = "STEP 1: PILIH BAN YANG AKAN DIPINDAH";

            // Fetch Vehicle Detail
            fetch(`{{ url('vehicle-detail') }}/${vehicleId}`)
               .then(response => response.json())
               .then(res => {
                  const data = res.vehicle;
                  $('#vehicle_type_display').val(data.jenis_kendaraan || '-');

                  const odo = res.last_odometer || 0;
                  const hm = res.last_hour_meter || 0;

                  $('#last_odo_display').text(odo.toLocaleString());
                  $('#last_hm_display').text(hm.toLocaleString());

                  // Auto-fill values
                  $('#odometer').val(odo);
                  $('#hour_meter').val(hm);

                  $('#odometer').attr('placeholder', 'Previous: ' + odo);
                  $('#hour_meter').attr('placeholder', 'Previous: ' + hm);

                  // Auto-fill Location and Segment
                  if (data.operational_segment_id) {
                     $('#operational_segment_id').val(data.operational_segment_id).trigger('change');
                  }

                  if (data.area) {
                     const locOption = $('#work_location_id option').filter(function() {
                        return $(this).text().trim().toLowerCase() === data.area.toLowerCase();
                     });
                     if (locOption.length) {
                        $('#work_location_id').val(locOption.val()).trigger('change');
                     }
                  }
               })
               .catch(err => {
                  console.error('Error fetching vehicle detail:', err);
               });

            // Load Layout
            layoutContainer.innerHTML = '<div class="spinner-border text-primary"></div>';
            fetch(`{{ url('layout') }}/${vehicleId}`)
               .then(response => response.text())
               .then(html => {
                  layoutContainer.innerHTML = html;
                  attachLayoutEvents();
               });

            // Load Positions for Dropdown
            fetch(`{{ url('position-info') }}?vehicle_id=${vehicleId}&type=Removal`)
               .then(response => response.json())
               .then(data => {
                  assignedTyres = data.assignedTyres;
                  sourceSelect.empty().append('<option value="">-- Pilih Posisi A --</option>');
                  targetSelect.empty().append('<option value="">-- Pilih Posisi B --</option>');

                  // Source only shows filled positions
                  data.positions.forEach(pos => {
                     const tyre = assignedTyres[pos.id];
                     if (tyre) {
                        sourceSelect.append(
                           `<option value="${pos.id}">${pos.position_code} - ${pos.position_name} (${tyre.serial_number})</option>`
                        );
                     }
                     // Target shows all positions
                     targetSelect.append(
                        `<option value="${pos.id}">${pos.position_code} - ${pos.position_name} ${tyre ? '('+tyre.serial_number+')' : '[KOSONG]'}</option>`
                     );
                  });

                  sourceSelect.prop('disabled', false);
                  targetSelect.prop('disabled', false);
               });
         });

         function resetSelection() {
            selectionStep = 1;
            sourceSelect.val('').trigger('change');
            targetSelect.val('').trigger('change');
            $('#source_tyre_info').hide();
            $('#target_tyre_info').hide();
            $('#rotation_summary').hide();
            $('#inspection_section').hide();
            const nodes = document.querySelectorAll('.m-tyre-node');
            nodes.forEach(n => n.classList.remove('selected-source', 'selected-target'));
         }

         function attachLayoutEvents() {
            const nodes = document.querySelectorAll('.m-tyre-node');
            nodes.forEach(node => {
               node.addEventListener('click', function() {
                  const posId = this.getAttribute('data-position-id');

                  if (selectionStep === 1) {
                     if (!this.classList.contains('filled')) {
                        Swal.fire('Info', 'Pilih posisi yang ada bannya untuk dipindah.', 'info');
                        return;
                     }
                     sourceSelect.val(posId).trigger('change');
                     selectionStep = 2;
                     statusText.textContent = "STEP 2: PILIH POSISI TUJUAN";
                     statusText.className = "mb-0 small fw-bold text-success";
                  } else {
                     if (posId == sourceSelect.val()) {
                        resetSelection();
                        statusText.textContent = "STEP 1: PILIH BAN YANG AKAN DIPINDAH";
                        statusText.className = "mb-0 small fw-bold text-muted";
                        return;
                     }
                     targetSelect.val(posId).trigger('change');
                  }
               });
            });
         }

         sourceSelect.on('change', function() {
            const posId = $(this).val();
            const nodes = document.querySelectorAll('.m-tyre-node');
            nodes.forEach(n => n.classList.remove('selected-source'));

            if (posId && assignedTyres[posId]) {
               const tyre = assignedTyres[posId];
               $('#src_sn').text(tyre.serial_number);
               $('#src_brand').text(tyre.brand ? tyre.brand.brand_name : '-');
               $('#source_tyre_info').fadeIn();

               const node = document.querySelector(`.m-tyre-node[data-position-id="${posId}"]`);
               if (node) node.classList.add('selected-source');

               // Info for inspection
               $('#rtd_sn_a').text(tyre.serial_number);
               $('#rtd_reading').val(tyre.current_tread_depth || '');
               $('#inspection_section').fadeIn();

               updateSummary();
            } else {
               $('#source_tyre_info').hide();
            }
         });

         targetSelect.on('change', function() {
            const posId = $(this).val();
            const nodes = document.querySelectorAll('.m-tyre-node');
            nodes.forEach(n => n.classList.remove('selected-target'));

            if (posId) {
               const srcId = sourceSelect.val();
               if (posId == srcId) {
                  $(this).val('').trigger('change');
                  return;
               }

               const node = document.querySelector(`.m-tyre-node[data-position-id="${posId}"]`);
               if (node) node.classList.add('selected-target');

               const tyreB = assignedTyres[posId];
               if (tyreB) {
                  $('#target_tyre_detail').show();
                  $('#target_status_text').hide();
                  $('#tgt_sn').text(tyreB.serial_number);
                  $('#tgt_brand').text(tyreB.brand ? tyreB.brand.brand_name : '-');

                  $('#swap_inspection').fadeIn();
                  $('#rtd_sn_b').text(tyreB.serial_number);
                  $('#target_rtd_reading').val(tyreB.current_tread_depth || '').prop('required', true);
                  $('#target_psi_reading').prop('required', true);
               } else {
                  $('#target_tyre_detail').hide();
                  $('#target_status_text').show();
                  $('#swap_inspection').hide();
                  $('#target_rtd_reading').prop('required', false);
                  $('#target_psi_reading').prop('required', false);
               }
               $('#target_tyre_info').fadeIn();
               updateSummary();
            } else {
               $('#target_tyre_info').hide();
               $('#rotation_summary').hide();
            }
         });

         // Calculate RTD average for A
         $(document).on('input', '.rtd-input-a', function() {
            let total = 0,
               count = 0;
            $('.rtd-input-a').each(function() {
               let val = parseFloat($(this).val());
               if (!isNaN(val)) {
                  total += val;
                  count++;
               }
            });
            if (count > 0) $('#rtd_reading').val((total / count).toFixed(2));
            else $('#rtd_reading').val('');
         });

         // Calculate RTD average for B
         $(document).on('input', '.rtd-input-b', function() {
            let total = 0,
               count = 0;
            $('.rtd-input-b').each(function() {
               let val = parseFloat($(this).val());
               if (!isNaN(val)) {
                  total += val;
                  count++;
               }
            });
            if (count > 0) $('#target_rtd_reading').val((total / count).toFixed(2));
            else $('#target_rtd_reading').val('');
         });

         function updateSummary() {
            const srcId = sourceSelect.val();
            const tgtId = targetSelect.val();

            if (srcId && tgtId) {
               const srcNode = document.querySelector(`.m-tyre-node[data-position-id="${srcId}"]`);
               const tgtNode = document.querySelector(`.m-tyre-node[data-position-id="${tgtId}"]`);

               $('#sum_src_code').text(srcNode ? srcNode.getAttribute('data-code') : '-');
               $('#sum_tgt_code').text(tgtNode ? tgtNode.getAttribute('data-code') : '-');

               if (assignedTyres[tgtId]) {
                  $('#sum_type_badge').text('SWAP').removeClass('bg-label-primary').addClass('bg-label-warning');
               } else {
                  $('#sum_type_badge').text('MOVE').removeClass('bg-label-warning').addClass('bg-label-primary');
               }

               $('#rotation_summary').fadeIn();
            }
         }

         $('#rotasi_form').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            Swal.fire({
               title: 'Konfirmasi Rotasi',
               text: 'Simpan perubahan posisi ban?',
               icon: 'question',
               showCancelButton: true,
               confirmButtonText: 'Ya, Simpan',
               customClass: {
                  confirmButton: 'btn btn-primary me-3',
                  cancelButton: 'btn btn-outline-secondary'
               },
               buttonsStyling: false
            }).then((result) => {
               if (result.isConfirmed) {
                  const btn = $('#btn_submit');
                  btn.prop('disabled', true).html(
                     '<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

                  fetch(`{{ url('tyre-store') }}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                           'Accept': 'application/json',
                           'X-Requested-With': 'XMLHttpRequest'
                        }
                     })
                     .then(response => response.json())
                     .then(data => {
                        if (data.success) {
                           Swal.fire('Berhasil!', data.message, 'success').then(() => {
                              window.location.href = "{{ route('tyre-movement.index') }}";
                           });
                        } else {
                           Swal.fire('Gagal', data.message, 'error');
                           btn.prop('disabled', false).html(
                              '<i class="ri ri-arrow-left-right-line me-1"></i> Eksekusi Rotasi Ban'
                              );
                        }
                     })
                     .catch(err => {
                        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                        btn.prop('disabled', false).html(
                           '<i class="ri ri-arrow-left-right-line me-1"></i> Eksekusi Rotasi Ban');
                     });
               }
            });
         });
      });
   </script>
@endsection
