@extends('layouts.admin')

@section('title', 'Backup & Pemulihan Data')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('page-style')
   <style>
      .trash-stat-card {
         border-radius: 12px;
         border: none;
         transition: all 0.2s ease;
      }
      .trash-stat-card:hover { transform: translateY(-2px); }
      .trash-stat-card .stat-icon {
         width: 48px; height: 48px; border-radius: 12px;
         display: flex; align-items: center; justify-content: center;
         font-size: 1.4rem;
      }
      .time-badge { font-size: 0.7rem; padding: 4px 8px; border-radius: 20px; }
      .time-critical { background: #ffe0e0; color: #d32f2f; }
      .time-warning { background: #fff3e0; color: #ef6c00; }
      .time-safe { background: #e8f5e9; color: #2e7d32; }
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <div>
            <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Sistem /</span> Backup & Pemulihan Data</h4>
            <small class="text-muted">Data yang dihapus akan tersimpan disini selama 3 hari sebelum dipindahkan ke Super Admin.</small>
         </div>
         @if(auth()->user()->role_id == 1)
         <a href="{{ route('trash.admin') }}" class="btn btn-outline-danger">
            <i class="ri-shield-keyhole-line me-1"></i> Super Admin Trash
         </a>
         @endif
      </div>

      {{-- Tab Navigation --}}
      <div class="card mb-4">
         <div class="card-body pb-0">
            <ul class="nav nav-tabs" role="tablist">
               <li class="nav-item">
                  <button class="nav-link active" data-type="tyres" data-bs-toggle="tab">
                     <i class="ri-circle-line me-1"></i> Ban <span class="badge bg-danger ms-1 trash-count-tyres">0</span>
                  </button>
               </li>
               <li class="nav-item">
                  <button class="nav-link" data-type="vehicles" data-bs-toggle="tab">
                     <i class="ri-truck-line me-1"></i> Kendaraan <span class="badge bg-danger ms-1 trash-count-vehicles">0</span>
                  </button>
               </li>
            </ul>
         </div>
      </div>

      {{-- Bulk Actions --}}
      <div class="card mb-4" id="bulkActionsCard" style="display: none;">
         <div class="card-body py-3 d-flex justify-content-between align-items-center bg-light rounded">
            <div>
               <span class="fw-bold" id="selectedCount">0</span> data terpilih
            </div>
            <div class="d-flex gap-2">
               <button class="btn btn-success" id="btnBulkRestore">
                  <i class="ri-arrow-go-back-line me-1"></i> Pulihkan Terpilih
               </button>
               <button class="btn btn-outline-danger" id="btnBulkForceDelete">
                  <i class="ri-delete-bin-line me-1"></i> Hapus Permanen Terpilih
               </button>
            </div>
         </div>
      </div>

      {{-- DataTable --}}
      <div class="card">
         <div class="card-datatable table-responsive">
            <table class="table border-top" id="trashTable">
               <thead>
                  <tr>
                     <th style="width: 50px;"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                     <th>Nama Data</th>
                     <th>Detail</th>
                     <th>Instansi</th>
                     <th>Status</th>
                     <th>Dihapus Pada</th>
                     <th>Sisa Waktu</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody></tbody>
            </table>
         </div>
      </div>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         let currentType = 'tyres';

         const table = $('#trashTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
               url: '{{ route("trash.data") }}',
               data: function(d) {
                  d.type = currentType;
               }
            },
            columns: [
               { 
                  data: 'id', 
                  orderable: false, 
                  searchable: false,
                  render: function(data) {
                     return `<input type="checkbox" class="row-checkbox form-check-input" value="${data}">`;
                  }
               },
               { data: 'name', render: (data) => `<strong>${data}</strong>` },
               { data: 'detail' },
               { data: 'company', render: (data) => `<span class="badge bg-label-info">${data}</span>` },
               { data: 'status', render: (data) => {
                  const colors = { 'New': 'success', 'Installed': 'primary', 'Scrap': 'danger', 'Repaired': 'warning', 'Active': 'success', 'Inactive': 'secondary' };
                  return `<span class="badge bg-label-${colors[data] || 'secondary'}">${data}</span>`;
               }},
               { data: 'deleted_at' },
               { data: 'hours_left', render: function(data, type, row) {
                  if (row.expired) return '<span class="time-badge time-critical"><i class="ri-timer-line me-1"></i>Expired</span>';
                  if (data <= 24) return `<span class="time-badge time-critical"><i class="ri-timer-line me-1"></i>${data} jam</span>`;
                  if (data <= 48) return `<span class="time-badge time-warning"><i class="ri-timer-line me-1"></i>${Math.ceil(data/24)} hari</span>`;
                  return `<span class="time-badge time-safe"><i class="ri-timer-line me-1"></i>${Math.ceil(data/24)} hari</span>`;
               }},
               { data: null, orderable: false, render: function(data) {
                  let html = '<div class="d-flex gap-1">';
                  html += `<button class="btn btn-sm btn-success restore-btn"
                     data-id="${data.id}" data-type="${data.type}" data-name="${data.name}" title="Pulihkan">
                     <i class="ri-arrow-go-back-line me-1"></i>Pulihkan
                  </button>`;
                  html += `<button class="btn btn-sm btn-outline-danger force-delete-btn"
                     data-id="${data.id}" data-type="${data.type}" data-name="${data.name}" title="Hapus Permanen">
                     <i class="ri-delete-bin-line me-1"></i>Hapus
                  </button>`;
                  html += '</div>';
                  return html;
               }}
            ],
            order: [[4, 'desc']],
            language: {
               emptyTable: '<div class="py-4 text-center"><i class="ri-delete-bin-line ri-3x text-muted mb-2 d-block opacity-25"></i><p class="text-muted mb-0">Tidak ada data di tempat sampah.</p></div>',
               processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat...'
            }
         });

          // Tab switch
          $('.nav-link[data-type]').on('click', function() {
             currentType = $(this).data('type');
             table.ajax.reload();
             $('#selectAll').prop('checked', false);
             updateBulkActionVisibility();
          });

          // Checkbox logic
          $('#selectAll').on('change', function() {
             $('.row-checkbox').prop('checked', $(this).prop('checked'));
             updateBulkActionVisibility();
          });

          $(document).on('change', '.row-checkbox', function() {
             if (!$(this).prop('checked')) {
                $('#selectAll').prop('checked', false);
             } else if ($('.row-checkbox:checked').length === $('.row-checkbox').length) {
                $('#selectAll').prop('checked', true);
             }
             updateBulkActionVisibility();
          });

          // Listen to datatable draw event to reset checkboxes
          table.on('draw', function() {
             $('#selectAll').prop('checked', false);
             updateBulkActionVisibility();
          });

          function updateBulkActionVisibility() {
             const selectedCount = $('.row-checkbox:checked').length;
             $('#selectedCount').text(selectedCount);
             if (selectedCount > 0) {
                $('#bulkActionsCard').slideDown('fast');
             } else {
                $('#bulkActionsCard').slideUp('fast');
             }
          }

          function getSelectedIds() {
             return $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
          }

          // Bulk Restore
          $('#btnBulkRestore').on('click', function() {
             const ids = getSelectedIds();
             if (ids.length === 0) return;

             Swal.fire({
                title: 'Pulihkan Data Terpilih?',
                html: `<strong>${ids.length} data</strong> akan dikembalikan ke sistem aktif.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-arrow-go-back-line me-1"></i> Ya, Pulihkan!',
                cancelButtonText: 'Batal',
                customClass: { confirmButton: 'btn btn-success me-3', cancelButton: 'btn btn-outline-secondary' },
                buttonsStyling: false
             }).then((result) => {
                if (result.isConfirmed) {
                   fetch(`{{ route('trash.bulk-restore') }}`, {
                      method: 'POST',
                      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                      body: JSON.stringify({ ids: ids, type: currentType })
                   })
                   .then(res => res.json())
                   .then(data => {
                      if (data.success) {
                         Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 2000, showConfirmButton: false });
                         table.ajax.reload();
                      } else {
                         Swal.fire('Gagal', data.message, 'error');
                      }
                   });
                }
             });
          });

          // Bulk Force Delete
          $('#btnBulkForceDelete').on('click', function() {
             const ids = getSelectedIds();
             if (ids.length === 0) return;

             Swal.fire({
                title: 'Hapus Permanen Terpilih?',
                html: `<strong>${ids.length} data</strong> akan dihapus dari tampilan Anda.<br><small class="text-muted">Super Admin masih dapat memulihkan data ini selama 3 hari.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-delete-bin-line me-1"></i> Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-outline-secondary' },
                buttonsStyling: false
             }).then((result) => {
                if (result.isConfirmed) {
                   fetch(`{{ route('trash.bulk-force-delete') }}`, {
                      method: 'DELETE',
                      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                      body: JSON.stringify({ ids: ids, type: currentType })
                   })
                   .then(res => res.json())
                   .then(data => {
                      if (data.success) {
                         Swal.fire({ icon: 'success', title: 'Dihapus!', text: data.message, timer: 2000, showConfirmButton: false });
                         table.ajax.reload();
                      } else {
                         Swal.fire('Gagal', data.message, 'error');
                      }
                   });
                }
             });
          });

         // Restore
         $(document).on('click', '.restore-btn', function() {
            const id = $(this).data('id');
            const type = $(this).data('type');
            const name = $(this).data('name');

            Swal.fire({
               title: 'Pulihkan Data?',
               html: `Data <strong>"${name}"</strong> akan dikembalikan ke sistem aktif.`,
               icon: 'question',
               showCancelButton: true,
               confirmButtonText: '<i class="ri-arrow-go-back-line me-1"></i> Ya, Pulihkan!',
               cancelButtonText: 'Batal',
               customClass: { confirmButton: 'btn btn-success me-3', cancelButton: 'btn btn-outline-secondary' },
               buttonsStyling: false
            }).then((result) => {
               if (result.isConfirmed) {
                  fetch(`{{ url('trash') }}/${type}/${id}/restore`, {
                     method: 'POST',
                     headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                  })
                  .then(res => res.json())
                  .then(data => {
                     if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 2000, showConfirmButton: false });
                        table.ajax.reload();
                     } else {
                        Swal.fire('Gagal', data.message, 'error');
                     }
                  });
               }
            });
         });

         // Force Delete
         $(document).on('click', '.force-delete-btn', function() {
            const id = $(this).data('id');
            const type = $(this).data('type');
            const name = $(this).data('name');

            Swal.fire({
               title: 'Hapus Permanen?',
               html: `Data <strong>"${name}"</strong> akan dihapus dari tampilan Anda.<br><small class="text-muted">Super Admin masih dapat memulihkan data ini selama 3 hari.</small>`,
               icon: 'warning',
               showCancelButton: true,
               confirmButtonText: '<i class="ri-delete-bin-line me-1"></i> Ya, Hapus!',
               cancelButtonText: 'Batal',
               customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-outline-secondary' },
               buttonsStyling: false
            }).then((result) => {
               if (result.isConfirmed) {
                  fetch(`{{ url('trash') }}/${type}/${id}/force-delete`, {
                     method: 'DELETE',
                     headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                  })
                  .then(res => res.json())
                  .then(data => {
                     if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Dihapus!', text: data.message, timer: 2000, showConfirmButton: false });
                        table.ajax.reload();
                     } else {
                        Swal.fire('Gagal', data.message, 'error');
                     }
                  });
               }
            });
         });
      });
   </script>
@endsection
