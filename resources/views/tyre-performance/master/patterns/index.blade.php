@extends('layouts.admin')

@section('title', 'Master Tyre Patterns')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <div>
            <h4 class="fw-bold py-1 mb-0"><span class="text-muted fw-light">Master /</span> Tyre Patterns</h4>
            <small class="text-muted">Manajemen pola kembangan (pattern) ban berdasarkan brand</small>
         </div>
         <div class="d-flex gap-2">
            <div class="btn-group">
               <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  <i class="ri-download-2-line me-1"></i> Export Data
               </button>
               <ul class="dropdown-menu">
                  <li><a class="dropdown-item"
                        href="{{ route('master_data.export', ['type' => 'patterns', 'format' => 'csv']) }}"><i
                           class="ri-file-text-line me-2"></i>CSV Format</a></li>
                  <li><a class="dropdown-item"
                        href="{{ route('master_data.export', ['type' => 'patterns', 'format' => 'excel']) }}"><i
                           class="ri-file-excel-2-line me-2"></i>Excel Format</a></li>
               </ul>
            </div>
            @if (hasPermission('Patterns', 'create'))
               <button type="button" class="btn btn-outline-secondary shadow-sm" data-bs-toggle="modal"
                  data-bs-target="#importModal">
                  <i class="ri-upload-2-line me-1"></i> Import
               </button>
               <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal"
                  data-bs-target="#addPatternModal">
                  <i class="ri-add-line me-1"></i> Add Pattern
               </button>
            @endif
         </div>
      </div>

      <div class="card shadow-sm border-0">
         <div class="card-datatable table-responsive">
            <table class="datatables-patterns table border-top table-hover">
               <thead>
                  <tr>
                     <th>Pattern Name</th>
                     <th>Status</th>
                     <th class="text-center">Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @foreach ($patterns as $pattern)
                     <tr>

                        <td><strong>{{ $pattern->name }}</strong></td>
                        <td>
                           <span class="badge bg-label-{{ $pattern->status == 'Active' ? 'success' : 'secondary' }}">
                              {{ $pattern->status }}
                           </span>
                        </td>
                        <td>
                           <div class="d-flex align-items-center justify-content-center">
                              @if (hasPermission('Patterns', 'update'))
                                 <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 edit-pattern"
                                    href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editPatternModal"
                                    data-id="{{ $pattern->id }}" data-name="{{ $pattern->name }}"
                                    data-status="{{ $pattern->status }}" title="Edit">
                                    <i class="icon-base ri ri-pencil-line"></i>
                                 </a>
                              @endif
                              @if (hasPermission('Patterns', 'delete'))
                                 <button type="button"
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light delete-pattern"
                                    data-id="{{ $pattern->id }}" data-name="{{ $pattern->name }}" title="Delete">
                                    <i class="icon-base ri ri-delete-bin-line"></i>
                                 </button>
                              @endif
                           </div>
                        </td>
                     </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
      </div>
   </div>

   <form id="deleteForm" method="POST" style="display: none;">
      @csrf
      @method('DELETE')
   </form>

   <!-- Add Pattern Modal -->
   <div class="modal fade" id="addPatternModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary py-3">
               <h5 class="modal-title text-white">Add New Pattern</h5>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-patterns.store') }}" method="POST">
               @csrf
               <div class="modal-body pt-4">

                  <div class="mb-3">
                     <label for="name" class="form-label fw-bold">Pattern Name</label>
                     <input type="text" id="name" name="name" class="form-control"
                        placeholder="e.g. Rough Terrain (R150)" required>
                  </div>
                  <div class="mb-3">
                     <label for="status" class="form-label fw-bold">Status</label>
                     <select name="status" class="form-select" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                     </select>
                  </div>
               </div>
               <div class="modal-footer border-top">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary shadow">Save changes</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <!-- Edit Pattern Modal -->
   <div class="modal fade" id="editPatternModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning py-3">
               <h5 class="modal-title text-dark">Edit Pattern</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPatternForm" method="POST">
               @csrf
               @method('PUT')
               <div class="modal-body pt-4">

                  <div class="mb-3">
                     <label for="edit_name" class="form-label fw-bold">Pattern Name</label>
                     <input type="text" id="edit_name" name="name" class="form-control" required>
                  </div>
                  <div class="mb-3">
                     <label for="edit_status" class="form-label fw-bold">Status</label>
                     <select id="edit_status" name="status" class="form-select" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                     </select>
                  </div>
               </div>
               <div class="modal-footer border-top">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-warning shadow">Update changes</button>
               </div>
            </form>
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
         $('.datatables-patterns').DataTable({
            order: [
               [0, 'asc']
            ],
            displayLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
         });

         const editForm = $('#editPatternForm');

         $(document).on('click', '.edit-pattern', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const status = $(this).data('status');

            editForm.attr('action', `{{ url('master_data_tyre/master_pattern') }}/${id}`);
            $('#edit_name').val(name);

            $('#edit_status').val(status);
         });

         $(document).on('click', '.delete-pattern', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            Swal.fire({
               title: 'Yakin ingin menghapus?',
               text: `Pattern "${name}" akan dihapus permanen!`,
               icon: 'warning',
               showCancelButton: true,
               confirmButtonText: 'Ya, Hapus!',
               cancelButtonText: 'Batal',
               customClass: {
                  confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                  cancelButton: 'btn btn-outline-secondary waves-effect'
               },
               buttonsStyling: false
            }).then((result) => {
               if (result.isConfirmed) {
                  const form = document.getElementById('deleteForm');
                  form.action = `{{ url('master_data_tyre/master_pattern') }}/${id}`;
                  form.submit();
               }
            });
         });

         @if (session('success'))
            Swal.fire({
               icon: 'success',
               title: 'Berhasil!',
               text: '{{ session('success') }}',
               timer: 2000,
               showConfirmButton: false
            });
         @endif

         @if (session('error'))
            Swal.fire({
               icon: 'error',
               title: 'Oops...',
               text: '{{ session('error') }}',
            });
         @endif

         // Initialize Select2 with Modal fix
         $('.select2').each(function() {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
               placeholder: $this.data('placeholder'),
               dropdownParent: $this.closest('.modal')
            });
         });
      });
   </script>
@endsection
