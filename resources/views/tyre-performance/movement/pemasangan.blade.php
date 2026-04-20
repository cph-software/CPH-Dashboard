@extends('layouts.admin')

@section('title', 'Form Pemasangan Ban')

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

      .tyre-info-display-box {
         background: #f8f7fa;
         border-radius: 12px;
         padding: 15px;
         border: 1px dashed #dcdfe6;
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
         <h4 class="fw-bold mb-0"><span class="text-muted fw-light">Transaksi /</span> Pemasangan Ban</h4>
         <a href="{{ route('tyre-movement.index') }}" class="btn btn-outline-secondary">
            <i class="ri ri-arrow-left-line me-1"></i> Kembali
         </a>
      </div>

      <form id="pemasangan_form" enctype="multipart/form-data">
         @csrf
         <input type="hidden" name="movement_type" value="Installation">

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
                              <p class="mb-0">Pilih Unit Kendaraan untuk memuat visual layout.</p>
                           </div>
                        </div>
                     </div>
                     <!-- Selection Info Overlay (Premium Style) -->
                     <div id="selection_info" class="m-3 p-3 rounded-3 shadow-sm"
                        style="display: none; background: linear-gradient(135deg, #7367f0 0%, #a098f5 100%); color: white;">
                        <div class="d-flex align-items-center">
                           <div
                              class="avatar avatar-md bg-white-transparent me-3 d-flex align-items-center justify-content-center"
                              style="background: rgba(255,255,255,0.2); border-radius: 8px;">
                              <i class="ri ri-focus-3-line text-white ri-xl"></i>
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
                        <div class="form-section-icon"><i class="ri ri-truck-line"></i></div>
                        <h5 class="form-section-title">Identifikasi Unit</h5>
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
                           <label class="form-label fw-bold">Tanggal & Waktu</label>
                           <input type="date" name="movement_date" class="form-control" value="{{ date('Y-m-d') }}"
                              required>
                        </div>
                        <div class="col-md-4 mb-3">
                           <label class="form-label fw-bold">KM Saat Pasang</label>
                           <input type="number" name="odometer" id="odometer" class="form-control"
                              placeholder="KM Odometer" required>
                           <small class="text-muted extra-small d-block mt-1">Last KM: <span id="last_odo_display"
                                 class="fw-bold">-</span></small>
                        </div>
                        <div class="col-md-4 mb-3">
                           <label class="form-label fw-bold">HM Saat Pasang</label>
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
                                 <h6 class="mb-0 small fw-bold text-dark"><i
                                       class="ri ri-refresh-line me-1 text-warning"></i>
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

               <!-- SECTION 2: Konfigurasi Ban -->
               <div class="card premium-card mb-4 border-start border-primary border-5">
                  <div class="card-body">
                     <div class="form-section-header">
                        <div class="form-section-icon"><i class="ri ri-steering-line"></i></div>
                        <h5 class="form-section-title">Konfigurasi Ban</h5>
                     </div>

                     <div class="mb-4">
                        <label class="form-label fw-bold text-primary" for="position_id">1. Pilih Konfigurasi
                           Pemasangan</label>
                        <select name="position_id" id="position_id" class="form-select select2" required disabled>
                           <option value="">-- Pilih melalui visual layout atau list ini --</option>
                        </select>
                     </div>

                     <div id="current_tyre_info" class="mb-4 removal-info-box shadow-sm border border-warning rounded p-3 bg-light" style="display: none;">
                        <h6 class="mb-3 fw-bold text-warning text-uppercase small"><i
                              class="ri ri-information-line me-1"></i>
                           Data Ban Sebelumnya di Posisi Ini</h6>
                        <div class="row g-3">
                           <div class="col-md-4">
                              <small class="text-muted d-block">Serial Number</small>
                              <strong id="old_info_sn" class="fs-5 text-dark">-</strong>
                           </div>
                           <div class="col-md-4">
                              <small class="text-muted d-block">Brand</small>
                              <span id="old_info_brand" class="fw-bold">-</span>
                           </div>
                           <div class="col-md-4">
                              <small class="text-muted d-block">Pattern/Size</small>
                              <span id="old_info_pattern_size" class="fw-bold">-</span>
                           </div>
                           <div class="col-12 mt-2 pt-2 border-top">
                              <div class="d-flex gap-4">
                                 <div>
                                    <small class="text-muted d-block">Terakhir Dipasang / Dicek</small>
                                    <span id="old_info_date" class="fw-bold text-warning">-</span>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>

                     <div class="mb-4 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold text-primary" for="tyre_id">2. Pilih Ban (Serial
                           Number)</label>
                        <select name="tyre_id" id="tyre_id" class="form-select" required>
                           <option value="">-- Cari SN Ban --</option>
                        </select>

                        <!-- Dynamic Tyre Info Box -->
                        <div id="tyre_info_display" class="mt-3 tyre-info-display-box" style="display: none;">
                           <div class="d-flex align-items-center mb-2">
                              <span class="badge bg-white text-primary border shadow-sm px-3 py-2">
                                 <i class="ri ri-price-tag-3-line me-1"></i> <span id="info_brand"></span>
                              </span>
                           </div>
                           <div class="row g-2 mb-2">
                              <div class="col-6">
                                 <small class="text-muted d-block">Pattern</small>
                                 <strong id="info_pattern">-</strong>
                              </div>
                              <div class="col-6">
                                 <small class="text-muted d-block">Size</small>
                                 <strong id="info_size">-</strong>
                              </div>
                           </div>
                           <div class="d-flex gap-3">
                              <div class="flex-fill p-2 bg-white rounded border text-center">
                                 <small class="text-muted d-block">OTD</small>
                                 <h6 class="mb-0 fw-bold" id="info_otd">-</h6>
                              </div>
                              <div class="flex-fill p-2 bg-white rounded border text-center">
                                 <small class="text-muted d-block">RTD</small>
                                 <h6 class="mb-0 fw-bold text-success" id="info_rtd">-</h6>
                              </div>
                           </div>
                        </div>
                     </div>

                     <div class="mb-0">
                        <label class="form-label fw-bold">Kondisi Ban Saat Pasang</label>
                        <select name="install_condition" id="install_condition" class="form-select select2" required>
                           <option value=""></option>
                           <option value="New">New (Baru)</option>
                           <option value="Spare">Spare (Bekas/Cadangan)</option>
                           <option value="Repair">Repair (Hasil Perbaikan/Vulkanisir)</option>
                        </select>
                     </div>
                  </div>
               </div>

               <!-- SECTION 3: Technical Specs -->
               <div class="card premium-card mb-4">
                  <div class="card-body">
                     <div class="form-section-header">
                        <div class="form-section-icon"><i class="ri ri-ruler-line"></i></div>
                        <h5 class="form-section-title">Spesifikasi Teknis</h5>
                     </div>
                     <div class="row g-3">
                        <div class="col-md-12">
                           <label class="form-label fw-bold">Remaining Tread Depth (4 Titik)</label>
                           <div class="row g-2">
                              <div class="col-3">
                                 <div class="input-group input-group-sm">
                                    <input type="number" name="rtd_1" id="rtd_1" class="form-control rtd-input"
                                       step="0.01" placeholder="P1">
                                    <span class="input-group-text px-1">mm</span>
                                 </div>
                              </div>
                              <div class="col-3">
                                 <div class="input-group input-group-sm">
                                    <input type="number" name="rtd_2" id="rtd_2" class="form-control rtd-input"
                                       step="0.01" placeholder="P2">
                                    <span class="input-group-text px-1">mm</span>
                                 </div>
                              </div>
                              <div class="col-3">
                                 <div class="input-group input-group-sm">
                                    <input type="number" name="rtd_3" id="rtd_3" class="form-control rtd-input"
                                       step="0.01" placeholder="P3">
                                    <span class="input-group-text px-1">mm</span>
                                 </div>
                              </div>
                              <div class="col-3">
                                 <div class="input-group input-group-sm">
                                    <input type="number" name="rtd_4" id="rtd_4" class="form-control rtd-input"
                                       step="0.01" placeholder="P4">
                                    <span class="input-group-text px-1">mm</span>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-6 mt-3">
                           <label class="form-label fw-bold">Actual RTD (Avg)</label>
                           <div class="input-group">
                              <input type="number" name="rtd_reading" id="rtd_reading"
                                 class="form-control border-primary bg-light" step="0.01" required readonly>
                              <span class="input-group-text bg-primary text-white border-primary">mm</span>
                           </div>
                        </div>
                        <div class="col-md-6 mt-3">
                           <label class="form-label fw-bold">Pressure (PSI)</label>
                           <div class="input-group">
                              <input type="number" name="psi_reading" class="form-control" placeholder="PSI" required>
                              <span class="input-group-text">PSI</span>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- SECTION 4: Team & Operation -->
               <div class="card premium-card mb-4">
                  <div class="card-body">
                     <div class="form-section-header">
                        <div class="form-section-icon"><i class="ri ri-user-settings-line"></i></div>
                        <h5 class="form-section-title">Operasional & Petugas</h5>
                     </div>
                     <div class="row g-3 mb-4">
                        <div class="col-md-6">
                           <label class="form-label fw-bold">Lokasi Pengerjaan</label>
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
                           <input type="text" name="tyreman_1" class="form-control"
                              placeholder="Nama Petugas Utama">
                        </div>
                        <div class="col-md-6">
                           <label class="form-label fw-bold">Tyreman 2 (Helper)</label>
                           <input type="text" name="tyreman_2" class="form-control" placeholder="Nama Helper">
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

                     <div class="divider text-start fw-bold mb-3"><span class="text-muted">Keterangan & Tambahan</span>
                     </div>

                     <div class="mb-3">
                        <label class="form-label fw-bold">Penggunaan Baut Baru</label>
                        <div class="d-flex align-items-center gap-4 p-2 rounded bg-light border">
                           <div class="form-check form-switch m-0">
                              <input class="form-check-input" type="checkbox" name="new_bolts_used" id="new_bolts"
                                 value="1">
                              <label class="form-check-label" for="new_bolts">Ya, Gunakan Baut Baru</label>
                           </div>
                           <div id="bolt_qty_container" style="display: none;">
                              <div class="input-group input-group-sm">
                                 <span class="input-group-text badge bg-primary">Qty Baut</span>
                                 <input type="number" name="new_bolts_quantity" class="form-control" placeholder="Qty"
                                    style="width: 80px;">
                              </div>
                           </div>
                        </div>
                     </div>

                     <div class="mb-3">
                        <label class="form-label fw-bold">Remarks</label>
                        <select name="remarks" class="form-select select2">
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
                        <label class="form-label fw-bold"><i class="ri ri-camera-line me-1"></i>Foto Bukti
                           Pemasangan</label>
                        <div class="p-3 border rounded bg-lighter text-center">
                           <input type="file" name="photo" id="photo" class="form-control mb-2"
                              accept="image/*">
                           <small class="text-muted italic">Format: JPG, PNG, WEBP (Maks. 5MB). Pastikan SN Ban terlihat
                              jelas.</small>
                        </div>
                     </div>

                     <div class="mb-4">
                        <label class="form-label fw-bold">Catatan (Notes)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Masukkan catatan tambahan jika ada..."></textarea>
                     </div>

                     <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow" id="btn_submit">
                           <i class="ri ri-save-3-line me-1"></i> Simpan Pemasangan
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
         const tyreSelect = $('#tyre_id');
         const layoutContainer = document.getElementById('layout_container');
         const selectionInfo = document.getElementById('selection_info');
         let suggestedSegmentId = null; // Store suggested segment from tyre history

         // Initialize Select2 (Removed dropdownParent to fix clipping issues)
         $('.select2').each(function() {
            var $this = $(this);
            $this.select2({
               placeholder: $this.data('placeholder') || $this.attr('placeholder'),
               allowClear: true
            });
         });

         // Initialize Tyre Select2 with AJAX
         tyreSelect.select2({
            placeholder: 'Cari SN Ban...',
            allowClear: true,
            ajax: {
               url: "{{ route('tyre-movement.search-tyres') }}",
               dataType: 'json',
               delay: 250,
               data: function(params) {
                  return {
                     q: params.term
                  };
               },
               processResults: function(data) {
                  return {
                     results: data.results
                  };
               },
               cache: true
            },
            minimumInputLength: 0,
            templateResult: formatTyreResult,
            templateSelection: formatTyreSelection
         });

         // Force load data when opened if empty
         tyreSelect.on('select2:open', function() {
            const searchField = $('.select2-search__field');
            if (searchField.length > 0 && !$(this).val()) {
               searchField.val('').trigger('input');
            }
         });

         function formatTyreResult(tyre) {
            if (tyre.loading) return tyre.text;
            const otdLabel = tyre.otd ? `OTD: ${tyre.otd}mm` : '';
            const rtdLabel = tyre.rtd ? `RTD: ${tyre.rtd}mm` : '';
            const depthInfo = (otdLabel || rtdLabel) ? ` | ${[otdLabel, rtdLabel].filter(Boolean).join(' / ')}` : '';
            return $(`
               <div class='select2-result-tyre'>
                  <div class='fw-bold'>${tyre.sn}</div>
                  <div class='small text-muted'>${tyre.brand} | ${tyre.size} | ${tyre.pattern}${depthInfo}</div>
               </div>
            `);
         }

         function formatTyreSelection(tyre) {
            return tyre.sn || tyre.text;
         }

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

         // Handle Vehicle Change
         vehicleSelect.on('change', function() {
            const vehicleId = $(this).val();
            const text = $(this).find('option:selected').text();
            document.getElementById('unit_code_display').textContent = vehicleId ? text : '-';

            if (!vehicleId) {
               layoutContainer.innerHTML =
                  '<div class="text-center text-muted p-5 bg-white rounded-4 shadow-sm border w-100"><i class="ri ri-truck-line ri-4x mb-3 d-block opacity-25"></i><p class="mb-0">Pilih Kendaraan untuk memuat layout ban.</p></div>';
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
            fetch(
                  `{{ url('position-info') }}?vehicle_id=${vehicleId}&type=Installation`
               )
               .then(response => response.json())
               .then(data => {
                  positionSelect.empty().append('<option value="">-- Pilih Posisi --</option>');

                  data.positions.forEach(pos => {
                     const node = document.querySelector(
                        `.m-tyre-node[data-position-id="${pos.id}"]`);
                     const isFilled = node && node.classList.contains('filled');
                     const label = isFilled ?
                        `${pos.position_code} - ${pos.position_name} (REPLACE)` :
                        `${pos.position_code} - ${pos.position_name}`;

                     positionSelect.append(
                        `<option value="${pos.id}">${label}</option>`
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

         // Handle Tyre Selection Info
         tyreSelect.on('select2:select', function(e) {
            const data = e.params.data;
            if (data.id) {
               $('#info_brand').text(data.brand);
               $('#info_pattern').text(data.pattern);
               $('#info_size').text(data.size);
               $('#info_otd').text(data.otd || '-');
               $('#info_rtd').text(data.rtd || '-');

               // Auto-fills
               $('#rtd_reading').val(data.rtd || '');

               if (data.location_id) {
                  $('#work_location_id').val(data.location_id).trigger('change');
               }

               if (data.latest_rim_size) {
                  $('input[name="rim_size"]').val(data.latest_rim_size);
               }

               if (data.latest_segment_id) {
                  suggestedSegmentId = data.latest_segment_id;
                  applySuggestedSegment();
               }

               if (data.status) {
                  let condition = 'Spare';
                  if (data.status === 'New') condition = 'New';
                  if (data.status === 'Repaired') condition = 'Repair';
                  $('#install_condition').val(condition).trigger('change');
                  // Optional: disable it to prevent manual change as per user request
                  $('#install_condition').prop('disabled', true);
               }

               $('#tyre_info_display').slideDown();
            } else {
               $('#tyre_info_display').slideUp();
               $('#rtd_reading').val('');
               $('.rtd-input').val('');
               suggestedSegmentId = null;
            }
         });

         // Calculate RTD average automatically
         $(document).on('input', '.rtd-input', function() {
            let total = 0;
            let count = 0;
            $('.rtd-input').each(function() {
               let val = parseFloat($(this).val());
               if (!isNaN(val)) {
                  total += val;
                  count++;
               }
            });
            if (count > 0) {
               $('#rtd_reading').val((total / count).toFixed(2));
            } else {
               $('#rtd_reading').val('');
            }
         });

         tyreSelect.on('select2:unselect', function() {
            $('#tyre_info_display').slideUp();
         });

         // Sync visual click to dropdown
         function attachLayoutEvents() {
            const nodes = document.querySelectorAll('.m-tyre-node');
            nodes.forEach(node => {
               node.addEventListener('click', function() {
                  const posId = this.getAttribute('data-position-id');
                  const isFilled = this.classList.contains('filled');
                  const sn = this.getAttribute('data-sn');

                  if (isFilled) {
                     Swal.fire({
                        title: 'Penggantian Ban?',
                        text: `Posisi ini sudah berisi ban (SN: ${sn}). Lanjutkan untuk melakukan penggantian (Replace)?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Ganti',
                        cancelButtonText: 'Batal',
                        customClass: {
                           confirmButton: 'btn btn-warning me-3',
                           cancelButton: 'btn btn-label-secondary'
                        },
                        buttonsStyling: false
                     }).then((result) => {
                        if (result.isConfirmed) {
                           positionSelect.val(posId).trigger('change');
                        }
                     });
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

            if (posId) {
               const targetNode = document.querySelector(`.m-tyre-node[data-position-id="${posId}"]`);
               if (targetNode) {
                  targetNode.classList.add('selected');
                  // Show Info
                  document.getElementById('info_pos_name').textContent = targetNode.getAttribute('data-name');
                  document.getElementById('info_pos_code').textContent = targetNode.getAttribute('data-code');
                  selectionInfo.style.display = 'block';

                  // Show old tyre info if replaced
                  if (targetNode.classList.contains('filled')) {
                     document.getElementById('old_info_sn').textContent = targetNode.getAttribute('data-sn') || '-';
                     document.getElementById('old_info_brand').textContent = targetNode.getAttribute('data-brand') || '-';
                     document.getElementById('old_info_pattern_size').textContent = (targetNode.getAttribute('data-pattern') || '-') + ' / ' + (targetNode.getAttribute('data-size') || '-');
                     document.getElementById('old_info_date').textContent = targetNode.getAttribute('data-date') || '-';
                     document.getElementById('current_tyre_info').style.display = 'block';
                  } else {
                     document.getElementById('current_tyre_info').style.display = 'none';
                  }
               }
            } else {
               selectionInfo.style.display = 'none';
               document.getElementById('current_tyre_info').style.display = 'none';
            }
         });

         // Form Submission
         document.getElementById('pemasangan_form').addEventListener('submit', function(e) {
            e.preventDefault();
            $('#install_condition').prop('disabled', false);
            const formData = new FormData(this);
            $('#install_condition').prop('disabled', true);
            const posId = positionSelect.val();
            const targetNode = document.querySelector(`.m-tyre-node[data-position-id="${posId}"]`);
            const tyreData = tyreSelect.select2('data')[0];
            const serialNumber = tyreData ? (tyreData.sn || tyreData.text) : '';

            if (!posId || !targetNode) {
               Swal.fire('Peringatan', 'Silakan pilih posisi pemasangan terlebih dahulu.', 'warning');
               return;
            }

            Swal.fire({
               title: 'Konfirmasi Pasang',
               text: `Pasang ban ${serialNumber} pada posisi ${targetNode.getAttribute('data-code')}?`,
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
                  const btn = document.getElementById('btn_submit');
                  btn.disabled = true;
                  btn.innerHTML =
                     '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

                  fetch(`{{ url('tyre-store') }}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                           'Accept': 'application/json',
                           'X-Requested-With': 'XMLHttpRequest'
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
                              text: data.message,
                              timer: 2000
                           }).then(() => {
                              window.location.href = "{{ route('tyre-movement.index') }}";
                           });
                        } else {
                           // This handles both status 422 and status 200 with success: false
                           Swal.fire('Gagal', data.message || 'Terjadi kesalahan sistem', 'error');
                           btn.disabled = false;
                           btn.innerHTML = '<i class="ri ri-save-3-line me-1"></i> Simpan Pemasangan';
                        }
                     })
                     .catch(err => {
                        console.error('Fetch Error:', err);
                        Swal.fire('Error', 'Terjadi kesalahan sistem/jaringan: ' + (err.message || 'Unknown Error'), 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ri ri-save-3-line me-1"></i> Simpan Pemasangan';
                     });
               }
            });
         });
      });
   </script>
@endsection
