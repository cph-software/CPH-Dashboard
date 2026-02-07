@extends('layouts.admin')

@section('title', 'Master Tyre Locations')

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyre Locations</h4>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
            <i class="ri-add-line me-1"></i> Add Location
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
                     <th>Location Name</th>
                     <th>Type</th>
                     <th>Capacity</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @forelse($locations as $loc)
                     <tr>
                        <td><strong>{{ $loc->location_name }}</strong></td>
                        <td>
                           <span
                              class="badge bg-label-{{ $loc->location_type == 'Warehouse' ? 'primary' : ($loc->location_type == 'Service' ? 'warning' : 'danger') }}">
                              {{ $loc->location_type }}
                           </span>
                        </td>
                        <td>{{ $loc->capacity ?? '-' }}</td>
                        <td>
                           <div class="dropdown">
                              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                 <i class="ri-more-2-fill"></i>
                              </button>
                              <div class="dropdown-menu">
                                 <a class="dropdown-item edit-location" href="javascript:void(0);" data-bs-toggle="modal"
                                    data-bs-target="#editLocationModal" data-id="{{ $loc->id }}"
                                    data-name="{{ $loc->location_name }}" data-type="{{ $loc->location_type }}"
                                    data-capacity="{{ $loc->capacity }}">
                                    <i class="ri-pencil-line me-1"></i> Edit
                                 </a>
                                 <form action="{{ route('tyre-locations.destroy', $loc->id) }}" method="POST"
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
                        <td colspan="4" class="text-center">No data found</td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
      </div>
   </div>

   <!-- Add Location Modal -->
   <div class="modal fade" id="addLocationModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Add New Location</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-locations.store') }}" method="POST">
               @csrf
               <div class="modal-body">
                  <div class="row">
                     <div class="col mb-3">
                        <label for="location_name" class="form-label">Location Name</label>
                        <input type="text" id="location_name" name="location_name" class="form-control"
                           placeholder="e.g. Workshop Store" required>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="location_type" class="form-label">Location Type</label>
                        <select name="location_type" class="form-select" required>
                           <option value="Warehouse">Warehouse</option>
                           <option value="Service">Service</option>
                           <option value="Disposal">Disposal</option>
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="capacity" class="form-label">Capacity</label>
                        <input type="number" id="capacity" name="capacity" class="form-control" placeholder="e.g. 50">
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

   <!-- Edit Location Modal -->
   <div class="modal fade" id="editLocationModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Edit Location</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editLocationForm" method="POST">
               @csrf
               @method('PUT')
               <div class="modal-body">
                  <div class="row">
                     <div class="col mb-3">
                        <label for="edit_location_name" class="form-label">Location Name</label>
                        <input type="text" id="edit_location_name" name="location_name" class="form-control"
                           required>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_location_type" class="form-label">Location Type</label>
                        <select id="edit_location_type" name="location_type" class="form-select" required>
                           <option value="Warehouse">Warehouse</option>
                           <option value="Service">Service</option>
                           <option value="Disposal">Disposal</option>
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_location_capacity" class="form-label">Capacity</label>
                        <input type="number" id="edit_location_capacity" name="capacity" class="form-control">
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
         const editButtons = document.querySelectorAll('.edit-location');
         const editForm = document.querySelector('#editLocationForm');

         editButtons.forEach(button => {
            button.addEventListener('click', function() {
               const id = this.getAttribute('data-id');
               const name = this.getAttribute('data-name');
               const type = this.getAttribute('data-type');
               const capacity = this.getAttribute('data-capacity');

               editForm.action = `/tyre_performance/master/locations/${id}`;
               document.querySelector('#edit_location_name').value = name;
               document.querySelector('#edit_location_type').value = type;
               document.querySelector('#edit_location_capacity').value = capacity === 'null' ? '' :
                  capacity;
            });
         });
      });
   </script>
@endsection
