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
         <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
            <i class="ri-add-line me-1"></i> Add Vehicle
         </button>
      </div>

      <div class="card shadow-sm border-0">
         <div class="card-datatable table-responsive">
            <table class="datatables-vehicles table border-top table-hover">
               <thead>
                  <tr>
                     <th>Unit Code</th>
                     <th>Type</th>
                     <th>Tyre Layout</th>
                     <th>Tyre Positions</th>
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
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-kendaraan.store') }}" method="POST">
               @csrf
               {{-- Default values for simplified fields --}}
               <input type="hidden" name="no_polisi" value="-">
               <input type="hidden" name="area" value="HO">

               <div class="modal-body pt-4">
                  <div class="mb-3">
                     <label for="kode_kendaraan" class="form-label fw-bold">Unit Code</label>
                     <input type="text" id="kode_kendaraan" name="kode_kendaraan" class="form-control"
                        placeholder="e.g. DT-101" required>
                  </div>
                  <div class="mb-3">
                     <label for="jenis_kendaraan" class="form-label fw-bold">Vehicle Type</label>
                     <input type="text" id="jenis_kendaraan" name="jenis_kendaraan" class="form-control"
                        placeholder="e.g. Dump Truck Hino 500">
                  </div>
                  <div class="mb-3">
                     <label for="tyre_position_configuration_id" class="form-label fw-bold">Tyre Layout
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
                        <label for="total_tyre_position" class="form-label fw-bold">Total Tyre Positions</label>
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
               {{-- Default values for simplified fields --}}
               <input type="hidden" name="no_polisi" value="-">
               <input type="hidden" name="area" value="HO">

               <div class="modal-body pt-4">
                  <div class="mb-3">
                     <label for="edit_kode_kendaraan" class="form-label fw-bold">Unit Code</label>
                     <input type="text" id="edit_kode_kendaraan" name="kode_kendaraan" class="form-control" required>
                  </div>
                  <div class="mb-3">
                     <label for="edit_jenis_kendaraan" class="form-label fw-bold">Vehicle Type</label>
                     <input type="text" id="edit_jenis_kendaraan" name="jenis_kendaraan" class="form-control">
                  </div>
                  <div class="mb-3">
                     <label for="edit_tyre_position_configuration_id" class="form-label fw-bold">Tyre Layout
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
                        <label for="edit_total_positions" class="form-label fw-bold">Total Tyre Positions</label>
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
               <h5 class="modal-title text-white">Vehicle Tyre Layout: <span id="layoutModalTitle"></span></h5>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function () {
         const table = $('.datatables-vehicles').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('tyre-kendaraan.data') }}",
            columns: [{
               data: 'kode_kendaraan',
               render: function (data) {
                  return `<strong>${data}</strong>`;
               }
            },
            {
               data: 'jenis_kendaraan',
               defaultContent: '-'
            },
            {
               data: 'tyre_position_configuration.name',
               defaultContent: '-'
            },
            {
               data: 'total_tyre_position',
               render: function (data) {
                  return `${data} Wheels`;
               }
            },
            {
               data: 'tyre_unit_status',
               render: function (data) {
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
               render: function (data, type, row) {
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
                  return `
                           <div class="d-flex align-items-center justify-content-center">
                              <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 edit-vehicle"
                                 href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editVehicleModal"
                                 data-id="${row.id}" data-kode="${row.kode_kendaraan}"
                                 data-jenis="${row.jenis_kendaraan}" data-positions="${row.total_tyre_position}"
                                 data-config-id="${row.tyre_position_configuration_id}"
                                 data-status="${row.tyre_unit_status}" title="Edit">
                                 <i class="icon-base ri ri-pencil-line"></i>
                              </a>
                              ${layoutBtn}
                              <button type="button"
                                 class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light delete-vehicle"
                                 data-id="${row.id}" data-kode="${row.kode_kendaraan}" title="Delete">
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

         const editForm = $('#editVehicleForm');

         $(document).on('click', '.edit-vehicle', function () {
            const id = $(this).data('id');
            const kode = $(this).data('kode');
            const jenis = $(this).data('jenis');
            const positions = $(this).data('positions');
            const configId = $(this).data('config-id');
            const status = $(this).data('status');

            editForm.attr('action', `{{ url('master_data/master_kendaraan') }}/${id}`);
            $('#edit_kode_kendaraan').val(kode);
            $('#edit_jenis_kendaraan').val(jenis === 'null' ? '' : (jenis || ''));
            $('#edit_total_positions').val(positions);
            $('#edit_tyre_position_configuration_id').val(configId === 'null' ? '' : (configId || '')).trigger(
               'change');
            $('#edit_unit_status').val(status);
         });

         $(document).on('click', '.delete-vehicle', function () {
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
                  form.action = `{{ url('master_data/master_kendaraan') }}/${id}`;
                  form.submit();
               }
            });
         });

         $(document).on('click', '.view-layout', function () {
            const configId = $(this).data('config-id');
            const configName = $(this).data('config-name');
            const layoutContainer = $('#layoutContainer');
            const layoutModalTitle = $('#layoutModalTitle');

            layoutModalTitle.text(configName);
            layoutContainer.html(
               '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
            );

            fetch(`/master_data/master_position/${configId}/layout`)
               .then(response => response.text())
               .then(html => {
                  layoutContainer.html(html);
               })
               .catch(err => {
                  layoutContainer.html('<div class="alert alert-danger">Gagal memuat layout.</div>');
               });
         });

         // Auto-detect Total Positions based on Configuration
         $(document).on('change', '.config-selector', function () {
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

         // Initialize Select2
         $('.select2').each(function () {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
               placeholder: $this.data('placeholder'),
               dropdownParent: $this.closest('.modal')
            });
         });
      });
   </script>
@endsection