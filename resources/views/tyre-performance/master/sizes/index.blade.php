@extends('layouts.admin')

@section('title', 'Master Tyre Sizes')

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyre Sizes</h4>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSizeModal">
            <i class="ri-add-line me-1"></i> Add Size
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
                     <th>Size</th>
                     <th>Brand</th>
                     <th>Type</th>
                     <th>Std OTD</th>
                     <th>Ply Rating</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @forelse($sizes as $size)
                     <tr>
                        <td><strong>{{ $size->size }}</strong></td>
                        <td>{{ $size->brand->brand_name ?? '-' }}</td>
                        <td>{{ $size->type }}</td>
                        <td>{{ $size->std_otd ?? '-' }}</td>
                        <td>{{ $size->ply_rating ?? '-' }}</td>
                        <td>
                           <div class="dropdown">
                              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                 <i class="ri-more-2-fill"></i>
                              </button>
                              <div class="dropdown-menu">
                                 <a class="dropdown-item edit-size" href="javascript:void(0);" data-bs-toggle="modal"
                                    data-bs-target="#editSizeModal" data-id="{{ $size->id }}"
                                    data-size="{{ $size->size }}" data-brand-id="{{ $size->tyre_brand_id }}"
                                    data-type="{{ $size->type }}" data-otd="{{ $size->std_otd }}"
                                    data-ply="{{ $size->ply_rating }}">
                                    <i class="ri-pencil-line me-1"></i> Edit
                                 </a>
                                 <form action="{{ route('tyre-sizes.destroy', $size->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item">
                                       <i class="ri-delete-bin-line me-1"></i> Delete
                                    </button>
                                 </form>
                              </div>
                           </div>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="6" class="text-center">No data found</td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
      </div>
   </div>

   <!-- Add Size Modal -->
   <div class="modal fade" id="addSizeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Add New Size</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-sizes.store') }}" method="POST">
               @csrf
               <div class="modal-body">
                  <div class="row">
                     <div class="col mb-3">
                        <label for="size" class="form-label">Size</label>
                        <input type="text" id="size" name="size" class="form-control"
                           placeholder="e.g. 11.00R20" required>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col mb-3">
                        <label for="tyre_brand_id" class="form-label">Brand</label>
                        <select name="tyre_brand_id" class="form-select" required>
                           <option value="">Select Brand</option>
                           @foreach ($brands as $brand)
                              <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                           <option value="Bias">Bias</option>
                           <option value="Radial">Radial</option>
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="std_otd" class="form-label">Std OTD</label>
                        <input type="number" step="0.01" id="std_otd" name="std_otd" class="form-control"
                           placeholder="e.g. 16.5">
                     </div>
                  </div>
                  <div class="row">
                     <div class="col mb-3">
                        <label for="ply_rating" class="form-label">Ply Rating</label>
                        <input type="number" id="ply_rating" name="ply_rating" class="form-control"
                           placeholder="e.g. 16">
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

   <!-- Edit Size Modal -->
   <div class="modal fade" id="editSizeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Edit Size</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSizeForm" method="POST">
               @csrf
               @method('PUT')
               <div class="modal-body">
                  <div class="row">
                     <div class="col mb-3">
                        <label for="edit_size" class="form-label">Size</label>
                        <input type="text" id="edit_size" name="size" class="form-control" required>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col mb-3">
                        <label for="edit_brand_id" class="form-label">Brand</label>
                        <select id="edit_brand_id" name="tyre_brand_id" class="form-select" required>
                           <option value="">Select Brand</option>
                           @foreach ($brands as $brand)
                              <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_type" class="form-label">Type</label>
                        <select id="edit_type" name="type" class="form-select" required>
                           <option value="Bias">Bias</option>
                           <option value="Radial">Radial</option>
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_otd" class="form-label">Std OTD</label>
                        <input type="number" step="0.01" id="edit_otd" name="std_otd" class="form-control">
                     </div>
                  </div>
                  <div class="row">
                     <div class="col mb-3">
                        <label for="edit_ply" class="form-label">Ply Rating</label>
                        <input type="number" id="edit_ply" name="ply_rating" class="form-control">
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
         const editButtons = document.querySelectorAll('.edit-size');
         const editForm = document.querySelector('#editSizeForm');

         editButtons.forEach(button => {
            button.addEventListener('click', function() {
               const id = this.getAttribute('data-id');
               const size = this.getAttribute('data-size');
               const brandId = this.getAttribute('data-brand-id');
               const type = this.getAttribute('data-type');
               const otd = this.getAttribute('data-otd');
               const ply = this.getAttribute('data-ply');

               editForm.action = `/tyre_performance/master/sizes/${id}`;
               document.querySelector('#edit_size').value = size;
               document.querySelector('#edit_brand_id').value = brandId;
               document.querySelector('#edit_type').value = type;
               document.querySelector('#edit_otd').value = otd === 'null' ? '' : otd;
               document.querySelector('#edit_ply').value = ply === 'null' ? '' : ply;
            });
         });
      });
   </script>
@endsection
