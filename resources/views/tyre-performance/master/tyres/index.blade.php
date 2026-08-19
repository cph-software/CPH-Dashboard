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
            @if (hasPermission('Master Tyre', 'export') || auth()->user()->role_id == 1)
            <a href="{{ route('master_data.export', ['type' => 'assets', 'format' => 'excel']) }}"
               class="btn btn-outline-primary">
               <i class="icon-base ri ri-file-excel-2-line me-1"></i> Export Excel
            </a>
            @endif
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
                     @if (auth()->user()->role_id == 1)
                        <th>Instansi</th>
                     @endif
                     <th>Brand</th>
                     <th>Size</th>
                     <th>Pattern</th>
                     <th>Segment</th>
                     <th>Warehouse / Stock Status</th>
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
                  @if (auth()->user()->role_id == 1)
                     <div class="mb-3">
                        <label for="tyre_company_id" class="form-label fw-bold">Instansi / Company <span class="text-danger">*</span></label>
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
                  
                  <div class="row g-2 mb-3">
                     <div class="col-md-6">
                        <label for="serial_number" class="form-label fw-bold">Serial Number (SN) <small class="text-muted fw-normal">(Opsional)</small></label>
                        <div class="input-group">
                           <input type="text" id="serial_number" name="serial_number" class="form-control text-uppercase"
                              placeholder="Kosongkan untuk Auto-Gen">
                           <button type="button" class="btn btn-outline-primary" id="btn_autogen_add_sn" title="Generate nomor seri otomatis">
                              <i class="ri-flashlight-line me-1"></i> Auto-Gen
                           </button>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <label for="custom_serial_number" class="form-label fw-bold">Custom Code</label>
                        <input type="text" id="custom_serial_number" name="custom_serial_number" class="form-control"
                           placeholder="Ex: BAN-01">
                     </div>
                     <div class="col-md-3">
                        <label for="add_quantity" class="form-label fw-bold text-primary">Jumlah (Qty)</label>
                        <input type="number" id="add_quantity" name="quantity" class="form-control fw-bold border-primary"
                           value="1" min="1" max="100">
                     </div>
                  </div>

                  <div class="row g-2 mb-3">
                     <div class="col-md-4">
                        <label for="tyre_brand_id" class="form-label fw-bold">Brand <span class="text-danger">*</span></label>
                        <select id="tyre_brand_id" name="tyre_brand_id" class="form-select select2-tags"
                           data-placeholder="Select Brand" required>
                           <option value="">Select Brand</option>
                           @foreach ($brands as $brand)
                              <option value="{{ $brand->id }}" {{ old('tyre_brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->brand_name }}</option>
                           @endforeach
                           @if(old('tyre_brand_id') && !$brands->contains('id', old('tyre_brand_id')))
                              <option value="{{ old('tyre_brand_id') }}" selected>{{ old('tyre_brand_id') }}</option>
                           @endif
                        </select>
                     </div>
                     <div class="col-md-4">
                        <label for="tyre_size_id" class="form-label fw-bold">Size <span class="text-danger">*</span></label>
                        <select name="tyre_size_id" id="tyre_size_id" class="form-select select2-tags"
                           data-placeholder="Select Size" required>
                           <option value="">Select Size</option>
                           @foreach ($sizes as $size)
                              <option value="{{ $size->id }}" data-brand-id="{{ $size->tyre_brand_id }}" {{ old('tyre_size_id') == $size->id ? 'selected' : '' }}>
                                 {{ $size->size }}
                              </option>
                           @endforeach
                           @if(old('tyre_size_id') && !$sizes->contains('id', old('tyre_size_id')))
                              <option value="{{ old('tyre_size_id') }}" selected>{{ old('tyre_size_id') }}</option>
                           @endif
                        </select>
                     </div>
                     <div class="col-md-4">
                        <label for="tyre_pattern_id" class="form-label fw-bold">Pattern</label>
                        <select name="tyre_pattern_id" id="tyre_pattern_id" class="form-select select2-tags"
                           data-placeholder="Select Pattern">
                           <option value="">Select Pattern</option>
                           @foreach ($patterns as $pattern)
                              <option value="{{ $pattern->id }}" data-brand-id="{{ $pattern->tyre_brand_id }}" {{ old('tyre_pattern_id') == $pattern->id ? 'selected' : '' }}>
                                 {{ $pattern->name }}
                              </option>
                           @endforeach
                           @if(old('tyre_pattern_id') && !$patterns->contains('id', old('tyre_pattern_id')))
                              <option value="{{ old('tyre_pattern_id') }}" selected>{{ old('tyre_pattern_id') }}</option>
                           @endif
                        </select>
                     </div>
                  </div>

                  <div class="row g-2 mb-3">
                     <div class="col-md-6">
                        <label for="segment_name" class="form-label fw-bold">Segment Name</label>
                        <select id="segment_name" name="segment_name" class="form-select select2-tags-segment" data-placeholder="Ex: Mining, Logging, dll.">
                           <option value="">Pilih Segmen</option>
                           @foreach ($segments as $segment)
                              <option value="{{ $segment->segment_name }}" {{ old('segment_name') == $segment->segment_name ? 'selected' : '' }}>
                                 {{ $segment->segment_name }} ({{ $segment->location->location_name ?? '-' }})
                              </option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-md-6">
                        <label for="current_location_id" class="form-label fw-bold">Warehouse / Lokasi</label>
                        <select name="current_location_id" id="current_location_id" class="form-select select2"
                           data-placeholder="Pilih Lokasi">
                           <option value=""></option>
                           @foreach ($locations as $loc)
                              <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>

                  <div class="row g-2 mb-3">
                     <div class="col-md-6">
                        <label for="ply_rating" class="form-label fw-bold">Ply Rating (PR)</label>
                        <input type="text" id="ply_rating" name="ply_rating" class="form-control"
                           placeholder="Ex: 16PR, 18PR">
                     </div>
                     <div class="col-md-6">
                        <label for="initial_tread_depth" class="form-label fw-bold">OTD (Original Tread Depth - mm)</label>
                        <input type="number" id="initial_tread_depth" name="initial_tread_depth"
                           class="form-control" placeholder="18.5" step="0.01">
                     </div>
                  </div>

                  <div class="row g-2 mb-3">
                     <div class="col-md-6">
                        <label for="price" class="form-label fw-bold">Harga Beli (IDR)</label>
                        <input type="text" id="price" name="price" class="form-control currency-input"
                           placeholder="3.500.000">
                     </div>
                     <div class="col-md-6">
                        <label for="status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                           <option value="New">New</option>
                           <option value="Installed">Installed</option>
                           <option value="Repaired">Repaired</option>
                           <option value="Retread">Retread</option>
                           <option value="Scrap">Scrap</option>
                        </select>
                     </div>
                  </div>

                  <div class="row g-2">
                     <div class="col-md-12">
                        <label class="form-label d-block fw-bold">Location Context</label>
                        <div class="form-check form-switch mt-1">
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
                  @if (auth()->user()->role_id == 1)
                     <div class="mb-3">
                        <label for="edit_tyre_company_id" class="form-label fw-bold">Instansi / Company <span class="text-danger">*</span></label>
                        <select name="tyre_company_id" id="edit_tyre_company_id" class="form-select select2"
                           data-placeholder="Pilih Perusahaan">
                           <option value="">-- Pilih Perusahaan --</option>
                           @foreach ($companies as $company)
                              <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                           @endforeach
                        </select>
                     </div>
                  @endif
                  
                  <div class="row g-2 mb-3">
                     <div class="col-md-7">
                        <label for="edit_serial_number" class="form-label fw-bold">Serial Number (SN) <span class="text-danger">*</span></label>
                        <div class="input-group">
                           <input type="text" id="edit_serial_number" name="serial_number" class="form-control text-uppercase" required>
                           <button type="button" class="btn btn-outline-primary" id="btn_autogen_edit_sn" title="Generate nomor seri otomatis">
                              <i class="ri-flashlight-line me-1"></i> Auto-Gen
                           </button>
                        </div>
                     </div>
                     <div class="col-md-5">
                        <label for="edit_custom_serial_number" class="form-label fw-bold">Custom Code</label>
                        <input type="text" id="edit_custom_serial_number" name="custom_serial_number" class="form-control" placeholder="Ex: BAN-01">
                     </div>
                  </div>

                  <div class="row g-2 mb-3">
                     <div class="col-md-4">
                        <label for="edit_brand_id" class="form-label fw-bold">Brand</label>
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
                        <select id="edit_segment_name" name="segment_name" class="form-select select2-tags-segment" data-placeholder="Ex: Mining, Logging, dll.">
                           <option value="">Pilih Segmen</option>
                           @foreach ($segments as $segment)
                              <option value="{{ $segment->segment_name }}">
                                 {{ $segment->segment_name }} ({{ $segment->location->location_name ?? '-' }})
                              </option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="edit_ply_rating" class="form-label">Ply Rating</label>
                        <input type="text" id="edit_ply_rating" name="ply_rating" class="form-control">
                     </div>
                  </div>

                  <div class="row g-2">
                     <div class="col-md-6 mb-3">
                        <label for="edit_initial_tread_depth" class="form-label">OTD (Original Tread Depth - mm)</label>
                        <input type="number" id="edit_initial_tread_depth" name="initial_tread_depth"
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
                        <label for="edit_current_location_id" class="form-label">Warehouse / Lokasi</label>
                        <select id="edit_current_location_id" name="current_location_id" class="form-select select2"
                           data-placeholder="Pilih Lokasi">
                           <option value=""></option>
                           @foreach ($locations as $loc)
                              <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-md-12 mb-3">
                        <label class="form-label d-block">Inventory Tracking</label>
                        <div class="form-check form-switch mt-2">
                           <input class="form-check-input" type="checkbox" name="is_in_warehouse" value="1"
                              id="edit_is_in_warehouse">
                           <label class="form-check-label" for="edit_is_in_warehouse">In Warehouse (Set as Stock)</label>
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
                     <label class="form-label">Update Lokasi (Gudang)</label>
                     <select name="current_location_id" class="form-select select2-bulk"
                        data-placeholder="Select Location">
                        <option value=""></option>
                        @foreach ($locations as $loc)
                           <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="mb-3">
                     <label class="form-label">Update Segment</label>
                     <input type="text" name="segment_name" class="form-control" placeholder="Update Segment Name">
                  </div>
                  <div class="mb-3">
                     <label class="form-label d-block">Update Stock Status</label>
                     <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_in_warehouse" value="1"
                           id="bulk_warehouse">
                        <label class="form-check-label" for="bulk_warehouse">Set as Stock (In Warehouse)</label>
                     </div>
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
                   render: function(data, type, row) {
                      let custom = row.custom_serial_number ? `<br><span class="badge bg-label-secondary font-monospace mt-1" style="font-size: 0.72rem;"><i class="ri-hashtag me-1"></i>${row.custom_serial_number}</span>` : '';
                      return `<strong class="text-dark">${data}</strong>${custom}`;
                   }
                },
               @if (auth()->user()->role_id == 1)
               {
                  data: 'company',
                  render: function(data) {
                     return data ? `<span class="badge bg-label-primary shadow-sm"><i class="ri-building-4-line me-1"></i>${data.company_name}</span>` : '<span class="text-muted">-</span>';
                  }
               },
               @endif
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
                  data: 'segment_name',
                  defaultContent: '-'
               },
               {
                  data: 'is_in_warehouse',
                  render: function(data, type, row) {
                     if (data) {
                        const locName = row.location ? row.location.location_name : 'Gudang';
                        return `<span class="badge bg-label-info"><i class="ri-home-4-line me-1"></i>${locName}</span>`;
                     }
                     return '<span class="badge bg-label-warning"><i class="ri-truck-line me-1"></i>Terpasang</span>';
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
                               data-warehouse="${row.is_in_warehouse ? 1 : 0}"
                               data-location-id="${row.current_location_id || ''}"
                               data-status="${row.status}"
                               data-price="${row.price || ''}"
                               data-initial-tread="${row.initial_tread_depth || ''}"
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
            const customSerial = $(this).data('custom-serial');
            const brandId = $(this).data('brand-id');
            const sizeId = $(this).data('size-id');
            const patternId = $(this).data('pattern-id');
            const segmentName = $(this).data('segment-name');
            const locationId = $(this).data('location-id');
            const status = $(this).data('status');
            const warehouse = $(this).data('warehouse');
            const price = $(this).data('price');
            const initialTread = $(this).data('initial-tread');
            const plyRating = $(this).data('ply-rating');
            const retreadCount = $(this).data('retread-count');
            const companyId = $(this).data('company-id');

            editForm.attr('action', `{{ url('master_tyre') }}/${id}`);
            $('#edit_serial_number').val(serial);
            $('#edit_custom_serial_number').val(customSerial);
            $('#edit_tyre_company_id').val(companyId).trigger('change');
            $('#edit_brand_id').val(brandId).trigger('change');
            $('#edit_size_id').val(sizeId).trigger('change');
            $('#edit_pattern_id').val(patternId).trigger('change');
            if (segmentName && !$('#edit_segment_name').find("option[value='" + segmentName + "']").length) {
               $('#edit_segment_name').append(new Option(segmentName, segmentName, true, true));
            }
            $('#edit_segment_name').val(segmentName).trigger('change');
            $('#edit_current_location_id').val(locationId).trigger('change');
            $('#edit_is_in_warehouse').prop('checked', warehouse == 1);
            $('#edit_status').val(status);

            // Format existing price
            if (price) {
               $('#edit_price').val(parseInt(price, 10).toLocaleString('id-ID'));
            } else {
               $('#edit_price').val('');
            }

            $('#edit_initial_tread_depth').val(initialTread);
            $('#edit_ply_rating').val(plyRating);
         });

         // Hierarchical Dropdowns (Brand > Size > Pattern) using Array backing to prevent Select2 glitches
         const sizeOptions = [];
         $('#tyre_size_id option').each(function() {
            if ($(this).val() !== "") sizeOptions.push({ val: $(this).val(), text: $(this).text().trim(), brandId: $(this).data('brand-id') });
         });

         const patternOptions = [];
         $('#tyre_pattern_id option').each(function() {
            if ($(this).val() !== "") patternOptions.push({ val: $(this).val(), text: $(this).text().trim(), brandId: $(this).data('brand-id') });
         });

         const editSizeOptions = [];
         $('#edit_size_id option').each(function() {
            if ($(this).val() !== "") editSizeOptions.push({ val: $(this).val(), text: $(this).text().trim(), brandId: $(this).data('brand-id') });
         });

         const editPatternOptions = [];
         $('#edit_pattern_id option').each(function() {
            if ($(this).val() !== "") editPatternOptions.push({ val: $(this).val(), text: $(this).text().trim(), brandId: $(this).data('brand-id') });
         });

         // User Permission for Select2 tags (direct adding of Brand, Size, Pattern)
         const canCreate = {{ (hasPermission('Master Tyre', 'create') || auth()->user()->role_id == 1) ? 'true' : 'false' }};

         function initSelect2Tags(selector, modalId) {
            $(selector).each(function() {
               var $this = $(this);
               if ($this.data('select2')) {
                  $this.select2('destroy');
               }
               
               $this.select2({
                  placeholder: $this.data('placeholder'),
                  dropdownParent: $this.parent(),
                  tags: canCreate,
                  width: '100%',
                  createTag: function(params) {
                     var term = $.trim(params.term);
                     if (term === '') {
                        return null;
                     }
                     return {
                        id: term,
                        text: '+ Tambah Baru: "' + term + '"',
                        newTag: true
                     };
                  }
               });
            });
         }

         function initStandardSelect2() {
            $('.select2').each(function() {
               var $this = $(this);
               if ($this.data('select2')) {
                  $this.select2('destroy');
               }
               
               $this.select2({
                  placeholder: $this.data('placeholder'),
                  dropdownParent: $this.parent(),
                  width: '100%'
               });
            });
         }

         function filterDropdownsDOM(brandId, targetPrefix = '') {
            const sizeSelector = `#${targetPrefix}size_id`;
            const patternSelector = `#${targetPrefix}pattern_id`;
            const sOpts = targetPrefix === 'edit_' ? editSizeOptions : sizeOptions;
            const pOpts = targetPrefix === 'edit_' ? editPatternOptions : patternOptions;

            const currentSize = $(sizeSelector).val();
            const currentPattern = $(patternSelector).val();

            if ($(sizeSelector).data('select2')) $(sizeSelector).select2('destroy');
            if ($(patternSelector).data('select2')) $(patternSelector).select2('destroy');

            $(sizeSelector).find('option:not(:first)').remove();
            $(patternSelector).find('option:not(:first)').remove();

            sOpts.forEach(opt => {
               if (!brandId || String(opt.brandId) === String(brandId)) {
                  $(sizeSelector).append(new Option(opt.text, opt.val, false, opt.val === currentSize));
               }
            });

            pOpts.forEach(opt => {
               if (!brandId || String(opt.brandId) === String(brandId)) {
                  $(patternSelector).append(new Option(opt.text, opt.val, false, opt.val === currentPattern));
               }
            });

            // Re-init completely so Select2 tags engine works smoothly for new items
            $(sizeSelector).select2({
               placeholder: $(sizeSelector).data('placeholder'),
               dropdownParent: $(sizeSelector).parent(),
               tags: canCreate,
               width: '100%',
               createTag: function(params) {
                  var term = $.trim(params.term);
                  if (term === '') return null;
                  return { id: term, text: '+ Tambah Baru: "' + term + '"', newTag: true };
               }
            });
            $(patternSelector).select2({
               placeholder: $(patternSelector).data('placeholder'),
               dropdownParent: $(patternSelector).parent(),
               tags: canCreate,
               width: '100%',
               createTag: function(params) {
                  var term = $.trim(params.term);
                  if (term === '') return null;
                  return { id: term, text: '+ Tambah Baru: "' + term + '"', newTag: true };
               }
            });
         }
         $('#addTyreModal').on('shown.bs.modal', function () {
            if (!$('#tyre_brand_id').data('select2')) initSelect2Tags('#tyre_brand_id');
            if (!$('#tyre_size_id').data('select2')) initSelect2Tags('#tyre_size_id');
            if (!$('#tyre_pattern_id').data('select2')) initSelect2Tags('#tyre_pattern_id');
            if (!$('#segment_name').data('select2')) {
               $('#segment_name').select2({ placeholder: $(this).data('placeholder'), dropdownParent: $('#segment_name').parent(), tags: true, width: '100%' });
            }
         });

         $('#editTyreModal').on('shown.bs.modal', function () {
            if (!$('#edit_brand_id').data('select2')) initSelect2Tags('#edit_brand_id');
            if (!$('#edit_size_id').data('select2')) initSelect2Tags('#edit_size_id');
            if (!$('#edit_pattern_id').data('select2')) initSelect2Tags('#edit_pattern_id');
            if (!$('#edit_segment_name').data('select2')) {
               $('#edit_segment_name').select2({ placeholder: $(this).data('placeholder'), dropdownParent: $('#edit_segment_name').parent(), tags: true, width: '100%' });
            }
         });

         initStandardSelect2();

         $('#tyre_brand_id').on('change', function() {
            filterDropdownsDOM($(this).val(), 'tyre_');
         });

         $('#edit_brand_id').on('change', function() {
            filterDropdownsDOM($(this).val(), 'edit_');
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

            // Auto-Gen SN Helper
         function generateRandomStockSN(brandSelectId) {
            const brandText = $(brandSelectId + ' option:selected').text().trim().replace(/[^A-Za-z0-9]/g, '');
            const brandPrefix = brandText && brandText !== 'SelectBrand' && brandText !== '' ? brandText.substring(0, 3).toUpperCase() : 'STK';
            const now = new Date();
            const y = now.getFullYear();
            const m = String(now.getMonth() + 1).padStart(2, '0');
            const d = String(now.getDate()).padStart(2, '0');
            const rand = Math.random().toString(36).substring(2, 6).toUpperCase();
            return `STK-${brandPrefix}-${y}${m}${d}-${rand}`;
         }

         $('#btn_autogen_add_sn').on('click', function() {
            $('#serial_number').val(generateRandomStockSN('#tyre_brand_id'));
         });

         $('#btn_autogen_edit_sn').on('click', function() {
            $('#edit_serial_number').val(generateRandomStockSN('#edit_brand_id'));
         });

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

         @if ($errors->any())
            Swal.fire({
               icon: 'error',
               title: 'Validasi Gagal!',
               html: `{!! implode('<br>', $errors->all()) !!}`
            });
         @endif
      });
   </script>
@endsection
