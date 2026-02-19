@extends('layouts.admin')

@section('title', 'Master Tyre Sizes')

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
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Tyre Sizes</h4>
         @if (hasPermission('Sizes', 'create'))
            <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSizeModal">
               <i class="icon-base ri ri-add-line me-1"></i> Add Size
            </a>
         @endif
      </div>

      <div class="card">
         <div class="card-datatable table-responsive">
            <table class="datatables-sizes table border-top table-hover">
               <thead>
                  <tr>
                     <th>Size</th>
                     <th>Brand</th>
                     <th>Pattern</th>
                     <th>Type</th>
                     <th>Std OTD</th>
                     <th>Ply Rating</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @foreach ($sizes as $size)
                     <tr>
                        <td><strong>{{ $size->size }}</strong></td>
                        <td>{{ $size->brand->brand_name ?? '-' }}</td>
                        <td>{{ $size->pattern->name ?? '-' }}</td>
                        <td>{{ $size->type }}</td>
                        <td>{{ $size->std_otd ?? '-' }}</td>
                        <td>{{ $size->ply_rating ?? '-' }}</td>
                        <td>
                           <div class="d-flex align-items-center">
                              @if (hasPermission('Sizes', 'update'))
                                 <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 edit-size"
                                    href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editSizeModal"
                                    data-id="{{ $size->id }}" data-size="{{ $size->size }}"
                                    data-brand-id="{{ $size->tyre_brand_id }}"
                                    data-pattern-id="{{ $size->tyre_pattern_id }}" data-type="{{ $size->type }}"
                                    data-otd="{{ $size->std_otd }}" data-ply="{{ $size->ply_rating }}" title="Edit">
                                    <i class="icon-base ri ri-pencil-line"></i>
                                 </a>
                              @endif
                              @if (hasPermission('Sizes', 'delete'))
                                 <button type="button"
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light delete-size"
                                    data-id="{{ $size->id }}" data-size="{{ $size->size }}" title="Delete">
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
                        <select name="tyre_brand_id" class="form-select select2" data-placeholder="Select Brand" required>
                           <option value="">Select Brand</option>
                           @foreach ($brands as $brand)
                              <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="tyre_pattern_id" class="form-label">Pattern</label>
                        <select name="tyre_pattern_id" class="form-select select2-tags"
                           data-placeholder="Select or Type Pattern">
                           <option value="">Select Pattern</option>
                           @foreach ($patterns as $pattern)
                              <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                           <option value="Bias">Bias</option>
                           <option value="Radial">Radial</option>
                        </select>
                     </div>
                  </div>
                  <div class="row">
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
                        <select id="edit_brand_id" name="tyre_brand_id" class="form-select select2" required>
                           <option value="">Select Brand</option>
                           @foreach ($brands as $brand)
                              <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_pattern_id" class="form-label">Pattern</label>
                        <select id="edit_pattern_id" name="tyre_pattern_id" class="form-select select2-tags"
                           data-placeholder="Select or Type Pattern">
                           <option value="">Select Pattern</option>
                           @foreach ($patterns as $pattern)
                              <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                           @endforeach
                        </select>
                     </div>
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
         $('.datatables-sizes').DataTable({
            order: [
               [0, 'desc']
            ],
            displayLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
         });

         const editForm = $('#editSizeForm');

         $(document).on('click', '.edit-size', function() {
            const id = $(this).data('id');
            const size = $(this).data('size');
            const brandId = $(this).data('brand-id');
            const patternId = $(this).data('pattern-id');
            const type = $(this).data('type');
            const otd = $(this).data('otd');
            const ply = $(this).data('ply');

            editForm.attr('action', `{{ url('master_data_tyre/master_size') }}/${id}`);
            $('#edit_size').val(size);
            $('#edit_brand_id').val(brandId).trigger('change');
            $('#edit_pattern_id').val(patternId).trigger('change');
            $('#edit_type').val(type);
            $('#edit_otd').val(otd === 'null' || otd === null ? '' : otd);
            $('#edit_ply').val(ply === 'null' || ply === null ? '' : ply);
         });

         $(document).on('click', '.delete-size', function() {
            const id = $(this).data('id');
            const sizeValue = $(this).data('size');

            Swal.fire({
               title: 'Yakin ingin menghapus?',
               text: `Ukuran Ban "${sizeValue}" akan dihapus permanen!`,
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
                  form.action = `{{ url('master_data_tyre/master_size') }}/${id}`;
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

         // Auto-detect Tyre Type from Size string
         function detectType(size) {
            if (!size) return null;
            const s = size.toUpperCase();
            if (s.includes('R') || s.includes('RADIAL')) return 'Radial';
            if (s.includes('-') || s.includes('BIAS')) return 'Bias';
            return null;
         }

         $('#size').on('input', function() {
            const type = detectType($(this).val());
            if (type) {
               $('select[name="type"]').val(type);
            }
         });

         $('#edit_size').on('input', function() {
            const type = detectType($(this).val());
            if (type) {
               $('#edit_type').val(type);
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

         // Initialize Select2 with Tags
         $('.select2-tags').each(function() {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
               placeholder: $this.data('placeholder'),
               dropdownParent: $this.closest('.modal'),
               tags: true
            });
         });
      });
   </script>
@endsection
