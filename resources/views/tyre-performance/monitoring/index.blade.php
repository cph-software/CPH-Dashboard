@extends('layouts.admin')

@section('title', 'Tyre Monitoring')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
   <style>
      #layoutPreview .v-chassis {
         transform: scale(0.7);
         transform-origin: top center;
         margin-bottom: -100px;
      }

      .v-chassis {
         position: relative;
         width: 100%;
         max-width: 350px;
         margin: 0 auto;
         background: #fff;
         border-radius: 15px;
         padding: 30px 15px;
         border: 1px solid #ddd;
      }

      .v-cabin {
         width: 80px;
         height: 35px;
         background: #333;
         margin: 0 auto 20px auto;
         border-radius: 6px;
         color: #fff;
         font-size: 10px;
         line-height: 35px;
         font-weight: bold;
      }

      .v-axle {
         display: flex;
         justify-content: space-between;
         margin-bottom: 25px;
         position: relative;
      }

      .v-axle::after {
         content: '';
         position: absolute;
         top: 50%;
         left: 50%;
         transform: translate(-50%, -50%);
         width: 60%;
         height: 2px;
         background: #eee;
         z-index: 1;
      }

      .v-tyre {
         width: 28px;
         height: 48px;
         background: #fff;
         border: 1px solid #ccc;
         border-radius: 4px;
         z-index: 2;
         position: relative;
         display: flex;
         justify-content: center;
         align-items: center;
      }

      .v-tyre.filled {
         background: #333 !important;
      }

      .v-tyre-code {
         font-size: 8px;
         font-weight: bold;
         color: #666;
      }

      .v-tyre.filled .v-tyre-code {
         color: #fff;
      }

      .v-tyre-sn-hint {
         position: absolute;
         bottom: -15px;
         font-size: 8px;
         color: #7367f0;
         font-weight: bold;
      }

      .v-group {
         display: flex;
         gap: 4px;
      }

      .v-spare-list {
         display: flex;
         justify-content: center;
         gap: 10px;
         margin-top: 15px;
         padding-top: 15px;
         border-top: 1px dashed #eee;
      }

      .v-tyre.spare {
         width: 48px;
         height: 28px;
      }
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Operations /</span> Tyre Monitoring</h4>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
            <i class="ri ri-add-line me-1"></i> Add Vehicle for Monitoring
         </button>
      </div>

      <div class="card">
         <div class="card-datatable table-responsive">
            <table class="datatables-monitoring-vehicles table border-top table-hover">
               <thead>
                  <tr>
                     <th>Fleet Name</th>
                     <th>Vehicle Number</th>
                     <th>Driver</th>
                     <th>Pos</th>
                     <th>Active Sessions</th>
                     <th>Status</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($vehicles as $vehicle)
                     <tr>
                        <td>{{ $vehicle->fleet_name }}</td>
                        <td>{{ $vehicle->vehicle_number }}</td>
                        <td>{{ $vehicle->driver_name }}</td>
                        <td>{{ $vehicle->tire_positions }}</td>
                        <td>
                           @if ($vehicle->sessions_count > 0)
                              <span class="badge bg-label-success">{{ $vehicle->sessions_count }} Active</span>
                           @else
                              <span class="badge bg-label-secondary">None</span>
                           @endif
                        </td>
                        <td>
                           <span class="badge bg-label-{{ $vehicle->status == 'active' ? 'success' : 'danger' }}">
                              {{ ucfirst($vehicle->status) }}
                           </span>
                        </td>
                        <td>
                           <div class="d-flex gap-2">
                              <a href="{{ route('monitoring.vehicle.show', $vehicle->vehicle_id) }}"
                                 class="btn btn-sm btn-icon btn-outline-primary" title="View Sessions">
                                 <i class="ri ri-eye-line"></i>
                              </a>
                              <button type="button" class="btn btn-sm btn-icon btn-outline-warning edit-vehicle-btn"
                                 title="Edit Vehicle" data-bs-toggle="modal" data-bs-target="#editVehicleModal"
                                 data-id="{{ $vehicle->vehicle_id }}" data-fleet="{{ $vehicle->fleet_name }}"
                                 data-no="{{ $vehicle->vehicle_number }}" data-driver="{{ $vehicle->driver_name }}"
                                 data-phone="{{ $vehicle->phone_number }}" data-app="{{ $vehicle->application }}"
                                 data-capacity="{{ $vehicle->load_capacity }}" data-pos="{{ $vehicle->tire_positions }}"
                                 data-master="{{ $vehicle->master_vehicle_id }}">
                                 <i class="ri ri-edit-line"></i>
                              </button>
                               <form action="{{ route('monitoring.vehicle.destroy', $vehicle->vehicle_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus monitoring kendaraan ini?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-icon btn-outline-danger"><i class="ri ri-delete-bin-line"></i></button></form>
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
      <div class="modal-dialog modal-dialog-centered modal-lg">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Add Monitoring Vehicle</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('monitoring.vehicle.store') }}" method="POST">
               @csrf
               <div class="modal-body">
                  <div class="row">
                     <div class="col-md-7 border-end">
                        <div class="row g-3">
                           <div class="col-md-12">
                              <label class="form-label">Link to Master Vehicle (Optional)</label>
                              <select name="master_vehicle_id" class="form-select select2" id="selectMasterVehicle">
                                 <option value="">-- No Link --</option>
                                 @foreach ($masterVehicles as $m)
                                    <option value="{{ $m->id }}" data-no="{{ $m->no_polisi }}"
                                       data-kode="{{ $m->kode_kendaraan }}" data-payload="{{ $m->payload_capacity }}"
                                       data-pos="{{ $m->total_tyre_position }}">
                                       {{ $m->no_polisi }} ({{ $m->kode_kendaraan }})
                                    </option>
                                 @endforeach
                              </select>
                              <div class="form-text">Auto-syncs Monitoring with vehicle master data.</div>
                           </div>
                           <div class="col-md-6">
                              <label class="form-label">Fleet Name</label>
                              <input type="text" name="fleet_name" id="fleetNameField" class="form-control" required
                                 placeholder="example: FL-01">
                           </div>
                           <div class="col-md-6">
                              <label class="form-label">Vehicle Number (Plate)</label>
                              <input type="text" name="vehicle_number" id="vehicleNumberField" class="form-control"
                                 required placeholder="B 1234 ABC">
                           </div>
                           <div class="col-md-6">
                              <label class="form-label">Driver Name</label>
                              <input type="text" name="driver_name" class="form-control" required>
                           </div>
                           <div class="col-md-6">
                              <label class="form-label">Phone Number</label>
                              <input type="text" name="phone_number" class="form-control">
                           </div>
                           <div class="col-md-12">
                              <label class="form-label">Application / Route</label>
                              <input type="text" name="application" class="form-control"
                                 placeholder="example: Maros-Toraja">
                           </div>
                           <div class="col-md-6">
                              <label class="form-label">Load Capacity (Payload)</label>
                              <input type="text" name="load_capacity" id="loadCapacityField" class="form-control"
                                 placeholder="e.g. 30 Ton">
                           </div>
                           <div class="col-md-6">
                              <label class="form-label">Tire Positions</label>
                              <input type="number" name="tire_positions" id="tirePositionsField" class="form-control"
                                 value="6" required min="1">
                           </div>
                        </div>
                     </div>
                     <div
                        class="col-md-5 text-center bg-light rounded d-flex flex-column align-items-center justify-content-center p-3">
                        <small class="fw-bold mb-3 text-primary">LAYOUT PREVIEW</small>
                        <div id="layoutPreview" class="w-100">
                           <span class="text-muted italic small"><i class="ri-information-line me-1"></i>Select master
                              vehicle to preview layout and installed tyres</span>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Save Vehicle</button>
               </div>
            </form>
         </div>
      </div>
   </div>
   <!-- Edit Vehicle Modal -->
   <div class="modal fade" id="editVehicleModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Edit Monitoring Vehicle</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editVehicleForm" method="POST">
               @csrf
               @method('PUT')
               <div class="modal-body">
                  <div class="row">
                     <div class="col-md-7 border-end">
                        <div class="row g-3">
                           <div class="col-md-12">
                              <label class="form-label">Link to Master Vehicle (Optional)</label>
                              <select name="master_vehicle_id" class="form-select select2" id="editSelectMasterVehicle">
                                 <option value="">-- No Link --</option>
                                 @foreach ($masterVehicles as $m)
                                    <option value="{{ $m->id }}" data-no="{{ $m->no_polisi }}"
                                       data-kode="{{ $m->kode_kendaraan }}" data-payload="{{ $m->payload_capacity }}"
                                       data-pos="{{ $m->total_tyre_position }}">
                                       {{ $m->no_polisi }} ({{ $m->kode_kendaraan }})
                                    </option>
                                 @endforeach
                              </select>
                           </div>
                           <div class="col-md-6">
                              <label class="form-label">Fleet Name</label>
                              <input type="text" name="fleet_name" id="editFleetNameField" class="form-control"
                                 required>
                           </div>
                           <div class="col-md-6">
                              <label class="form-label">Vehicle Number (Plate)</label>
                              <input type="text" name="vehicle_number" id="editVehicleNumberField"
                                 class="form-control" required>
                           </div>
                           <div class="col-md-6">
                              <label class="form-label">Driver Name</label>
                              <input type="text" name="driver_name" id="editDriverNameField" class="form-control"
                                 required>
                           </div>
                           <div class="col-md-6">
                              <label class="form-label">Phone Number</label>
                              <input type="text" name="phone_number" id="editPhoneField" class="form-control">
                           </div>
                           <div class="col-md-12">
                              <label class="form-label">Application / Route</label>
                              <input type="text" name="application" id="editApplicationField" class="form-control">
                           </div>
                           <div class="col-md-6">
                              <label class="form-label">Load Capacity (Payload)</label>
                              <input type="text" name="load_capacity" id="editLoadCapacityField"
                                 class="form-control">
                           </div>
                           <div class="col-md-6">
                              <label class="form-label">Tire Positions</label>
                              <input type="number" name="tire_positions" id="editTirePositionsField"
                                 class="form-control" required min="1">
                           </div>
                        </div>
                     </div>
                     <div
                        class="col-md-5 text-center bg-light rounded d-flex flex-column align-items-center justify-content-center p-3">
                        <small class="fw-bold mb-3 text-primary">LAYOUT PREVIEW</small>
                        <div id="editLayoutPreview" class="w-100">
                           <span class="text-muted italic small"><i class="ri-information-line me-1"></i>Vehicle layout
                              preview</span>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Update Vehicle</button>
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
      $(function() {
         $('.datatables-monitoring-vehicles').DataTable({
            order: [
               [0, 'desc']
            ]
         });

         $(document).on('change', '#selectMasterVehicle, #editSelectMasterVehicle', function() {
            const isEdit = $(this).attr('id').includes('edit');
            const prefix = isEdit ? '#edit' : '#';
            const selected = $(this).find(':selected');
            const vehicleId = selected.val();

            if (vehicleId) {
               // Initial auto-fill from data attributes
               $(prefix + 'FleetNameField').val(selected.data('code') || selected.data('kode'));
               $(prefix + 'VehicleNumberField').val(selected.data('no'));
               $(prefix + 'LoadCapacityField').val(selected.data('payload'));

               // Force fill the position field
               const posCount = selected.data('pos');
               if (posCount) {
                  $(prefix + 'TirePositionsField').val(posCount);
               }

               // Fetch detailed preview
               $(prefix + 'LayoutPreview').html(
                  '<div class="spinner-border text-primary spinner-border-sm" role="status"></div><br><span class="small text-muted">Loading preview...</span>'
               );

               const detailUrl = "{{ route('monitoring.master-vehicle.details', ':id') }}".replace(':id',
                  vehicleId);

               $.get(detailUrl, function(res) {
                  if (res.success) {
                     $(prefix + 'LayoutPreview').html(res.data.layout_html);

                     // Initialize tooltips for the new layout
                     if (typeof bootstrap !== 'undefined') {
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll(
                           prefix + 'LayoutPreview [title]'))
                        tooltipTriggerList.map(function(tooltipTriggerEl) {
                           return new bootstrap.Tooltip(tooltipTriggerEl)
                        });
                     }

                     // Auto-fill from AJAX results if exists (highest priority)
                     if (res.data.payload_capacity) $(prefix + 'LoadCapacityField').val(res.data
                        .payload_capacity);
                     if (res.data.total_tyre_position) $(prefix + 'TirePositionsField').val(res.data
                        .total_tyre_position);
                  } else {
                     $(prefix + 'LayoutPreview').html(
                        '<span class="text-danger small">Failed to load preview</span>');
                  }
               }).fail(function() {
                  $(prefix + 'LayoutPreview').html(
                     '<span class="text-danger small">Error loading preview</span>');
               });
            } else {
               if (!isEdit) {
                  $(prefix + 'FleetNameField').val('');
                  $(prefix + 'VehicleNumberField').val('');
                  $(prefix + 'LoadCapacityField').val('');
                  $(prefix + 'TirePositionsField').val(6);
               }
               $(prefix + 'LayoutPreview').html(
                  '<span class="text-muted italic small">Select master vehicle to preview layout</span>');
            }
         });

         // Populate Edit Modal
         $(document).on('click', '.edit-vehicle-btn', function() {
            const btn = $(this);
            const id = btn.data('id');
            const form = $('#editVehicleForm');

            // Set action URL
            form.attr('action', "{{ route('monitoring.vehicle.update', ':id') }}".replace(':id', id));

            // Fill fields
            $('#editFleetNameField').val(btn.data('fleet'));
            $('#editVehicleNumberField').val(btn.data('no'));
            $('#editDriverNameField').val(btn.data('driver'));
            $('#editPhoneField').val(btn.data('phone'));
            $('#editApplicationField').val(btn.data('app'));
            $('#editLoadCapacityField').val(btn.data('capacity'));
            $('#editTirePositionsField').val(btn.data('pos'));

            // Set Master Vehicle link
            const masterId = btn.data('master');
            if (masterId) {
               $('#editSelectMasterVehicle').val(masterId).trigger('change');
            } else {
               $('#editSelectMasterVehicle').val('').trigger('change');
            }
         });
      });
   </script>
@endsection
