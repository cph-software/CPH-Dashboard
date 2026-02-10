@extends('layouts.admin')

@section('title', 'Pergerakan Ban (Eks/Ins)')

@section('page-style')
   <style>
      .movement-card {
         min-height: 500px;
      }

      .status-indicator {
         width: 12px;
         height: 12px;
         border-radius: 50%;
         display: inline-block;
         margin-right: 5px;
      }

      .status-empty {
         background-color: #e0e0e0;
         border: 1px solid #ccc;
      }

      .status-filled {
         background-color: #28c76f;
         border: 1px solid #1eb05d;
      }

      /* Visual Layout Specific for Movement */
      .m-tyre-node {
         cursor: pointer;
         transition: transform 0.2s, background-color 0.2s;
      }

      .m-tyre-node:hover {
         transform: scale(1.1);
         z-index: 10;
      }

      .m-tyre-node.empty {
         background-color: #fff !important;
         border: 2px dashed #ccc !important;
         color: #ccc !important;
      }

      .m-tyre-node.filled {
         background-color: #333 !important;
         color: #fff !important;
      }
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Transaksi /</span> Pergerakan Ban</h4>
         <div class="d-flex gap-2">
            <a href="{{ route('tyre-movement.pemasangan') }}" class="btn btn-primary btn-sm">
               <i class="ri-add-line me-1"></i> Form Pemasangan
            </a>
            <a href="{{ route('tyre-movement.pelepasan') }}" class="btn btn-danger btn-sm">
               <i class="ri-delete-bin-line me-1"></i> Form Pelepasan
            </a>
            <span class="badge bg-label-success d-flex align-items-center"><i class="ri-checkbox-circle-line me-1"></i>
               Terpasang</span>
            <span class="badge bg-label-secondary d-flex align-items-center"><i
                  class="ri-checkbox-blank-circle-line me-1"></i> Kosong</span>
         </div>
      </div>

      <div class="row">
         <!-- Sidebar Selection -->
         <div class="col-md-4">
            <div class="card mb-4">
               <div class="card-body">
                  <label for="vehicle_select" class="form-label text-uppercase fw-bold">Pilih Kendaraan</label>
                  <select id="vehicle_select" class="form-select select2">
                     <option value="">-- Cari Unit --</option>
                     @foreach ($kendaraans as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->kode_kendaraan }} ({{ $unit->no_polisi }})</option>
                     @endforeach
                  </select>
                  <div id="unit_info" class="mt-3" style="display: none;">
                     <hr>
                     <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Tipe Unit:</span>
                        <span id="info_tipe" class="fw-bold">-</span>
                     </div>
                     <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Konfigurasi:</span>
                        <span id="info_config" class="fw-bold">-</span>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Transaction Form (Hidden initially) -->
            <div id="transaction_card" class="card" style="display: none;">
               <div class="card-header border-bottom">
                  <h6 class="card-title mb-0">Form Transaksi</h6>
               </div>
               <div class="card-body pt-4">
                  <form id="movement_form">
                     @csrf
                     <input type="hidden" name="vehicle_id" id="form_vehicle_id">
                     <input type="hidden" name="position_id" id="form_position_id">
                     <input type="hidden" name="movement_type" id="form_movement_type">

                     <div class="mb-3">
                        <label class="form-label">Posisi Terpilih</label>
                        <input type="text" id="display_position" class="form-control bg-light" readonly>
                     </div>

                     <div id="installation_fields" style="display: none;">
                        <div class="mb-3">
                           <label class="form-label">Pilih Ban (SN)</label>
                           <select name="tyre_id" id="tyre_select" class="form-select select2">
                              <option value="">-- Pilih Ban Tersedia --</option>
                           </select>
                        </div>
                     </div>

                     <div id="removal_fields" style="display: none;">
                        <div class="alert alert-info py-2">
                           <small>Ban SN: <strong id="display_sn">-</strong> akan dilepas.</small>
                        </div>
                        <div class="mb-3">
                           <label class="form-label">Status Akhir Ban</label>
                           <select name="target_status" class="form-select">
                              <option value="Repaired">Repair (Gudang)</option>
                              <option value="Scrap">Scrap (Rusak Total)</option>
                              <option value="New">Ready (Kembali ke Stock)</option>
                           </select>
                        </div>
                     </div>

                     <div class="mb-3">
                        <label class="form-label">Tanggal Transaksi</label>
                        <input type="date" name="movement_date" class="form-control" value="{{ date('Y-m-d') }}"
                           required>
                     </div>

                     <div class="mb-3">
                        <label class="form-label">Odometer (KM)</label>
                        <input type="number" name="odometer" class="form-control" placeholder="Opsional">
                     </div>

                     <div class="mb-4">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                     </div>

                     <div class="d-grid mt-2">
                        <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                        <button type="button" class="btn btn-link text-muted mt-2 btn-sm"
                           onclick="resetForm()">Batal</button>
                     </div>
                  </form>
               </div>
            </div>
         </div>

         <!-- Visual Layout Area -->
         <div class="col-md-8">
            <div class="card movement-card">
               <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                  <h5 class="card-title mb-0">Visualisasi Unit</h5>
                  <div id="layout_loading" class="spinner-border spinner-border-sm text-primary" style="display: none;">
                  </div>
               </div>
               <div class="card-body d-flex align-items-center justify-content-center bg-light">
                  <div id="layout_container" class="text-center w-100 py-5">
                     <div class="text-muted">
                        <i class="ri-truck-line ri-4x mb-3 d-block"></i>
                        <p>Silakan pilih kendaraan untuk melihat layout ban.</p>
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
         const vehicleSelect = $('#vehicle_select');
         const layoutContainer = document.getElementById('layout_container');
         const transactionCard = document.getElementById('transaction_card');
         const movementForm = document.getElementById('movement_form');

         vehicleSelect.on('change', function() {
            const vehicleId = this.value;
            if (!vehicleId) return;

            // Loading state
            document.getElementById('layout_loading').style.display = 'inline-block';
            layoutContainer.innerHTML =
               '<div class="py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Memuat layout...</p></div>';
            resetForm();

            fetch(`/tyre_performance/movement/layout/${vehicleId}`)
               .then(response => response.text())
               .then(html => {
                  layoutContainer.innerHTML = html;
                  document.getElementById('layout_loading').style.display = 'none';
                  attachNodeEvents();
               })
               .catch(err => {
                  layoutContainer.innerHTML =
                     '<div class="alert alert-danger mx-4 mt-4">Gagal memuat layout kendaraan.</div>';
                  document.getElementById('layout_loading').style.display = 'none';
               });
         });

         function attachNodeEvents() {
            const nodes = document.querySelectorAll('.m-tyre-node');
            nodes.forEach(node => {
               node.addEventListener('click', function() {
                  nodes.forEach(n => n.style.boxShadow = 'none');
                  this.style.boxShadow = '0 0 0 4px rgba(105, 108, 255, 0.4)';

                  const positionId = this.getAttribute('data-position-id');
                  const vehicleId = vehicleSelect.val();
                  const positionCode = this.getAttribute('data-code');
                  const sn = this.getAttribute('data-sn');

                  openTransactionForm(vehicleId, positionId, positionCode, sn);
               });
            });
         }

         function openTransactionForm(vehicleId, positionId, positionCode, sn) {
            transactionCard.style.display = 'block';
            document.getElementById('form_vehicle_id').value = vehicleId;
            document.getElementById('form_position_id').value = positionId;
            document.getElementById('display_position').value = positionCode;

            if (sn) {
               // Removal
               document.getElementById('form_movement_type').value = 'Removal';
               document.getElementById('installation_fields').style.display = 'none';
               document.getElementById('removal_fields').style.display = 'block';
               document.getElementById('display_sn').textContent = sn;
            } else {
               // Installation
               document.getElementById('form_movement_type').value = 'Installation';
               document.getElementById('installation_fields').style.display = 'block';
               document.getElementById('removal_fields').style.display = 'none';

               // Fetch available tyres
               fetch(`/tyre_performance/movement/position-info?vehicle_id=${vehicleId}&position_id=${positionId}`)
                  .then(response => response.json())
                  .then(data => {
                     const tyreSelect = $('#tyre_select');
                     tyreSelect.empty().append('<option value="">-- Pilih Ban --</option>');
                     data.availableTyres.forEach(tyre => {
                        tyreSelect.append(
                           `<option value="${tyre.id}">${tyre.serial_number} (${tyre.brand.brand_name} - ${tyre.size.size})</option>`
                        );
                     });
                  });
            }
         }

         movementForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            Swal.fire({
               title: 'Konfirmasi Simpan',
               text: 'Apakah data pergerakan ban ini sudah benar?',
               icon: 'question',
               showCancelButton: true,
               confirmButtonText: 'Ya, Simpan',
               cancelButtonText: 'Batal'
            }).then((result) => {
               if (result.isConfirmed) {
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
                           Swal.fire('Berhasil!', data.message, 'success');
                           vehicleSelect.trigger('change');
                        } else {
                           Swal.fire('Gagal!', data.message, 'error');
                        }
                     });
               }
            });
         });
      });

      function resetForm() {
         document.getElementById('transaction_card').style.display = 'none';
         document.getElementById('movement_form').reset();
      }
   </script>
@endsection
