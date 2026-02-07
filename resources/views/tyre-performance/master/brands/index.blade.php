@extends('layouts.admin')

@section('title', 'Master Tyre Brands')

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyre Brands</h4>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBrandModal">
            <i class="ri-add-line me-1"></i> Add Brand
         </button>
      </div>

      @if (session('success'))
         <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif

      <div class="card">
         <div class="table-responsive text-nowrap">
            <table class="table table-hover">
               <thead>
                  <tr>
                     <th>Brand Name</th>
                     <th>Type</th>
                     <th>Status</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @forelse($brands as $brand)
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
                                 href="javascript:void(0);" data-bs-toggle="modal"
                                 data-bs-target="#editBrandModal" data-id="{{ $brand->id }}"
                                 data-name="{{ $brand->brand_name }}" data-type="{{ $brand->brand_type }}"
                                 data-status="{{ $brand->status }}" title="Edit">
                                 <i class="ri-pencil-line"></i>
                              </a>
                              <form action="{{ route('tyre-brands.destroy', $brand->id) }}" method="POST"
                                 onsubmit="return confirm('Are you sure?')" class="d-inline">
                                 @csrf
                                 @method('DELETE')
                                 <button type="submit" class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light" title="Delete">
                                    <i class="ri-delete-bin-line"></i>
                                 </button>
                              </form>
                           </div>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="4" class="text-center">No data found</td>
                     </tr>
                  @endforelse
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

@endsection

@section('page-script')
   <script>
      document.addEventListener('DOMContentLoaded', function() {
         const editButtons = document.querySelectorAll('.edit-brand');
         const editForm = document.querySelector('#editBrandForm');

         editButtons.forEach(button => {
            button.addEventListener('click', function() {
               const id = this.getAttribute('data-id');
               const name = this.getAttribute('data-name');
               const type = this.getAttribute('data-type');
               const status = this.getAttribute('data-status');

               editForm.action = `/tyre_performance/master/brands/${id}`;
               document.querySelector('#edit_brand_name').value = name;
               document.querySelector('#edit_brand_type').value = type === 'null' ? '' : type;
               document.querySelector('#edit_status').value = status;
            });
         });
      });
   </script>
@endsection
