@extends('layouts.admin')

@section('title', 'Master Vehicles')

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
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Vehicles</h4>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
            <i class="ri-add-line me-1"></i> Add Vehicle
         </button>
      </div>

      <div class="card">
         <div class="card-datatable table-responsive">
            <table class="datatables-vehicles table border-top table-hover">
               <thead>
                  <tr>
                     <th>Unit Code</th>
                     <th>Plate No</th>
                     <th>Location</th>
                     <th>Type</th>
                     <th>Tyre Layout</th>
                     <th>Tyre Positions</th>
                     <th>Status</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @foreach ($kendaraans as $kv)
                     <tr>
                        <td><strong>{{ $kv->kode_kendaraan }}</strong></td>
                        <td>{{ $kv->no_polisi }}</td>
                        <td>{{ $kv->area }}</td>
                        <td>{{ $kv->jenis_kendaraan ?? '-' }}</td>
                        <td>{{ $kv->tyrePositionConfiguration->name ?? '-' }}</td>
                        <td>{{ $kv->total_tyre_position }}</td>
                        <td>
                           <span
                              class="badge bg-label-{{ $kv->tyre_unit_status == 'Active' ? 'success' : ($kv->tyre_unit_status == 'Maintenance' ? 'warning' : 'secondary') }}">
                              {{ $kv->tyre_unit_status }}
                           </span>
                        </td>
                        <td>
                           <div class="d-flex align-items-center">
                              <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 edit-vehicle"
                                 href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editVehicleModal"
                                 data-id="{{ $kv->id }}" data-kode="{{ $kv->kode_kendaraan }}"
                                 data-polisi="{{ $kv->no_polisi }}" data-area="{{ $kv->area }}"
                                 data-jenis="{{ $kv->jenis_kendaraan }}" data-tipe="{{ $kv->tipe_kendaraan }}"
                                 data-tahun="{{ $kv->tahun_rakit }}" data-usia="{{ $kv->usia_kendaraan }}"
                                 data-silinder="{{ $kv->kapasitas_silinder }}" data-bpkb="{{ $kv->no_bpkb }}"
                                 data-rangka="{{ $kv->no_rangka }}" data-mesin="{{ $kv->no_mesin }}"
                                 data-positions="{{ $kv->total_tyre_position }}"
                                 data-config-id="{{ $kv->tyre_position_configuration_id }}"
                                 data-status="{{ $kv->tyre_unit_status }}" title="Edit">
                                 <i class="icon-base ri ri-pencil-line"></i>
                              </a>
                              @if ($kv->tyre_position_configuration_id)
                                 <button type="button"
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light me-1 view-layout"
                                    data-bs-toggle="modal" data-bs-target="#viewLayoutModal"
                                    data-config-name="{{ $kv->tyrePositionConfiguration->name }}"
                                    data-config-id="{{ $kv->tyre_position_configuration_id }}" title="View Layout">
                                    <i class="icon-base ri ri-layout-6-line text-primary"></i>
                                 </button>
                              @endif
                              <button type="button"
                                 class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light delete-vehicle"
                                 data-id="{{ $kv->id }}" data-kode="{{ $kv->kode_kendaraan }}" title="Delete">
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

   <!-- Add Vehicle Modal -->
   <div class="modal fade" id="addVehicleModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Add New Vehicle</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-kendaraan.store') }}" method="POST">
               @csrf
               <div class="modal-body">
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="kode_kendaraan" class="form-label">Unit Code</label>
                        <input type="text" id="kode_kendaraan" name="kode_kendaraan" class="form-control"
                           placeholder="e.g. DT-101" required>
                     </div>
                     <div class="col mb-3">
                        <label for="no_polisi" class="form-label">Plate No</label>
                        <input type="text" id="no_polisi" name="no_polisi" class="form-control"
                           placeholder="e.g. B 1234 ABC" required>
                     </div>
                     <div class="col mb-3">
                        <label for="area" class="form-label">Location (Area)</label>
                        <select id="area" name="area" class="form-select" required>
                           <option value="">-- Select Location --</option>
                           @foreach ($locations as $loc)
                              <option value="{{ $loc->location_name }}">{{ $loc->location_name }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="jenis_kendaraan" class="form-label">Vehicle Type</label>
                        <input type="text" id="jenis_kendaraan" name="jenis_kendaraan" class="form-control"
                           placeholder="e.g. Dump Truck">
                     </div>
                     <div class="col mb-3">
                        <label for="tipe_kendaraan" class="form-label">Model/Brand</label>
                        <input type="text" id="tipe_kendaraan" name="tipe_kendaraan" class="form-control"
                           placeholder="e.g. Hino 500">
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="tahun_rakit" class="form-label">Year</label>
                        <input type="number" id="tahun_rakit" name="tahun_rakit" class="form-control"
                           placeholder="e.g. 2022">
                     </div>
                     <div class="col mb-3">
                        <label for="usia_kendaraan" class="form-label">Age</label>
                        <input type="text" id="usia_kendaraan" name="usia_kendaraan" class="form-control"
                           placeholder="e.g. 2 Years">
                     </div>
                     <div class="col mb-3">
                        <label for="kapasitas_silinder" class="form-label">Cylinder Cap.</label>
                        <input type="text" id="kapasitas_silinder" name="kapasitas_silinder" class="form-control"
                           placeholder="e.g. 5000cc">
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="no_bpkb" class="form-label">BPKB No</label>
                        <input type="text" id="no_bpkb" name="no_bpkb" class="form-control">
                     </div>
                     <div class="col mb-3">
                        <label for="no_rangka" class="form-label">Frame No</label>
                        <input type="text" id="no_rangka" name="no_rangka" class="form-control">
                     </div>
                     <div class="col mb-3">
                        <label for="no_mesin" class="form-label">Engine No</label>
                        <input type="text" id="no_mesin" name="no_mesin" class="form-control">
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="total_tyre_position" class="form-label">Total Tyre Positions</label>
                        <input type="number" id="total_tyre_position" name="total_tyre_position" class="form-control"
                           placeholder="e.g. 10" required>
                     </div>
                     <div class="col mb-3">
                        <label for="tyre_position_configuration_id" class="form-label">Tyre Layout Configuration</label>
                        <select name="tyre_position_configuration_id" class="form-select">
                           <option value="">-- Select Configuration --</option>
                           @foreach ($configurations as $config)
                              <option value="{{ $config->id }}">{{ $config->name }} ({{ $config->code }})</option>
                           @endforeach
                        </select>
                        <small class="text-muted">Optional: Link to a visual layout template</small>
                     </div>
                     <div class="col mb-3">
                        <label for="tyre_unit_status" class="form-label">Status</label>
                        <select name="tyre_unit_status" class="form-select" required>
                           <option value="Active">Active</option>
                           <option value="Inactive">Inactive</option>
                           <option value="Maintenance">Maintenance</option>
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

   <!-- Edit Vehicle Modal -->
   <div class="modal fade" id="editVehicleModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Edit Vehicle</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editVehicleForm" method="POST">
               @csrf
               @method('PUT')
               <div class="modal-body">
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_kode_kendaraan" class="form-label">Unit Code</label>
                        <input type="text" id="edit_kode_kendaraan" name="kode_kendaraan" class="form-control"
                           required>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_no_polisi" class="form-label">Plate No</label>
                        <input type="text" id="edit_no_polisi" name="no_polisi" class="form-control" required>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_area" class="form-label">Location (Area)</label>
                        <select id="edit_area" name="area" class="form-select" required>
                           <option value="">-- Select Location --</option>
                           @foreach ($locations as $loc)
                              <option value="{{ $loc->location_name }}">{{ $loc->location_name }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_jenis_kendaraan" class="form-label">Vehicle Type</label>
                        <input type="text" id="edit_jenis_kendaraan" name="jenis_kendaraan" class="form-control">
                     </div>
                     <div class="col mb-3">
                        <label for="edit_tipe_kendaraan" class="form-label">Model/Brand</label>
                        <input type="text" id="edit_tipe_kendaraan" name="tipe_kendaraan" class="form-control">
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_tahun_rakit" class="form-label">Year</label>
                        <input type="number" id="edit_tahun_rakit" name="tahun_rakit" class="form-control">
                     </div>
                     <div class="col mb-3">
                        <label for="edit_usia_kendaraan" class="form-label">Age</label>
                        <input type="text" id="edit_usia_kendaraan" name="usia_kendaraan" class="form-control">
                     </div>
                     <div class="col mb-3">
                        <label for="edit_kapasitas_silinder" class="form-label">Cylinder Cap.</label>
                        <input type="text" id="edit_kapasitas_silinder" name="kapasitas_silinder"
                           class="form-control">
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_no_bpkb" class="form-label">BPKB No</label>
                        <input type="text" id="edit_no_bpkb" name="no_bpkb" class="form-control">
                     </div>
                     <div class="col mb-3">
                        <label for="edit_no_rangka" class="form-label">Frame No</label>
                        <input type="text" id="edit_no_rangka" name="no_rangka" class="form-control">
                     </div>
                     <div class="col mb-3">
                        <label for="edit_no_mesin" class="form-label">Engine No</label>
                        <input type="text" id="edit_no_mesin" name="no_mesin" class="form-control">
                     </div>
                  </div>
                  <div class="row g-2">
                     <div class="col mb-3">
                        <label for="edit_total_positions" class="form-label">Total Tyre Positions</label>
                        <input type="number" id="edit_total_positions" name="total_tyre_position" class="form-control"
                           required>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_tyre_position_configuration_id" class="form-label">Tyre Layout
                           Configuration</label>
                        <select id="edit_tyre_position_configuration_id" name="tyre_position_configuration_id"
                           class="form-select">
                           <option value="">-- No Configuration --</option>
                           @foreach ($configurations as $config)
                              <option value="{{ $config->id }}">{{ $config->name }} ({{ $config->code }})</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col mb-3">
                        <label for="edit_unit_status" class="form-label">Status</label>
                        <select id="edit_unit_status" name="tyre_unit_status" class="form-select" required>
                           <option value="Active">Active</option>
                           <option value="Inactive">Inactive</option>
                           <option value="Maintenance">Maintenance</option>
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

   <!-- View Layout Modal -->
   <div class="modal fade" id="viewLayoutModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Vehicle Tyre Layout: <span id="layoutModalTitle"></span></h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         $('.datatables-vehicles').DataTable({
            order: [
               [0, 'desc']
            ],
            displayLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
         });

         const editForm = $('#editVehicleForm');

         $(document).on('click', '.edit-vehicle', function() {
            const id = $(this).data('id');
            const kode = $(this).data('kode');
            const polisi = $(this).data('polisi');
            const area = $(this).data('area');
            const jenis = $(this).data('jenis');
            const tipe = $(this).data('tipe');
            const tahun = $(this).data('tahun');
            const usia = $(this).data('usia');
            const silinder = $(this).data('silinder');
            const bpkb = $(this).data('bpkb');
            const rangka = $(this).data('rangka');
            const mesin = $(this).data('mesin');
            const positions = $(this).data('positions');
            const configId = $(this).data('config-id');
            const status = $(this).data('status');

            editForm.attr('action', `{{ url('tyre_performance/master_kendaraan') }}/${id}`);
            $('#edit_kode_kendaraan').val(kode);
            $('#edit_no_polisi').val(polisi);
            $('#edit_area').val(area);
            $('#edit_jenis_kendaraan').val(jenis === 'null' ? '' : (jenis || ''));
            $('#edit_tipe_kendaraan').val(tipe === 'null' ? '' : (tipe || ''));
            $('#edit_tahun_rakit').val(tahun === 'null' ? '' : (tahun || ''));
            $('#edit_usia_kendaraan').val(usia === 'null' ? '' : (usia || ''));
            $('#edit_kapasitas_silinder').val(silinder === 'null' ? '' : (silinder || ''));
            $('#edit_no_bpkb').val(bpkb === 'null' ? '' : (bpkb || ''));
            $('#edit_no_rangka').val(rangka === 'null' ? '' : (rangka || ''));
            $('#edit_no_mesin').val(mesin === 'null' ? '' : (mesin || ''));
            $('#edit_total_positions').val(positions);
            $('#edit_tyre_position_configuration_id').val(configId === 'null' ? '' : (configId || ''));
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
                  form.action = `{{ url('tyre_performance/master_kendaraan') }}/${id}`;
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

            fetch(`/tyre_performance/master_position/${configId}/layout`)
               .then(response => response.text())
               .then(html => {
                  layoutContainer.html(html);
               })
               .catch(err => {
                  layoutContainer.html('<div class="alert alert-danger">Gagal memuat layout.</div>');
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
      });
   </script>
@endsection
