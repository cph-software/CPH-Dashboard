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
               <h5 class="mb-0 fw-bold text-danger"><i class="ri-truck-line me-2"></i>Identifikasi Unit (Pelepasan)</h5>
            </div>
            <div class="card-body pt-3">
               <div class="row align-items-end">
                  <div class="col-md-5 mb-3 mb-md-0">
                     <label class="form-label fw-bold font-size-13" for="vehicle_id">Pilih Unit / Kendaraan</label>
                     <select name="vehicle_id" id="vehicle_id" class="form-select select2" data-placeholder="Pilih Unit..."
                        required>
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
                        <span class="badge bg-label-danger text-uppercase px-3 py-2">Removal</span>
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
            <div class="col-xl-4 col-lg-5">
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

                     <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Alasan (Failure Code)</label>
                        <select name="failure_code_id" id="failure_code_id" class="form-select select2"
                           data-placeholder="Kenapa dilepas?">
                           <option value="">-- Pilih Alasan --</option>
                           @foreach ($failureCodes as $fc)
                              <option value="{{ $fc->id }}">{{ $fc->failure_code }} - {{ $fc->failure_name }}
                              </option>
                           @endforeach
                        </select>
                     </div>

                     <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Status Akhir Ban</label>
                        <select name="target_status" id="target_status" class="form-select" required>
                           <option value="Repaired">REPAIR (Butuh Perbaikan)</option>
                           <option value="Scrap">SCRAP (Rusak Total / Afkir)</option>
                           <option value="New">STOCK (Bagus / Pindah Unit)</option>
                        </select>
                     </div>

                     <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Tyreman</label>
                        <input type="text" name="tyreman_1" class="form-control mb-2" placeholder="Tyreman 1">
                        <input type="text" name="tyreman_2" class="form-control" placeholder="Helper (Optional)">
                     </div>

                     <div class="row g-2 mb-3">
                        <div class="col-6">
                           <label class="form-label fw-bold font-size-13 small">Pressure (PSI)</label>
                           <input type="number" name="psi_reading" class="form-control" placeholder="PSI">
                        </div>
                        <div class="col-6">
                           <label class="form-label fw-bold font-size-13 small">Odometer (KM)</label>
                           <input type="number" name="odometer" class="form-control" placeholder="KM">
                        </div>
                     </div>

                     <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Dates & Time</label>
                        <input type="date" name="movement_date" class="form-control mb-2"
                           value="{{ date('Y-m-d') }}">
                        <div class="row g-2">
                           <div class="col-6"><input type="time" name="start_time" class="form-control small">
                           </div>
                           <div class="col-6"><input type="time" name="end_time" class="form-control small"></div>
                        </div>
                     </div>

                     <div class="mb-4">
                        <label class="form-label fw-bold font-size-13">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan..."></textarea>
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
      $(document).ready(function() {
         const vehicleSelect = $('#vehicle_id');
         const positionSelect = $('#position_id');
         const infoArea = document.getElementById('current_tyre_info');
         const layoutContainer = document.getElementById('layout_container');
         const selectionInfo = document.getElementById('selection_info');
         let assignedTyres = {};

         // Initialize Select2
         $('.select2').each(function() {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
               placeholder: $this.data('placeholder'),
               dropdownParent: $(this).parent()
            });
         });

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

            // Fetch Vehicle Detail
            fetch(`{{ url('tyre_performance/movement/vehicle-detail') }}/${vehicleId}`)
               .then(response => response.json())
               .then(data => {
                  $('#vehicle_type_display').val(data.jenis_kendaraan || '-');
               });

            // Load Layout
            layoutContainer.innerHTML = '<div class="spinner-border text-primary"></div>';
            fetch(`{{ url('tyre_performance/movement/layout') }}/${vehicleId}`)
               .then(response => response.text())
               .then(html => {
                  layoutContainer.innerHTML = html;
                  attachLayoutEvents();
               });

            // Load Positions
            fetch(`{{ url('tyre_performance/movement/position-info') }}?vehicle_id=${vehicleId}&type=Removal`)
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
                  positionSelect.prop('disabled', false).trigger('change');
               });
         });

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
               infoArea.style.display = 'block';
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
