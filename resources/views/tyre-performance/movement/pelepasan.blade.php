@extends('layouts.admin')

@section('title', 'Pelepasan Ban Massal (Bulk)')

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
      .form-section-icon { width: 32px; height: 32px; background: rgba(234, 84, 85, 0.1); color: #ea5455; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 1.2rem; }
      .premium-card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(165, 163, 174, 0.3); border-radius: 0.75rem; transition: transform 0.2s; }
      .premium-card:hover { transform: translateY(-2px); }
      .visual-layout-card { border-radius: 1rem; overflow: hidden; background: #fff; border: 1px solid #e9e9e9; }
      .card, .card-body { overflow: visible !important; }

      /* Removal Cart / Dropzone */
      .removal-dropzone {
         border: 2px dashed #ea5455;
         background: #fffcf0;
         border-radius: 10px;
         padding: 30px;
         text-align: center;
         transition: all 0.3s ease;
      }
      .removal-dropzone.drag-over {
         background: #ffebee;
         transform: scale(1.02);
         border-color: #d33;
      }

      /* Task Card Styles */
      .task-card {
         background: #ffffff;
         border: 1px solid #eef0f2;
         border-left: 4px solid #ea5455;
         border-radius: 12px;
         padding: 18px;
         margin-bottom: 15px;
         box-shadow: 0 4px 12px rgba(0,0,0,0.03);
         transition: all 0.2s;
         position: relative;
      }
      .task-card:hover {
         box-shadow: 0 6px 16px rgba(0,0,0,0.06);
      }
      .task-card .form-label {
         font-weight: 600;
         color: #566a7f;
         font-size: 0.8rem;
      }
      .task-card .form-control, .task-card .form-select {
         border-radius: 8px;
         border: 1px solid #d9dee3;
         background-color: #fcfdfd;
         font-size: 0.85rem;
      }
      .task-card .form-control:focus, .task-card .form-select:focus {
         border-color: #ea5455;
         box-shadow: 0 0 0 0.2rem rgba(234, 84, 85, 0.1);
      }
      .task-card .remove-task {
         position: absolute;
         top: 10px;
         right: 10px;
         color: #6c757d;
         cursor: pointer;
         font-size: 1.2rem;
         transition: color 0.2s;
      }
      .task-card .remove-task:hover { color: #d33; }
      
      .m-tyre-node.filled { cursor: grab; }
      .m-tyre-node.filled:active { cursor: grabbing; }
      .m-tyre-node.queued-removal {
         opacity: 0.5;
         border: 2px dashed #ea5455 !important;
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
         <h4 class="fw-bold mb-0 text-danger"><span class="text-muted fw-light">Transaksi /</span> Pelepasan Ban (Bulk)</h4>
         <a href="{{ route('tyre-movement.index') }}" class="btn btn-outline-secondary">
            <i class="ri ri-arrow-left-line me-1"></i> Kembali
         </a>
      </div>

      <form id="pelepasan_form" enctype="multipart/form-data">
         @csrf
         <input type="hidden" name="movement_type" value="Removal">

         <div class="row g-4">
            <!-- LEFT PANEL: Sticky Visual Layout -->
            <div class="col-lg-5 col-xl-4 order-2 order-lg-1">
               <div class="sticky-panel">
                  <div class="visual-layout-card shadow-sm mb-4">
                     <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                        <h6 class="mb-0 fw-bold"><i class="ri ri-mouse-line me-2 text-danger"></i>Visual Axle Layout</h6>
                        <span class="badge bg-label-secondary" id="unit_code_display">-</span>
                     </div>
                     <div class="card-body p-0 d-flex flex-column align-items-center justify-content-center"
                        style="min-height: 480px; background: #fafafa;">
                        <div id="layout_container" class="w-100 h-100 d-flex align-items-center justify-content-center p-4">
                           <div class="text-center text-muted p-5 w-100">
                              <i class="ri ri-truck-line ri-4x mb-3 d-block opacity-25"></i>
                              <p class="mb-0">Pilih Unit Kendaraan untuk memuat posisi ban.</p>
                           </div>
                        </div>
                     </div>
                     <div class="bg-light p-2 text-center border-top">
                        <small class="text-muted"><i class="ri-drag-drop-line me-1"></i> Drag ban (hitam) dari layout ke Keranjang Pelepasan.</small>
                     </div>
                  </div>
               </div>
            </div>

            <!-- RIGHT PANEL: Form Sections -->
            <div class="col-lg-7 col-xl-8 order-1 order-lg-2">
               <!-- SECTION 1: Identifikasi Unit -->
               <div class="card premium-card mb-4">
                  <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <div class="d-flex align-items-center">
                           <div class="form-section-icon mb-0 me-2"><i class="ri ri-truck-line"></i></div>
                           <h5 class="form-section-title mb-0">Identifikasi Unit & Waktu</h5>
                        </div>
                        <div class="btn-group" role="group" aria-label="Mode Toggle">
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
                                 <option value="{{ $v->id }}">{{ $v->kode_kendaraan }} {{ $v->no_polisi ? '[' . $v->no_polisi . ']' : '' }} - [{{ $v->tyres_count }}/{{ $v->total_tyre_position }}]</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-6 mb-3">
                           <label class="form-label fw-bold">Tanggal Pelepasan</label>
                           <input type="date" name="movement_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3 mb-3" id="odometer_container">
                           <label class="form-label fw-bold">KM Saat Lepas</label>
                           <input type="number" name="odometer" id="odometer" class="form-control" placeholder="KM Odometer" required>
                           <small class="text-muted extra-small d-block mt-1">Last KM: <span id="last_odo_display" class="fw-bold">-</span></small>
                        </div>
                        <div class="col-md-3 mb-3" id="hour_meter_container">
                           <label class="form-label fw-bold">HM Saat Lepas</label>
                           <input type="number" name="hour_meter" id="hour_meter" class="form-control" placeholder="Hour Meter" required>
                           <small class="text-muted extra-small d-block mt-1">Last HM: <span id="last_hm_display" class="fw-bold">-</span></small>
                        </div>
                        <div class="col-md-3 mb-3">
                           <label class="form-label fw-bold">Tipe Unit</label>
                           <input type="text" id="vehicle_type_display" class="form-control bg-light" readonly placeholder="Auto-filled">
                        </div>
                        <div class="col-md-3 mb-3">
                           <div class="bg-light p-2 rounded mt-4 border border-dashed d-flex align-items-center justify-content-between">
                              <div>
                                 <h6 class="mb-0 small fw-bold text-dark"><i class="ri ri-refresh-line me-1 text-warning"></i> Reset Meteran?</h6>
                              </div>
                              <div class="form-check form-switch mb-0">
                                 <input class="form-check-input ms-0" type="checkbox" name="is_meter_reset" id="is_meter_reset" value="1" style="width: 2.5em; height: 1.25em;">
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- SECTION 2: Keranjang Pelepasan -->
               <div id="visual_mode_container">
                  <div class="card premium-card mb-4 border-start border-danger border-5">
                     <div class="card-body">
                        <div class="form-section-header">
                           <div class="form-section-icon"><i class="ri ri-delete-bin-line"></i></div>
                           <h5 class="form-section-title">Keranjang Pelepasan & Inspeksi</h5>
                        </div>
                        
                        <div id="removal_dropzone" class="removal-dropzone mb-3">
                           <i class="ri-download-2-line ri-3x text-danger mb-2 d-block opacity-50"></i>
                           <h6 class="fw-bold text-danger">Area Drop Pelepasan</h6>
                           <p class="mb-0 text-muted small">Tarik ban dari Visual Layout dan lepas (drop) di area ini untuk menambahkan ke daftar pelepasan.</p>
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
                           <h5 class="form-section-title">Detail Pelepasan (Satu per Satu)</h5>
                        </div>
                        <div class="row g-3 bg-light p-3 rounded border border-dashed">
                           <div class="col-md-6">
                              <label class="form-label fw-bold">Pilih Posisi (Ban yang Dilepas)</label>
                              <select id="c_position_id" class="form-select select2">
                                 <option value="">-- Pilih Posisi (Pilih Unit Dulu) --</option>
                              </select>
                           </div>
                           <div class="col-12 mt-1" id="c_position_info" style="display:none;"></div>
                           <div class="col-md-6">
                              <label class="form-label fw-bold">Status Akhir Ban</label>
                              <select id="c_target_status" class="form-select select2">
                                 <option value="Repaired">Masuk Gudang (Bisa Dipakai Lagi/Repaired)</option>
                                 <option value="Scrap">Scrap (Afkir / Rusak)</option>
                              </select>
                           </div>
                           <div class="col-md-6">
                              <label class="form-label fw-bold">Alasan Pelepasan (Failure Code)</label>
                              <select id="c_failure_code_id" class="form-select select2">
                                 <option value="">-- Opsional --</option>
                                 @foreach($failureCodes as $fc)
                                 <option value="{{ $fc->id }}">{{ $fc->failure_code }} - {{ $fc->getDisplayNameByCompanyId(auth()->user()->tyre_company_id) }}</option>
                                 @endforeach
                              </select>
                           </div>
                           <div class="col-md-3">
                              <label class="form-label fw-bold">Sisa RTD (mm)</label>
                              <input type="number" step="0.01" id="c_rtd" class="form-control" placeholder="Isi RTD">
                           </div>
                           <div class="col-md-3">
                              <label class="form-label fw-bold">PSI</label>
                              <input type="number" step="0.01" id="c_psi" class="form-control" placeholder="Wajib Diisi">
                           </div>
                           <div class="col-md-12">
                              <label class="form-label fw-bold">Catatan / Remarks</label>
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
                           <label class="form-label fw-bold">Gudang / Lokasi Tujuan</label>
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
                           <input type="text" name="tyreman_1" class="form-control" placeholder="Nama Petugas">
                        </div>
                        <div class="col-md-6">
                           <label class="form-label fw-bold">Tyreman 2 (Helper)</label>
                           <input type="text" name="tyreman_2" class="form-control" placeholder="Helper">
                        </div>
                     </div>

                     <div class="d-grid gap-2 mt-4">
                        <button type="button" class="btn btn-danger btn-lg shadow" id="btn_submit">
                           <i class="ri ri-delete-bin-line me-1"></i> Proses Pelepasan Massal
                        </button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </form>
   </div>

   <!-- Hidden Data for Selects -->
   <div id="failure_codes_data" style="display: none;">
      <option value="">-- Pilih Alasan --</option>
      @foreach ($failureCodes as $fc)
         <option value="{{ $fc->id }}">{{ $fc->failure_code }} - {{ $fc->getDisplayNameByCompanyId(auth()->user()->tyre_company_id) }}</option>
      @endforeach
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
         const dropzone = document.getElementById('removal_dropzone');
         const taskContainer = document.getElementById('task_queue_container');
         let taskIndex = 0;
         let tasks = []; 

         $('.select2').each(function() {
            var $this = $(this);
            $this.select2({ placeholder: $this.attr('placeholder'), allowClear: true });
         });

         // UI Mode Toggle
         $('input[name="ui_mode"]').change(function() {
            if ($(this).val() === 'visual') {
               $('#visual_mode_container').fadeIn();
               $('#task_queue_container').fadeIn();
               $('#classic_mode_container').hide();
            } else {
               $('#visual_mode_container').hide();
               $('#task_queue_container').hide();
               $('#classic_mode_container').fadeIn();
            }
         });

         vehicleSelect.on('change', function() {
            const vehicleId = $(this).val();
            const text = $(this).find('option:selected').text();
            document.getElementById('unit_code_display').textContent = vehicleId ? text : '-';

            if (!vehicleId) {
               layoutContainer.innerHTML = '<div class="text-center text-muted p-5 bg-white rounded-4 shadow-sm border w-100"><i class="ri ri-truck-line ri-4x mb-3 d-block opacity-25"></i><p class="mb-0">Pilih Kendaraan untuk memuat layout ban.</p></div>';
               return;
            }

            fetch(`{{ url('vehicle-detail') }}/${vehicleId}`)
               .then(res => res.json())
               .then(res => {
                  let mode = (res.vehicle.company && res.vehicle.company.measurement_mode) ? res.vehicle.company.measurement_mode : 'BOTH';
                  
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

                  $('#vehicle_type_display').val(res.vehicle.jenis_kendaraan || '-');
                  $('#last_odo_display').text(res.last_odometer.toLocaleString());
                  $('#last_hm_display').text(res.last_hour_meter.toLocaleString());
                  $('#odometer').attr('placeholder', 'Previous: ' + res.last_odometer);
                  $('#hour_meter').attr('placeholder', 'Previous: ' + res.last_hour_meter);
                  if (res.vehicle.operational_segment_id) $('#operational_segment_id').val(res.vehicle.operational_segment_id).trigger('change');
               });

            layoutContainer.innerHTML = '<div class="spinner-border text-primary"></div>';
            fetch(`{{ url('layout') }}/${vehicleId}`)
               .then(res => res.text())
               .then(html => {
                  layoutContainer.innerHTML = html;
                  attachLayoutDragEvents();

                  // Populate classic position dropdown
                  let posOptions = '<option value="">-- Pilih Posisi --</option>';
                  document.querySelectorAll('.m-tyre-node.filled').forEach(node => {
                     posOptions += `<option value="${node.dataset.positionId}" data-code="${node.dataset.code}" data-sn="${node.dataset.sn}" data-brand="${node.dataset.brand || '-'}" data-size="${node.dataset.size || '-'}" data-pattern="${node.dataset.pattern || '-'}" data-rtd="${node.dataset.rtd || '-'}">Posisi ${node.dataset.code} (${node.dataset.sn})</option>`;
                  });
                  $('#c_position_id').html(posOptions).select2({
                     placeholder: '-- Pilih Posisi --',
                     allowClear: true,
                     templateResult: function(pos) {
                        if (!pos.id) { return pos.text; }
                        var ds = pos.element.dataset;
                        return $(
                           '<div class="d-flex flex-column">' +
                             '<div class="fw-bold text-danger mb-1">Posisi ' + ds.code + ' - ' + ds.sn + '</div>' +
                             '<div class="small text-muted"><b class="text-dark">' + ds.brand + '</b> | ' + ds.size + ' | ' + ds.pattern + ' | Sisa RTD: <b class="text-dark">' + ds.rtd + ' mm</b></div>' +
                           '</div>'
                        );
                     },
                     templateSelection: function(pos) {
                        if (!pos.id) { return pos.text; }
                        return 'Posisi ' + pos.element.dataset.code + ' - ' + pos.element.dataset.sn;
                     }
                  });
                  $('#c_position_id').trigger('change');
               });
         });

         function attachLayoutDragEvents() {
            const nodes = document.querySelectorAll('.m-tyre-node.filled');
            nodes.forEach(node => {
               node.addEventListener('dragstart', function(e) {
                  e.dataTransfer.setData('pos_id', this.dataset.positionId);
                  e.dataTransfer.setData('pos_code', this.dataset.code);
                  e.dataTransfer.setData('sn', this.dataset.sn);
                  e.dataTransfer.setData('rtd', this.dataset.rtd || '-');
                  e.dataTransfer.setData('km', this.dataset.km || '0');
                  e.dataTransfer.setData('hm', this.dataset.hm || '0');
                  e.dataTransfer.setData('brand', this.dataset.brand || '-');
                  e.dataTransfer.setData('size', this.dataset.size || '-');
                  e.dataTransfer.setData('pattern', this.dataset.pattern || '-');
               });
               node.addEventListener('dragend', function() {
                  dropzone.classList.remove('drag-over');
               });
               
               node.addEventListener('click', function() {
                  const isClassic = $('#mode_klasik').is(':checked');
                  if (isClassic) {
                     $('#c_position_id').val(this.dataset.positionId).trigger('change');
                     const classicModeContainer = document.getElementById('classic_mode_container');
                     if(classicModeContainer) classicModeContainer.scrollIntoView({behavior: 'smooth', block: 'center'});
                  } else {
                     if(this.classList.contains('queued-removal')) return;
                     
                     const posId = this.dataset.positionId;
                     const posCode = this.dataset.code;
                     const sn = this.dataset.sn;
                     const rtd = this.dataset.rtd || '-';
                     const km = this.dataset.km || '0';
                     const hm = this.dataset.hm || '0';
                     const brand = this.dataset.brand || '-';
                     const size = this.dataset.size || '-';
                     const pattern = this.dataset.pattern || '-';
                     
                     if(tasks.find(t => t.position_id == posId)) {
                        Swal.fire('Info', 'Posisi ini sudah berada di antrean pelepasan.', 'info');
                        return;
                     }
                     
                     Swal.fire({
                        title: 'Lepaskan Ban?',
                        text: `Tambahkan Posisi ${posCode} (SN: ${sn}) ke antrean pelepasan?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lepas',
                        cancelButtonText: 'Batal'
                     }).then((result) => {
                        if (result.isConfirmed) {
                           addTaskCard(posId, posCode, sn, rtd, km, hm, brand, size, pattern);
                           this.classList.add('queued-removal');
                        }
                     });
                  }
               });
            });
         }

         dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
         });
         
         dropzone.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
         });

         dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            const posId = e.dataTransfer.getData('pos_id');
            const posCode = e.dataTransfer.getData('pos_code');
            const sn = e.dataTransfer.getData('sn');
            const rtd = e.dataTransfer.getData('rtd');
            const km = e.dataTransfer.getData('km');
            const hm = e.dataTransfer.getData('hm');
            const brand = e.dataTransfer.getData('brand');
            const size = e.dataTransfer.getData('size');
            const pattern = e.dataTransfer.getData('pattern');
            
            if(!posId) return;
            
            if(tasks.find(t => t.position_id == posId)) {
               Swal.fire('Sudah Ditambahkan', 'Ban dari posisi ini sudah ada di keranjang pelepasan.', 'info');
               return;
            }
            
            addTaskCard(posId, posCode, sn, rtd, km, hm, brand, size, pattern);
            
            const node = document.querySelector(`.m-tyre-node[data-position-id="${posId}"]`);
            if(node) node.classList.add('queued-removal');
         });

         function addTaskCard(posId, posCode, sn, rtd, km, hm, brand, size, pattern) {
            tasks.push({ position_id: posId });
            const fcOptions = document.getElementById('failure_codes_data').innerHTML;
            
            const html = `
               <div class="task-card" id="task_${taskIndex}">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                     <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm bg-danger me-2 d-flex align-items-center justify-content-center text-white rounded">
                           <i class="ri-download-2-line"></i>
                        </div>
                        <h6 class="mb-0 fw-bold">Lepas Posisi <span class="text-danger">${posCode}</span> <i class="ri-arrow-right-line mx-1 text-muted"></i> SN Ban: <span class="text-danger">${sn}</span></h6>
                     </div>
                     <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTask(${taskIndex}, '${posId}')"><i class="ri-close-line"></i> Batal</button>
                  </div>
                  
                  <div class="bg-light p-2 rounded mb-3 border border-dashed d-flex gap-3 text-muted small ms-4 ps-2 border-start border-2 border-danger">
                     <div><b class="text-dark">${brand}</b> | ${size} | ${pattern}</div>
                     <div><i class="ri-ruler-line ms-2 text-primary"></i> Sisa RTD: <span class="text-dark fw-bold">${rtd} mm</span></div>
                     <div><i class="ri-dashboard-3-line ms-2 text-warning"></i> Lifetime: <span class="text-dark fw-bold">${km} KM / ${hm} HM</span></div>
                  </div>

                  <div class="row g-2 mb-2">
                     <div class="col-md-6">
                        <label class="form-label small fw-bold">Alasan Pelepasan (Failure Code)</label>
                        <select class="form-select form-select-sm t-failure-code select2-init">${fcOptions}</select>
                     </div>
                     <div class="col-md-6">
                        <label class="form-label small fw-bold">Status Akhir Ban</label>
                        <select class="form-select form-select-sm t-target-status">
                           <option value="Repaired">REPAIR (Butuh Perbaikan)</option>
                           <option value="Scrap">SCRAP (Rusak Total / Afkir)</option>
                           <option value="New">STOCK (Bagus / Pindah Unit)</option>
                        </select>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col-md-3">
                        <label class="form-label small fw-bold text-danger">Sisa RTD Baru (mm)</label>
                        <input type="number" step="0.01" class="form-control form-control-sm t-rtd" placeholder="Sisa saat ini">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label small fw-bold">PSI Akhir</label>
                        <input type="number" step="0.01" class="form-control form-control-sm t-psi" placeholder="Wajib">
                     </div>
                     <div class="col-md-4">
                        <label class="form-label small fw-bold">Foto Bukti</label>
                        <input type="file" class="form-control form-control-sm t-photo" id="photo_${taskIndex}" accept="image/*">
                     </div>
                     <div class="col-md-2">
                        <label class="form-label small fw-bold">Remarks</label>
                        <input type="text" class="form-control form-control-sm t-notes">
                     </div>
                  </div>
                  <input type="hidden" class="t-position-id" value="${posId}">
               </div>
            `;
            taskContainer.insertAdjacentHTML('beforeend', html);
            
            // init select2 dynamically
            $(`#task_${taskIndex} .select2-init`).select2();
            
            taskIndex++;
         }

         window.removeTask = function(index, posId) {
            document.getElementById(`task_${index}`).remove();
            tasks = tasks.filter(t => t.position_id != posId);
            const node = document.querySelector(`.m-tyre-node[data-position-id="${posId}"]`);
            if(node) node.classList.remove('queued-removal');
         };

         $('#c_position_id').on('change', function() {
            const opt = $(this).find('option:selected');
            const infoBox = $('#c_position_info');
            if(opt.val()) {
               infoBox.html(`
                  <div class="bg-white p-3 rounded border mb-2 border-start border-4 border-primary shadow-sm mt-2">
                     <div class="d-flex align-items-center mb-2">
                         <i class="ri-information-line ri-xl me-2 text-primary"></i>
                         <h6 class="mb-0 fw-bold">Ban yang akan Dilepas (Posisi ${opt.data('code')})</h6>
                     </div>
                     <div class="bg-light p-2 rounded border border-dashed d-flex justify-content-between align-items-center flex-wrap gap-1">
                         <div>
                             <span class="fw-bold text-dark me-1">${opt.data('sn')}</span>
                             <span class="small text-muted">&bull; ${opt.data('brand')} | ${opt.data('size')} | ${opt.data('pattern')} | HM: ${opt.data('hm') || 0}</span>
                         </div>
                         <span class="badge bg-label-secondary text-dark"><i class="ri-ruler-line"></i> Sisa RTD: ${opt.data('rtd')} mm</span>
                     </div>
                  </div>
               `).slideDown();
            } else {
               infoBox.slideUp();
            }
         });

         $('#btn_submit').click(function() {
            let isClassic = $('#mode_klasik').is(':checked');

            if(isClassic) {
               if(!$('#c_position_id').val()) {
                  Swal.fire('Peringatan', 'Posisi wajib dipilih pada Mode Klasik.', 'warning');
                  return;
               }
               if(!$('#c_psi').val()) {
                  Swal.fire('Peringatan', 'PSI wajib diisi.', 'warning');
                  return;
               }
            } else {
               if(tasks.length === 0) {
                  Swal.fire('Peringatan', 'Antrean pelepasan masih kosong.', 'warning');
                  return;
               }
            }

            if(!vehicleSelect.val()) {
               Swal.fire('Peringatan', 'Unit kendaraan wajib dipilih.', 'warning');
               return;
            }
            if($('#odometer').prop('required') && !$('#odometer').val()) {
               Swal.fire('Peringatan', 'Odometer wajib diisi untuk unit ini.', 'warning');
               return;
            }
            if($('#hour_meter').prop('required') && !$('#hour_meter').val()) {
               Swal.fire('Peringatan', 'Hour Meter wajib diisi untuk unit ini.', 'warning');
               return;
            }

            let isValid = true;
            let movements = [];
            let formData = new FormData(document.getElementById('pelepasan_form'));

            if (isClassic) {
                movements.push({
                   type: 'Removal',
                   position_id: $('#c_position_id').val(),
                   rtd: $('#c_rtd').val() || '',
                   psi: $('#c_psi').val(),
                   target_status: $('#c_target_status').val(),
                   failure_code_id: $('#c_failure_code_id').val(),
                   notes: $('#c_notes').val() || ''
                });
            } else {
                for(let i=0; i<tasks.length; i++) {
                   const t = tasks[i];
                   const card = document.getElementById(`task_${i}`);
                   if(!card) continue;
                   
                   const psi = card.querySelector('.t-psi').value;
                   if(!psi) {
                      isValid = false;
                      card.style.border = '1px solid red';
                   } else {
                      card.style.border = '';
                   }
                   
                   movements.push({
                      type: 'Removal',
                      position_id: t.position_id,
                      rtd: card.querySelector('.t-rtd').value || '',
                      psi: psi,
                      target_status: card.querySelector('.t-target-status').value,
                      failure_code_id: card.querySelector('.t-failure-code').value || null,
                      notes: card.querySelector('.t-notes').value || ''
                   });
                   
                   const photoInput = card.querySelector('.t-photo');
                   if(photoInput && photoInput.files.length > 0) {
                      formData.append(`move_photo_${i}`, photoInput.files[0]);
                   }
                }
            }

            if(!isClassic && !isValid) {
               Swal.fire('Peringatan', 'Lengkapi semua input wajib (PSI) pada antrean.', 'warning');
               return;
            }

            formData.append('movements', JSON.stringify(movements));

            Swal.fire({
               title: 'Simpan Pelepasan Massal?',
               text: `Memproses ${tasks.length} pelepasan ban. Lanjutkan?`,
               icon: 'question',
               showCancelButton: true,
               confirmButtonText: 'Ya, Proses Semua',
               customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-outline-secondary' },
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
                           Swal.fire('Gagal', data.message || 'Terjadi kesalahan sistem', 'error');
                           btn.disabled = false;
                           btn.innerHTML = '<i class="ri ri-delete-bin-line me-1"></i> Proses Pelepasan Massal';
                        }
                     })
                     .catch(err => {
                        console.error('Error:', err);
                        Swal.fire('Error', 'Kesalahan jaringan/sistem.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ri ri-delete-bin-line me-1"></i> Proses Pelepasan Massal';
                     });
               }
            });
         });
      });
   </script>
@endsection
