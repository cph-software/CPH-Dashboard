@extends('layouts.admin')

@section('title', 'Master Tyre Segments')

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
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyre Segments</h4>
         @if (hasPermission('Segments', 'create'))
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSegmentModal">
               <i class="ri-add-line me-1"></i> Add Segment
            </button>
         @endif
      </div>

      @if (session('success'))
         <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif

      <div class="card">
         <div class="card-datatable table-responsive">
            <table class="datatables-segments table border-top table-hover">
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
                  @foreach ($segments as $segment)
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
                              @if (hasPermission('Segments', 'update'))
                                 <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 edit-segment"
                                    href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editSegmentModal"
                                    data-id="{{ $segment->id }}" data-segment-id="{{ $segment->segment_id }}"
                                    data-name="{{ $segment->segment_name }}"
                                    data-location-id="{{ $segment->tyre_location_id }}"
                                    data-terrain="{{ $segment->terrain_type }}" data-status="{{ $segment->status }}"
                                    title="Edit">
                                    <i class="icon-base ri ri-pencil-line"></i>
                                 </a>
                              @endif
                              @if (hasPermission('Segments', 'delete'))
                                 <button type="button"
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light delete-segment"
                                    data-id="{{ $segment->id }}" data-name="{{ $segment->segment_name }}"
                                    title="Delete">
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
                        <select name="tyre_location_id" class="form-select select2" data-placeholder="Select Location">
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
                        <select id="edit_location_id" name="tyre_location_id" class="form-select select2">
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

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         $('.datatables-segments').DataTable();

         const editForm = $('#editSegmentForm');

         $(document).on('click', '.edit-segment', function() {
            const id = $(this).data('id');
            const segmentId = $(this).data('segment-id');
            const name = $(this).data('name');
            const locationId = $(this).data('location-id');
            const terrain = $(this).data('terrain');
            const status = $(this).data('status');

            editForm.attr('action', `{{ url('master_data_tyre/master_segment') }}/${id}`);
            $('#edit_segment_id').val(segmentId);
            $('#edit_segment_name').val(name);
            $('#edit_location_id').val(locationId === 'null' ? '' : (locationId || ''));
            $('#edit_terrain').val(terrain);
            $('#edit_segment_status').val(status);
         });

         $(document).on('click', '.delete-segment', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            Swal.fire({
               title: 'Yakin ingin menghapus?',
               text: `Segment "${name}" akan dihapus permanen!`,
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
                  form.action = `{{ url('master_data_tyre/master_segment') }}/${id}`;
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

         // Initialize Select2
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
