@extends('layouts.admin')

@section('title', 'Master Tyre Locations')

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
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyre Locations</h4>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
            <i class="ri-add-line me-1"></i> Add Location
         </button>
      </div>

      <div class="card">
         <div class="card-datatable table-responsive">
            <table class="datatables-locations table border-top table-hover">
               <thead>
                  <tr>
                     <th>Location Name</th>
                     <th>Type</th>
                     <th>Capacity</th>
                     <th>Current Stock</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @foreach ($locations as $loc)
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
                           <span
                              class="badge bg-label-{{ $loc->current_stock > $loc->capacity * 0.8 ? 'danger' : ($loc->current_stock > $loc->capacity * 0.5 ? 'warning' : 'success') }} rounded-pill">
                              {{ $loc->current_stock ?? 0 }} tyres
                           </span>
                        </td>
                        <td>
                           <div class="d-flex align-items-center">
                              <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 edit-location"
                                 href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editLocationModal"
                                 data-id="{{ $loc->id }}" data-name="{{ $loc->location_name }}"
                                 data-type="{{ $loc->location_type }}" data-capacity="{{ $loc->capacity }}" title="Edit">
                                 <i class="icon-base ri ri-pencil-line"></i>
                              </a>
                              <button type="button"
                                 class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light delete-location"
                                 data-id="{{ $loc->id }}" data-name="{{ $loc->location_name }}" title="Delete">
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
                        <input type="text" id="edit_location_name" name="location_name" class="form-control" required>
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
      $(document).ready(function () {
         $('.datatables-locations').DataTable({
            order: [
               [0, 'desc']
            ],
            displayLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
         });

         const editForm = $('#editLocationForm');

         $(document).on('click', '.edit-location', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const type = $(this).data('type');
            const capacity = $(this).data('capacity');

            editForm.attr('action', `{{ url('master_data/master_location') }}/${id}`);
            $('#edit_location_name').val(name);
            $('#edit_location_type').val(type);
            $('#edit_location_capacity').val(capacity === 'null' ? '' : capacity);
         });

         $(document).on('click', '.delete-location', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');

            Swal.fire({
               title: 'Yakin ingin menghapus?',
               text: `Lokasi "${name}" akan dihapus permanen!`,
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
                  form.action = `{{ url('master_data/master_location') }}/${id}`;
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
         });
   </script>
@endsection