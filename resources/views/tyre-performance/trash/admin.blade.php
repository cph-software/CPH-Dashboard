@extends('layouts.admin')

@section('title', 'Super Admin — Trash Tier 2')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('page-style')
   <style>
      .tier2-header {
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         border-radius: 12px; color: white; padding: 24px;
      }
      .time-badge { font-size: 0.7rem; padding: 4px 8px; border-radius: 20px; }
      .time-critical { background: #ffe0e0; color: #d32f2f; }
      .time-warning { background: #fff3e0; color: #ef6c00; }
      .time-safe { background: #e8f5e9; color: #2e7d32; }
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="tier2-header mb-4 shadow-sm">
         <div class="d-flex justify-content-between align-items-center">
            <div>
               <h4 class="fw-bold mb-1 text-white"><i class="ri-shield-keyhole-line me-2"></i>Super Admin Trash (Tier 2)</h4>
               <p class="mb-0 opacity-75">Data yang sudah dihapus permanen oleh perusahaan. Auto-purge setelah 3 hari.</p>
            </div>
            <a href="{{ route('trash.index') }}" class="btn btn-light">
               <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
         </div>
      </div>

      {{-- Filters --}}
      <div class="card mb-4">
         <div class="card-body">
            <div class="row align-items-end g-3">
               <div class="col-md-4">
                  <label class="form-label fw-bold">Filter Instansi</label>
                  <select id="companyFilter" class="form-select select2">
                     <option value="">Semua Instansi</option>
                     @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                     @endforeach
                  </select>
               </div>
               <div class="col-md-4">
                  <label class="form-label fw-bold">Tipe Data</label>
                  <select id="typeFilter" class="form-select">
                     <option value="tyres">Ban</option>
                     <option value="vehicles">Kendaraan</option>
                  </select>
               </div>
               <div class="col-md-4 text-end">
                  <button class="btn btn-outline-primary" id="refreshBtn">
                     <i class="ri-refresh-line me-1"></i> Refresh
                  </button>
               </div>
            </div>
         </div>
      </div>

      {{-- DataTable --}}
      <div class="card">
         <div class="card-datatable table-responsive">
            <table class="table border-top" id="adminTrashTable">
               <thead>
                  <tr>
                     <th>Nama Data</th>
                     <th>Detail</th>
                     <th>Instansi</th>
                     <th>Dihapus User</th>
                     <th>Masuk Tier 2</th>
                     <th>Auto-Purge</th>
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
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         $('#companyFilter').select2({ width: '100%' });

         const table = $('#adminTrashTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
               url: '{{ route("trash.admin.data") }}',
               data: function(d) {
                  d.type = $('#typeFilter').val();
                  d.company_id = $('#companyFilter').val();
               }
            },
            columns: [
               { data: 'name', render: (data) => `<strong>${data}</strong>` },
               { data: 'detail' },
               { data: 'company', render: (data) => `<span class="badge bg-label-info">${data}</span>` },
               { data: 'deleted_at' },
               { data: 'permanent_deleted_at' },
               { data: 'hours_left', render: function(data) {
                  if (data <= 12) return `<span class="time-badge time-critical"><i class="ri-alarm-warning-line me-1"></i>${data} jam lagi</span>`;
                  if (data <= 48) return `<span class="time-badge time-warning"><i class="ri-timer-line me-1"></i>${Math.ceil(data/24)} hari lagi</span>`;
                  return `<span class="time-badge time-safe"><i class="ri-timer-line me-1"></i>${Math.ceil(data/24)} hari lagi</span>`;
               }},
               { data: null, orderable: false, render: function(data) {
                  return `
                     <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-success admin-restore-btn"
                           data-id="${data.id}" data-type="${data.type}" data-name="${data.name}" title="Pulihkan">
                           <i class="ri-arrow-go-back-line me-1"></i>Pulihkan
                        </button>
                        <button class="btn btn-sm btn-outline-danger admin-purge-btn"
                           data-id="${data.id}" data-type="${data.type}" data-name="${data.name}" title="Hapus Permanen">
                           <i class="ri-delete-bin-line me-1"></i>Hapus
                        </button>
                     </div>`;
               }}
            ],
            order: [[5, 'asc']],
            language: {
               emptyTable: '<div class="py-4 text-center"><i class="ri-shield-check-line ri-3x text-success mb-2 d-block opacity-50"></i><p class="text-muted mb-0">Tidak ada data di Tier 2. Semua aman!</p></div>',
               processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Memuat...'
            }
         });

         $('#companyFilter, #typeFilter').on('change', () => table.ajax.reload());
         $('#refreshBtn').on('click', () => table.ajax.reload());

         // Restore from Tier 2
         $(document).on('click', '.admin-restore-btn', function() {
            const id = $(this).data('id');
            const type = $(this).data('type');
            const name = $(this).data('name');

            Swal.fire({
               title: 'Pulihkan Data?',
               html: `Data <strong>"${name}"</strong> akan dikembalikan sepenuhnya ke sistem aktif.`,
               icon: 'question',
               showCancelButton: true,
               confirmButtonText: '<i class="ri-arrow-go-back-line me-1"></i> Ya, Pulihkan!',
               cancelButtonText: 'Batal',
               customClass: { confirmButton: 'btn btn-success me-3', cancelButton: 'btn btn-outline-secondary' },
               buttonsStyling: false
            }).then((result) => {
               if (result.isConfirmed) {
                  fetch(`{{ url('admin-trash') }}/${type}/${id}/restore`, {
                     method: 'POST',
                     headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                  })
                  .then(res => res.json())
                  .then(data => {
                     if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Dipulihkan!', text: data.message, timer: 2000, showConfirmButton: false });
                        table.ajax.reload();
                     } else {
                        Swal.fire('Gagal', data.message, 'error');
                     }
                  });
               }
            });
         });

         // Hard Delete (Purge)
         $(document).on('click', '.admin-purge-btn', function() {
            const id = $(this).data('id');
            const type = $(this).data('type');
            const name = $(this).data('name');

            Swal.fire({
               title: 'Hapus Permanen?',
               html: `Data <strong>"${name}"</strong> akan dihapus permanen dari database dan tidak dapat dipulihkan lagi.`,
               icon: 'warning',
               showCancelButton: true,
               confirmButtonText: '<i class="ri-delete-bin-line me-1"></i> Ya, Hapus!',
               cancelButtonText: 'Batal',
               customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-outline-secondary' },
               buttonsStyling: false
            }).then((result) => {
               if (result.isConfirmed) {
                  fetch(`{{ url('admin-trash') }}/${type}/${id}/purge`, {
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
