@extends('layouts.admin')

@section('title', 'Pemasangan Ban Massal (Bulk)')

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
      
      /* Tyre Rack Styles */
      .tyre-rack-container {
         display: flex;
         gap: 10px;
         overflow-x: auto;
         padding: 15px 10px;
         background: #2d2d2d;
         border-radius: 10px;
         margin-bottom: 15px;
      }
      .rack-item {
         min-width: 100px;
         background: #fdfdfd;
         border: 1px solid #d1d5db;
         border-radius: 6px;
         padding: 6px 10px;
         margin: 4px;
         display: inline-block;
         cursor: grab;
         text-align: center;
         transition: all 0.2s ease;
         user-select: none;
      }
      .rack-item:hover {
         border-color: #696cff;
         background: #fff;
      }
      .rack-item:active { cursor: grabbing; transform: scale(0.95); }
      .rack-item.dragging { opacity: 0.5; border-color: #7367f0; }
      .rack-item .sn { font-weight: 700; font-size: 12px; color: #495057; margin-bottom: 2px; }
      .rack-item .info { font-size: 10px; color: #6c757d; margin-bottom: 4px; }
      .rack-item .badge-container { display: flex; flex-direction: column; gap: 4px; align-items: center; }

      /* Task Card Styles */
      .task-card {
         background: #ffffff;
         border: 1px solid #eef0f2;
         border-left: 4px solid #696cff;
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
      .task-card .form-control {
         border-radius: 8px;
         border: 1px solid #d9dee3;
         background-color: #fcfdfd;
         font-size: 0.85rem;
      }
      .task-card .form-control:focus {
         border-color: #696cff;
         box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.1);
      }
      .task-card .remove-task {
         position: absolute;
         top: 10px;
         right: 10px;
         color: #ea5455;
         cursor: pointer;
         font-size: 1.2rem;
         transition: color 0.2s;
      }
      .task-card .remove-task:hover { color: #d33; }

      @media (max-width: 991.98px) {
         .sticky-panel { position: static !important; }
      }
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold mb-0"><span class="text-muted fw-light">Transaksi /</span> Pemasangan Ban (Bulk)</h4>
         <a href="{{ route('tyre-movement.index') }}" class="btn btn-outline-secondary">
            <i class="ri ri-arrow-left-line me-1"></i> Kembali
         </a>
      </div>

      <form id="pemasangan_form" enctype="multipart/form-data">
         @csrf
         <input type="hidden" name="movement_type" value="Installation">

         <div class="row g-4">
            <!-- LEFT PANEL: Sticky Visual Layout & Tyre Rack -->
            <div class="col-lg-5 col-xl-4 order-2 order-lg-1">
               <div class="sticky-panel">
                  
                  <!-- Rak Ban (Source) -->
                  <div class="visual-layout-card shadow-sm mb-3">
                     <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0 fw-bold"><i class="ri ri-inbox-archive-line me-2 text-primary"></i>Rak Ban Siap Pasang</h6>
                        <div class="input-group input-group-sm w-50">
                           <input type="text" id="rack_search" class="form-control" placeholder="Cari SN, Merek, Ukuran...">
                           <button class="btn btn-outline-secondary" type="button" id="btn_reload_rack"><i class="ri-refresh-line"></i></button>
                        </div>
                     </div>
                     <div class="card-body p-2">
                        <div class="tyre-rack-container" id="tyre_rack">
                           <div class="text-muted small p-2 w-100 text-center">Loading stock...</div>
                        </div>
                        <small class="text-muted d-block text-center mt-1"><i class="ri-drag-drop-line me-1"></i>Drag ban ke posisi di bawah</small>
                     </div>
                  </div>

                  <!-- Visual Layout (Dropzone) -->
                  <div class="visual-layout-card shadow-sm mb-4">
                     <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                        <h6 class="mb-0 fw-bold"><i class="ri ri-mouse-line me-2 text-primary"></i>Visual Axle Layout</h6>
                        <span class="badge bg-label-secondary" id="unit_code_display">-</span>
                     </div>
                     <div class="card-body p-0 d-flex flex-column align-items-center justify-content-center"
                        style="min-height: 400px; background: #fafafa;">
                        <div id="layout_container" class="w-100 h-100 d-flex align-items-center justify-content-center p-4">
                           <div class="text-center text-muted p-5 w-100">
                              <i class="ri ri-truck-line ri-4x mb-3 d-block opacity-25"></i>
                              <p class="mb-0">Pilih Unit Kendaraan untuk memuat visual layout.</p>
                           </div>
                        </div>
                     </div>
                     <div id="tyre_info_display" class="p-3 border-top bg-white" style="display:none;"></div>
                     <div class="bg-light p-2 text-center border-top">
                        <small class="text-muted"><i class="ri-drag-drop-line me-1"></i> Drag ban dari Rak lalu drop ke posisi kosong di layout.</small>
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
                           <label class="form-label fw-bold">Tanggal</label>
                           <input type="date" name="movement_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3 mb-3" id="odometer_container">
                           <label class="form-label fw-bold">KM Saat Pasang</label>
                           <input type="number" name="odometer" id="odometer" class="form-control" placeholder="KM Odometer" required>
                           <small class="text-muted extra-small d-block mt-1">Last KM: <span id="last_odo_display" class="fw-bold">-</span></small>
                        </div>
                        <div class="col-md-3 mb-3" id="hour_meter_container">
                           <label class="form-label fw-bold">HM Saat Pasang</label>
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

               <!-- SECTION 2: Antrean Tugas (Visual Mode) -->
               <div id="visual_mode_container">
                  <div class="card premium-card mb-4 border-start border-primary border-5">
                     <div class="card-body">
                        <div class="form-section-header">
                           <div class="form-section-icon"><i class="ri ri-list-check"></i></div>
                           <h5 class="form-section-title">Antrean Pemasangan & Inspeksi</h5>
                        </div>
                     
                        <div id="task_queue_container">
                           <div class="text-center p-4 text-muted bg-light rounded border border-dashed" id="empty_task_state">
                              <i class="ri-drag-drop-line ri-3x mb-2 d-block opacity-50"></i>
                              <p class="mb-0">Belum ada ban yang ditambahkan.<br>Pilih unit, lalu <b>Drag & Drop</b> ban dari Rak ke Visual Layout.</p>
                           </div>
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
                           <h5 class="form-section-title">Detail Pemasangan (Satu per Satu)</h5>
                        </div>
                        <div class="row g-3 bg-light p-3 rounded border border-dashed">
                           <div class="col-md-6">
                              <label class="form-label fw-bold">Pilih Ban (Dari Rak)</label>
                              <select id="c_tyre_id" class="form-select select2">
                                 <option value="">-- Pilih Ban --</option>
                              </select>
                           </div>
                           <div class="col-12 mt-1" id="c_tyre_info" style="display:none;"></div>
                           <div class="col-md-6">
                              <label class="form-label fw-bold">Posisi Pemasangan</label>
                              <select id="c_position_id" class="form-select select2">
                                 <option value="">-- Pilih Posisi (Pilih Unit Dulu) --</option>
                              </select>
                           </div>
                           <div class="col-12 mt-1" id="c_position_info" style="display:none;"></div>
                           <div class="col-md-6">
                              <label class="form-label fw-bold">Sisa RTD (mm)</label>
                              <input type="number" step="0.01" id="c_rtd" class="form-control" placeholder="Isi RTD Baru">
                           </div>
                           <div class="col-md-6">
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
                           <label class="form-label fw-bold">Lokasi Pengerjaan</label>
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
                           <i class="ri ri-save-3-line me-1"></i> Simpan Bulk Transaksi
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
         const layoutContainer = document.getElementById('layout_container');
         const tyreRack = document.getElementById('tyre_rack');
         const taskContainer = document.getElementById('task_queue_container');
         const emptyState = document.getElementById('empty_task_state');
         let taskIndex = 0;
         let tasks = []; // Track added tyres
         let activeRackTyre = null;

         $('.select2').each(function() {
            var $this = $(this);
            $this.select2({ placeholder: $this.attr('placeholder'), allowClear: true });
         });

         // Load Warehouse Stock
         function loadWarehouseStock(search = '') {
            tyreRack.innerHTML = '<div class="text-muted small p-2 w-100 text-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>';
            fetch(`{{ route('tyre-movement.warehouse-stock') }}?search=${search}`)
               .then(res => res.json())
               .then(res => {
                  if (res.success && res.data.length > 0) {
                     tyreRack.innerHTML = '';
                     res.data.forEach(tyre => {
                        // Skip if already in task list
                        if(tasks.find(t => t.tyre_id == tyre.id)) return;
                        
                        const item = document.createElement('div');
                        item.className = 'rack-item';
                        item.setAttribute('draggable', 'true');
                        item.dataset.tyreId = tyre.id;
                        item.dataset.sn = tyre.serial_number;
                        item.dataset.brand = tyre.brand ? tyre.brand.brand_name : '-';
                        item.dataset.size = tyre.size ? tyre.size.size : '-';
                        
                        const statusBadge = tyre.status === 'New' 
                           ? '<span class="badge bg-success" style="font-size:0.65rem;">Baru</span>'
                           : '<span class="badge bg-warning" style="font-size:0.65rem;">Repaired</span>';
                        const rtdText = tyre.current_tread_depth ? tyre.current_tread_depth + ' mm' : '-';
                        
                        item.innerHTML = `
                           <div class="sn">${tyre.serial_number}</div>
                           <div class="info">${tyre.brand ? tyre.brand.brand_name : ''}</div>
                           <div class="badge-container">
                              ${statusBadge}
                              <span class="text-primary" style="font-size:0.7rem; font-weight:700;"><i class="ri-ruler-line"></i> ${rtdText}</span>
                           </div>
                        `;
                        
                        item.addEventListener('click', function() {
                           document.querySelectorAll('.rack-item').forEach(el => el.classList.remove('border-primary', 'shadow-sm', 'bg-label-primary'));
                           if(activeRackTyre && activeRackTyre.id === tyre.id) {
                               activeRackTyre = null;
                           } else {
                               this.classList.add('border-primary', 'shadow-sm', 'bg-label-primary');
                               activeRackTyre = {
                                   id: tyre.id,
                                   sn: tyre.serial_number,
                                   brand: tyre.brand ? tyre.brand.brand_name : '-',
                                   size: tyre.size ? tyre.size.size : '-',
                                   pattern: tyre.pattern ? tyre.pattern.name : '-',
                                   status: tyre.status,
                                   rtd: tyre.current_tread_depth || '-',
                                   elem: this
                               };
                           }
                        });
                        
                        // Drag Events for Rack Items
                        item.addEventListener('dragstart', function(e) {
                           e.dataTransfer.setData('tyre_id', tyre.id);
                           e.dataTransfer.setData('sn', tyre.serial_number);
                           e.dataTransfer.setData('brand', tyre.brand ? tyre.brand.brand_name : '-');
                           e.dataTransfer.setData('size', tyre.size ? tyre.size.size : '-');
                           e.dataTransfer.setData('pattern', tyre.pattern ? tyre.pattern.name : '-');
                           e.dataTransfer.setData('status', tyre.status);
                           e.dataTransfer.setData('rtd', tyre.current_tread_depth || '-');
                           this.classList.add('dragging');
                        });
                        item.addEventListener('dragend', function() {
                           this.classList.remove('dragging');
                           document.querySelectorAll('.m-tyre-node').forEach(node => {
                              node.classList.remove('drag-over', 'drag-invalid');
                           });
                        });
                        
                        tyreRack.appendChild(item);
                     });
                     
                     if(tyreRack.innerHTML === '') {
                        tyreRack.innerHTML = '<div class="text-muted small p-2 w-100 text-center">Semua ban tersedia sedang dalam antrean.</div>';
                     }
                  } else {
                     tyreRack.innerHTML = '<div class="text-muted small p-2 w-100 text-center">Stok Ban Kosong</div>';
                  }
               });
         }
         
         loadWarehouseStock();
         $('#btn_reload_rack').click(() => loadWarehouseStock($('#rack_search').val()));
         $('#rack_search').on('input', function() {
            clearTimeout(this.delay);
            this.delay = setTimeout(() => loadWarehouseStock($(this).val()), 500);
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
               
               // Load classic dropdowns
               if($('#c_tyre_id').children('option').length <= 1) {
                   fetch(`{{ route('tyre-movement.warehouse-stock') }}?limit=100`)
                      .then(res => res.json())
                      .then(res => {
                         if (res.success) {
                            let options = '<option value="">-- Pilih Ban --</option>';
                            res.data.forEach(t => {
                               const brand = t.brand ? t.brand.brand_name : '-';
                               const size = t.size ? t.size.size : '-';
                               const pattern = t.pattern ? t.pattern.name : '-';
                               const rtd = t.current_tread_depth || '-';
                               options += `<option value="${t.id}" data-sn="${t.serial_number}" data-brand="${brand}" data-size="${size}" data-pattern="${pattern}" data-status="${t.status}" data-rtd="${rtd}">${t.serial_number} - ${brand}</option>`;
                            });
                            $('#c_tyre_id').html(options).select2({
                               placeholder: '-- Pilih Ban --',
                               allowClear: true,
                               templateResult: function(tyre) {
                                  if (!tyre.id) { return tyre.text; }
                                  var ds = tyre.element.dataset;
                                  var badge = ds.status === 'New' ? '<span class="badge bg-label-success me-1" style="font-size:0.65rem;">Baru</span>' : '<span class="badge bg-label-warning me-1" style="font-size:0.65rem;">Repaired</span>';
                                  var rtdLabel = ds.status === 'New' ? 'OTD: ' : 'RTD: ';
                                  return $(
                                     '<div class="d-flex flex-column">' +
                                       '<div class="fw-bold text-dark mb-1">' + ds.sn + '</div>' +
                                       '<div class="small text-muted">' + badge + ' <b>' + ds.brand + '</b> | ' + ds.size + ' | ' + ds.pattern + ' | ' + rtdLabel + '<b class="text-dark">' + ds.rtd + ' mm</b></div>' +
                                     '</div>'
                                  );
                               },
                               templateSelection: function(tyre) {
                                  if (!tyre.id) { return tyre.text; }
                                  return tyre.element.dataset.sn + ' - ' + tyre.element.dataset.brand;
                               }
                            });
                         }
                      });
               }
            }
         });

         // Vehicle Change
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
                  attachLayoutDragDropEvents();

                  // Populate classic position dropdown
                  let posOptions = '<option value="">-- Pilih Posisi --</option>';
                  document.querySelectorAll('.m-tyre-node').forEach(node => {
                     const isFilled = node.classList.contains('filled');
                     const filledText = isFilled ? ` (Terisi: ${node.dataset.sn || '-'})` : ' (Kosong)';
                     posOptions += `<option value="${node.dataset.positionId}" data-code="${node.dataset.code}" data-filled="${isFilled}" data-sn="${node.dataset.sn}" data-brand="${node.dataset.brand}" data-size="${node.dataset.size}" data-pattern="${node.dataset.pattern}" data-hm="${node.dataset.hm}" data-rtd="${node.dataset.rtd}">Posisi ${node.dataset.code}${filledText}</option>`;
                  });
                  $('#c_position_id').html(posOptions);
               });
         });

         function attachLayoutDragDropEvents() {
            const nodes = document.querySelectorAll('.m-tyre-node');
            nodes.forEach(node => {
               // Enable drag-to-correct for queued nodes
               node.addEventListener('dragstart', function(e) {
                  if (this.classList.contains('queued-install')) {
                      e.dataTransfer.setData('tyre_id', this.dataset.queuedTyreId);
                      e.dataTransfer.setData('sn', this.dataset.queuedSn);
                      e.dataTransfer.setData('from_pos_id', this.dataset.positionId);
                  } else {
                      e.preventDefault();
                  }
               });

               node.addEventListener('dragover', function(e) {
                  e.preventDefault(); 
                  this.classList.add('drag-over');
               });

               // Clickable node
               node.addEventListener('click', function() {
                  // Mode Klasik auto-select
                  const isClassic = $('#mode_klasik').is(':checked');
                  if (isClassic) {
                     $('#c_position_id').val(this.dataset.positionId).trigger('change');
                     const classicModeContainer = document.getElementById('classic_mode_container');
                     if(classicModeContainer) classicModeContainer.scrollIntoView({behavior: 'smooth', block: 'center'});
                  } else {
                     // Mode Visual Point-and-Click
                     if (activeRackTyre && !this.classList.contains('queued-install')) {
                        const tgtPosId = this.dataset.positionId;
                        const tgtPosCode = this.dataset.code;
                        const isFilled = this.classList.contains('filled');
                        const replaceMsg = isFilled ? `
                           <div class="bg-white p-2 rounded border border-dashed border-danger mt-2">
                               <div class="small fw-bold text-danger mb-1"><i class="ri-alert-line me-1"></i>Peringatan Replace (Gusur Ban)</div>
                               <div class="small text-muted d-flex justify-content-between align-items-center flex-wrap gap-1">
                                   <div><span class="fw-bold text-dark">${this.dataset.sn}</span> &bull; ${this.dataset.brand} | ${this.dataset.size} | ${this.dataset.pattern}</div>
                                   <span class="badge bg-label-danger">RTD: ${this.dataset.rtd} mm | HM: ${this.dataset.hm || 0}</span>
                               </div>
                           </div>
                        ` : '';
                        
                        if(tasks.find(t => t.position_id == tgtPosId)) {
                           Swal.fire('Posisi Terisi', 'Posisi ini sudah berada di dalam antrean.', 'info');
                           return;
                        }
                        
                        addTaskCard(activeRackTyre.id, activeRackTyre.sn, tgtPosId, tgtPosCode, isFilled, replaceMsg, activeRackTyre.brand, activeRackTyre.size, activeRackTyre.pattern, activeRackTyre.status, activeRackTyre.rtd);
                        if(activeRackTyre.elem) activeRackTyre.elem.remove();
                        
                        this.classList.add('filled', 'queued-install');
                        this.draggable = true;
                        this.dataset.queuedTyreId = activeRackTyre.id;
                        this.dataset.queuedSn = activeRackTyre.sn;
                        this.style.border = '2px solid #28c76f';
                        this.innerHTML = `<span class="v-tyre-code text-white">${tgtPosCode}</span><span class="v-tyre-sn-hint text-success" style="opacity:1">${activeRackTyre.sn.slice(-4)}</span>`;
                        
                        activeRackTyre = null; // reset
                     }
                  }
                  
                  // Display Info
                  const infoBox = document.getElementById('tyre_info_display');
                  if (!infoBox) return;
                  
                  if (this.classList.contains('filled')) {
                     const sn = this.dataset.sn || '-';
                     const brand = this.dataset.brand || '-';
                     const size = this.dataset.size || '-';
                     const pattern = this.dataset.pattern || '-';
                     const rtd = this.dataset.rtd || '-';
                     
                     infoBox.innerHTML = `
                        <div class="alert alert-info d-flex align-items-center mb-0 p-2">
                           <i class="ri-information-line ri-2x me-3"></i>
                           <div>
                              <h6 class="alert-heading fw-bold mb-1" style="font-size: 0.9rem;">Posisi ${this.dataset.code} Terisi: <span class="text-primary">${sn}</span></h6>
                              <p class="mb-0" style="font-size: 0.8rem;">Merek: <b>${brand}</b> | Ukuran: <b>${size}</b> | Pattern: <b>${pattern}</b> | Sisa RTD: <b>${rtd} mm</b></p>
                           </div>
                        </div>
                     `;
                     infoBox.style.display = 'block';
                  } else {
                     infoBox.innerHTML = `
                        <div class="alert alert-secondary d-flex align-items-center mb-0 p-2">
                           <i class="ri-checkbox-circle-line ri-2x me-3 text-success"></i>
                           <div>
                              <h6 class="alert-heading fw-bold mb-1" style="font-size: 0.9rem;">Posisi ${this.dataset.code} Kosong</h6>
                              <p class="mb-0 text-muted" style="font-size: 0.8rem;">Siap untuk dipasangkan ban baru.</p>
                           </div>
                        </div>
                     `;
                     infoBox.style.display = 'block';
                  }
               });
               node.addEventListener('dragleave', function() {
                  this.classList.remove('drag-over', 'drag-invalid');
               });
               node.addEventListener('drop', function(e) {
                  e.preventDefault();
                  this.classList.remove('drag-over', 'drag-invalid');
                  
                  const tyreId = e.dataTransfer.getData('tyre_id');
                  const sn = e.dataTransfer.getData('sn');
                  const fromPosId = e.dataTransfer.getData('from_pos_id');
                  
                  const tgtPosId = this.dataset.positionId;
                  const tgtPosCode = this.dataset.code;
                  
                  if(!tyreId) return; 
                  if(fromPosId === tgtPosId) return; // Dropped on itself
                  
                  // Check if position already in tasks
                  if(tasks.find(t => t.position_id == tgtPosId)) {
                     Swal.fire('Posisi Terisi', 'Posisi ini sudah berada di dalam antrean.', 'info');
                     return;
                  }
                  
                  const brand = e.dataTransfer.getData('brand');
                  const size = e.dataTransfer.getData('size');
                  const pattern = e.dataTransfer.getData('pattern');
                  const status = e.dataTransfer.getData('status');
                  const rtd = e.dataTransfer.getData('rtd');
                  
                  const isFilled = this.classList.contains('filled');
                  const replaceMsg = isFilled ? `
                     <div class="bg-white p-2 rounded border border-dashed border-danger mt-2">
                         <div class="small fw-bold text-danger mb-1"><i class="ri-alert-line me-1"></i>Peringatan Replace (Gusur Ban)</div>
                         <div class="small text-muted d-flex justify-content-between align-items-center flex-wrap gap-1">
                             <div><span class="fw-bold text-dark">${this.dataset.sn}</span> &bull; ${this.dataset.brand} | ${this.dataset.size} | ${this.dataset.pattern}</div>
                             <span class="badge bg-label-danger">RTD: ${this.dataset.rtd} mm | HM: ${this.dataset.hm || 0}</span>
                         </div>
                     </div>
                  ` : '';
                  
                  if (fromPosId) {
                      // Move logic (Correction)
                      let task = tasks.find(t => t.tyre_id == tyreId);
                      if (task) {
                          task.position_id = tgtPosId;
                          // Update DOM hidden inputs and text
                          const card = document.querySelector(`.t-tyre-id[value="${tyreId}"]`).closest('.task-card');
                          card.querySelector('.t-position-id').value = tgtPosId;
                          card.querySelector('h6').innerHTML = `Pasang Ban: <span class="text-primary">${sn}</span> <i class="ri-arrow-right-line mx-1 text-muted"></i> Posisi <span class="text-primary">${tgtPosCode}</span> ${replaceMsg}`;
                          
                          // Restore old node
                          const oldNode = document.querySelector(`.m-tyre-node[data-position-id="${fromPosId}"]`);
                          if(oldNode) {
                              oldNode.style.border = '';
                              oldNode.classList.remove('queued-install');
                              oldNode.draggable = false;
                              oldNode.removeAttribute('data-queued-tyre-id');
                              oldNode.removeAttribute('data-queued-sn');
                              const origSn = oldNode.dataset.sn;
                              if(origSn) {
                                 oldNode.innerHTML = `<span class="v-tyre-code">${oldNode.dataset.code}</span><span class="v-tyre-sn-hint">${origSn.slice(-4)}</span>`;
                              } else {
                                 oldNode.classList.remove('filled');
                                 oldNode.classList.add('empty');
                                 oldNode.innerHTML = `<span class="v-tyre-code">${oldNode.dataset.code}</span>`;
                              }
                          }
                      }
                  } else {
                      // New assignment from Rack
                      addTaskCard(tyreId, sn, tgtPosId, tgtPosCode, isFilled, replaceMsg, brand, size, pattern, status, rtd);
                      const item = document.querySelector(`.rack-item[data-tyre-id="${tyreId}"]`);
                      if(item) item.remove();
                  }
                  
                  // Update Visual node
                  this.classList.add('filled', 'queued-install');
                  this.draggable = true;
                  this.dataset.queuedTyreId = tyreId;
                  this.dataset.queuedSn = sn;
                  this.style.border = '2px solid #28c76f';
                  this.innerHTML = `<span class="v-tyre-code text-white">${tgtPosCode}</span><span class="v-tyre-sn-hint text-success" style="opacity:1">${sn.slice(-4)}</span>`;
               });
            });
         }

         function addTaskCard(tyreId, sn, posId, posCode, isReplacement, replaceMsg, brand, size, pattern, status, rtd) {
            emptyState.style.display = 'none';
            tasks.push({ tyre_id: tyreId, position_id: posId });
            
            const statusBadge = status === 'New' 
               ? '<span class="badge bg-label-success me-1">Baru</span>' 
               : '<span class="badge bg-label-warning me-1">Repaired</span>';
            const rtdLabel = status === 'New' ? 'OTD: ' : 'RTD: ';
            
            const html = `
               <div class="task-card" id="task_${taskIndex}">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                     <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm bg-primary me-2 d-flex align-items-center justify-content-center text-white rounded">
                           <i class="ri-arrow-right-down-line"></i>
                        </div>
                        <h6 class="mb-0 fw-bold">Pasang di Posisi <span class="text-primary">${posCode}</span> <i class="ri-arrow-right-line mx-1 text-muted"></i> SN Ban: <span class="text-primary">${sn}</span></h6>
                     </div>
                     <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTask(${taskIndex}, '${tyreId}', '${posId}')"><i class="ri-close-line"></i> Batal</button>
                  </div>
                  
                  <div class="bg-light p-2 rounded mb-3 border border-dashed d-flex gap-3 text-muted small ms-4 ps-2 border-start border-2 border-primary">
                     <div><b class="text-dark">${brand}</b> | ${size} | ${pattern}</div>
                     <div>${statusBadge}</div>
                     <div><i class="ri-ruler-line ms-2 text-primary"></i> ${rtdLabel}: <span class="text-dark fw-bold">${rtd} mm</span></div>
                  </div>

                  <div class="row g-2">
                     <div class="col-md-3">
                        <label class="form-label small fw-bold">Sisa RTD (mm)</label>
                        <input type="number" step="0.01" class="form-control form-control-sm t-rtd" placeholder="Isi RTD Baru">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label small fw-bold">PSI</label>
                        <input type="number" step="0.01" class="form-control form-control-sm t-psi" placeholder="Wajib">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label small fw-bold">Foto Bukti</label>
                        <input type="file" class="form-control form-control-sm t-photo" id="photo_${taskIndex}" accept="image/*">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label small fw-bold">Remarks</label>
                        <input type="text" class="form-control form-control-sm t-notes">
                     </div>
                  </div>
                  <!-- Hidden inputs -->
                  <input type="hidden" class="t-type" value="Installation">
                  <input type="hidden" class="t-tyre-id" value="${tyreId}">
                  <input type="hidden" class="t-position-id" value="${posId}">
               </div>
            `;
            taskContainer.insertAdjacentHTML('beforeend', html);
            taskIndex++;
         }

         window.removeTask = function(index, tyreId, posId) {
            document.getElementById(`task_${index}`).remove();
            tasks = tasks.filter(t => t.tyre_id != tyreId);
            
            if(tasks.length === 0) emptyState.style.display = 'block';
            
            // Revert layout node
            const node = document.querySelector(`.m-tyre-node[data-position-id="${posId}"]`);
            if(node) {
               node.style.border = '';
               node.draggable = false;
               node.classList.remove('queued-install');
               const origSn = node.dataset.sn;
               if(origSn) {
                  node.innerHTML = `<span class="v-tyre-code">${node.dataset.code}</span><span class="v-tyre-sn-hint">${origSn.slice(-4)}</span>`;
               } else {
                  node.classList.remove('filled');
                  node.classList.add('empty');
                  node.innerHTML = `<span class="v-tyre-code">${node.dataset.code}</span>`;
               }
            }
            
            loadWarehouseStock($('#rack_search').val());
         };

         $('#c_position_id').on('change', function() {
            const opt = $(this).find('option:selected');
            const infoBox = $('#c_position_info');
            if(opt.val() && opt.data('filled') === true) {
               infoBox.html(`
                  <div class="bg-white p-3 rounded border mb-2 border-start border-4 border-danger shadow-sm mt-2">
                     <div class="d-flex align-items-center mb-2">
                         <i class="ri-alert-line ri-xl me-2 text-danger"></i>
                         <h6 class="mb-0 fw-bold text-danger">Peringatan Replace (Gusur Ban)</h6>
                     </div>
                     <div class="small text-muted mb-2">Posisi ini sudah terisi ban. Melanjutkan pemasangan akan otomatis melepaskan ban saat ini.</div>
                     <div class="bg-light p-2 rounded border border-dashed d-flex justify-content-between align-items-center flex-wrap gap-1">
                         <div>
                             <span class="fw-bold text-dark me-1">${opt.data('sn')}</span>
                             <span class="small text-muted">&bull; ${opt.data('brand')} | ${opt.data('size')} | ${opt.data('pattern')} | HM: ${opt.data('hm') || 0}</span>
                         </div>
                         <span class="badge bg-label-danger"><i class="ri-ruler-line"></i> RTD: ${opt.data('rtd')} mm</span>
                     </div>
                  </div>
               `).slideDown();
            } else {
               infoBox.slideUp();
            }
         });
         
         $('#c_tyre_id').on('change', function() {
            const opt = $(this).find('option:selected');
            const infoBox = $('#c_tyre_info');
            if(opt.val()) {
               const isNew = opt.data('status') === 'New';
               const rtdLabel = isNew ? 'OTD: ' : 'RTD: ';
               const badge = isNew ? '<span class="badge bg-label-success me-1">Baru</span>' : '<span class="badge bg-label-warning me-1">Repaired</span>';
               infoBox.html(`
                  <div class="bg-white p-3 rounded border mb-2 border-start border-4 border-primary shadow-sm mt-1">
                     <div class="d-flex align-items-center mb-2">
                         <i class="ri-information-line ri-xl me-2 text-primary"></i>
                         <h6 class="mb-0 fw-bold">Ban yang akan Dipasang</h6>
                     </div>
                     <div class="bg-light p-2 rounded border border-dashed d-flex justify-content-between align-items-center flex-wrap gap-1">
                         <div>
                             ${badge}
                             <span class="fw-bold text-dark me-1">${opt.data('sn')}</span>
                             <span class="small text-muted">&bull; ${opt.data('brand')} | ${opt.data('size')} | ${opt.data('pattern')}</span>
                         </div>
                         <span class="badge bg-label-secondary text-dark"><i class="ri-ruler-line"></i> ${rtdLabel} ${opt.data('rtd')} mm</span>
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
               if(!$('#c_tyre_id').val() || !$('#c_position_id').val()) {
                  Swal.fire('Peringatan', 'Ban dan Posisi wajib dipilih pada Mode Klasik.', 'warning');
                  return;
               }
               if(!$('#c_psi').val()) {
                  Swal.fire('Peringatan', 'PSI wajib diisi.', 'warning');
                  return;
               }
            } else {
               if(tasks.length === 0) {
                  Swal.fire('Peringatan', 'Antrean pemasangan masih kosong.', 'warning');
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
            let formData = new FormData(document.getElementById('pemasangan_form'));

            if (isClassic) {
                movements.push({
                   type: 'Installation',
                   tyre_id: $('#c_tyre_id').val(),
                   position_id: $('#c_position_id').val(),
                   rtd: $('#c_rtd').val() || '',
                   psi: $('#c_psi').val(),
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
                      type: 'Installation',
                      tyre_id: t.tyre_id,
                      position_id: t.position_id,
                      rtd: card.querySelector('.t-rtd').value || '',
                      psi: psi,
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
               title: 'Simpan Pemasangan Massal?',
               text: `Anda akan memproses ${tasks.length} pemasangan ban. Lanjutkan?`,
               icon: 'question',
               showCancelButton: true,
               confirmButtonText: 'Ya, Proses Semua',
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
                           Swal.fire('Gagal', data.message || 'Terjadi kesalahan sistem', 'error');
                           btn.disabled = false;
                           btn.innerHTML = '<i class="ri ri-save-3-line me-1"></i> Simpan Bulk Transaksi';
                        }
                     })
                     .catch(err => {
                        console.error('Error:', err);
                        Swal.fire('Error', 'Kesalahan jaringan/sistem.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ri ri-save-3-line me-1"></i> Simpan Bulk Transaksi';
                     });
               }
            });
         });
      });
   </script>
@endsection
