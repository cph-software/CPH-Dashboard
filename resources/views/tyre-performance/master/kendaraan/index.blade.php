@extends('layouts.admin')

@section('title', 'Master Vehicles')

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Vehicles</h4>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
            <i class="ri-add-line me-1"></i> Add Vehicle
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
                     <th>Unit Code</th>
                     <th>Plate No</th>
                     <th>Area</th>
                     <th>Type</th>
                     <th>Tyre Positions</th>
                     <th>Status</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody class="table-border-bottom-0">
                  @forelse($kendaraans as $kv)
                     <tr>
                        <td><strong>{{ $kv->kode_kendaraan }}</strong></td>
                        <td>{{ $kv->no_polisi }}</td>
                        <td>{{ $kv->area }}</td>
                        <td>{{ $kv->jenis_kendaraan ?? '-' }}</td>
                        <td>{{ $kv->total_tyre_position }}</td>
                        <td>
                           <span
                              class="badge bg-label-{{ $kv->tyre_unit_status == 'Active' ? 'success' : ($kv->tyre_unit_status == 'Maintenance' ? 'warning' : 'secondary') }}">
                              {{ $kv->tyre_unit_status }}
                           </span>
                        </td>
                        <td>
                           <div class="dropdown">
                              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                 <i class="ri-more-2-fill"></i>
                              </button>
                              <div class="dropdown-menu">
                                 <a class="dropdown-item edit-vehicle" href="javascript:void(0);" data-bs-toggle="modal"
                                    data-bs-target="#editVehicleModal" data-id="{{ $kv->id }}"
                                    data-kode="{{ $kv->kode_kendaraan }}" data-polisi="{{ $kv->no_polisi }}"
                                    data-area="{{ $kv->area }}" data-jenis="{{ $kv->jenis_kendaraan }}"
                                    data-tipe="{{ $kv->tipe_kendaraan }}" data-tahun="{{ $kv->tahun_rakit }}"
                                    data-usia="{{ $kv->usia_kendaraan }}" data-silinder="{{ $kv->kapasitas_silinder }}"
                                    data-bpkb="{{ $kv->no_bpkb }}" data-rangka="{{ $kv->no_rangka }}"
                                    data-mesin="{{ $kv->no_mesin }}" data-positions="{{ $kv->total_tyre_position }}"
                                    data-status="{{ $kv->tyre_unit_status }}">
                                    <i class="ri-pencil-line me-1"></i> Edit
                                 </a>
                                 <form action="{{ route('tyre-kendaraan.destroy', $kv->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item">
                                       <i class="ri-delete-bin-line me-1"></i> Delete
                                    </button>
                                 </form>
                              </div>
                           </div>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="7" class="text-center">No data found</td>
                     </tr>
                  @endforelse
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
                        <label for="area" class="form-label">Area</label>
                        <input type="text" id="area" name="area" class="form-control" placeholder="e.g. Site A"
                           required>
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
                        <label for="total_tyre_position" class="form-label">Tyre Positions</label>
                        <input type="number" id="total_tyre_position" name="total_tyre_position" class="form-control"
                           placeholder="e.g. 10" required>
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
                        <label for="edit_area" class="form-label">Area</label>
                        <input type="text" id="edit_area" name="area" class="form-control" required>
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
                        <label for="edit_total_positions" class="form-label">Tyre Positions</label>
                        <input type="number" id="edit_total_positions" name="total_tyre_position" class="form-control"
                           required>
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

@endsection

@section('page-script')
   <script>
      document.addEventListener('DOMContentLoaded', function() {
         const editButtons = document.querySelectorAll('.edit-vehicle');
         const editForm = document.querySelector('#editVehicleForm');

         editButtons.forEach(button => {
            button.addEventListener('click', function() {
               const id = this.getAttribute('data-id');
               const kode = this.getAttribute('data-kode');
               const polisi = this.getAttribute('data-polisi');
               const area = this.getAttribute('data-area');
               const jenis = this.getAttribute('data-jenis');
               const tipe = this.getAttribute('data-tipe');
               const tahun = this.getAttribute('data-tahun');
               const usia = this.getAttribute('data-usia');
               const silinder = this.getAttribute('data-silinder');
               const bpkb = this.getAttribute('data-bpkb');
               const rangka = this.getAttribute('data-rangka');
               const mesin = this.getAttribute('data-mesin');
               const positions = this.getAttribute('data-positions');
               const status = this.getAttribute('data-status');

               editForm.action = `/tyre_performance/master/kendaraan/${id}`;
               document.querySelector('#edit_kode_kendaraan').value = kode;
               document.querySelector('#edit_no_polisi').value = polisi;
               document.querySelector('#edit_area').value = area;
               document.querySelector('#edit_jenis_kendaraan').value = jenis === 'null' ? '' : (jenis ||
                  '');
               document.querySelector('#edit_tipe_kendaraan').value = tipe === 'null' ? '' : (tipe ||
               '');
               document.querySelector('#edit_tahun_rakit').value = tahun === 'null' ? '' : (tahun || '');
               document.querySelector('#edit_usia_kendaraan').value = usia === 'null' ? '' : (usia ||
               '');
               document.querySelector('#edit_kapasitas_silinder').value = silinder === 'null' ? '' : (
                  silinder || '');
               document.querySelector('#edit_no_bpkb').value = bpkb === 'null' ? '' : (bpkb || '');
               document.querySelector('#edit_no_rangka').value = rangka === 'null' ? '' : (rangka || '');
               document.querySelector('#edit_no_mesin').value = mesin === 'null' ? '' : (mesin || '');
               document.querySelector('#edit_total_positions').value = positions;
               document.querySelector('#edit_unit_status').value = status;
            });
         });
      });
   </script>
@endsection
