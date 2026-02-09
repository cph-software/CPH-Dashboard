@extends('layouts.admin')

@section('title', 'Master Tyre Patterns')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyre Patterns</h4>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatternModal">
            <i class="ri-add-line me-1"></i> Add Pattern
         </button>
      </div>

      @if (session('success'))
         <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif

      <div class="card">
         <div class="card-datatable table-responsive">
            <table class="datatables-patterns table border-top table-hover">
               <thead>
                  <tr>
                     <th>Pattern Name</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @foreach ($patterns as $pattern)
                     <tr>
                        <td><strong>{{ $pattern->name }}</strong></td>
                        <td>
                           <div class="d-flex align-items-center">
                              <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 edit-pattern"
                                 href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editPatternModal"
                                 data-id="{{ $pattern->id }}" data-name="{{ $pattern->name }}" title="Edit">
                                 <i class="ri-pencil-line"></i>
                              </a>
                              <button type="button"
                                 class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light delete-pattern"
                                 data-id="{{ $pattern->id }}" data-name="{{ $pattern->name }}" title="Delete">
                                 <i class="ri-delete-bin-line"></i>
                              </button>
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
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Add New Pattern</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-patterns.store') }}" method="POST">
               @csrf
               <div class="modal-body">
                  <div class="row">
                     <div class="col mb-3">
                        <label for="name" class="form-label">Pattern Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                           placeholder="e.g. Rough Terrain" required>
                     </div>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Save changes</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <!-- Edit Pattern Modal -->
   <div class="modal fade" id="editPatternModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Edit Pattern</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPatternForm" method="POST">
               @csrf
               @method('PUT')
               <div class="modal-body">
                  <div class="row">
                     <div class="col mb-3">
                        <label for="edit_name" class="form-label">Pattern Name</label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                     </div>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Update changes</button>
               </div>
            </form>
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
         $('.datatables-patterns').DataTable();

         const editForm = $('#editPatternForm');

         $(document).on('click', '.edit-pattern', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            editForm.attr('action', `{{ url('tyre_performance/master_pattern') }}/${id}`);
            $('#edit_name').val(name);
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
                  form.action = `{{ url('tyre_performance/master_pattern') }}/${id}`;
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
      });
   </script>
@endsection
