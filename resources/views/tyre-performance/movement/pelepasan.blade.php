@extends('layouts.admin')

@section('title', 'Form Pelepasan Ban')

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

      <div class="row">
         <!-- Main Form -->
         <div class="col-xl-7 col-lg-6">
            <div class="card mb-4 shadow-sm">
               <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom">
                  <div>
                     <h5 class="mb-0 fw-bold text-danger">Form Pelepasan Ban (Removal)</h5>
                     <small class="text-muted">Pelepasan ban dari unit untuk repair/scrap/stock</small>
                  </div>
                  <span class="badge bg-label-danger text-uppercase">Removal</span>
               </div>
               <div class="card-body pt-4">
                  <form id="pelepasan_form">
                     @csrf
                     <input type="hidden" name="movement_type" value="Removal">

                     <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                           <label class="form-label fw-bold font-size-13" for="vehicle_id">1. Cari Unit /
                              Kendaraan</label>
                           <select name="vehicle_id" id="vehicle_id" class="form-select select2"
                              data-placeholder="Pilih Unit..." required>
                              <option value="">-- Pilih Unit --</option>
                              @foreach ($kendaraans as $v)
                                 <option value="{{ $v->id }}">{{ $v->kode_kendaraan }} - {{ $v->no_polisi }}
                                 </option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-12 mb-3">
                           <label class="form-label fw-bold font-size-13" for="position_id">2. Pilih Posisi Ban (yang akan
                              dilepas)</label>
                           <select name="position_id" id="position_id" class="form-select select2"
                              data-placeholder="Pilih Posisi (atau klik pada layout)" required disabled>
                              <option value="">-- Pilih Posisi --</option>
                           </select>
                           <div class="form-text">Pastikan Anda memilih posisi yang benar pada <strong>Visual
                                 Layout</strong>.</div>
                        </div>
                     </div>

                     <!-- Current Tyre Display (Auto-fill on position select) -->
                     <div id="current_tyre_info" class="p-4 mb-4 rounded-4 border-start border-danger border-5 shadow-sm"
                        style="display: none; background: #fffcf0;">
                        <h6 class="mb-3 fw-bold text-muted text-uppercase small">Detail Ban Saat Ini</h6>
                        <div class="row">
                           <div class="col-md-6 mb-3">
                              <small class="text-muted d-block">Nomor Seri (Serial Number)</small>
                              <strong id="info_sn" class="fs-4 text-dark">-</strong>
                           </div>
                           <div class="col-md-6 mb-3">
                              <small class="text-muted d-block">Brand / Ukuran Ban</small>
                              <span id="info_brand_size" class="fw-bold">-</span>
                           </div>
                           <div class="col-md-6">
                              <small class="text-muted d-block">Lama Terpasang (Hari)</small>
                              <span class="badge bg-label-info mt-1" id="info_installed_days">Calculated on save</span>
                           </div>
                        </div>
                     </div>

                     <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                           <label class="form-label fw-bold font-size-13" for="failure_code_id">3. Alasan Pelepasan
                              (Failure
                              Code)</label>
                           <select name="failure_code_id" id="failure_code_id" class="form-select select2"
                              data-placeholder="Kenapa dilepas?">
                              <option value="">-- Pilih Alasan --</option>
                              @foreach ($failureCodes as $fc)
                                 <option value="{{ $fc->id }}">{{ $fc->failure_code }} - {{ $fc->failure_name }}
                                 </option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-6 mb-3">
                           <label class="form-label fw-bold font-size-13" for="target_status">Status Akhir Ban</label>
                           <select name="target_status" id="target_status" class="form-select" required>
                              <option value="Repaired">REPAIR (Butuh Perbaikan)</option>
                              <option value="Scrap">SCRAP (Rusak Total / Afkir)</option>
                              <option value="New">STOCK (Bagus / Pindah Unit)</option>
                           </select>
                        </div>
                     </div>

                     <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                           <label class="form-label fw-bold font-size-13" for="movement_date">Tanggal Lepas</label>
                           <input type="date" name="movement_date" id="movement_date" class="form-control"
                              value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                           <label class="form-label fw-bold font-size-13" for="odometer">Odometer KM</label>
                           <div class="input-group">
                              <input type="number" name="odometer" id="odometer" class="form-control"
                                 placeholder="KM Unit saat lepas">
                              <span class="input-group-text">KM</span>
                           </div>
                        </div>
                     </div>

                     <div class="mb-4">
                        <label class="form-label fw-bold font-size-13" for="notes">Analisa Awal / Keterangan</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2"
                           placeholder="Jelaskan kondisi ban saat dilepas..."></textarea>
                     </div>

                     <div class="d-flex justify-content-end gap-3 mt-4">
                        <a href="{{ route('tyre-movement.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                        <button type="submit" class="btn btn-danger px-5 shadow" id="btn_submit">
                           <i class="ri-delete-bin-line me-1"></i> Proses Pelepasan
                        </button>
                     </div>
                  </form>
               </div>
            </div>
         </div>

         <!-- Visual Preview Side -->
         <div class="col-xl-5 col-lg-6">
            <div class="card h-100 shadow-sm border-0 bg-transparent">
               <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                  <h6 class="mb-0 fw-bold">Visual Preview</h6>
                  <button class="btn btn-sm btn-label-danger px-2 py-0" id="unit_code_display">-</button>
               </div>
               <div class="card-body d-flex flex-column align-items-center justify-content-center p-0">
                  <div id="layout_container" class="w-100 h-100 d-flex align-items-center justify-content-center p-4">
                     <div class="text-center text-muted p-5 bg-white rounded-4 shadow-sm border w-100">
                        <i class="ri-truck-line ri-4x mb-3 d-block opacity-25"></i>
                        <p class="mb-0">Pilih Kendaraan untuk memuat layout ban.</p>
                     </div>
                  </div>
               </div>

               {{-- Quick Info Card --}}
               <div id="selection_info" class="card mx-4 mb-4 border-0 shadow-sm"
                  style="display: none; background: linear-gradient(135deg, #ea5455 0%, #feb1b2 100%); color: white;">
                  <div class="card-body p-3">
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
         </div>
      </div>
   </div>
