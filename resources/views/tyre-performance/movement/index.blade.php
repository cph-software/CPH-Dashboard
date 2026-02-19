@extends('layouts.admin')

@section('title', 'Pergerakan Ban (Eks/Ins)')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

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
            @if (hasPermission('Pemasangan (Install)', 'create'))
               <a href="{{ route('tyre-movement.pemasangan') }}" class="btn btn-primary btn-sm">
                  <i class="ri-add-line me-1"></i> Form Pasang Baru
               </a>
            @endif
            @if (hasPermission('Pelepasan (Remove)', 'create'))
               <a href="{{ route('tyre-movement.pelepasan') }}" class="btn btn-danger btn-sm">
                  <i class="ri-delete-bin-line me-1"></i> Form Lepas Ban
               </a>
            @endif
         </div>
      </div>

      <div class="row">
         <div class="col-12">
            <!-- Vehicle Selection Card -->
            <div class="card mb-4">
               <div class="card-body">
                  <div class="row align-items-center">
                     <div class="col-md-4">
                        <label for="vehicle_select" class="form-label text-uppercase fw-bold">Pilih Unit Kendaraan</label>
                        <select id="vehicle_select" class="form-select select2">
                           <option value="">-- Cari Unit --</option>
                           @foreach ($kendaraans as $unit)
                              <option value="{{ $unit->id }}">{{ $unit->kode_kendaraan }} ({{ $unit->no_polisi }})
                              </option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-md-8">
                        <div id="unit_info" class="d-flex gap-4 mt-3 mt-md-0" style="display: none !important;">
                           <div>
                              <small class="text-muted d-block">Tipe Unit</small>
                              <span id="info_tipe" class="fw-bold fs-5">-</span>
                           </div>
                           <div>
                              <small class="text-muted d-block">Konfigurasi Roda</small>
                              <span id="info_config" class="fw-bold fs-5">-</span>
                           </div>
                           <div class="ms-auto align-self-center">
                              <span class="badge bg-label-success me-2"><i class="ri-checkbox-circle-line me-1"></i>
                                 Terpasang</span>
                              <span class="badge bg-label-secondary"><i class="ri-checkbox-blank-circle-line me-1"></i>
                                 Kosong</span>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Visual Layout Area -->
            <div class="card movement-card mb-4">
               <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                  <h5 class="card-title mb-0">Visualisasi Layout Ban</h5>
                  <div id="layout_loading" class="spinner-border spinner-border-sm text-primary" style="display: none;">
                  </div>
               </div>
               <div class="card-body d-flex align-items-center justify-content-center bg-light" style="min-height: 400px;">
                  <div id="layout_container" class="text-center w-100 py-3">
                     <div class="text-muted">
                        <i class="ri-truck-line ri-4x mb-3 d-block" style="opacity: 0.3"></i>
                        <p class="fs-5">Silakan pilih kendaraan di atas untuk melihat posisi ban.</p>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Transaction History -->
            <div class="card">
               <div class="card-header border-bottom">
                  <h6 class="card-title mb-0">Riwayat Transaksi Terakhir</h6>
               </div>
               <div class="card-body pt-3">
                  <div class="table-responsive">
                     <table class="table table-sm table-hover" id="history_table">
                        <thead>
                           <tr>
                              <th>Tanggal</th>
                              <th>Tipe</th>
                              <th>Unit</th>
                              <th>Posisi</th>
                              <th>SN Ban</th>
                              <th>Kondisi/Kerusakan</th>
                              <th>Action</th>
                           </tr>
                        </thead>
                     </table>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         const vehicleSelect = $('#vehicle_select');
         const layoutContainer = document.getElementById('layout_container');

         $('.select2').each(function() {
            $(this).select2({
               placeholder: $(this).data('placeholder'),
               dropdownParent: $(this).parent()
            });
         });

         vehicleSelect.on('change', function() {
            const vehicleId = this.value;
            if (!vehicleId) {
               document.getElementById('unit_info').style.setProperty('display', 'none', 'important');
               return;
            };

            // Loading state
            document.getElementById('layout_loading').style.display = 'inline-block';
            document.getElementById('unit_info').style.setProperty('display', 'flex', 'important');

            layoutContainer.innerHTML =
               '<div class="py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Sedang memuat layout...</p></div>';

            fetch(`/master_data_tyre/layout/${vehicleId}`)
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
                  const vehicleId = vehicleSelect.val();
                  const positionId = this.getAttribute('data-position-id');
                  const sn = this.getAttribute('data-sn'); // Present if filled

                  if (sn) {
                     // Ban Terpasang -> Arahkan ke Form Lepas (Removal)
                     @if (hasPermission('Pelepasan (Remove)', 'create'))
                        window.location.href =
                           `{{ route('tyre-movement.pelepasan') }}?vehicle_id=${vehicleId}&position_id=${positionId}`;
                     @else
                        Swal.fire('Unauthorized', 'Anda tidak memiliki hak akses untuk Pelepasan Ban.',
                           'error');
                     @endif
                  } else {
                     // Ban Ban Kosong -> Arahkan ke Form Pasang (Installation)
                     @if (hasPermission('Pemasangan (Install)', 'create'))
                        window.location.href =
                           `{{ route('tyre-movement.pemasangan') }}?vehicle_id=${vehicleId}&position_id=${positionId}`;
                     @else
                        Swal.fire('Unauthorized', 'Anda tidak memiliki hak akses untuk Pemasangan Ban.',
                           'error');
                     @endif
                  }
               });
            });
         }

         // Initialize DataTable History
         const historyTable = $('#history_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('tyre-movement.history') }}",
            columns: [{
                  data: 'movement_date',
                  name: 'movement_date'
               },
               {
                  data: 'movement_type',
                  name: 'movement_type',
                  render: function(data, type, row) {
                     let badgeClass = data === 'Installation' ? 'bg-label-primary' : 'bg-label-danger';
                     let typeText = data;

                     if (data === 'Installation' && row.is_replacement) {
                        badgeClass = 'bg-label-warning';
                        typeText = 'Replacement';
                     }

                     let conditionBadge = '';
                     if (row.install_condition) {
                        conditionBadge =
                        `<br><small class="text-muted">${row.install_condition}</small>`;
                     }

                     return `<span class="badge ${badgeClass}">${typeText}</span>${conditionBadge}`;
                  }
               },
               {
                  data: 'vehicle_code',
                  name: 'vehicle_code'
               },
               {
                  data: 'position_name',
                  name: 'position_name'
               },
               {
                  data: 'tyre_sn',
                  name: 'tyre_sn'
               },
               {
                  data: 'failure_info',
                  name: 'failure_info'
               },
               {
                  data: 'action',
                  name: 'action',
                  orderable: false,
                  searchable: false
               }
            ],
            order: [
               [0, 'desc']
            ]
         });

         window.rollbackMovement = function(id) {
            Swal.fire({
               title: 'Konfirmasi Rollback',
               text: 'Anda akan membatalkan transaksi ini. Status ban dan posisi akan dikembalikan ke kondisi sebelum transaksi. Lanjutkan?',
               icon: 'warning',
               showCancelButton: true,
               confirmButtonText: 'Ya, Batalkan!',
               customClass: {
                  confirmButton: 'btn btn-danger me-3',
                  cancelButton: 'btn btn-outline-secondary'
               },
               buttonsStyling: false
            }).then((result) => {
               if (result.isConfirmed) {
                  fetch(`/master_data_tyre/rollback/${id}`, {
                        method: 'DELETE',
                        headers: {
                           'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                     })
                     .then(response => response.json())
                     .then(data => {
                        if (data.success) {
                           Swal.fire('Berhasil!', data.message, 'success');
                           historyTable.ajax.reload();
                           if (vehicleSelect.val()) vehicleSelect.trigger('change');
                        } else {
                           Swal.fire('Gagal!', data.message, 'error');
                        }
                     });
               }
            });
         };
      });
   </script>
@endsection
