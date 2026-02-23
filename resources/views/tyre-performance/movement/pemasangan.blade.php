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
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Transaksi /</span> Pemasangan Ban</h4>

      <form id="pemasangan_form">
         @csrf
         <input type="hidden" name="movement_type" value="Installation">

         <!-- Top Row: Identifikasi Unit -->
         <div class="card mb-4 shadow-sm">
            <div class="card-header bg-transparent border-bottom">
               <h5 class="mb-0 fw-bold"><i class="ri-truck-line me-2"></i>Identifikasi Unit</h5>
            </div>
            <div class="card-body pt-3">
               <div class="row">
                  <div class="col-md-4 mb-3">
                     <label class="form-label fw-bold font-size-13" for="vehicle_id">Pilih Unit / Kendaraan</label>
                     <select name="vehicle_id" id="vehicle_id" class="form-select select2"
                        data-placeholder="Cari Unit Kendaraan..." required>
                        <option value="">-- Pilih Unit --</option>
                        @foreach ($kendaraans as $v)
                           <option value="{{ $v->id }}">{{ $v->kode_kendaraan }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-3 mb-3">
                     <label class="form-label fw-bold font-size-13">Tanggal Pemasangan</label>
                     <input type="date" name="movement_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                  </div>
                  <div class="col-md-3 mb-3">
                     <label class="form-label fw-bold font-size-13">KM Saat Pasang</label>
                     <input type="number" name="odometer" class="form-control" placeholder="Odometer">
                  </div>
                  <div class="col-md-2 mb-3">
                     <label class="form-label fw-bold font-size-13">HM Saat Pasang</label>
                     <input type="number" name="hour_meter" class="form-control" placeholder="Hour Meter">
                  </div>
                  <div class="col-md-4 mb-3">
                     <label class="form-label fw-bold font-size-13">Vehicle Type</label>
                     <input type="text" id="vehicle_type_display" class="form-control bg-light" readonly
                        placeholder="Auto-filled">
                  </div>
                  <div class="col-md-8 pt-2">
                     <div class="d-flex align-items-center justify-content-end h-100">
                        <span class="badge bg-label-primary text-uppercase px-3 py-2">Installation</span>
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
                     style="display: none; background: linear-gradient(135deg, #7367f0 0%, #a098f5 100%); color: white;">
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

            <!-- Right Column: Detail Tyre & Petugas -->
            <div class="col-xl-6 col-lg-4">
               <div class="card mb-4 shadow-sm">
                  <div class="card-header bg-transparent border-bottom">
                     <h6 class="mb-0 fw-bold"><i class="ri-list-settings-line me-2"></i>Detail Pemasangan</h6>
                  </div>
                  <div class="card-body pt-3">
                     <!-- Position selection dropdown also here for sync -->
                     <div class="mb-3">
                        <label class="form-label fw-bold font-size-13" for="position_id">Posisi Pasang</label>
                        <select name="position_id" id="position_id" class="form-select select2"
                           data-placeholder="Pilih Posisi..." required disabled>
                           <option value="">-- Pilih Posisi --</option>
                        </select>
                     </div>

                     <div class="mb-3 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold font-size-13" for="tyre_id">Pilih Ban (SN)</label>
                        <select name="tyre_id" id="tyre_id" class="form-select" data-placeholder="Cari SN Ban..." required>
                           <option value="">-- Cari SN Ban --</option>
                        </select>
                        <div id="tyre_info_display" class="mt-2" style="display: none;">
                           <div class="d-flex flex-wrap gap-2">
                              <span class="badge bg-white text-dark border"><small id="info_brand"></small></span>
                              <span class="badge bg-white text-dark border"><small id="info_pattern"></small></span>
                              <span class="badge bg-white text-dark border"><small id="info_size"></small></span>
                           </div>
                           <div class="d-flex flex-wrap gap-2 mt-2">
                              <span class="badge bg-label-info"><i class="ri-ruler-line me-1"></i>OTD: <strong
                                    id="info_otd">-</strong> mm</span>
                              <span class="badge bg-label-warning"><i class="ri-ruler-line me-1"></i>RTD: <strong
                                    id="info_rtd">-</strong> mm</span>
                           </div>
                        </div>
                     </div>

                     <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Kondisi Ban Saat Pasang</label>
                        <select name="install_condition" id="install_condition" class="form-select select2"
                           data-placeholder="Pilih Kondisi..." required>
                           <option value=""></option>
                           <option value="New">New (Baru)</option>
                           <option value="Spare">Spare (Bekas/Cadangan)</option>
                           <option value="Repair">Repair (Hasil Perbaikan/Vulkanisir)</option>
                        </select>
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
                        <label class="form-label fw-bold font-size-13">Penggunaan Baut Baru</label>
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
                                 <input type="number" name="new_bolts_quantity" class="form-control border-primary"
                                    placeholder="Qty" style="width: 80px;">
                              </div>
                           </div>
                        </div>
                     </div>

                     <div class="row g-2 mb-3">
                        <div class="col-4">
                           <label class="form-label fw-bold font-size-13 small">Pressure (PSI)</label>
                           <input type="number" name="psi_reading" class="form-control" placeholder="PSI">
                        </div>
                        <div class="col-4">
                           <label class="form-label fw-bold font-size-13 small">RTD (mm)</label>
                           <input type="number" name="rtd_reading" id="rtd_reading" class="form-control" placeholder="RTD"
                              step="0.01">
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
                        <button type="submit" class="btn btn-primary shadow" id="btn_submit">
                           <i class="ri-check-line me-1"></i> Simpan Pemasangan
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
         const tyreSelect = $('#tyre_id');
         const layoutContainer = document.getElementById('layout_container');
         const selectionInfo = document.getElementById('selection_info');
         let suggestedSegmentId = null; // Store suggested segment from tyre history

         // Initialize Select2 first
         $('.select2').each(function () {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
               placeholder: $this.data('placeholder'),
               dropdownParent: $(this).parent()
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
               data: function (params) {
                  return {
                     q: params.term
                  };
               },
               processResults: function (data) {
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
         tyreSelect.on('select2:open', function () {
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
            fetch(`{{ url('master_data_tyre/segments') }}/${locId}`)
               .then(response => response.json())
               .then(data => {
                  data.forEach(seg => {
                     segmentSelect.append(`<option value="${seg.id}">${seg.segment_name}</option>`);
                  });

                  // Auto-select segment if suggested via tyre selection
                  if (suggestedSegmentId) {
                     segmentSelect.val(suggestedSegmentId);
                     if (!segmentSelect.val()) {
                        suggestedSegmentId = null;
                     }
                  }

                  segmentSelect.trigger('change');
               });
         });

         // Handle Vehicle Change
         vehicleSelect.on('change', function () {
            const vehicleId = $(this).val();
            const text = $(this).find('option:selected').text();
            document.getElementById('unit_code_display').textContent = vehicleId ? text : '-';

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
            fetch(`{{ url('master_data_tyre/vehicle-detail') }}/${vehicleId}`)
               .then(response => response.json())
               .then(data => {
                  $('#vehicle_type_display').val(data.jenis_kendaraan || '-');
               })
               .catch(err => console.error('Error fetching vehicle detail:', err));

            // Load Layout
            layoutContainer.innerHTML = '<div class="spinner-border text-primary"></div>';
            fetch(`{{ url('master_data_tyre/layout') }}/${vehicleId}`)
               .then(response => response.text())
               .then(html => {
                  layoutContainer.innerHTML = html;
                  attachLayoutEvents();
               });

            // Load Positions
            fetch(
               `{{ url('master_data_tyre/position-info') }}?vehicle_id=${vehicleId}&type=Installation`
            )
               .then(response => response.json())
               .then(data => {
                  positionSelect.empty().append('<option value="">-- Pilih Posisi --</option>');

                  // We also need to know which positions are occupied to label them
                  // Let's fetch the visual layout first and check filled nodes, 
                  // or better, rely on the position data if it includes occupation info
                  data.positions.forEach(pos => {
                     // Find if this position is currently occupied in the visual layout
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
            // Create option if not exists (though it should be in the list)
            if (vehicleSelect.find("option[value='" + preVehicleId + "']").length) {
               vehicleSelect.val(preVehicleId).trigger('change');
            }
         }

         // Handle Tyre Selection Info
         tyreSelect.on('select2:select', function (e) {
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
                  // If location already has segments loaded, try selecting
                  const currentSegments = $('#operational_segment_id option').length;
                  if (currentSegments > 1) {
                     $('#operational_segment_id').val(suggestedSegmentId).trigger('change');
                  }
               }

               $('#tyre_info_display').slideDown();
            } else {
               $('#tyre_info_display').slideUp();
               $('#rtd_reading').val('');
               $('input[name="rim_size"]').val('');
               suggestedSegmentId = null;
            }
         });

         tyreSelect.on('select2:unselect', function () {
            $('#tyre_info_display').slideUp();
         });

         // Sync visual click to dropdown
         function attachLayoutEvents() {
            const nodes = document.querySelectorAll('.m-tyre-node');
            nodes.forEach(node => {
               node.addEventListener('click', function () {
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
         positionSelect.on('change', function () {
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
               }
            } else {
               selectionInfo.style.display = 'none';
            }
         });

         // Form Submission
         document.getElementById('pemasangan_form').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
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

                  fetch(`{{ url('master_data_tyre/store') }}`, {
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
                              text: data.message,
                              timer: 2000
                           }).then(() => {
                              window.location.href = "{{ route('tyre-movement.index') }}";
                           });
                        } else {
                           Swal.fire('Gagal', data.message, 'error');
                           btn.disabled = false;
                           btn.innerHTML = '<i class="ri-check-line me-1"></i> Simpan Pemasangan';
                        }
                     });
               }
            });
         });
      });
   </script>
@endsection