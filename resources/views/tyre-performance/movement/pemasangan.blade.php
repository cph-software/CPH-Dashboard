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
               <div class="row align-items-end">
                  <div class="col-md-5 mb-3 mb-md-0">
                     <label class="form-label fw-bold font-size-13" for="vehicle_id">Pilih Unit / Kendaraan</label>
                     <select name="vehicle_id" id="vehicle_id" class="form-select select2"
                        data-placeholder="Cari Unit Kendaraan..." required>
                        <option value="">-- Pilih Unit --</option>
                        @foreach ($kendaraans as $v)
                           <option value="{{ $v->id }}">{{ $v->kode_kendaraan }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-4 mb-3 mb-md-0">
                     <label class="form-label fw-bold font-size-13">Vehicle Type</label>
                     <input type="text" id="vehicle_type_display" class="form-control bg-light" readonly
                        placeholder="Auto-filled">
                  </div>
                  <div class="col-md-3">
                     <div class="d-flex align-items-center justify-content-end">
                        <span class="badge bg-label-primary text-uppercase px-3 py-2">Installation</span>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <div class="row">
            <!-- Left Column: Layout Ban (Visual) -->
            <div class="col-xl-8 col-lg-7">
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
            <div class="col-xl-4 col-lg-5">
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
                        <select name="tyre_id" id="tyre_id" class="form-select select2" data-placeholder="Cari SN Ban..."
                           required>
                           <option value="">-- Cari SN Ban --</option>
                           @foreach ($availableTyres as $t)
                              <option value="{{ $t->id }}" data-brand="{{ $t->brand->brand_name }}"
                                 data-pattern="{{ $t->pattern->name ?? '-' }}" data-size="{{ $t->size->size }}"
                                 data-sn="{{ $t->serial_number }}">
                                 {{ $t->serial_number }}
                              </option>
                           @endforeach
                        </select>
                        <div id="tyre_info_display" class="mt-2" style="display: none;">
                           <div class="d-flex flex-wrap gap-2">
                              <span class="badge bg-white text-dark border"><small id="info_brand"></small></span>
                              <span class="badge bg-white text-dark border"><small id="info_pattern"></small></span>
                              <span class="badge bg-white text-dark border"><small id="info_size"></small></span>
                           </div>
                        </div>
                     </div>

                     <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Operational Segment</label>
                        <select name="operational_segment_id" class="form-select select2"
                           data-placeholder="Select Segment">
                           <option value=""></option>
                           @foreach ($segments as $seg)
                              <option value="{{ $seg->id }}">{{ $seg->segment_name }}</option>
                           @endforeach
                        </select>
                     </div>

                     <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Baut Baru?</label>
                        <div class="form-check form-switch mt-1">
                           <input class="form-check-input" type="checkbox" name="new_bolts_used" id="new_bolts"
                              value="1">
                           <label class="form-check-label" for="new_bolts">Ya, Menggunakan Baut Baru</label>
                        </div>
                     </div>

                     <div class="row g-2 mb-3">
                        <div class="col-6">
                           <label class="form-label fw-bold font-size-13 small">Pressure (PSI)</label>
                           <input type="number" name="psi_reading" class="form-control" placeholder="PSI">
                        </div>
                        <div class="col-6">
                           <label class="form-label fw-bold font-size-13 small">Rim Size</label>
                           <input type="text" name="rim_size" class="form-control" placeholder="Size">
                        </div>
                     </div>

                     <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Odometer & HM</label>
                        <div class="input-group input-group-merge mb-2">
                           <span class="input-group-text small">KM</span>
                           <input type="number" name="odometer" class="form-control" placeholder="Odo">
                        </div>
                        <div class="input-group input-group-merge">
                           <span class="input-group-text small">HM</span>
                           <input type="number" name="hour_meter" class="form-control" placeholder="HM">
                        </div>
                     </div>

                     <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Tyreman</label>
                        <input type="text" name="tyreman_1" class="form-control mb-2" placeholder="Tyreman 1">
                        <input type="text" name="tyreman_2" class="form-control" placeholder="Helper (Optional)">
                     </div>

                     <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Dates & Time</label>
                        <input type="date" name="movement_date" class="form-control mb-2"
                           value="{{ date('Y-m-d') }}">
                        <div class="row g-2">
                           <div class="col-6"><input type="time" name="start_time" class="form-control small"
                                 placeholder="Start"></div>
                           <div class="col-6"><input type="time" name="end_time" class="form-control small"
                                 placeholder="End"></div>
                        </div>
                     </div>

                     <div class="mb-4">
                        <label class="form-label fw-bold font-size-13">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan..."></textarea>
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
      $(document).ready(function() {
         const vehicleSelect = $('#vehicle_id');
         const positionSelect = $('#position_id');
         const tyreSelect = $('#tyre_id');
         const layoutContainer = document.getElementById('layout_container');
         const selectionInfo = document.getElementById('selection_info');

         // Initialize Select2 first
         $('.select2').each(function() {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
               placeholder: $this.data('placeholder'),
               dropdownParent: $(this).parent()
            });
         });

         // Handle Vehicle Change
         vehicleSelect.on('change', function() {
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
            fetch(`{{ url('tyre_performance/movement/vehicle-detail') }}/${vehicleId}`)
               .then(response => response.json())
               .then(data => {
                  $('#vehicle_type_display').val(data.jenis_kendaraan || '-');
               })
               .catch(err => console.error('Error fetching vehicle detail:', err));

            // Load Layout
            layoutContainer.innerHTML = '<div class="spinner-border text-primary"></div>';
            fetch(`{{ url('tyre_performance/movement/layout') }}/${vehicleId}`)
               .then(response => response.text())
               .then(html => {
                  layoutContainer.innerHTML = html;
                  attachLayoutEvents();
               });

            // Load Positions
            fetch(
                  `{{ url('tyre_performance/movement/position-info') }}?vehicle_id=${vehicleId}&type=Installation`
               )
               .then(response => response.json())
               .then(data => {
                  positionSelect.empty().append('<option value="">-- Pilih Posisi --</option>');
                  data.positions.forEach(pos => {
                     positionSelect.append(
                        `<option value="${pos.id}">${pos.position_code} - ${pos.position_name}</option>`
                     );
                  });
                  positionSelect.prop('disabled', false).trigger('change');
               });
         });

         // Handle Tyre Selection Info
         tyreSelect.on('change', function() {
            const selected = $(this).find(':selected');
            if (selected.val()) {
               $('#info_brand').text(selected.data('brand'));
               $('#info_pattern').text(selected.data('pattern'));
               $('#info_size').text(selected.data('size'));
               $('#tyre_info_display').slideDown();
            } else {
               $('#tyre_info_display').slideUp();
            }
         });

         // Sync visual click to dropdown
         function attachLayoutEvents() {
            const nodes = document.querySelectorAll('.m-tyre-node');
            nodes.forEach(node => {
               node.addEventListener('click', function() {
                  const posId = this.getAttribute('data-position-id');
                  if (this.classList.contains('filled')) {
                     Swal.fire('Informasi',
                        'Posisi ini sudah terisi ban. Gunakan Form Pelepasan jika ingin membongkar.',
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
         document.getElementById('pemasangan_form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const posId = positionSelect.val();
            const targetNode = document.querySelector(`.m-tyre-node[data-position-id="${posId}"]`);
            const serialNumber = tyreSelect.find('option:selected').attr('data-sn');

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

                  fetch(`{{ url('tyre_performance/movement/store') }}`, {
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