@endsection

@section('page-script')
   <script>
      document.addEventListener('DOMContentLoaded', function() {
         const vehicleSelect = $('#vehicle_id');
         const positionSelect = $('#position_id');
         const infoArea = document.getElementById('current_tyre_info');
         const layoutContainer = document.getElementById('layout_container');
         const selectionInfo = document.getElementById('selection_info');
         let assignedTyres = {};

         vehicleSelect.on('change', function() {
            const vehicleId = $(this).val();
            const text = $(this).find('option:selected').text();
            document.getElementById('unit_code_display').textContent = vehicleId ? text.split(' - ')[0] :
               '-';
            infoArea.style.display = 'none';

            if (!vehicleId) {
               layoutContainer.innerHTML =
                  '<div class="text-center text-muted p-5 bg-white rounded-4 shadow-sm border w-100"><i class="ri-truck-line ri-4x mb-3 d-block opacity-25"></i><p class="mb-0">Pilih Kendaraan untuk memuat layout ban.</p></div>';
               positionSelect.empty().append('<option value="">-- Pilih Posisi --</option>').prop('disabled',
                  true);
               selectionInfo.style.display = 'none';
               return;
            }

            // Load Layout
            layoutContainer.innerHTML = '<div class="spinner-border text-primary"></div>';
            fetch(`/tyre_performance/movement/layout/${vehicleId}`)
               .then(response => response.text())
               .then(html => {
                  layoutContainer.innerHTML = html;
                  attachLayoutEvents();
               });

            // Load Positions
            fetch(`/tyre_performance/movement/position-info?vehicle_id=${vehicleId}&type=Removal`)
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
                  targetNode.scrollIntoView({
                     behavior: 'smooth',
                     block: 'center'
                  });

                  // Show Info
                  document.getElementById('info_pos_name').textContent = targetNode.getAttribute(
                     'data-name');
                  document.getElementById('info_pos_code').textContent = targetNode.getAttribute(
                     'data-code');
                  selectionInfo.style.display = 'block';
               }

               // Update current tyre display
               document.getElementById('info_sn').textContent = tyre.serial_number;
               document.getElementById('info_brand_size').textContent =
                  `${tyre.brand.brand_name} / ${tyre.size.size}`;
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

            Swal.fire({
               title: 'Konfirmasi Pelepasan',
               text: 'Apakah Anda yakin ingin melepas ban ini dari unit?',
               icon: 'warning',
               showCancelButton: true,
               confirmButtonColor: '#ea5455',
               confirmButtonText: 'Ya, Lepas!',
               cancelButtonText: 'Batal'
            }).then((result) => {
               if (result.isConfirmed) {
                  const btn = document.getElementById('btn_submit');
                  btn.disabled = true;
                  btn.innerHTML =
                     '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

                  fetch('/tyre_performance/movement/store', {
                        method: 'POST',
                        body: formData,
                        headers: {
                           'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                     })
                     .then(response => response.json())
                     .then(data => {
                        if (data.success) {
                           // Animation
                           targetNode.classList.add('tyre-disappearing');
                           targetNode.classList.remove('filled');
                           targetNode.classList.add('empty');

                           Swal.fire('Berhasil', 'Ban berhasil dilepas dari unit', 'success').then(
                              () => {
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
