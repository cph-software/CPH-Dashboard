@extends('layouts.admin')

@section('title', 'Master Tyre Failure Codes')

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyre Failure Codes</h4>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFailureCodeModal">
            <i class="ri-add-line me-1"></i> Add Failure Code
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
                     <th>Code</th>
                     <th>Name</th>
                     <th>Image</th>
                     <th>Category</th>
                     <th>Status</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @forelse($failureCodes as $fc)
                     <tr>
                        <td><strong>{{ $fc->failure_code }}</strong></td>
                        <td>{{ $fc->failure_name }}</td>
                        <td>
                           @if ($fc->image_1)
                              <a href="{{ asset('storage/' . $fc->image_1) }}" target="_blank">
                                 <img src="{{ asset('storage/' . $fc->image_1) }}" alt="Img 1" class="rounded"
                                    width="100" height="100" style="object-fit: cover;">
                              </a>
                           @endif
                           @if ($fc->image_2)
                              <a href="{{ asset('storage/' . $fc->image_2) }}" target="_blank">
                                 <img src="{{ asset('storage/' . $fc->image_2) }}" alt="Img 2" class="rounded ms-1"
                                    width="100" height="100" style="object-fit: cover;">
                              </a>
                           @endif
                           @if (!$fc->image_1 && !$fc->image_2)
                              <span class="text-muted">-</span>
                           @endif
                        </td>
                        <td>
                           <span
                              class="badge bg-label-{{ $fc->default_category == 'Scrap' ? 'danger' : ($fc->default_category == 'Repair' ? 'warning' : 'primary') }}">
                              {{ $fc->default_category }}
                           </span>
                        </td>
                        <td>
                           <span class="badge bg-label-{{ $fc->status == 'Active' ? 'success' : 'secondary' }}">
                              {{ $fc->status }}
                           </span>
                        </td>
                        <td>
                           <div class="dropdown">
                              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                 <i class="ri-more-2-fill"></i>
                              </button>
                              <div class="dropdown-menu">
                                 <a class="dropdown-item edit-failure-code" href="javascript:void(0);"
                                    data-bs-toggle="modal" data-bs-target="#editFailureCodeModal"
                                    data-id="{{ $fc->id }}" data-code="{{ $fc->failure_code }}"
                                    data-name="{{ $fc->failure_name }}" data-category="{{ $fc->default_category }}"
                                    data-status="{{ $fc->status }}" data-description="{{ $fc->description }}"
                                    data-recommendations="{{ $fc->recommendations }}" data-image-1="{{ $fc->image_1 }}"
                                    data-image-2="{{ $fc->image_2 }}">
                                    <i class="ri-pencil-line me-1"></i> Edit
                                 </a>
                                 <form action="{{ route('tyre-failure-codes.destroy', $fc->id) }}" method="POST"
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
                        <td colspan="5" class="text-center">No data found</td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
      </div>
   </div>

   <!-- Add Failure Code Modal -->
   <div class="modal fade" id="addFailureCodeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Add New Failure Code</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-failure-codes.store') }}" method="POST" enctype="multipart/form-data">
               @csrf
               <div class="modal-body">
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="failure_code" class="form-label">Failure Code</label>
                        <input type="text" id="failure_code" name="failure_code" class="form-control"
                           placeholder="e.g. CUT-01" required>
                     </div>
                     <div class="col mb-3">
                        <label for="failure_name" class="form-label">Failure Name</label>
                        <input type="text" id="failure_name" name="failure_name" class="form-control"
                           placeholder="e.g. Side Wall Cut" required>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                     </div>
                     <div class="col mb-3">
                        <label for="recommendations" class="form-label">Recommendations</label>
                        <textarea id="recommendations" name="recommendations" class="form-control" rows="3"></textarea>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="image_1" class="form-label">Image 1</label>
                        <input type="file" id="image_1" name="image_1" class="form-control"
                           onchange="previewImage(this, 'preview_add_img1')">
                        <div class="mt-2 text-center" style="display: none;">
                           <img src="" id="preview_add_img1" class="img-fluid rounded"
                              style="max-height: 300px;">
                        </div>
                     </div>
                     <div class="col mb-3">
                        <label for="image_2" class="form-label">Image 2</label>
                        <input type="file" id="image_2" name="image_2" class="form-control"
                           onchange="previewImage(this, 'preview_add_img2')">
                        <div class="mt-2 text-center" style="display: none;">
                           <img src="" id="preview_add_img2" class="img-fluid rounded"
                              style="max-height: 300px;">
                        </div>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="default_category" class="form-label">Category</label>
                        <select name="default_category" class="form-select" required>
                           <option value="Scrap">Scrap</option>
                           <option value="Repair">Repair</option>
                           <option value="Claim">Claim</option>
                        </select>
                     </div>
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

   <!-- Edit Failure Code Modal -->
   <div class="modal fade" id="editFailureCodeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Edit Failure Code</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editFailureCodeForm" method="POST" enctype="multipart/form-data">
               @csrf
               @method('PUT')
               <div class="modal-body">
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_failure_code" class="form-label">Failure Code</label>
                        <input type="text" id="edit_failure_code" name="failure_code" class="form-control" required>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_failure_name" class="form-label">Failure Name</label>
                        <input type="text" id="edit_failure_name" name="failure_name" class="form-control" required>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_recommendations" class="form-label">Recommendations</label>
                        <textarea id="edit_recommendations" name="recommendations" class="form-control" rows="3"></textarea>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_image_1" class="form-label">Image 1 (Leave empty to keep current)</label>
                        <input type="file" id="edit_image_1" name="image_1" class="form-control"
                           onchange="previewImage(this, 'preview_edit_img1')">
                        <div class="mt-2 text-center" id="preview_edit_container_1" style="display: none;">
                           <img src="" id="preview_edit_img1" class="img-fluid rounded"
                              style="max-height: 300px;">
                        </div>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_image_2" class="form-label">Image 2 (Leave empty to keep current)</label>
                        <input type="file" id="edit_image_2" name="image_2" class="form-control"
                           onchange="previewImage(this, 'preview_edit_img2')">
                        <div class="mt-2 text-center" id="preview_edit_container_2" style="display: none;">
                           <img src="" id="preview_edit_img2" class="img-fluid rounded"
                              style="max-height: 300px;">
                        </div>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_category" class="form-label">Category</label>
                        <select id="edit_category" name="default_category" class="form-select" required>
                           <option value="Scrap">Scrap</option>
                           <option value="Repair">Repair</option>
                           <option value="Claim">Claim</option>
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_failure_status" class="form-label">Status</label>
                        <select id="edit_failure_status" name="status" class="form-select" required>
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
      function previewImage(input, previewId) {
         const preview = document.getElementById(previewId);
         const container = preview.parentElement;

         if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
               preview.src = e.target.result;
               container.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
         } else {
            // If no file is selected, hide the preview
            container.style.display = 'none';
            preview.src = '';
         }
      }

      document.addEventListener('DOMContentLoaded', function() {
         const editButtons = document.querySelectorAll('.edit-failure-code');
         const editForm = document.querySelector('#editFailureCodeForm');

         editButtons.forEach(button => {
            button.addEventListener('click', function() {
               const id = this.getAttribute('data-id');
               const code = this.getAttribute('data-code');
               const name = this.getAttribute('data-name');
               const category = this.getAttribute('data-category');
               const status = this.getAttribute('data-status');
               const description = this.getAttribute('data-description');
               const recommendations = this.getAttribute('data-recommendations');
               const image1 = this.getAttribute('data-image-1');
               const image2 = this.getAttribute('data-image-2');

               editForm.action = `/tyre_performance/master/failure-codes/${id}`;
               document.querySelector('#edit_failure_code').value = code;
               document.querySelector('#edit_failure_name').value = name;
               document.querySelector('#edit_category').value = category;
               document.querySelector('#edit_failure_status').value = status;
               document.querySelector('#edit_description').value = description === 'null' ? '' :
                  description;
               document.querySelector('#edit_recommendations').value = recommendations === 'null' ? '' :
                  recommendations;

               // Clear any previously selected files in the file inputs
               document.querySelector('#edit_image_1').value = '';
               document.querySelector('#edit_image_2').value = '';

               // Handle Image 1 Preview
               const preview1 = document.getElementById('preview_edit_img1');
               const container1 = document.getElementById('preview_edit_container_1');
               if (image1 && image1 !== 'null' && image1 !== '') {
                  preview1.src = `/storage/${image1}`;
                  container1.style.display = 'block';
               } else {
                  container1.style.display = 'none';
                  preview1.src = '';
               }

               // Handle Image 2 Preview
               const preview2 = document.getElementById('preview_edit_img2');
               const container2 = document.getElementById('preview_edit_container_2');
               if (image2 && image2 !== 'null' && image2 !== '') {
                  preview2.src = `/storage/${image2}`;
                  container2.style.display = 'block';
               } else {
                  container2.style.display = 'none';
                  preview2.src = '';
               }
            });
         });
      });
   </script>
@endsection
