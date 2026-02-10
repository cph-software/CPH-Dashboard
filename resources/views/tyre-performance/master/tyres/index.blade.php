@extends('layouts.admin')

@section('title', 'Master Tyres')

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
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyres</h4>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTyreModal">
            <i class="ri-add-line me-1"></i> Add Tyre
         </button>
      </div>

      <div class="card">
         <div class="card-datatable table-responsive">
            <table class="datatables-tyres table border-top table-hover">
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
                  {{-- Data loaded via AJAX --}}
               </tbody>
            </table>
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
                        <select name="tyre_brand_id" class="form-select select2" data-placeholder="Select Brand" required>
                           <option value="">Select Brand</option>
                           @foreach ($brands as $brand)
                              <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="tyre_size_id" class="form-label">Size</label>
                        <select name="tyre_size_id" id="tyre_size_id" class="form-select select2"
                           data-placeholder="Select Size" required>
                           <option value="">Select Size</option>
                           @foreach ($sizes as $size)
                              <option value="{{ $size->id }}" data-type="{{ $size->type }}">{{ $size->size }}
                              </option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="tyre_pattern_id" class="form-label">Pattern</label>
                        <select name="tyre_pattern_id" class="form-select select2" data-placeholder="Select Pattern">
                           <option value="">Select Pattern</option>
                           @foreach ($patterns as $pattern)
                              <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="tyre_segment_id" class="form-label">Segment</label>
                        <select name="tyre_segment_id" class="form-select select2" data-placeholder="Select Segment">
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
                        <select id="tyre_type" name="tyre_type" class="form-select" required>
                           <option value="Radial">Radial</option>
                           <option value="Bias">Bias</option>
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="work_location_id" class="form-label">Location</label>
                        <select name="work_location_id" class="form-select select2" data-placeholder="Select Location"
                           required>
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
                        <select id="edit_brand_id" name="tyre_brand_id" class="form-select select2" required>
                           <option value="">Select Brand</option>
                           @foreach ($brands as $brand)
                              <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_size_id" class="form-label">Size</label>
                        <select id="edit_size_id" name="tyre_size_id" class="form-select select2" required>
                           <option value="">Select Size</option>
                           @foreach ($sizes as $size)
                              <option value="{{ $size->id }}" data-type="{{ $size->type }}">{{ $size->size }}
                              </option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_pattern_id" class="form-label">Pattern</label>
                        <select id="edit_pattern_id" name="tyre_pattern_id" class="form-select select2">
                           <option value="">Select Pattern</option>
                           @foreach ($patterns as $pattern)
                              <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_segment_id" class="form-label">Segment</label>
                        <select id="edit_segment_id" name="tyre_segment_id" class="form-select select2">
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
                        <select id="edit_tyre_type" name="tyre_type" class="form-select" required>
                           <option value="Radial">Radial</option>
                           <option value="Bias">Bias</option>
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_work_location_id" class="form-label">Location</label>
                        <select id="edit_work_location_id" name="work_location_id" class="form-select select2" required>
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

   <form id="deleteForm" method="POST" style="display: none;">
      @csrf
      @method('DELETE')
   </form>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         const table = $('.datatables-tyres').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('tyre-master.data') }}",
            columns: [{
                  data: 'serial_number',
                  render: function(data) {
                     return `<strong>${data}</strong>`;
                  }
               },
               {
                  data: 'brand.brand_name',
                  defaultContent: '-'
               },
               {
                  data: 'size.size',
                  defaultContent: '-'
               },
               {
                  data: 'pattern.name',
                  defaultContent: '-'
               },
               {
                  data: 'segment.segment_name',
                  defaultContent: '-'
               },
               {
                  data: 'tyre_type',
                  defaultContent: '-'
               },
               {
                  data: 'location.location_name',
                  defaultContent: '-'
               },
               {
                  data: 'status',
                  render: function(data) {
                     const badges = {
                        'New': 'primary',
                        'Installed': 'success',
                        'Scrap': 'danger',
                        'Repaired': 'warning'
                     };
                     return `<span class="badge bg-label-${badges[data] || 'secondary'}">${data}</span>`;
                  }
               },
               {
                  data: null,
                  searchable: false,
                  orderable: false,
                  render: function(data, type, row) {
                     return `
                        <div class="d-flex align-items-center">
                           <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 edit-tyre"
                              href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editTyreModal"
                              data-id="${row.id}" data-serial="${row.serial_number}"
                              data-brand-id="${row.tyre_brand_id}" data-size-id="${row.tyre_size_id}"
                              data-pattern-id="${row.tyre_pattern_id}"
                              data-segment-id="${row.tyre_segment_id}" data-type="${row.tyre_type}"
                              data-location-id="${row.work_location_id}" data-status="${row.status}"
                              title="Edit">
                              <i class="icon-base ri ri-pencil-line"></i>
                           </a>
                           <button type="button"
                              class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light delete-tyre"
                              data-id="${row.id}" data-serial="${row.serial_number}" title="Delete">
                              <i class="icon-base ri ri-delete-bin-line"></i>
                           </button>
                        </div>
                     `;
                  }
               }
            ],
            displayLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
         });

         const editForm = $('#editTyreForm');

         $(document).on('click', '.edit-tyre', function() {
            const id = $(this).data('id');
            const serial = $(this).data('serial');
            const brandId = $(this).data('brand-id');
            const sizeId = $(this).data('size-id');
            const patternId = $(this).data('pattern-id');
            const segmentId = $(this).data('segment-id');
            const type = $(this).data('type');
            const locationId = $(this).data('location-id');
            const status = $(this).data('status');

            editForm.attr('action', `/tyre_performance/master/tyres/${id}`);
            $('#edit_serial_number').val(serial);
            $('#edit_brand_id').val(brandId).trigger('change');
            $('#edit_size_id').val(sizeId).trigger('change');
            $('#edit_pattern_id').val(patternId === 'null' ? '' : patternId).trigger('change');
            $('#edit_segment_id').val(segmentId === 'null' ? '' : segmentId).trigger('change');
            $('#edit_tyre_type').val(type === 'null' ? '' : type);
            $('#edit_work_location_id').val(locationId).trigger('change');
            $('#edit_status').val(status);
         });

         $(document).on('click', '.delete-tyre', function() {
            const id = $(this).data('id');
            const serial = $(this).data('serial');

            Swal.fire({
               title: 'Yakin ingin menghapus?',
               text: `Ban SN "${serial}" akan dihapus permanen!`,
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
                  form.action = `{{ url('tyre_performance/master_tyre') }}/${id}`;
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

         // Auto-sync Tyre Type based on Size
         $('#tyre_size_id').on('change', function() {
            const type = $(this).find(':selected').data('type');
            if (type) {
               $('#tyre_type').val(type);
            }
         });

         $('#edit_size_id').on('change', function() {
            const type = $(this).find(':selected').data('type');
            if (type) {
               $('#edit_tyre_type').val(type);
            }
         });

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
