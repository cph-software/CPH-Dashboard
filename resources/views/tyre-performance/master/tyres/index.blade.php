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
         <div class="d-flex gap-2">
            <div id="bulk-actions-container" style="display: none;">
               <div class="btn-group me-2">
                  <button type="button" class="btn btn-outline-danger" id="btn-bulk-delete">
                     <i class="icon-base ri ri-delete-bin-line me-1"></i> Hapus
                  </button>
                  <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                     data-bs-target="#bulkUpdateModal">
                     <i class="icon-base ri ri-edit-line me-1"></i> Update
                  </button>
               </div>
            </div>
            <a href="{{ route('master_data.export', ['type' => 'assets', 'format' => 'excel']) }}"
               class="btn btn-outline-primary">
               <i class="icon-base ri ri-file-excel-2-line me-1"></i> Export Excel
            </a>
            @if (hasPermission('Import Approval', 'create'))
               <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                  data-bs-target="#importModal">
                  <i class="icon-base ri ri-upload-2-line me-1"></i> Import
               </button>
            @endif
            @if (hasPermission('Master Tyre', 'create'))
               <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTyreModal">
                  <i class="icon-base ri ri-add-line me-1"></i> Add Tyre
               </button>
            @endif
         </div>
      </div>

      <div class="card">
         <div class="card-datatable table-responsive">
            <table class="datatables-tyres table border-top table-hover">
               <thead>
                  <tr>
                     <th width="10"><input type="checkbox" class="form-check-input" id="check-all"></th>
                     <th>Serial Number</th>
                     <th>Brand</th>
                     <th>Size</th>
                     <th>Segment</th>
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
                     @if (auth()->user()->role_id == 1)
                        <div class="col-md-6 mb-3">
                           <label for="tyre_company_id" class="form-label fw-bold">Instansi / Company</label>
                           <select name="tyre_company_id" id="tyre_company_id" class="form-select select2"
                              data-placeholder="Pilih Perusahaan">
                              <option value="">-- Pilih Perusahaan --</option>
                              @foreach ($companies as $company)
                                 <option value="{{ $company->id }}"
                                    {{ session('active_company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->company_name }}
                                 </option>
                              @endforeach
                           </select>
                        </div>
                     @endif
                     <div class="col-md-6 mb-3">
                        <label for="serial_number" class="form-label">Serial Number</label>
                        <input type="text" id="serial_number" name="serial_number" class="form-control"
                           placeholder="Enter Serial Number" required>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="custom_serial_number" class="form-label">Custom Serial Number (Opsional)</label>
                        <input type="text" id="custom_serial_number" name="custom_serial_number" class="form-control"
                           placeholder="Enter Custom Code">
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col-md-4 mb-3">
                        <label for="tyre_brand_id" class="form-label">Brand</label>
                        <select id="tyre_brand_id" name="tyre_brand_id" class="form-select select2-tags"
                           data-placeholder="Select Brand" required>
                           <option value="">Select Brand</option>
                           @foreach ($brands as $brand)
                              <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-md-4 mb-3">
                        <label for="tyre_size_id" class="form-label">Size</label>
                        <select name="tyre_size_id" id="tyre_size_id" class="form-select select2-tags"
                           data-placeholder="Select Size" required>
                           <option value="">Select Size</option>
                           @foreach ($sizes as $size)
                              <option value="{{ $size->id }}" data-brand-id="{{ $size->tyre_brand_id }}">
                                 {{ $size->size }}
                              </option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-md-4 mb-3">
                        <label for="tyre_pattern_id" class="form-label">Pattern</label>
                        <select name="tyre_pattern_id" id="tyre_pattern_id" class="form-select select2-tags"
                           data-placeholder="Select Pattern">
                           <option value="">Select Pattern</option>
                           @foreach ($patterns as $pattern)
                              <option value="{{ $pattern->id }}" data-brand-id="{{ $pattern->tyre_brand_id }}">
                                 {{ $pattern->name }}
                              </option>
                           @endforeach
                        </select>
                     </div>
                  </div>

                  <div class="row g-2">
                     <div class="col-md-6 mb-3">
                        <label for="segment_name" class="form-label">Segment Name</label>
                        <input type="text" id="segment_name" name="segment_name" class="form-control"
                           placeholder="Ex: Mining, Logging, etc.">
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="ply_rating" class="form-label">Ply Rating</label>
                        <input type="text" id="ply_rating" name="ply_rating" class="form-control"
                           placeholder="Ex: 16PR, 18PR">
                     </div>
                  </div>

                  <div class="row g-2">
                     <div class="col-md-6 mb-3">
                        <label for="original_tread_depth" class="form-label">OTD (Original Tread Depth - mm)</label>
                        <input type="number" id="original_tread_depth" name="original_tread_depth"
                           class="form-control" placeholder="18.5" step="0.01">
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="price" class="form-label">Harga Beli (IDR)</label>
                        <input type="text" id="price" name="price" class="form-control currency-input"
                           placeholder="3.500.000">
                     </div>
                  </div>

                  <div class="row g-2">
                     <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                           <option value="New">New</option>
                           <option value="Installed">Installed</option>
                           <option value="Repaired">Repaired</option>
                           <option value="Retread">Retread</option>
                           <option value="Scrap">Scrap</option>
                        </select>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label class="form-label d-block">Location Status</label>
                        <div class="form-check form-switch mt-2">
                           <input class="form-check-input" type="checkbox" name="is_in_warehouse" value="1"
                              id="is_in_warehouse" checked>
                           <label class="form-check-label" for="is_in_warehouse">In Warehouse (Stock)</label>
                        </div>
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
                     @if (auth()->user()->role_id == 1)
                        <div class="col-md-6 mb-3">
                           <label for="edit_tyre_company_id" class="form-label fw-bold">Instansi / Company</label>
                           <select name="tyre_company_id" id="edit_tyre_company_id" class="form-select select2"
                              data-placeholder="Pilih Perusahaan">
                              <option value="">-- Pilih Perusahaan --</option>
                              @foreach ($companies as $company)
                                 <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                              @endforeach
                           </select>
                        </div>
                     @endif
                     <div class="col-md-6 mb-3">
                        <label for="edit_serial_number" class="form-label">Serial Number</label>
                        <input type="text" id="edit_serial_number" name="serial_number" class="form-control"
                           required>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="edit_custom_serial_number" class="form-label">Custom Serial Number (Opsional)</label>
                        <input type="text" id="edit_custom_serial_number" name="custom_serial_number"
                           class="form-control">
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col-md-4 mb-3">
                        <label for="edit_brand_id" class="form-label">Brand</label>
                        <select id="edit_brand_id" name="tyre_brand_id" class="form-select select2-tags" required>
                           <option value="">Select Brand</option>
                           @foreach ($brands as $brand)
                              <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-md-4 mb-3">
                        <label for="edit_size_id" class="form-label">Size</label>
                        <select id="edit_size_id" name="tyre_size_id" class="form-select select2-tags" required>
                           <option value="">Select Size</option>
                           @foreach ($sizes as $size)
                              <option value="{{ $size->id }}" data-brand-id="{{ $size->tyre_brand_id }}">
                                 {{ $size->size }}
                              </option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-md-4 mb-3">
                        <label for="edit_pattern_id" class="form-label">Pattern</label>
                        <select id="edit_pattern_id" name="tyre_pattern_id" class="form-select select2-tags">
                           <option value="">Select Pattern</option>
                           @foreach ($patterns as $pattern)
                              <option value="{{ $pattern->id }}" data-brand-id="{{ $pattern->tyre_brand_id }}">
                                 {{ $pattern->name }}
                              </option>
                           @endforeach
                        </select>
                     </div>
                  </div>

                  <div class="row g-2">
                     <div class="col-md-6 mb-3">
                        <label for="edit_segment_name" class="form-label">Segment Name</label>
                        <input type="text" id="edit_segment_name" name="segment_name" class="form-control">
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="edit_ply_rating" class="form-label">Ply Rating</label>
                        <input type="text" id="edit_ply_rating" name="ply_rating" class="form-control">
                     </div>
                  </div>

                  <div class="row g-2">
                     <div class="col-md-6 mb-3">
                        <label for="edit_original_tread_depth" class="form-label">OTD (Original Tread Depth - mm)</label>
                        <input type="number" id="edit_original_tread_depth" name="original_tread_depth"
                           class="form-control" step="0.01">
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="edit_price" class="form-label">Harga Beli (IDR)</label>
                        <input type="text" id="edit_price" name="price" class="form-control currency-input">
                     </div>
                  </div>

                  <div class="row g-2">
                     <div class="col-md-6 mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select id="edit_status" name="status" class="form-select" required>
                           <option value="New">New</option>
                           <option value="Installed">Installed</option>
                           <option value="Repaired">Repaired</option>
                           <option value="Retread">Retread</option>
                           <option value="Scrap">Scrap</option>
                        </select>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label class="form-label d-block">Location Status</label>
                        <div class="form-check form-switch mt-2">
                           <input class="form-check-input" type="checkbox" name="is_in_warehouse" value="1"
                              id="edit_is_in_warehouse">
                           <label class="form-check-label" for="edit_is_in_warehouse">In Warehouse (Stock)</label>
                        </div>
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

   <!-- Bulk Update Modal -->
   <div class="modal fade" id="bulkUpdateModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Batch Update Ban</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-master.bulk-action') }}" method="POST" id="bulkActionForm">
               @csrf
               <input type="hidden" name="action" value="update">
               <div id="bulk-ids-container"></div>
               <div class="modal-body">
                  <div class="alert alert-info">
                     <i class="ri-information-line me-1"></i> Field yang dikosongkan tidak akan diperbarui.
                  </div>
                  <div class="mb-3">
                     <label class="form-label">Update Status</label>
                     <select name="status" class="form-select">
                        <option value="">-- No Change --</option>
                        <option value="New">New</option>
                        <option value="Installed">Installed</option>
                        <option value="Repaired">Repaired</option>
                        <option value="Retread">Retread</option>
                        <option value="Scrap">Scrap</option>
                     </select>
                  </div>
                  <div class="mb-3">
                     <label class="form-label">Update Lokasi</label>
                     <select name="work_location_id" class="form-select select2-bulk"
                        data-placeholder="Select Location">
                        <option value=""></option>
                        @foreach ($locations as $loc)
                           <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="mb-3">
                     <label class="form-label">Update Segment</label>
                     <select name="tyre_segment_id" class="form-select select2-bulk" data-placeholder="Select Segment">
                        <option value=""></option>
                        @foreach ($segments as $segment)
                           <option value="{{ $segment->id }}">
                              {{ $segment->segment_name }} ({{ $segment->location->location_name ?? 'Global' }})
                           </option>
                        @endforeach
                     </select>
                  </div>
                  <div class="mb-3">
                     <label class="form-label">Update Retread Count</label>
                     <select name="retread_count" class="form-select">
                        <option value="">-- No Change --</option>
                        <option value="0">0 (New/R0)</option>
                        <option value="1">1 (R1)</option>
                        <option value="2">2 (R2)</option>
                        <option value="3">3 (R3)</option>
                     </select>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                  <button type="submit" class="btn btn-primary">Update Semua Terpilih</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <form id="bulkDeleteForm" action="{{ route('tyre-master.bulk-action') }}" method="POST" style="display: none;">
      @csrf
      <input type="hidden" name="action" value="delete">
      <div id="bulk-delete-ids-container"></div>
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
         const canUpdate = {{ hasPermission('Master Tyre', 'update') ? 'true' : 'false' }};
         const canDelete = {{ hasPermission('Master Tyre', 'delete') ? 'true' : 'false' }};

         const table = $('.datatables-tyres').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('tyre-master.data') }}",
            columns: [{
                  data: 'id',
                  orderable: false,
                  searchable: false,
                  render: function(data) {
                     return `<input type="checkbox" class="form-check-input tyre-checkbox" value="${data}">`;
                  }
               },
               {
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
                  data: 'segment_name',
                  defaultContent: '-'
               },
               {
                  data: 'is_in_warehouse',
                  render: function(data) {
                     return data ? '<span class="badge bg-label-info">Gudang</span>' :
                        '<span class="badge bg-label-warning">Terpasang</span>';
                  }
               },
               {
                  data: 'status',
                  render: function(data, type, row) {
                     const badges = {
                        'New': 'primary',
                        'Installed': 'success',
                        'Scrap': 'danger',
                        'Repaired': 'warning',
                        'Retread': 'info'
                     };

                     let displayText = data;
                     if (data === 'New') {
                        displayText = 'New (R0)';
                     } else if (data === 'Retread' && row.retread_count) {
                        displayText = `Retread R${row.retread_count}`;
                     }

                     return `<span class="badge bg-label-${badges[data] || 'secondary'}">${displayText}</span>`;
                  }
               },
               {
                  data: null,
                  searchable: false,
                  orderable: false,
                  render: function(data, type, row) {
                     let actions = `<div class="d-flex align-items-center">
                        <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1"
                           href="/master_tyre/${row.id}"
                           title="View Details">
                           <i class="icon-base ri ri-eye-line"></i>
                        </a>`;

                     if (canUpdate) {
                        actions += `
                            <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 edit-tyre"
                               href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editTyreModal"
                               data-id="${row.id}" data-serial="${row.serial_number}" data-custom-serial="${row.custom_serial_number || ''}" 
                               data-company-id="${row.tyre_company_id}"
                               data-brand-id="${row.tyre_brand_id}" data-size-id="${row.tyre_size_id}"
                               data-pattern-id="${row.tyre_pattern_id}"
                               data-segment-name="${row.segment_name || ''}"
                               data-warehouse="${row.is_in_warehouse ? 1 : 0}" data-status="${row.status}"
                               data-price="${row.price || ''}"
                               data-original-tread="${row.original_tread_depth || ''}"
                               data-ply-rating="${row.ply_rating || ''}"
                               data-retread-count="${row.retread_count || 0}"
                               title="Edit">
                               <i class="icon-base ri ri-pencil-line"></i>
                            </a>`;
                     }

                     if (canDelete) {
                        actions += `
                           <button type="button"
                              class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light delete-tyre"
                              data-id="${row.id}" data-serial="${row.serial_number}" title="Delete">
                              <i class="icon-base ri ri-delete-bin-line"></i>
                           </button>`;
                     }

                     actions += `</div>`;
                     return actions;
                  }
               }
            ],
            order: [],
            displayLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
         });

         // --- BULK ACTION LOGIC ---
         function updateBulkActions() {
            const selectedCount = $('.tyre-checkbox:checked').length;
            if (selectedCount > 0) {
               $('#bulk-actions-container').fadeIn();
            } else {
               $('#bulk-actions-container').fadeOut();
               $('#check-all').prop('checked', false);
            }
         }

         $(document).on('change', '#check-all', function() {
            $('.tyre-checkbox').prop('checked', this.checked);
            updateBulkActions();
         });

         $(document).on('change', '.tyre-checkbox', function() {
            updateBulkActions();
         });

         $('#btn-bulk-delete').on('click', function() {
            const selectedIds = $('.tyre-checkbox:checked').map(function() {
               return $(this).val();
            }).get();

            Swal.fire({
               title: 'Hapus Massal?',
               text: `Yakin ingin menghapus ${selectedIds.length} data ban terpilih? Data yang memiliki riwayat pergerakan tidak akan terhapus.`,
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
                  const $form = $('#bulkDeleteForm');
                  const $container = $('#bulk-delete-ids-container');
                  $container.empty();
                  selectedIds.forEach(id => {
                     $container.append(`<input type="hidden" name="ids[]" value="${id}">`);
                  });
                  $form.submit();
               }
            });
         });

         $('#bulkUpdateModal').on('show.bs.modal', function() {
            const selectedIds = $('.tyre-checkbox:checked').map(function() {
               return $(this).val();
            }).get();

            const $container = $('#bulk-ids-container');
            $container.empty();
            selectedIds.forEach(id => {
               $container.append(`<input type="hidden" name="ids[]" value="${id}">`);
            });
         });

         // Initialize bulk select2
         $('.select2-bulk').each(function() {
            $(this).wrap('<div class="position-relative"></div>').select2({
               placeholder: $(this).data('placeholder'),
               dropdownParent: $('#bulkUpdateModal'),
               allowClear: true,
               width: '100%'
            });
         });
         // --- END BULK ACTION LOGIC ---

         const editForm = $('#editTyreForm');

         $(document).on('click', '.edit-tyre', function() {
            const id = $(this).data('id');
            const serial = $(this).data('serial');
            const brandId = $(this).data('brand-id');
            const sizeId = $(this).data('size-id');
            const patternId = $(this).data('pattern-id');
            const segmentId = $(this).data('segment-id');
            const locationId = $(this).data('location-id');
            const status = $(this).data('status');
            const price = $(this).data('price');
            const initialTread = $(this).data('initial-tread');
            const currentTread = $(this).data('current-tread');
            const retreadCount = $(this).data('retread-count');
            const companyId = $(this).data('company-id');
            const currentKm = $(this).data('current-km');
            const currentHm = $(this).data('current-hm');

            editForm.attr('action', `{{ url('master_tyre') }}/${id}`);
            $('#edit_serial_number').val(serial);
            $('#edit_tyre_company_id').val(companyId).trigger('change');
            $('#edit_brand_id').val(brandId).trigger('change');
            $('#edit_size_id').val(sizeId).trigger('change');
            $('#edit_segment_id').val(segmentId === 'null' ? '' : segmentId).trigger('change');
            $('#edit_work_location_id').val(locationId).trigger('change');
            $('#edit_status').val(status);

            // Format existing price
            if (price) {
               $('#edit_price').val(parseInt(price, 10).toLocaleString('id-ID'));
            } else {
               $('#edit_price').val('');
            }

            $('#edit_initial_tread_depth').val(initialTread);
            $('#edit_current_tread_depth').val(currentTread);
            $('#edit_retread_count').val(retreadCount);
            $('#edit_current_km').val(currentKm);
            $('#edit_current_hm').val(currentHm);
         });

         // Hierarchical Dropdowns (Brand > Size > Pattern)
         function filterDropdowns(brandId, targetPrefix = '') {
            const sizeSelector = `#${targetPrefix}tyre_size_id`;
            const patternSelector = `#${targetPrefix}tyre_pattern_id`;

            // Reset and show all if no brand selected
            if (!brandId) {
               $(`${sizeSelector} option`).show();
               $(`${patternSelector} option`).show();
               return;
            }

            // Filter sizes
            $(`${sizeSelector} option`).each(function() {
               if ($(this).val() === "" || $(this).data('brand-id') == brandId) {
                  $(this).show();
               } else {
                  $(this).hide();
               }
            });

            // Filter patterns
            $(`${patternSelector} option`).each(function() {
               if ($(this).val() === "" || $(this).data('brand-id') == brandId) {
                  $(this).show();
               } else {
                  $(this).hide();
               }
            });

            // If current selection is hidden, reset it
            if ($(`${sizeSelector} option:selected`).css('display') === 'none') {
               $(`${sizeSelector}`).val('').trigger('change');
            }
            if ($(`${patternSelector} option:selected`).css('display') === 'none') {
               $(`${patternSelector}`).val('').trigger('change');
            }
         }

         $('#tyre_brand_id').on('change', function() {
            filterDropdowns($(this).val());
         });

         $('#edit_brand_id').on('change', function() {
            filterDropdowns($(this).val(), 'edit_');
         });

         // Role-based Select2 tags
         const isAdmin = {{ auth()->user()->role_id == 1 ? 'true' : 'false' }};

         $('.select2-tags').each(function() {
            $(this).wrap('<div class="position-relative"></div>').select2({
               placeholder: $(this).data('placeholder'),
               dropdownParent: $(this).parent(),
               tags: isAdmin,
               allowClear: true,
               width: '100%'
            });
         });

         // Initialize standard select2
         $('.select2').each(function() {
            $(this).wrap('<div class="position-relative"></div>').select2({
               placeholder: $(this).data('placeholder'),
               dropdownParent: $(this).parent()
            });
         });

         $(document).on('click', '.edit-tyre', function() {
            const id = $(this).data('id');
            const serial = $(this).data('serial');
            const brandId = $(this).data('brand-id');
            const sizeId = $(this).data('size-id');
            const patternId = $(this).data('pattern-id');
            const segmentName = $(this).data('segment-name');
            const status = $(this).data('status');
            const price = $(this).data('price');
            const originalTread = $(this).data('original-tread');
            const plyRating = $(this).data('ply-rating');
            const companyId = $(this).data('company-id');
            const isInWarehouse = $(this).data('warehouse');

            editForm.attr('action', `{{ url('master_tyre') }}/${id}`);
            $('#edit_serial_number').val(serial);
            $('#edit_tyre_company_id').val(companyId).trigger('change');
            $('#edit_brand_id').val(brandId).trigger('change');
            $('#edit_size_id').val(sizeId).trigger('change');
            $('#edit_pattern_id').val(patternId).trigger('change');
            $('#edit_segment_name').val(segmentName);
            $('#edit_ply_rating').val(plyRating);
            $('#edit_status').val(status);
            $('#edit_is_in_warehouse').prop('checked', isInWarehouse == 1);

            // Format existing price
            if (price) {
               $('#edit_price').val(parseInt(price, 10).toLocaleString('id-ID'));
            } else {
               $('#edit_price').val('');
            }

            $('#edit_original_tread_depth').val(originalTread);
         });

         // Currency Formatting Logic
         function formatCurrency(input) {
            let value = input.value.replace(/\D/g, ''); // Remove non-digits
            if (value) {
               value = parseInt(value, 10).toLocaleString('id-ID'); // Format to 1.000.000
               input.value = value;
            } else {
               input.value = '';
            }
         }

         $(document).on('input', '.currency-input', function() {
            formatCurrency(this);
         });

         // Unformat currency before submit
         $('form').on('submit', function() {
            $('.currency-input').each(function() {
               let value = $(this).val().replace(/\./g, ''); // Remove dots
               $(this).val(value);
            });
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
                  form.action = `{{ url('master_tyre') }}/${id}`;
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
});
</script>
@endsection
