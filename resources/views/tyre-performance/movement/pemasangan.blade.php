@extends('layouts.admin')

@section('title', 'Form Pemasangan Ban')

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

      <div class="row">
         <!-- Main Form -->
         <div class="col-xl-7 col-lg-6">
            <div class="card mb-4 shadow-sm">
               <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom">
                  <div>
                     <h5 class="mb-0 fw-bold">Form Pemasangan Ban</h5>
                     <small class="text-muted">Input transaksi pemasangan ban baru/repair ke unit</small>
                  </div>
                  <span class="badge bg-label-primary text-uppercase">Installation</span>
               </div>
               <div class="card-body pt-4">
                  <form id="pemasangan_form">
                     @csrf
                     <input type="hidden" name="movement_type" value="Installation">

                     <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                           <label class="form-label fw-bold font-size-13" for="vehicle_id">1. Pilih Unit /
                              Kendaraan</label>
                           <select name="vehicle_id" id="vehicle_id" class="form-select select2"
                              data-placeholder="Cari Unit Kendaraan..." required>
                              <option value="">-- Pilih Unit --</option>
                              @foreach ($kendaraans as $v)
                                 <option value="{{ $v->id }}">{{ $v->kode_kendaraan }} - {{ $v->no_polisi }}
                                 </option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-12 mb-3">
                           <label class="form-label fw-bold font-size-13" for="position_id">2. Tentukan Posisi
                              Pasang</label>
                           <select name="position_id" id="position_id" class="form-select select2"
                              data-placeholder="Pilih Posisi (atau klik pada layout)" required disabled>
                              <option value="">-- Pilih Posisi --</option>
                           </select>
                           <div class="form-text">Anda juga bisa memilih langsung pada <strong>Visual Layout</strong> di
                              samping.</div>
                        </div>
                     </div>

                     <div class="mb-4 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold font-size-13" for="tyre_id">3. Pilih Ban (Serial
                           Number)</label>
                        <select name="tyre_id" id="tyre_id" class="form-select select2"
                           data-placeholder="Ketik SN Ban..." required>
                           <option value="">-- Cari SN Ban --</option>
                           @foreach ($availableTyres as $t)
                              <option value="{{ $t->id }}" data-sn="{{ $t->serial_number }}">
                                 {{ $t->serial_number }} | {{ $t->brand->brand_name }} - {{ $t->size->size }}
                                 ({{ $t->status }})
                              </option>
                           @endforeach
                        </select>
                        <div class="form-text mt-2">Hanya ban berstatus <strong>New</strong> atau
                           <strong>Repaired</strong>.
                        </div>
                     </div>

                     <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                           <label class="form-label fw-bold font-size-13" for="movement_date">Tanggal Pasang</label>
                           <input type="date" name="movement_date" id="movement_date" class="form-control"
                              value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                           <label class="form-label fw-bold font-size-13" for="odometer">Odometer KM</label>
                           <div class="input-group">
                              <input type="number" name="odometer" id="odometer" class="form-control"
                                 placeholder="KM Unit saat pasang">
                              <span class="input-group-text">KM</span>
                           </div>
                        </div>
                     </div>

                     <div class="mb-4">
                        <label class="form-label fw-bold font-size-13" for="notes">Catatan Transaksi</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2"
                           placeholder="Contoh: Tekanan ban awal 110 PSI..."></textarea>
                     </div>

                     <div class="d-flex justify-content-end gap-3 mt-4">
                        <a href="{{ route('tyre-movement.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-5 shadow" id="btn_submit">
                           <i class="ri-check-line me-1"></i> Simpan Pemasangan
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
                  <button class="btn btn-sm btn-label-primary px-2 py-0" id="unit_code_display">-</button>
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
                  style="display: none; background: linear-gradient(135deg, #7367f0 0%, #a098f5 100%); color: white;">
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
         const tyreSelect = $('#tyre_id');
         const layoutContainer = document.getElementById('layout_container');
         const selectionInfo = document.getElementById('selection_info');

         // Handle Vehicle Change
         vehicleSelect.on('change', function() {
            const vehicleId = $(this).val();
            const text = $(this).find('option:selected').text();
            document.getElementById('unit_code_display').textContent = vehicleId ? text.split(' - ')[0] :
               '-';

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
            fetch(`/tyre_performance/movement/position-info?vehicle_id=${vehicleId}&type=Installation`)
               .then(response => response.json())
               .then(data => {
                  positionSelect.empty().append('<option value="">-- Pilih Posisi --</option>');
                  data.positions.forEach(pos => {
                     positionSelect.append(
                        `<option value="${pos.id}">${pos.position_code} - ${pos.position_name}</option>`
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
            } else {
               selectionInfo.style.display = 'none';
            }
         });

         // Form Submission with Animation
         document.getElementById('pemasangan_form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const posId = positionSelect.val();
            const targetNode = document.querySelector(`.m-tyre-node[data-position-id="${posId}"]`);
            const serialNumber = tyreSelect.find('option:selected').attr('data-sn');

            Swal.fire({
               title: 'Konfirmasi Pasang',
               text: `Pasang ban ${serialNumber} pada posisi ${targetNode.getAttribute('data-code')}?`,
               icon: 'question',
               showCancelButton: true,
               confirmButtonText: 'Ya, Pasang SEKARANG',
               cancelButtonColor: '#7367f0'
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
                           targetNode.classList.add('tyre-appearing', 'filled');
                           targetNode.classList.remove('empty');

                           Swal.fire('Berhasil!', data.message, 'success').then(() => {
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
