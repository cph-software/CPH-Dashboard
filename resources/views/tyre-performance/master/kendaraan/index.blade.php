@extends('layouts.admin')

@section('title', 'Master Vehicles')

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
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Vehicles</h4>
         <div class="d-flex gap-2">
            <div id="bulk-actions-container" style="display: none;">
               <div class="btn-group me-2">
                  <button type="button" class="btn btn-outline-danger" id="btn-bulk-delete">
                     <i class="ri-delete-bin-line me-1"></i> Hapus
                  </button>
                  <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                     data-bs-target="#bulkUpdateModal">
                     <i class="ri-edit-line me-1"></i> Update
                  </button>
               </div>
            </div>
            <a href="{{ route('master_data.export', ['type' => 'vehicles', 'format' => 'excel']) }}"
               class="btn btn-outline-primary">
               <i class="ri-file-excel-2-line me-1"></i> Export Excel
            </a>
            @if (hasPermission('Import Approval', 'create'))
               <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                  data-bs-target="#importModal">
                  <i class="ri-upload-2-line me-1"></i> Import
               </button>
            @endif
            @if (hasPermission('Vehicle Master', 'create'))
               <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal"
                  data-bs-target="#addVehicleModal">
                  <i class="ri-add-line me-1"></i> Add Vehicle
               </button>
            @endif
         </div>
      </div>

      <div class="card shadow-sm border-0">
         <div class="card-datatable table-responsive">
            <table class="datatables-vehicles table border-top table-hover">
               <thead>
                  <tr>
                     <th width="10"><input type="checkbox" class="form-check-input" id="check-all"></th>
                     <th>Unit Code</th>
                     @if (auth()->user()->role_id == 1)
                        <th>Instansi</th>
                     @endif
                     <th>No. Polisi</th>
                     <th>Type</th>
                     <th>Area</th>
                     <th>Default Segment</th>
                     <th>Axle Layout</th>
                     <th>Wheels</th>
                     <th>Status</th>
                     <th class="text-center">Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  {{-- Data loaded via AJAX --}}
               </tbody>
            </table>
         </div>
      </div>
   </div>

   <!-- Add Vehicle Modal -->
   <div class="modal fade" id="addVehicleModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary">
               <h5 class="modal-title text-white">Add New Vehicle</h5>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-kendaraan.store') }}" method="POST">
               @csrf
               {{-- Default values for simplified fields --}}

               <div class="modal-body pt-4">
                  <div class="row g-2">
                     @if (auth()->user()->role_id == 1)
                        <div class="col-md-12 mb-3">
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
                        <label for="kode_kendaraan" class="form-label fw-bold">Unit Code</label>
                        <input type="text" id="kode_kendaraan" name="kode_kendaraan" class="form-control"
                           placeholder="e.g. DT-101" required>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="no_polisi" class="form-label fw-bold">No. Polisi</label>
                        <input type="text" id="no_polisi" name="no_polisi" class="form-control" placeholder="B 1234 ABC"
                           required>
                     </div>
                  </div>

                  <div class="row g-2">
                     <div class="col-md-6 mb-3">
                        <label for="jenis_kendaraan" class="form-label fw-bold">Vehicle Type</label>
                        <input type="text" id="jenis_kendaraan" name="jenis_kendaraan" class="form-control"
                           placeholder="e.g. Dump Truck">
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="vehicle_brand" class="form-label fw-bold">Merk Kendaraan</label>
                        <input type="text" id="vehicle_brand" name="vehicle_brand" class="form-control"
                           placeholder="e.g. Volvo, Hino, Isuzu">
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col-md-6 mb-3">
                        <label for="area" class="form-label fw-bold">Operational Area</label>
                        <select name="area" id="area" class="form-select select2" required
                           data-placeholder="Select Area">
                           <option value="">-- Select Area --</option>
                           @foreach ($locations as $loc)
                              <option value="{{ $loc->location_name }}">{{ $loc->location_name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="operational_segment_id" class="form-label fw-bold">Default Working Segment</label>
                        <select name="operational_segment_id" id="operational_segment_id" class="form-select select2"
                           data-placeholder="Select Segment">
                           <option value="">-- Select Segment --</option>
                           @foreach ($segments as $seg)
                              <option value="{{ $seg->id }}">{{ $seg->segment_name }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col-md-6 mb-3">
                        <label for="curb_weight" class="form-label fw-bold">Curb Weight <span
                              class="text-muted fw-normal">(kg)</span></label>
                        <input type="number" id="curb_weight" name="curb_weight" class="form-control"
                           placeholder="e.g. 12000" min="0">
                        <div class="form-text text-muted">Berat kosong kendaraan</div>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="payload_capacity" class="form-label fw-bold">Payload Capacity <span
                              class="text-muted fw-normal">(ton)</span></label>
                        <input type="number" id="payload_capacity" name="payload_capacity" class="form-control"
                           placeholder="e.g. 30" min="0" step="0.01">
                        <div class="form-text text-muted">Kapasitas muat maksimum</div>
                     </div>
                  </div>

                  <div class="mb-3">
                     <label for="tyre_position_configuration_id" class="form-label fw-bold">Axle Layout
                        Configuration</label>
                     <select name="tyre_position_configuration_id" class="form-select select2 config-selector"
                        data-placeholder="Select Configuration">
                        <option value="">-- Select Configuration --</option>
                        @foreach ($configurations as $config)
                           <option value="{{ $config->id }}" data-total="{{ $config->total_positions }}">
                              {{ $config->name }} ({{ $config->total_positions }} Wheels)
                           </option>
                        @endforeach
                     </select>
                  </div>
                  <div class="row g-2">
                     <div class="col-md-6 mb-3">
                        <label for="total_tyre_position" class="form-label fw-bold">Total Wheels</label>
                        <input type="number" name="total_tyre_position" class="form-control total-pos-input"
                           placeholder="e.g. 10" required>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="tyre_unit_status" class="form-label fw-bold">Status</label>
                        <select name="tyre_unit_status" class="form-select" required>
                           <option value="Active">Active</option>
                           <option value="Inactive">Inactive</option>
                           <option value="Maintenance">Maintenance</option>
                        </select>
                     </div>
                  </div>
               </div>
               <div class="modal-footer border-top">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary shadow">Save changes</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <!-- Edit Vehicle Modal -->
   <div class="modal fade" id="editVehicleModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning">
               <h5 class="modal-title">Edit Vehicle</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editVehicleForm" method="POST">
               @csrf
               @method('PUT')

               <div class="modal-body pt-4">
                  <div class="row g-2">
                     @if (auth()->user()->role_id == 1)
                        <div class="col-md-12 mb-3">
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
                        <label for="edit_kode_kendaraan" class="form-label fw-bold">Unit Code</label>
                        <input type="text" id="edit_kode_kendaraan" name="kode_kendaraan" class="form-control"
                           required>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="edit_no_polisi" class="form-label fw-bold">No. Polisi</label>
                        <input type="text" id="edit_no_polisi" name="no_polisi" class="form-control" required>
                     </div>
                  </div>

                  <div class="row g-2">
                     <div class="col-md-6 mb-3">
                        <label for="edit_jenis_kendaraan" class="form-label fw-bold">Vehicle Type</label>
                        <input type="text" id="edit_jenis_kendaraan" name="jenis_kendaraan" class="form-control">
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="edit_vehicle_brand" class="form-label fw-bold">Merk Kendaraan</label>
                        <input type="text" id="edit_vehicle_brand" name="vehicle_brand" class="form-control"
                           placeholder="e.g. Volvo, Hino">
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col-md-6 mb-3">
                        <label for="edit_area" class="form-label fw-bold">Operational Area (Gudang Base)</label>
                        <select name="area" id="edit_area" class="form-select select2" required
                           data-placeholder="Select Area">
                           <option value="">-- Select Area --</option>
                           @foreach ($locations as $loc)
                              <option value="{{ $loc->location_name }}">{{ $loc->location_name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="edit_operational_segment_id" class="form-label fw-bold">Default Working
                           Segment</label>
                        <select name="operational_segment_id" id="edit_operational_segment_id"
                           class="form-select select2" data-placeholder="Select Segment">
                           <option value="">-- Select Segment --</option>
                           @foreach ($segments as $seg)
                              <option value="{{ $seg->id }}">{{ $seg->segment_name }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col-md-6 mb-3">
                        <label for="edit_curb_weight" class="form-label fw-bold">Curb Weight <span
                              class="text-muted fw-normal">(kg)</span></label>
                        <input type="number" id="edit_curb_weight" name="curb_weight" class="form-control"
                           placeholder="e.g. 12000" min="0">
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="edit_payload_capacity" class="form-label fw-bold">Payload Capacity <span
                              class="text-muted fw-normal">(ton)</span></label>
                        <input type="number" id="edit_payload_capacity" name="payload_capacity" class="form-control"
                           placeholder="e.g. 30" min="0" step="0.01">
                     </div>
                  </div>
                  <div class="mb-3">
                     <label for="edit_tyre_position_configuration_id" class="form-label fw-bold">Axle Layout
                        Configuration</label>
                     <select id="edit_tyre_position_configuration_id" name="tyre_position_configuration_id"
                        class="form-select select2 config-selector">
                        <option value="">-- No Configuration --</option>
                        @foreach ($configurations as $config)
                           <option value="{{ $config->id }}" data-total="{{ $config->total_positions }}">
                              {{ $config->name }} ({{ $config->total_positions }} Wheels)
                           </option>
                        @endforeach
                     </select>
                  </div>
                  <div class="row g-2">
                     <div class="col-md-6 mb-3">
                        <label for="edit_total_positions" class="form-label fw-bold">Total Wheels</label>
                        <input type="number" id="edit_total_positions" name="total_tyre_position"
                           class="form-control total-pos-input" required>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label for="edit_unit_status" class="form-label fw-bold">Status</label>
                        <select id="edit_unit_status" name="tyre_unit_status" class="form-select" required>
                           <option value="Active">Active</option>
                           <option value="Inactive">Inactive</option>
                           <option value="Maintenance">Maintenance</option>
                        </select>
                     </div>
                  </div>
               </div>
               <div class="modal-footer border-top">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-warning shadow">Update changes</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <!-- View Layout Modal -->
   <div class="modal fade" id="viewLayoutModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary">
               <h5 class="modal-title text-white">Vehicle Axle Layout: <span id="layoutModalTitle"></span></h5>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light text-center">
               <div id="layoutContainer">
                  <div class="text-center py-5">
                     <div class="spinner-border text-primary" role="status"></div>
                  </div>
               </div>
            </div>
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
               <h5 class="modal-title">Batch Update Kendaraan</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-kendaraan.bulk-action') }}" method="POST" id="bulkActionForm">
               @csrf
               <input type="hidden" name="action" value="update">
               <div id="bulk-ids-container"></div>
               <div class="modal-body">
                  <div class="alert alert-info">
                     <i class="ri-information-line me-1"></i> Field yang dikosongkan tidak akan diperbarui.
                  </div>
                  <div class="mb-3">
                     <label class="form-label">Update Status</label>
                     <select name="tyre_unit_status" class="form-select">
                        <option value="">-- No Change --</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Maintenance">Maintenance</option>
                     </select>
                  </div>
                  <div class="mb-3">
                     <label class="form-label">Update Area</label>
                     <select name="area" class="form-select select2-bulk" data-placeholder="Select Area">
                        <option value=""></option>
                        @foreach ($locations as $loc)
                           <option value="{{ $loc->location_name }}">{{ $loc->location_name }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="mb-3">
                     <label class="form-label">Update Segment</label>
                     <select name="operational_segment_id" class="form-select select2-bulk"
                        data-placeholder="Select Segment">
                        <option value=""></option>
                        @foreach ($segments as $segment)
                           <option value="{{ $segment->id }}">{{ $segment->segment_name }}</option>
                        @endforeach
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

   <form id="bulkDeleteForm" action="{{ route('tyre-kendaraan.bulk-action') }}" method="POST" style="display: none;">
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
         const canUpdate = {{ hasPermission('Vehicle Master', 'update') ? 'true' : 'false' }};
         const canDelete = {{ hasPermission('Vehicle Master', 'delete') ? 'true' : 'false' }};

         const table = $('.datatables-vehicles').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('tyre-kendaraan.data') }}",
            columns: [{
                  data: 'id',
                  orderable: false,
                  searchable: false,
                  render: function(data) {
                     return `<input type="checkbox" class="form-check-input vehicle-checkbox" value="${data}">`;
                  }
               },
               {
                  data: 'kode_kendaraan',
                  render: function(data) {
                     return `<strong>${data}</strong>`;
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
                  data: 'no_polisi',
                  defaultContent: '-'
               },
               {
                  data: 'jenis_kendaraan',
                  defaultContent: '-'
               },
               {
                  data: 'area',
                  defaultContent: '-'
               },
               {
                  data: 'segment.segment_name',
                  defaultContent: '-'
               },
               {
                  data: 'tyre_position_configuration.name',
                  defaultContent: '-'
               },
               {
                  data: 'total_tyre_position',
                  render: function(data) {
                     return `${data} Wheels`;
                  }
               },
               {
                  data: 'tyre_unit_status',
                  render: function(data) {
                     const badges = {
                        'Active': 'success',
                        'Maintenance': 'warning',
                        'Inactive': 'secondary'
                     };
                     return `<span class="badge bg-label-${badges[data] || 'secondary'}">${data}</span>`;
                  }
               },
               {
                  data: null,
                  searchable: false,
                  orderable: false,
                  className: 'text-center',
                  render: function(data, type, row) {
                     let layoutBtn = '';
                     if (row.tyre_position_configuration_id) {
                        layoutBtn = `
                                                                  <button type="button"
                                                                     class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 view-layout"
                                                                     data-bs-toggle="modal" data-bs-target="#viewLayoutModal"
                                                                     data-config-name="${row.tyre_position_configuration ? row.tyre_position_configuration.name : ''}"
                                                                     data-config-id="${row.tyre_position_configuration_id}" title="View Layout">
                                                                     <i class="icon-base ri ri-layout-6-line text-primary"></i>
                                                                  </button>
                                                               `;
                     }

                     let actions = `<div class="d-flex align-items-center justify-content-center">`;

                     // Detail button (always visible)
                     actions += `<a class="btn btn-sm btn-icon btn-text-primary rounded-pill waves-effect waves-light me-1"
                                        href="{{ url('master_kendaraan') }}/${row.id}" title="Lihat Detail">
                                        <i class="icon-base ri ri-eye-line"></i>
                                    </a>`;

                     if (canUpdate) {
                        actions += `
                                                            <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 edit-vehicle"
                                                               href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editVehicleModal"
                                                               data-id="${row.id}" data-kode="${row.kode_kendaraan}" data-company-id="${row.tyre_company_id}"
                                                               data-nopol="${row.no_polisi}" data-area="${row.area}"
                                                               data-segment-id="${row.operational_segment_id}"
                                                               data-brand="${row.vehicle_brand}"
                                                               data-curb-weight="${row.curb_weight}"
                                                               data-payload="${row.payload_capacity}"
                                                               data-jenis="${row.jenis_kendaraan}" data-positions="${row.total_tyre_position}"
                                                               data-config-id="${row.tyre_position_configuration_id}"
                                                               data-status="${row.tyre_unit_status}" title="Edit">
                                                               <i class="icon-base ri ri-pencil-line"></i>
                                                            </a>`;
                     }

                     actions += layoutBtn;

                     if (canDelete) {
                        actions += `
                                                            <button type="button"
                                                               class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light delete-vehicle"
                                                               data-id="${row.id}" data-kode="${row.kode_kendaraan}" title="Delete">
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
            const selectedCount = $('.vehicle-checkbox:checked').length;
            if (selectedCount > 0) {
               $('#bulk-actions-container').fadeIn();
            } else {
               $('#bulk-actions-container').fadeOut();
               $('#check-all').prop('checked', false);
            }
         }

         $(document).on('change', '#check-all', function() {
            $('.vehicle-checkbox').prop('checked', this.checked);
            updateBulkActions();
         });

         $(document).on('change', '.vehicle-checkbox', function() {
            updateBulkActions();
         });

         $('#btn-bulk-delete').on('click', function() {
            const selectedIds = $('.vehicle-checkbox:checked').map(function() {
               return $(this).val();
            }).get();

            Swal.fire({
               title: 'Hapus Massal?',
               text: `Yakin ingin menghapus ${selectedIds.length} data kendaraan terpilih? Kendaraan yang masih memiliki ban terpasang tidak akan terhapus.`,
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
            const selectedIds = $('.vehicle-checkbox:checked').map(function() {
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

         const editForm = $('#editVehicleForm');

         $(document).on('click', '.edit-vehicle', function() {
            const id = $(this).data('id');
            const kode = $(this).data('kode');
            const nopol = $(this).data('nopol');
            const area = $(this).data('area');
            const segmentId = $(this).data('segment-id');
            const jenis = $(this).data('jenis');
            const brand = $(this).data('brand');
            const curbWeight = $(this).data('curb-weight');
            const payload = $(this).data('payload');
            const positions = $(this).data('positions');
            const configId = $(this).data('config-id');
            const status = $(this).data('status');
            const companyId = $(this).data('company-id');

            editForm.attr('action', `{{ url('master_kendaraan') }}/${id}`);
            $('#edit_tyre_company_id').val(companyId).trigger('change');
            $('#edit_kode_kendaraan').val(kode);
            $('#edit_no_polisi').val(nopol);
            $('#edit_area').val(area).trigger('change');
            $('#edit_operational_segment_id').val(segmentId === 'null' ? '' : (segmentId || '')).trigger(
               'change');
            $('#edit_jenis_kendaraan').val(jenis === 'null' ? '' : (jenis || ''));
            $('#edit_vehicle_brand').val(brand === 'null' ? '' : (brand || ''));
            $('#edit_curb_weight').val(curbWeight === 'null' ? '' : (curbWeight || ''));
            $('#edit_payload_capacity').val(payload === 'null' ? '' : (payload || ''));
            $('#edit_total_positions').val(positions);
            $('#edit_tyre_position_configuration_id').val(configId === 'null' ? '' : (configId || '')).trigger(
               'change');
            $('#edit_unit_status').val(status);
         });

         $(document).on('click', '.delete-vehicle', function() {
            const id = $(this).data('id');
            const kode = $(this).data('kode');

            Swal.fire({
               title: 'Yakin ingin menghapus?',
               text: `Kendaraan "${kode}" akan dihapus permanen!`,
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
                  form.action = `{{ url('master_kendaraan') }}/${id}`;
                  form.submit();
               }
            });
         });

         $(document).on('click', '.view-layout', function() {
            const configId = $(this).data('config-id');
            const configName = $(this).data('config-name');
            const layoutContainer = $('#layoutContainer');
            const layoutModalTitle = $('#layoutModalTitle');

            layoutModalTitle.text(configName);
            layoutContainer.html(
               '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
            );

            fetch(`/master_position/${configId}/layout`)
               .then(response => response.text())
               .then(html => {
                  layoutContainer.html(html);
               })
               .catch(err => {
                  layoutContainer.html('<div class="alert alert-danger">Gagal memuat layout.</div>');
               });
         });

         // Auto-detect Total Positions based on Configuration
         $(document).on('change', '.config-selector', function() {
            const total = $(this).find(':selected').data('total');
            const modal = $(this).closest('.modal');
            if (total) {
               modal.find('.total-pos-input').val(total);
            }
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
               title: 'Validasi Gagal',
               html: '{!! implode('<br>', $errors->all()) !!}'
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
