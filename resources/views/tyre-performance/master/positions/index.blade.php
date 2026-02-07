@extends('layouts.admin')

@section('title', 'Master Tyre Positions')

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyre Positions</h4>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPositionModal">
            <i class="ri-add-line me-1"></i> Add Position
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
                     <th>Axle</th>
                     <th>Side</th>
                     <th>Order</th>
                     <th>Description</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @forelse($positions as $pos)
                     <tr>
                        <td><strong>{{ $pos->position_code }}</strong></td>
                        <td>{{ $pos->axle }}</td>
                        <td>{{ $pos->side }}</td>
                        <td>{{ $pos->position_order }}</td>
                        <td>{{ $pos->description ?? '-' }}</td>
                        <td>
                           <div class="dropdown">
                              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                 <i class="ri-more-2-fill"></i>
                              </button>
                              <div class="dropdown-menu">
                                 <a class="dropdown-item edit-position" href="javascript:void(0);" data-bs-toggle="modal"
                                    data-bs-target="#editPositionModal" data-id="{{ $pos->id }}"
                                    data-code="{{ $pos->position_code }}" data-axle="{{ $pos->axle }}"
                                    data-side="{{ $pos->side }}" data-order="{{ $pos->position_order }}"
                                    data-desc="{{ $pos->description }}">
                                    <i class="ri-pencil-line me-1"></i> Edit
                                 </a>
                                 <form action="{{ route('tyre-positions.destroy', $pos->id) }}" method="POST"
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

   <!-- Add Position Modal -->
   <div class="modal fade" id="addPositionModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Add New Position</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-positions.store') }}" method="POST">
               @csrf
               <div class="modal-body">
                  <div class="row">
                     <div class="col mb-3">
                        <label for="position_code" class="form-label">Position Code</label>
                        <input type="text" id="position_code" name="position_code" class="form-control"
                           placeholder="e.g. F-L" required>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="axle" class="form-label">Axle</label>
                        <select name="axle" class="form-select" required>
                           <option value="Front">Front</option>
                           <option value="Middle">Middle</option>
                           <option value="Rear">Rear</option>
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="side" class="form-label">Side</label>
                        <select name="side" class="form-select" required>
                           <option value="Left">Left</option>
                           <option value="Right">Right</option>
                        </select>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col mb-3">
                        <label for="position_order" class="form-label">Order index</label>
                        <input type="number" id="position_order" name="position_order" class="form-control"
                           placeholder="e.g. 1" required>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" id="description" name="description" class="form-control"
                           placeholder="e.g. Front Left Outer">
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

   <!-- Edit Position Modal -->
   <div class="modal fade" id="editPositionModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Edit Position</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPositionForm" method="POST">
               @csrf
               @method('PUT')
               <div class="modal-body">
                  <div class="row">
                     <div class="col mb-3">
                        <label for="edit_position_code" class="form-label">Position Code</label>
                        <input type="text" id="edit_position_code" name="position_code" class="form-control"
                           required>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_axle" class="form-label">Axle</label>
                        <select id="edit_axle" name="axle" class="form-select" required>
                           <option value="Front">Front</option>
                           <option value="Middle">Middle</option>
                           <option value="Rear">Rear</option>
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_side" class="form-label">Side</label>
                        <select id="edit_side" name="side" class="form-select" required>
                           <option value="Left">Left</option>
                           <option value="Right">Right</option>
                        </select>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col mb-3">
                        <label for="edit_position_order" class="form-label">Order index</label>
                        <input type="number" id="edit_position_order" name="position_order" class="form-control"
                           required>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <input type="text" id="edit_description" name="description" class="form-control">
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
         const editButtons = document.querySelectorAll('.edit-position');
         const editForm = document.querySelector('#editPositionForm');

         editButtons.forEach(button => {
            button.addEventListener('click', function() {
               const id = this.getAttribute('data-id');
               const code = this.getAttribute('data-code');
               const axle = this.getAttribute('data-axle');
               const side = this.getAttribute('data-side');
               const order = this.getAttribute('data-order');
               const desc = this.getAttribute('data-desc');

               editForm.action = `/tyre_performance/master/positions/${id}`;
               document.querySelector('#edit_position_code').value = code;
               document.querySelector('#edit_axle').value = axle;
               document.querySelector('#edit_side').value = side;
               document.querySelector('#edit_position_order').value = order;
               document.querySelector('#edit_description').value = desc === 'null' ? '' : desc;
            });
         });
      });
   </script>
@endsection
