@extends('layouts.admin')

@section('title', 'Form Rotasi Ban')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('page-style')
   <style>
      .select2-container { width: 100% !important; }
      .sticky-panel { position: sticky; top: 85px; z-index: 10; transition: all 0.3s ease; }
      .form-section-header { display: flex; align-items: center; margin-bottom: 1.25rem; padding-bottom: 0.5rem; border-bottom: 1px solid #ebedef; }
      .form-section-title { font-weight: 700; color: #5d596c; margin-bottom: 0; display: flex; align-items: center; }
      .form-section-icon { width: 32px; height: 32px; background: rgba(115, 103, 240, 0.1); color: #7367f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 1.2rem; }
      .premium-card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(165, 163, 174, 0.3); border-radius: 0.75rem; transition: transform 0.2s; }
      .premium-card:hover { transform: translateY(-2px); }
      .visual-layout-card { border-radius: 1rem; overflow: hidden; background: #fff; border: 1px solid #e9e9e9; }
      .card, .card-body { overflow: visible !important; }

      /* Drag-Drop Rotation */
      .rotation-dropzone {
         border: 2px dashed #7367f0;
         background: #f8f7ff;
         border-radius: 10px;
         padding: 30px;
         text-align: center;
         transition: all 0.3s ease;
      }
      .rotation-dropzone.drag-over {
         background: #edeaff;
         transform: scale(1.02);
         border-color: #5b50d6;
      }
      .rotation-dropzone.has-source {
         border-color: #28c76f;
         background: #f0fdf4;
      }
      .rotation-dropzone.has-source.drag-over {
         background: #dcfce7;
         border-color: #16a34a;
      }

      /* Task Card */
      .task-card {
         background: #ffffff;
         border: 1px solid #eef0f2;
         border-left: 4px solid #7367f0;
         border-radius: 12px;
         padding: 18px;
         margin-bottom: 15px;
         box-shadow: 0 4px 12px rgba(0,0,0,0.03);
         transition: all 0.2s;
      }
      .task-card:hover { box-shadow: 0 6px 16px rgba(0,0,0,0.06); }
      .task-card .form-label { font-weight: 600; color: #566a7f; font-size: 0.8rem; }
      .task-card .form-control, .task-card .form-select { border-radius: 8px; border: 1px solid #d9dee3; background-color: #fcfdfd; font-size: 0.85rem; }

      .m-tyre-node.filled { cursor: grab; }
      .m-tyre-node.filled:active { cursor: grabbing; }
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
      .m-tyre-node.queued-rotation {
         opacity: 0.5;
         border: 2px dashed #7367f0 !important;
         pointer-events: none;
      }

      @media (max-width: 991.98px) {
         .sticky-panel { position: static !important; }
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
                     <div class="bg-light p-2 text-center border-top">
                        <small class="text-muted"><i class="ri-drag-drop-line me-1"></i> Drag ban dari layout: drop ke ban lain untuk SWAP, atau drop ke dropzone untuk memilih tujuan.</small>
                     </div>
                  </div>
               </div>
            </div>

            <!-- RIGHT PANEL: Scrollable Form Sections -->
            <div class="col-lg-7 col-xl-8 order-1 order-lg-2">
               <!-- SECTION 1: Identifikasi Unit -->
               <div class="card premium-card mb-4">
                  <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <div class="d-flex align-items-center">
                           <div class="form-section-icon mb-0 me-2"><i class="ri ri-steering-line"></i></div>
                           <h5 class="form-section-title mb-0">Identifikasi Unit & Waktu</h5>
                        </div>
                        <div class="btn-group" role="group">
                           <input type="radio" class="btn-check" name="ui_mode" id="mode_visual" value="visual" autocomplete="off" checked>
                           <label class="btn btn-outline-primary btn-sm" for="mode_visual"><i class="ri-drag-drop-line me-1"></i> Mode Visual</label>
                           <input type="radio" class="btn-check" name="ui_mode" id="mode_klasik" value="klasik" autocomplete="off">
                           <label class="btn btn-outline-primary btn-sm" for="mode_klasik"><i class="ri-list-check-2 me-1"></i> Mode Klasik</label>
                        </div>
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
                        <div class="col-md-4 mb-3" id="odometer_container">
                           <label class="form-label fw-bold">KM Saat Rotasi</label>
                           <input type="number" name="odometer" id="odometer" class="form-control"
                              placeholder="KM Odometer" required>
                           <small class="text-muted extra-small d-block mt-1">Last KM: <span id="last_odo_display"
                                 class="fw-bold">-</span></small>
                        </div>
                        <div class="col-md-4 mb-3" id="hour_meter_container">
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
                     </div>
                  </div>
               </div>

               <!-- SECTION 2: Dropzone Rotasi (Visual Mode) -->
               <div id="visual_mode_container">
                  <div class="card premium-card mb-4 border-start border-primary border-5">
                     <div class="card-body">
                        <div class="form-section-header">
                           <div class="form-section-icon"><i class="ri ri-arrow-left-right-line"></i></div>
                           <h5 class="form-section-title">Antrean Rotasi & Inspeksi</h5>
                        </div>

                        <div id="rotation_dropzone" class="rotation-dropzone mb-3">
                           <i class="ri-arrow-left-right-line ri-3x text-primary mb-2 d-block opacity-50"></i>
                           <h6 class="fw-bold text-primary">Area Drop Rotasi</h6>
                           <p class="mb-0 text-muted small">Klik ban pada layout untuk memilih Sumber (A), lalu klik ban lain untuk Tujuan (B). Atau drag & drop antar ban.</p>
                        </div>

                        <div id="task_queue_container">
                           <!-- Task cards will be appended here -->
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Mode Klasik Container -->
               <div id="classic_mode_container" style="display:none;" class="mb-4">
                  <div class="card premium-card">
                     <div class="card-body">
                        <div class="form-section-header">
                           <div class="form-section-icon"><i class="ri ri-list-check"></i></div>
                           <h5 class="form-section-title">Detail Rotasi (Klasik)</h5>
                        </div>
                        <div class="row g-3 bg-light p-3 rounded border border-dashed">
                           <div class="col-md-6">
                              <label class="form-label fw-bold">Pilih Posisi Sumber (Ban A)</label>
                              <select id="c_source_id" class="form-select select2">
                                 <option value="">-- Pilih Posisi Sumber --</option>
                              </select>
                           </div>
                           <div class="col-12 mt-1" id="c_source_info" style="display:none;"></div>
                           <div class="col-md-6">
                              <label class="form-label fw-bold">Pilih Posisi Tujuan (Posisi B)</label>
                              <select id="c_target_id" class="form-select select2">
                                 <option value="">-- Pilih Posisi Tujuan --</option>
                              </select>
                           </div>
                           <div class="col-12 mt-1" id="c_target_info" style="display:none;"></div>
                           <div class="col-md-4">
                              <label class="form-label fw-bold">RTD Ban A (mm)</label>
                              <input type="number" step="0.01" id="c_rtd_a" class="form-control" placeholder="Sisa RTD">
                           </div>
                           <div class="col-md-4">
                              <label class="form-label fw-bold">PSI Ban A</label>
                              <input type="number" step="0.01" id="c_psi_a" class="form-control" placeholder="Wajib">
                           </div>
                           <div class="col-md-4">
                              <label class="form-label fw-bold">Catatan</label>
                              <input type="text" id="c_notes" class="form-control" placeholder="Opsional">
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- SECTION 3: Operasional & Petugas -->
               <div class="card premium-card mb-4">
                  <div class="card-body">
                     <div class="form-section-header mt-4">
                        <div class="form-section-icon"><i class="ri ri-user-settings-line"></i></div>
                        <h5 class="form-section-title">Operasional & Petugas</h5>
                     </div>
                     <div class="row g-3 mb-4">
                        <div class="col-md-4">
                           <label class="form-label fw-bold">Waktu Pengerjaan</label>
                           <input type="time" name="start_time" id="start_time" class="form-control" value="{{ date('H:i') }}">
                        </div>
                        <div class="col-md-4">
                           <label class="form-label fw-bold">Workshop / Lokasi Kerja</label>
                           <select name="work_location_id" id="work_location_id" class="form-select select2">
                              <option value=""></option>
                              @foreach ($locations as $loc)
                                 <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-4">
                           <label class="form-label fw-bold">Operational Segment</label>
                           <select name="operational_segment_id" id="operational_segment_id" class="form-select select2">
                              <option value=""></option>
                              @foreach ($segments as $seg)
                                 <option value="{{ $seg->id }}">{{ $seg->segment_name }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label class="form-label fw-bold">Tyreman 1</label>
                           <input type="text" name="tyreman_1" class="form-control" placeholder="Nama Petugas Utama">
                        </div>
                        <div class="col-md-6">
                           <label class="form-label fw-bold">Tyreman 2 (Helper)</label>
                           <input type="text" name="tyreman_2" class="form-control" placeholder="Nama Helper">
                        </div>
                     </div>

                     <div class="d-grid gap-2 mt-4">
                        <button type="button" class="btn btn-primary btn-lg shadow" id="btn_submit">
                           <i class="ri ri-arrow-left-right-line me-1"></i> Proses Rotasi Ban
                        </button>
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
         const layoutContainer = document.getElementById('layout_container');
         const dropzone = document.getElementById('rotation_dropzone');
         const taskContainer = document.getElementById('task_queue_container');
         let taskIndex = 0;
         let tasks = [];
         let pendingSource = null; // For 2-click rotation
         let allNodeData = {}; // Store node data by positionId

         $('.select2').each(function() {
            $(this).select2({ placeholder: $(this).attr('placeholder'), allowClear: true });
         });

         // UI Mode Toggle
         $('input[name="ui_mode"]').change(function() {
            if ($(this).val() === 'visual') {
               $('#visual_mode_container').fadeIn();
               $('#classic_mode_container').hide();
            } else {
               $('#visual_mode_container').hide();
               $('#classic_mode_container').fadeIn();
            }
         });

         vehicleSelect.on('change', function() {
            const vehicleId = $(this).val();
            const text = $(this).find('option:selected').text();
            document.getElementById('unit_code_display').textContent = vehicleId ? text : '-';
            pendingSource = null;
            tasks = [];
            taskIndex = 0;
            taskContainer.innerHTML = '';
            allNodeData = {};

            if (!vehicleId) {
               layoutContainer.innerHTML = '<div class="text-center text-muted p-5 w-100"><i class="ri ri-truck-line ri-4x mb-3 d-block opacity-25"></i><p class="mb-0">Pilih Kendaraan untuk memuat layout ban.</p></div>';
               return;
            }

            fetch(`{{ url('vehicle-detail') }}/${vehicleId}`)
               .then(res => res.json())
               .then(res => {
                  const data = res.vehicle;
                  let mode = (data.company && data.company.measurement_mode) ? data.company.measurement_mode : 'BOTH';
                  if (mode === 'HM') {
                      $('#odometer_container').hide();
                      $('#odometer').removeAttr('required');
                      $('#hour_meter_container').show();
                      $('#hour_meter').attr('required', 'required');
                  } else if (mode === 'KM') {
                      $('#hour_meter_container').hide();
                      $('#hour_meter').removeAttr('required');
                      $('#odometer_container').show();
                      $('#odometer').attr('required', 'required');
                  } else {
                      $('#odometer_container').show();
                      $('#odometer').attr('required', 'required');
                      $('#hour_meter_container').show();
                      $('#hour_meter').attr('required', 'required');
                  }
                  $('#vehicle_type_display').val(data.jenis_kendaraan || '-');
                  $('#last_odo_display').text((res.last_odometer || 0).toLocaleString());
                  $('#last_hm_display').text((res.last_hour_meter || 0).toLocaleString());
                  $('#odometer').attr('placeholder', 'Previous: ' + (res.last_odometer || 0));
                  $('#hour_meter').attr('placeholder', 'Previous: ' + (res.last_hour_meter || 0));
                  $('#odometer').val(res.last_odometer || 0);
                  $('#hour_meter').val(res.last_hour_meter || 0);
                  if (data.operational_segment_id) $('#operational_segment_id').val(data.operational_segment_id).trigger('change');
                  if (data.area) {
                     const locOption = $('#work_location_id option').filter(function() {
                        return $(this).text().trim().toLowerCase() === data.area.toLowerCase();
                     });
                     if (locOption.length) $('#work_location_id').val(locOption.val()).trigger('change');
                  }
               });

            layoutContainer.innerHTML = '<div class="spinner-border text-primary"></div>';
            fetch(`{{ url('layout') }}/${vehicleId}`)
               .then(res => res.text())
               .then(html => {
                  layoutContainer.innerHTML = html;
                  cacheNodeData();
                  attachLayoutDragEvents();

                  // Populate classic dropdowns
                  let srcOpts = '<option value="">-- Pilih Posisi Sumber --</option>';
                  let tgtOpts = '<option value="">-- Pilih Posisi Tujuan --</option>';
                  document.querySelectorAll('.m-tyre-node.filled').forEach(n => {
                     srcOpts += `<option value="${n.dataset.positionId}" data-code="${n.dataset.code}" data-sn="${n.dataset.sn}" data-brand="${n.dataset.brand||'-'}" data-size="${n.dataset.size||'-'}" data-pattern="${n.dataset.pattern||'-'}" data-rtd="${n.dataset.rtd||'-'}">Posisi ${n.dataset.code} (${n.dataset.sn})</option>`;
                  });
                  document.querySelectorAll('.m-tyre-node').forEach(n => {
                     const sn = n.dataset.sn;
                     tgtOpts += `<option value="${n.dataset.positionId}" data-code="${n.dataset.code}" data-sn="${sn||''}" data-brand="${n.dataset.brand||'-'}" data-size="${n.dataset.size||'-'}" data-pattern="${n.dataset.pattern||'-'}" data-rtd="${n.dataset.rtd||'-'}">${n.dataset.code} ${sn ? '('+sn+')' : '[KOSONG]'}</option>`;
                  });
                  $('#c_source_id').html(srcOpts).select2({ placeholder: '-- Pilih Posisi Sumber --', allowClear: true });
                  $('#c_target_id').html(tgtOpts).select2({ placeholder: '-- Pilih Posisi Tujuan --', allowClear: true });
               });
         });

         function cacheNodeData() {
            document.querySelectorAll('.m-tyre-node').forEach(n => {
               allNodeData[n.dataset.positionId] = {
                  posId: n.dataset.positionId,
                  code: n.dataset.code,
                  sn: n.dataset.sn || '',
                  brand: n.dataset.brand || '-',
                  size: n.dataset.size || '-',
                  pattern: n.dataset.pattern || '-',
                  rtd: n.dataset.rtd || '-',
                  filled: n.classList.contains('filled')
               };
            });
         }

         function attachLayoutDragEvents() {
            const nodes = document.querySelectorAll('.m-tyre-node');
            nodes.forEach(node => {
               // Drag events for filled nodes
               if (node.classList.contains('filled')) {
                  node.addEventListener('dragstart', function(e) {
                     e.dataTransfer.setData('src_pos_id', this.dataset.positionId);
                  });
               }

               // Drop target - allow dropping on any node
               node.addEventListener('dragover', function(e) { e.preventDefault(); this.style.outline = '3px solid #28c76f'; });
               node.addEventListener('dragleave', function() { this.style.outline = ''; });
               node.addEventListener('drop', function(e) {
                  e.preventDefault();
                  this.style.outline = '';
                  const srcPosId = e.dataTransfer.getData('src_pos_id');
                  const tgtPosId = this.dataset.positionId;
                  if (!srcPosId || srcPosId === tgtPosId) return;
                  const srcData = allNodeData[srcPosId];
                  if (!srcData || !srcData.filled) return;
                  tryAddRotation(srcPosId, tgtPosId);
               });

               // Click handler for 2-step selection
               node.addEventListener('click', function() {
                  if (this.classList.contains('queued-rotation')) return;
                  const isClassic = $('#mode_klasik').is(':checked');
                  if (isClassic) {
                     if (this.classList.contains('filled')) {
                        $('#c_source_id').val(this.dataset.positionId).trigger('change');
                     }
                     return;
                  }

                  const posId = this.dataset.positionId;
                  if (!pendingSource) {
                     if (!this.classList.contains('filled')) {
                        Swal.fire({toast:true, position:'top-end', icon:'info', title:'Pilih posisi berisi ban sebagai sumber.', showConfirmButton:false, timer:2000});
                        return;
                     }
                     pendingSource = posId;
                     document.querySelectorAll('.m-tyre-node').forEach(n => n.classList.remove('selected-source','selected-target'));
                     this.classList.add('selected-source');
                     dropzone.innerHTML = '<i class="ri-arrow-right-s-line ri-3x text-success mb-2 d-block opacity-50"></i><h6 class="fw-bold text-success">Ban A Terpilih: ' + (allNodeData[posId]?.sn || posId) + '</h6><p class="mb-0 text-muted small">Klik posisi tujuan pada layout, atau drag ban lain ke sini.</p>';
                     dropzone.classList.add('has-source');
                  } else {
                     if (posId === pendingSource) {
                        // Deselect
                        pendingSource = null;
                        document.querySelectorAll('.m-tyre-node').forEach(n => n.classList.remove('selected-source'));
                        resetDropzone();
                        return;
                     }
                     this.classList.add('selected-target');
                     tryAddRotation(pendingSource, posId);
                     pendingSource = null;
                     document.querySelectorAll('.m-tyre-node').forEach(n => n.classList.remove('selected-source','selected-target'));
                     resetDropzone();
                  }
               });
            });
         }

         function resetDropzone() {
            dropzone.classList.remove('has-source');
            dropzone.innerHTML = '<i class="ri-arrow-left-right-line ri-3x text-primary mb-2 d-block opacity-50"></i><h6 class="fw-bold text-primary">Area Drop Rotasi</h6><p class="mb-0 text-muted small">Klik ban pada layout untuk memilih Sumber (A), lalu klik ban lain untuk Tujuan (B).</p>';
         }

         // Dropzone also accepts drops (source already dragged)
         dropzone.addEventListener('dragover', function(e) { e.preventDefault(); this.classList.add('drag-over'); });
         dropzone.addEventListener('dragleave', function() { this.classList.remove('drag-over'); });
         dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            const srcPosId = e.dataTransfer.getData('src_pos_id');
            if (!srcPosId) return;
            // Set as pending source and wait for target click
            pendingSource = srcPosId;
            document.querySelectorAll('.m-tyre-node').forEach(n => n.classList.remove('selected-source'));
            const srcNode = document.querySelector(`.m-tyre-node[data-position-id="${srcPosId}"]`);
            if (srcNode) srcNode.classList.add('selected-source');
            dropzone.innerHTML = '<i class="ri-arrow-right-s-line ri-3x text-success mb-2 d-block opacity-50"></i><h6 class="fw-bold text-success">Ban A: ' + (allNodeData[srcPosId]?.sn || srcPosId) + '</h6><p class="mb-0 text-muted small">Sekarang klik posisi tujuan pada layout.</p>';
            dropzone.classList.add('has-source');
         });

         function tryAddRotation(srcPosId, tgtPosId) {
            const src = allNodeData[srcPosId];
            const tgt = allNodeData[tgtPosId];
            if (!src || !src.filled) {
               Swal.fire('Error', 'Posisi sumber tidak memiliki ban.', 'error');
               return;
            }
            // Check not already queued
            if (tasks.find(t => t.src === srcPosId || t.tgt === srcPosId || t.src === tgtPosId || t.tgt === tgtPosId)) {
               Swal.fire('Info', 'Salah satu posisi ini sudah ada di antrean rotasi.', 'info');
               return;
            }
            addTaskCard(srcPosId, tgtPosId, src, tgt);
         }

         function addTaskCard(srcPosId, tgtPosId, src, tgt) {
            const isSwap = tgt && tgt.filled;
            tasks.push({ src: srcPosId, tgt: tgtPosId, index: taskIndex });

            const html = `
               <div class="task-card" id="task_${taskIndex}">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                     <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm bg-primary me-2 d-flex align-items-center justify-content-center text-white rounded">
                           <i class="ri-arrow-left-right-line"></i>
                        </div>
                        <h6 class="mb-0 fw-bold">${isSwap ? 'SWAP' : 'MOVE'}: <span class="text-primary">${src.code}</span> <i class="ri-arrow-right-line mx-1 text-muted"></i> <span class="text-primary">${tgt?.code || '?'}</span></h6>
                     </div>
                     <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRotationTask(${taskIndex}, '${srcPosId}', '${tgtPosId}')"><i class="ri-close-line"></i> Batal</button>
                  </div>

                  <div class="bg-light p-2 rounded mb-3 border border-dashed text-muted small ms-4 ps-2 border-start border-2 border-primary">
                     <div class="d-flex gap-3 mb-1">
                        <div><b>Ban A:</b> <span class="text-dark fw-bold">${src.sn}</span></div>
                        <div><b class="text-dark">${src.brand}</b> | ${src.size} | ${src.pattern}</div>
                        <div><i class="ri-ruler-line ms-2 text-primary"></i> RTD: <span class="text-dark fw-bold">${src.rtd} mm</span></div>
                     </div>
                     ${isSwap ? `
                     <div class="d-flex gap-3">
                        <div><b>Ban B:</b> <span class="text-dark fw-bold">${tgt.sn}</span></div>
                        <div><b class="text-dark">${tgt.brand}</b> | ${tgt.size} | ${tgt.pattern}</div>
                        <div><i class="ri-ruler-line ms-2 text-warning"></i> RTD: <span class="text-dark fw-bold">${tgt.rtd} mm</span></div>
                     </div>
                     ` : '<div class="fst-italic">Tujuan: Posisi Kosong (Move)</div>'}
                  </div>

                  <div class="row g-2">
                     <div class="col-md-4">
                        <label class="form-label small fw-bold">RTD Ban A (mm)</label>
                        <input type="number" step="0.01" class="form-control form-control-sm t-rtd" placeholder="Sisa RTD" value="${src.rtd !== '-' ? src.rtd : ''}">
                     </div>
                     <div class="col-md-4">
                        <label class="form-label small fw-bold">PSI Ban A</label>
                        <input type="number" step="0.01" class="form-control form-control-sm t-psi" placeholder="Wajib">
                     </div>
                     <div class="col-md-4">
                        <label class="form-label small fw-bold">Catatan</label>
                        <input type="text" class="form-control form-control-sm t-notes">
                     </div>
                     ${isSwap ? `
                     <div class="col-md-4">
                        <label class="form-label small fw-bold text-warning">RTD Ban B (mm)</label>
                        <input type="number" step="0.01" class="form-control form-control-sm t-target-rtd" placeholder="Sisa RTD" value="${tgt.rtd !== '-' ? tgt.rtd : ''}">
                     </div>
                     <div class="col-md-4">
                        <label class="form-label small fw-bold text-warning">PSI Ban B</label>
                        <input type="number" step="0.01" class="form-control form-control-sm t-target-psi" placeholder="Wajib">
                     </div>
                     ` : ''}
                     <div class="col-md-4">
                        <label class="form-label small fw-bold">Foto</label>
                        <input type="file" class="form-control form-control-sm t-photo" accept="image/*">
                     </div>
                  </div>
                  <input type="hidden" class="t-src-pos" value="${srcPosId}">
                  <input type="hidden" class="t-tgt-pos" value="${tgtPosId}">
                  <input type="hidden" class="t-is-swap" value="${isSwap ? 1 : 0}">
               </div>
            `;
            taskContainer.insertAdjacentHTML('beforeend', html);

            // Mark nodes as queued
            const srcNode = document.querySelector(`.m-tyre-node[data-position-id="${srcPosId}"]`);
            const tgtNode = document.querySelector(`.m-tyre-node[data-position-id="${tgtPosId}"]`);
            if (srcNode) srcNode.classList.add('queued-rotation');
            if (tgtNode) tgtNode.classList.add('queued-rotation');

            taskIndex++;
         }

         window.removeRotationTask = function(index, srcPosId, tgtPosId) {
            document.getElementById(`task_${index}`).remove();
            tasks = tasks.filter(t => t.index !== index);
            const srcNode = document.querySelector(`.m-tyre-node[data-position-id="${srcPosId}"]`);
            const tgtNode = document.querySelector(`.m-tyre-node[data-position-id="${tgtPosId}"]`);
            if (srcNode) srcNode.classList.remove('queued-rotation');
            if (tgtNode) tgtNode.classList.remove('queued-rotation');
         };

         // Classic mode info
         $('#c_source_id').on('change', function() {
            const opt = $(this).find('option:selected');
            if (opt.val()) {
               $('#c_source_info').html(`<div class="bg-white p-2 rounded border border-start border-4 border-primary shadow-sm"><b>Ban A:</b> ${opt.data('sn')} | ${opt.data('brand')} | ${opt.data('size')} | ${opt.data('pattern')} | RTD: ${opt.data('rtd')} mm</div>`).slideDown();
            } else { $('#c_source_info').slideUp(); }
         });
         $('#c_target_id').on('change', function() {
            const opt = $(this).find('option:selected');
            if (opt.val()) {
               const sn = opt.data('sn');
               if (sn) {
                  $('#c_target_info').html(`<div class="bg-white p-2 rounded border border-start border-4 border-success shadow-sm"><b>Ban B (SWAP):</b> ${sn} | ${opt.data('brand')} | ${opt.data('size')} | ${opt.data('pattern')} | RTD: ${opt.data('rtd')} mm</div>`).slideDown();
               } else {
                  $('#c_target_info').html(`<div class="bg-white p-2 rounded border border-start border-4 border-secondary shadow-sm"><b>Tujuan:</b> Posisi Kosong (MOVE)</div>`).slideDown();
               }
            } else { $('#c_target_info').slideUp(); }
         });

         // Submit
         $('#btn_submit').click(function() {
            const isClassic = $('#mode_klasik').is(':checked');
            let movements = [];
            let formData = new FormData(document.getElementById('rotasi_form'));

            if (!vehicleSelect.val()) {
               Swal.fire('Peringatan', 'Unit kendaraan wajib dipilih.', 'warning');
               return;
            }

            if (isClassic) {
               if (!$('#c_source_id').val() || !$('#c_target_id').val()) {
                  Swal.fire('Peringatan', 'Posisi Sumber dan Tujuan wajib dipilih.', 'warning');
                  return;
               }
               if ($('#c_source_id').val() === $('#c_target_id').val()) {
                  Swal.fire('Peringatan', 'Posisi Sumber dan Tujuan tidak boleh sama.', 'warning');
                  return;
               }
               if (!$('#c_psi_a').val()) {
                  Swal.fire('Peringatan', 'PSI Ban A wajib diisi.', 'warning');
                  return;
               }
               movements.push({
                  type: 'Rotation',
                  position_id: $('#c_source_id').val(),
                  target_position_id: $('#c_target_id').val(),
                  rtd: $('#c_rtd_a').val() || '',
                  psi: $('#c_psi_a').val(),
                  notes: $('#c_notes').val() || ''
               });
            } else {
               if (tasks.length === 0) {
                  Swal.fire('Peringatan', 'Antrean rotasi masih kosong. Klik ban pada layout untuk menambahkan rotasi.', 'warning');
                  return;
               }

               let isValid = true;
               tasks.forEach((t, idx) => {
                  const card = document.getElementById(`task_${t.index}`);
                  if (!card) return;
                  const psi = card.querySelector('.t-psi').value;
                  if (!psi) { isValid = false; card.style.border = '2px solid red'; }
                  else { card.style.border = ''; }

                  const mov = {
                     type: 'Rotation',
                     position_id: card.querySelector('.t-src-pos').value,
                     target_position_id: card.querySelector('.t-tgt-pos').value,
                     rtd: card.querySelector('.t-rtd').value || '',
                     psi: psi,
                     notes: card.querySelector('.t-notes').value || ''
                  };

                  if (card.querySelector('.t-is-swap').value === '1') {
                     mov.target_rtd = card.querySelector('.t-target-rtd')?.value || '';
                     mov.target_psi = card.querySelector('.t-target-psi')?.value || '';
                  }

                  movements.push(mov);

                  const photoInput = card.querySelector('.t-photo');
                  if (photoInput && photoInput.files.length > 0) {
                     formData.append(`move_photo_${idx}`, photoInput.files[0]);
                  }
               });

               if (!isValid) {
                  Swal.fire('Peringatan', 'Lengkapi PSI wajib pada semua antrean.', 'warning');
                  return;
               }
            }

            formData.append('movements', JSON.stringify(movements));

            Swal.fire({
               title: 'Proses Rotasi?',
               text: `Memproses ${movements.length} rotasi ban. Lanjutkan?`,
               icon: 'question',
               showCancelButton: true,
               confirmButtonText: 'Ya, Proses',
               customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-outline-secondary' },
               buttonsStyling: false
            }).then((result) => {
               if (result.isConfirmed) {
                  const btn = document.getElementById('btn_submit');
                  btn.disabled = true;
                  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

                  fetch(`{{ route('tyre-movement.bulk-store') }}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                           'Accept': 'application/json',
                           'X-Requested-With': 'XMLHttpRequest'
                        }
                     })
                     .then(res => res.json())
                     .then(data => {
                        if (data.success) {
                           Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 2000 })
                           .then(() => window.location.href = "{{ route('tyre-movement.index') }}");
                        } else {
                           Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                           btn.disabled = false;
                           btn.innerHTML = '<i class="ri ri-arrow-left-right-line me-1"></i> Proses Rotasi Ban';
                        }
                     })
                     .catch(err => {
                        Swal.fire('Error', 'Kesalahan jaringan/sistem.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ri ri-arrow-left-right-line me-1"></i> Proses Rotasi Ban';
                     });
               }
            });
         });
      });
   </script>
@endsection
