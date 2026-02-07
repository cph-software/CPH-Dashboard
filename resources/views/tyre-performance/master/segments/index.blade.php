@extends('layouts.admin')

@section('title', 'Master Tyre Segments')

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyre Segments</h4>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSegmentModal">
            <i class="ri-add-line me-1"></i> Add Segment
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
                     <th>Segment ID</th>
                     <th>Name</th>
                     <th>Work Location</th>
                     <th>Terrain</th>
                     <th>Status</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @forelse($segments as $segment)
                     <tr>
                        <td><strong>{{ $segment->segment_id }}</strong></td>
                        <td>{{ $segment->segment_name }}</td>
                        <td>{{ $segment->location->location_name ?? '-' }}</td>
                        <td>
                           <span class="badge bg-label-info">{{ $segment->terrain_type }}</span>
                        </td>
                        <td>
                           <span class="badge bg-label-{{ $segment->status == 'Active' ? 'success' : 'secondary' }}">
                              {{ $segment->status }}
                           </span>
                        </td>
                        <td>
                           <div class="d-flex align-items-center">
                              <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 edit-segment"
                                 href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editSegmentModal"
                                 data-id="{{ $segment->id }}" data-segment-id="{{ $segment->segment_id }}"
                                 data-name="{{ $segment->segment_name }}"
                                 data-location-id="{{ $segment->tyre_location_id }}"
                                 data-terrain="{{ $segment->terrain_type }}" data-status="{{ $segment->status }}"
                                 title="Edit">
                                 <i class="ri-pencil-line"></i>
                              </a>
                              <form action="{{ route('tyre-segments.destroy', $segment->id) }}" method="POST"
                                 onsubmit="return confirm('Are you sure?')" class="d-inline">
                                 @csrf
                                 @method('DELETE')
                                 <button type="submit"
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light"
                                    title="Delete">
                                    <i class="ri-delete-bin-line"></i>
                                 </button>
                              </form>
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

   <!-- Add Segment Modal -->
   <div class="modal fade" id="addSegmentModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Add New Segment</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-segments.store') }}" method="POST">
               @csrf
               <div class="modal-body">
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="segment_id" class="form-label">Segment ID</label>
                        <input type="text" id="segment_id" name="segment_id" class="form-control"
                           placeholder="e.g. BB-01" required>
                     </div>
                     <div class="col mb-3">
                        <label for="segment_name" class="form-label">Segment Name</label>
                        <input type="text" id="segment_name" name="segment_name" class="form-control"
                           placeholder="e.g. Coal Hauling" required>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col mb-3">
                        <label for="tyre_location_id" class="form-label">Work Location</label>
                        <select name="tyre_location_id" class="form-select">
                           <option value="">Select Location</option>
                           @foreach ($locations as $loc)
                              <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="terrain_type" class="form-label">Terrain Type</label>
                        <select name="terrain_type" class="form-select" required>
                           <option value="Muddy">Muddy</option>
                           <option value="Rocky">Rocky</option>
                           <option value="Asphalt">Asphalt</option>
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

   <!-- Edit Segment Modal -->
   <div class="modal fade" id="editSegmentModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Edit Segment</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSegmentForm" method="POST">
               @csrf
               @method('PUT')
               <div class="modal-body">
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_segment_id" class="form-label">Segment ID</label>
                        <input type="text" id="edit_segment_id" name="segment_id" class="form-control" required>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_segment_name" class="form-label">Segment Name</label>
                        <input type="text" id="edit_segment_name" name="segment_name" class="form-control" required>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col mb-3">
                        <label for="edit_location_id" class="form-label">Work Location</label>
                        <select id="edit_location_id" name="tyre_location_id" class="form-select">
                           <option value="">Select Location</option>
                           @foreach ($locations as $loc)
                              <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_terrain" class="form-label">Terrain Type</label>
                        <select id="edit_terrain" name="terrain_type" class="form-select" required>
                           <option value="Muddy">Muddy</option>
                           <option value="Rocky">Rocky</option>
                           <option value="Asphalt">Asphalt</option>
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_segment_status" class="form-label">Status</label>
                        <select id="edit_segment_status" name="status" class="form-select" required>
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
         const editButtons = document.querySelectorAll('.edit-segment');
         const editForm = document.querySelector('#editSegmentForm');

         editButtons.forEach(button => {
            button.addEventListener('click', function() {
               const id = this.getAttribute('data-id');
               const segmentId = this.getAttribute('data-segment-id');
               const name = this.getAttribute('data-name');
               const locationId = this.getAttribute('data-location-id');
               const terrain = this.getAttribute('data-terrain');
               const status = this.getAttribute('data-status');

               editForm.action = `/tyre_performance/master/segments/${id}`;
               document.querySelector('#edit_segment_id').value = segmentId;
               document.querySelector('#edit_segment_name').value = name;
               document.querySelector('#edit_location_id').value = locationId === 'null' ? '' :
                  locationId;
               document.querySelector('#edit_terrain').value = terrain;
               document.querySelector('#edit_segment_status').value = status;
            });
         });
      });
   </script>
@endsection
