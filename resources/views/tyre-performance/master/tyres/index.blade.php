@extends('layouts.admin')

@section('title', 'Master Tyres')

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyres</h4>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTyreModal">
            <i class="ri-add-line me-1"></i> Add Tyre
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
                     <th>Serial Number</th>
                     <th>Brand</th>
                     <th>Size</th>
                     <th>Pattern</th>
                     <th>Segment</th>
                     <th>Type</th>
                     <th>Location</th>
                     <th>Status</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @forelse($tyres as $tyre)
                     <tr>
                        <td><strong>{{ $tyre->serial_number }}</strong></td>
                        <td>{{ $tyre->brand->brand_name ?? '-' }}</td>
                        <td>{{ $tyre->size->size ?? '-' }}</td>
                        <td>{{ $tyre->pattern->name ?? '-' }}</td>
                        <td>{{ $tyre->segment->segment_name ?? '-' }}</td>
                        <td>{{ $tyre->tyre_type ?? '-' }}</td>
                        <td>{{ $tyre->location->location_name ?? '-' }}</td>
                        <td>
                           <span
                              class="badge bg-label-{{ $tyre->status == 'New' ? 'primary' : ($tyre->status == 'Installed' ? 'success' : ($tyre->status == 'Scrap' ? 'danger' : 'warning')) }}">
                              {{ $tyre->status }}
                           </span>
                        </td>
                        <td>
                           <div class="d-flex align-items-center">
                              <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 edit-tyre"
                                 href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editTyreModal"
                                 data-id="{{ $tyre->id }}" data-serial="{{ $tyre->serial_number }}"
                                 data-brand-id="{{ $tyre->tyre_brand_id }}" data-size-id="{{ $tyre->tyre_size_id }}"
                                 data-pattern-id="{{ $tyre->tyre_pattern_id }}"
                                 data-segment-id="{{ $tyre->tyre_segment_id }}" data-type="{{ $tyre->tyre_type }}"
                                 data-location-id="{{ $tyre->work_location_id }}" data-status="{{ $tyre->status }}"
                                 title="Edit">
                                 <i class="ri-pencil-line"></i>
                              </a>
                              <form action="{{ route('tyre-master.destroy', $tyre->id) }}" method="POST"
                                 onsubmit="return confirm('Are you sure?')" class="d-inline">
                                 @csrf
                                 @method('DELETE')
                                 <button type="submit"
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light"
                                    title="Delete">
                                    <i class="icon-base ri ri-delete-bin-line"></i>
                                 </button>
                              </form>
                           </div>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="9" class="text-center">No data found</td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
            <div class="card-footer px-3 py-2 border-top">
               <div class="d-flex justify-content-center overflow-auto">
                  {{ $tyres->links() }}
               </div>
            </div>
         </div>
      </div>

      <!-- Add Tyre Modal -->
      <div class="modal fade" id="addTyreModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title">Add New Tyre</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <form action="{{ route('tyre-master.store') }}" method="POST">
                  @csrf
                  <div class="modal-body">
                     <div class="row">
                        <div class="col mb-3">
                           <label for="serial_number" class="form-label">Serial Number</label>
                           <input type="text" id="serial_number" name="serial_number" class="form-control"
                              placeholder="Enter Serial Number" required>
                        </div>
                     </div>
                     <div class="row g-2">
                        <div class="col mb-3">
                           <label for="tyre_brand_id" class="form-label">Brand</label>
                           <select name="tyre_brand_id" class="form-select" required>
                              <option value="">Select Brand</option>
                              @foreach ($brands as $brand)
                                 <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col mb-3">
                           <label for="tyre_size_id" class="form-label">Size</label>
                           <select name="tyre_size_id" class="form-select" required>
                              <option value="">Select Size</option>
                              @foreach ($sizes as $size)
                                 <option value="{{ $size->id }}">{{ $size->size }}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                     <div class="row g-2">
                        <div class="col mb-3">
                           <label for="tyre_pattern_id" class="form-label">Pattern</label>
                           <select name="tyre_pattern_id" class="form-select">
                              <option value="">Select Pattern</option>
                              @foreach ($patterns as $pattern)
                                 <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col mb-3">
                           <label for="tyre_segment_id" class="form-label">Segment</label>
                           <select name="tyre_segment_id" class="form-select">
                              <option value="">Select Segment</option>
                              @foreach ($segments as $segment)
                                 <option value="{{ $segment->id }}">{{ $segment->segment_name }}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                     <div class="row g-2">
                        <div class="col mb-3">
                           <label for="tyre_type" class="form-label">Type</label>
                           <input type="text" id="tyre_type" name="tyre_type" class="form-control"
                              placeholder="e.g. Radial" required>
                        </div>
                        <div class="col mb-3">
                           <label for="work_location_id" class="form-label">Location</label>
                           <select name="work_location_id" class="form-select" required>
                              <option value="">Select Location</option>
                              @foreach ($locations as $loc)
                                 <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col mb-3">
                           <label for="status" class="form-label">Status</label>
                           <select name="status" class="form-select" required>
                              <option value="New">New</option>
                              <option value="Installed">Installed</option>
                              <option value="Repaired">Repaired</option>
                              <option value="Scrap">Scrap</option>
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

      <!-- Edit Tyre Modal -->
      <div class="modal fade" id="editTyreModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title">Edit Tyre</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <form id="editTyreForm" method="POST">
                  @csrf
                  @method('PUT')
                  <div class="modal-body">
                     <div class="row">
                        <div class="col mb-3">
                           <label for="edit_serial_number" class="form-label">Serial Number</label>
                           <input type="text" id="edit_serial_number" name="serial_number" class="form-control"
                              required>
                        </div>
                     </div>
                     <div class="row g-2">
                        <div class="col mb-3">
                           <label for="edit_brand_id" class="form-label">Brand</label>
                           <select id="edit_brand_id" name="tyre_brand_id" class="form-select" required>
                              <option value="">Select Brand</option>
                              @foreach ($brands as $brand)
                                 <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col mb-3">
                           <label for="edit_size_id" class="form-label">Size</label>
                           <select id="edit_size_id" name="tyre_size_id" class="form-select" required>
                              <option value="">Select Size</option>
                              @foreach ($sizes as $size)
                                 <option value="{{ $size->id }}">{{ $size->size }}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                     <div class="row g-2">
                        <div class="col mb-3">
                           <label for="edit_pattern_id" class="form-label">Pattern</label>
                           <select id="edit_pattern_id" name="tyre_pattern_id" class="form-select">
                              <option value="">Select Pattern</option>
                              @foreach ($patterns as $pattern)
                                 <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col mb-3">
                           <label for="edit_segment_id" class="form-label">Segment</label>
                           <select id="edit_segment_id" name="tyre_segment_id" class="form-select">
                              <option value="">Select Segment</option>
                              @foreach ($segments as $segment)
                                 <option value="{{ $segment->id }}">{{ $segment->segment_name }}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                     <div class="row g-2">
                        <div class="col mb-3">
                           <label for="edit_tyre_type" class="form-label">Type</label>
                           <input type="text" id="edit_tyre_type" name="tyre_type" class="form-control" required>
                        </div>
                        <div class="col mb-3">
                           <label for="edit_work_location_id" class="form-label">Location</label>
                           <select id="edit_work_location_id" name="work_location_id" class="form-select" required>
                              <option value="">Select Location</option>
                              @foreach ($locations as $loc)
                                 <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col mb-3">
                           <label for="edit_status" class="form-label">Status</label>
                           <select id="edit_status" name="status" class="form-select" required>
                              <option value="New">New</option>
                              <option value="Installed">Installed</option>
                              <option value="Repaired">Repaired</option>
                              <option value="Scrap">Scrap</option>
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
            const editButtons = document.querySelectorAll('.edit-tyre');
            const editForm = document.querySelector('#editTyreForm');

            editButtons.forEach(button => {
               button.addEventListener('click', function() {
                  const id = this.getAttribute('data-id');
                  const serial = this.getAttribute('data-serial');
                  const brandId = this.getAttribute('data-brand-id');
                  const sizeId = this.getAttribute('data-size-id');
                  const patternId = this.getAttribute('data-pattern-id');
                  const segmentId = this.getAttribute('data-segment-id');
                  const type = this.getAttribute('data-type');
                  const locationId = this.getAttribute('data-location-id');
                  const status = this.getAttribute('data-status');

                  editForm.action = `/tyre_performance/master/tyres/${id}`;
                  document.querySelector('#edit_serial_number').value = serial;
                  document.querySelector('#edit_brand_id').value = brandId;
                  document.querySelector('#edit_size_id').value = sizeId;
                  document.querySelector('#edit_pattern_id').value = patternId === 'null' ? '' : patternId;
                  document.querySelector('#edit_segment_id').value = segmentId === 'null' ? '' : segmentId;
                  document.querySelector('#edit_tyre_type').value = type === 'null' ? '' : type;
                  document.querySelector('#edit_work_location_id').value = locationId;
                  document.querySelector('#edit_status').value = status;
               });
            });
         });
      </script>
   @endsection
