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
            <a href="{{ route('master_data.export', ['type' => 'movements', 'format' => 'excel']) }}"
               class="btn btn-outline-primary btn-sm">
               <i class="ri-file-excel-2-line me-1"></i> Export Excel
            </a>

            @if (hasPermission('Pemasangan (Install)', 'create'))
               <a href="{{ route('tyre-movement.pemasangan') }}" class="btn btn-primary btn-sm">
                  <i class="ri-add-line me-1"></i> Form Pasang Baru
               </a>
            @endif
            @if (hasPermission('Rotasi (Rotate)', 'create'))
               <a href="{{ route('tyre-movement.rotasi') }}" class="btn btn-info btn-sm">
                  <i class="ri-arrow-left-right-line me-1"></i> Form Rotasi
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
                           <div class="ms-auto align-self-center text-end">
                              <small class="text-muted d-block mb-1">Legenda Status:</small>
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

   <!-- TYRE DETAIL OFFCANVAS -->
   <div class="offcanvas offcanvas-end" tabindex="-1" id="tyreDetailPanel" style="width: 480px;">
      <div class="offcanvas-header border-bottom bg-light py-3">
         <h5 class="offcanvas-title fw-bold mb-0">
            <i class="ri-circle-fill text-success me-2" style="font-size: 10px;"></i>
            <span id="td_title">Detail Ban</span>
         </h5>
         <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body p-0" id="td_body">
         <!-- Content loaded dynamically -->
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
         let currentVehicleId = null;

         $('.select2').each(function() {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
               placeholder: $this.data('placeholder'),
               dropdownParent: $(this).parent()
            });
         });

         vehicleSelect.on('change', function() {
            const vehicleId = this.value;
            currentVehicleId = vehicleId;
            if (!vehicleId) {
               document.getElementById('unit_info').style.setProperty('display', 'none', 'important');
               return;
            };

            // Loading state
            document.getElementById('layout_loading').style.display = 'inline-block';
            document.getElementById('unit_info').style.setProperty('display', 'flex', 'important');

            layoutContainer.innerHTML =
               '<div class="py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Sedang memuat layout...</p></div>';

            fetch(`/layout/${vehicleId}`)
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

            // Fetch Vehicle Detail for info card
            fetch(`/vehicle-detail/${vehicleId}`)
               .then(response => response.json())
               .then(data => {
                  document.getElementById('info_tipe').textContent = data.vehicle?.jenis_kendaraan || '-';
                  document.getElementById('info_config').textContent = data.vehicle
                     ?.tyre_position_configuration ?
                     data.vehicle.tyre_position_configuration.name : '-';
               })
               .catch(err => console.error('Error fetching vehicle detail:', err));
         });

         function attachNodeEvents() {
            const nodes = document.querySelectorAll('.m-tyre-node');
            nodes.forEach(node => {
               node.addEventListener('click', function() {
                  const positionId = this.getAttribute('data-position-id');
                  const sn = this.getAttribute('data-sn');

                  if (!sn) {
                     // Posisi kosong → toast info
                     Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: 'Posisi ini kosong',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                     });
                     return;
                  }

                  // Fetch tyre detail and show offcanvas
                  showTyreDetail(positionId);
               });
            });
         }

         function showTyreDetail(positionId) {
            const body = document.getElementById('td_body');
            body.innerHTML = `
               <div class="d-flex align-items-center justify-content-center" style="min-height: 300px;">
                  <div class="text-center">
                     <div class="spinner-border text-primary mb-3"></div>
                     <p class="text-muted">Memuat data ban...</p>
                  </div>
               </div>`;

            const panel = new bootstrap.Offcanvas(document.getElementById('tyreDetailPanel'));
            panel.show();

            fetch(`/tyre-detail?position_id=${positionId}&vehicle_id=${currentVehicleId}`)
               .then(r => r.json())
               .then(data => {
                  if (!data.success) {
                     body.innerHTML = `<div class="alert alert-warning m-4">${data.message}</div>`;
                     return;
                  }

                  const t = data.tyre;
                  const m = data.movements;

                  // Status badge color
                  const statusColors = {
                     'Installed': 'success',
                     'New': 'primary',
                     'Repaired': 'warning',
                     'Scrap': 'danger'
                  };
                  const statusColor = statusColors[t.status] || 'secondary';

                  // Movement type badge
                  const movBadge = (type) => {
                     const c = {
                        'Installation': 'primary',
                        'Removal': 'danger',
                        'Rotation': 'info'
                     };
                     return `bg-label-${c[type] || 'secondary'}`;
                  };

                  // RTD Progress bar
                  let rtdBar = '';
                  if (t.rtd_wear_pct !== null) {
                     const barColor = t.rtd_wear_pct > 75 ? 'danger' : (t.rtd_wear_pct > 50 ? 'warning' :
                        'success');
                     rtdBar = `
                        <div class="mt-2">
                           <div class="d-flex justify-content-between mb-1">
                              <small class="fw-bold">RTD Wear</small>
                              <small class="fw-bold text-${barColor}">${t.rtd_wear_pct}%</small>
                           </div>
                           <div class="progress" style="height: 8px;">
                              <div class="progress-bar bg-${barColor}" style="width: ${t.rtd_wear_pct}%"></div>
                           </div>
                           <div class="d-flex justify-content-between mt-1">
                              <small class="text-muted">OTD: ${t.initial_rtd ?? '-'}mm</small>
                              <small class="text-muted">Now: ${t.current_rtd ?? '-'}mm</small>
                           </div>
                        </div>`;
                  }

                  // Movement history rows
                  let movRows = '';
                  if (m.length > 0) {
                     m.forEach(mv => {
                        movRows += `
                        <tr>
                           <td><small>${mv.date}</small></td>
                           <td><span class="badge ${movBadge(mv.type_raw)} badge-sm">${mv.type}</span></td>
                           <td class="text-end"><small>${(mv.running_km || 0).toLocaleString()}</small></td>
                           <td class="text-end"><small>${mv.rtd ?? '-'}</small></td>
                        </tr>`;
                     });
                  } else {
                     movRows =
                        '<tr><td colspan="4" class="text-center text-muted py-3">Belum ada riwayat</td></tr>';
                  }

                  // CPK calculation
                  let cpkText = '-';
                  if (t.price && t.total_lifetime_km > 0) {
                     cpkText = 'Rp ' + Math.round(t.price / t.total_lifetime_km).toLocaleString();
                  }

                  body.innerHTML = `
                     <!-- Header Card -->
                     <div class="p-3 border-bottom" style="background: linear-gradient(135deg, #f8f7ff 0%, #eef2ff 100%);">
                        <div class="d-flex align-items-start">
                           <div class="flex-grow-1">
                              <h5 class="mb-1 fw-bold">${t.serial_number}</h5>
                              <span class="badge bg-${statusColor} mb-2">${t.status}</span>
                              <div class="text-muted small">
                                 <i class="ri-price-tag-3-line me-1"></i>${t.brand} · ${t.pattern} · ${t.size}
                              </div>
                           </div>
                           <div class="text-end">
                              <small class="text-muted d-block">Retread</small>
                              <span class="badge bg-label-secondary fs-6">${t.retread_count}x</span>
                           </div>
                        </div>
                     </div>

                     <!-- Stats Grid -->
                     <div class="p-3 border-bottom">
                        <div class="row g-2">
                           <div class="col-6">
                              <div class="p-2 rounded bg-light text-center">
                                 <i class="ri-road-map-line text-primary d-block mb-1" style="font-size: 1.3rem;"></i>
                                 <div class="fw-bold">${t.total_lifetime_km.toLocaleString()}</div>
                                 <small class="text-muted">Total KM</small>
                              </div>
                           </div>
                           <div class="col-6">
                              <div class="p-2 rounded bg-light text-center">
                                 <i class="ri-time-line text-warning d-block mb-1" style="font-size: 1.3rem;"></i>
                                 <div class="fw-bold">${t.total_lifetime_hm.toLocaleString()}</div>
                                 <small class="text-muted">Total HM</small>
                              </div>
                           </div>
                           <div class="col-6">
                              <div class="p-2 rounded bg-light text-center">
                                 <i class="ri-money-dollar-circle-line text-success d-block mb-1" style="font-size: 1.3rem;"></i>
                                 <div class="fw-bold">${cpkText}</div>
                                 <small class="text-muted">Cost/KM</small>
                              </div>
                           </div>
                           <div class="col-6">
                              <div class="p-2 rounded bg-light text-center">
                                 <i class="ri-calendar-check-line text-info d-block mb-1" style="font-size: 1.3rem;"></i>
                                 <div class="fw-bold">${t.days_since_install !== null ? t.days_since_install + ' hari' : '-'}</div>
                                 <small class="text-muted">Sejak Pasang</small>
                              </div>
                           </div>
                        </div>
                        ${rtdBar}
                     </div>

                     <!-- Installation Info -->
                     <div class="p-3 border-bottom">
                        <h6 class="fw-bold text-uppercase small text-muted mb-2">
                           <i class="ri-pushpin-line me-1"></i>Info Pemasangan Terakhir
                        </h6>
                        <div class="d-flex gap-4">
                           <div>
                              <small class="text-muted d-block">Tanggal Pasang</small>
                              <span class="fw-bold">${t.install_date || '-'}</span>
                           </div>
                           <div>
                              <small class="text-muted d-block">KM Saat Pasang</small>
                              <span class="fw-bold">${t.install_odo !== null ? t.install_odo.toLocaleString() : '-'}</span>
                           </div>
                           <div>
                              <small class="text-muted d-block">Total Transaksi</small>
                              <span class="fw-bold">${t.total_movements}x</span>
                           </div>
                        </div>
                     </div>

                     <!-- Movement History -->
                     <div class="p-3">
                        <h6 class="fw-bold text-uppercase small text-muted mb-2">
                           <i class="ri-history-line me-1"></i>Riwayat Pergerakan (Last 10)
                        </h6>
                        <div class="table-responsive">
                           <table class="table table-sm table-borderless mb-0">
                              <thead>
                                 <tr class="text-muted">
                                    <th><small>Tanggal</small></th>
                                    <th><small>Tipe</small></th>
                                    <th class="text-end"><small>Run KM</small></th>
                                    <th class="text-end"><small>RTD</small></th>
                                 </tr>
                              </thead>
                              <tbody>
                                 ${movRows}
                              </tbody>
                           </table>
                        </div>
                     </div>
                  `;

                  document.getElementById('td_title').textContent = 'Detail: ' + t.serial_number;
               })
               .catch(err => {
                  console.error('Tyre detail error:', err);
                  body.innerHTML = '<div class="alert alert-danger m-4">Gagal memuat data ban.</div>';
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
                     var badgeClass = 'bg-label-info';
                     var typeText = 'Inspeksi';

                     if (data === 'Installation') {
                        badgeClass = row.is_replacement ? 'bg-label-warning' : 'bg-label-primary';
                        typeText = row.is_replacement ? 'Replacement' : 'Pasang';
                     } else if (data === 'Removal') {
                        badgeClass = 'bg-label-danger';
                        typeText = 'Lepas';
                     } else if (data === 'Rotation') {
                        badgeClass = 'bg-label-info';
                        typeText = 'Rotasi';
                     }

                     var conditionBadge = '';
                     if (row.install_condition) {
                        conditionBadge = '<br><small class="text-muted">' + row.install_condition +
                           '</small>';
                     }

                     return '<span class="badge ' + badgeClass + '">' + typeText + '</span>' +
                        conditionBadge;
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
                  fetch(`/rollback/${id}`, {
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
