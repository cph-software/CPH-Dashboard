@extends('layouts.admin')

@section('title', 'Master Tyre Brands')

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
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyre Brands</h4>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBrandModal">
            <i class="icon-base ri ri-add-line me-1"></i> Add Brand
         </button>
      </div>

      <div class="card">
         <div class="card-datatable table-responsive">
            <table class="datatables-brands table border-top table-hover">
               <thead>
                  <tr>
                     <th>Brand Name</th>
                     <th>Type</th>
                     <th>Status</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @foreach ($brands as $brand)
                     <tr>
                        <td><strong>{{ $brand->brand_name }}</strong></td>
                        <td>{{ $brand->brand_type ?? '-' }}</td>
                        <td>
                           <span class="badge bg-label-{{ $brand->status == 'Active' ? 'success' : 'secondary' }}">
                              {{ $brand->status }}
                           </span>
                        </td>
                        <td>
                           <div class="d-flex align-items-center">
                              <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 edit-brand"
                                 href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editBrandModal"
                                 data-id="{{ $brand->id }}" data-name="{{ $brand->brand_name }}"
                                 data-type="{{ $brand->brand_type }}" data-status="{{ $brand->status }}" title="Edit">
                                 <i class="icon-base ri ri-pencil-line"></i>
                              </a>
                              <button type="button"
                                 class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light delete-brand"
                                 data-id="{{ $brand->id }}" data-name="{{ $brand->brand_name }}" title="Delete">
                                 <i class="icon-base ri ri-delete-bin-line"></i>
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

   <!-- Add Brand Modal -->
   <div class="modal fade" id="addBrandModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Add New Brand</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-brands.store') }}" method="POST">
               @csrf
               <div class="modal-body">
                  <div class="row">
                     <div class="col mb-3">
                        <label for="brand_name" class="form-label">Brand Name</label>
                        <input type="text" id="brand_name" name="brand_name" class="form-control"
                           placeholder="Enter Brand Name" required>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col mb-3">
                        <label for="brand_type" class="form-label">Brand Type</label>
                        <input type="text" id="brand_type" name="brand_type" class="form-control"
                           placeholder="Enter Type (Optional)">
                     </div>
                  </div>
                  <div class="row">
                     <div class="col mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                           <option value="Active">Active</option>
                           <option value="Inactive">Inactive</option>
                        </select>
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

   <!-- Edit Brand Modal -->
   <div class="modal fade" id="editBrandModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Edit Brand</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBrandForm" method="POST">
               @csrf
               @method('PUT')
               <div class="modal-body">
                  <div class="row">
                     <div class="col mb-3">
                        <label for="edit_brand_name" class="form-label">Brand Name</label>
                        <input type="text" id="edit_brand_name" name="brand_name" class="form-control"
                           placeholder="Enter Brand Name" required>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col mb-3">
                        <label for="edit_brand_type" class="form-label">Brand Type</label>
                        <input type="text" id="edit_brand_type" name="brand_type" class="form-control"
                           placeholder="Enter Type (Optional)">
                     </div>
                  </div>
                  <div class="row">
                     <div class="col mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select id="edit_status" name="status" class="form-select" required>
                           <option value="Active">Active</option>
                           <option value="Inactive">Inactive</option>
                        </select>
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

   <form id="deleteForm" method="POST" style="display: none;">
      @csrf
      @method('DELETE')
   </form>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         $('.datatables-brands').DataTable({
            order: [
               [0, 'desc']
            ],
            displayLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
         });

         const editForm = $('#editBrandForm');

         $(document).on('click', '.edit-brand', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const type = $(this).data('type');
            const status = $(this).data('status');

            editForm.attr('action', `{{ url('tyre_performance/master_brand') }}/${id}`);
            $('#edit_brand_name').val(name);
            $('#edit_brand_type').val(type === 'null' ? '' : type);
            $('#edit_status').val(status);
         });

         $(document).on('click', '.delete-brand', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            Swal.fire({
               title: 'Yakin ingin menghapus?',
               text: `Brand "${name}" akan dihapus permanen!`,
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
                  form.action = `{{ url('tyre_performance/master_brand') }}/${id}`;
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
