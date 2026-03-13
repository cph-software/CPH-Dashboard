@extends('layouts.admin')

@section('title', 'Vehicle Monitoring Status')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <style>
      .v-chassis {
         transform: scale(0.85);
         transform-origin: top center;
         margin-bottom: -50px;
      }

      .status-dot {
         width: 10px;
         height: 10px;
         border-radius: 50%;
         display: inline-block;
         margin-right: 5px;
      }

      .status-empty {
         background: #eee;
         border: 1px solid #ddd;
      }

      .status-filled {
         background: #2d2d2d;
      }
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <div class="d-flex align-items-center">
            <a href="{{ route('monitoring.index') }}" class="btn btn-icon btn-outline-secondary me-3">
               <i class="ri ri-arrow-left-line"></i>
            </a>
            <div>
               <h4 class="fw-bold py-1 mb-0"><span class="text-muted fw-light">Operations / Monitoring /</span>
                  {{ $vehicle->fleet_name }}</h4>
               <p class="text-muted mb-0">{{ $vehicle->vehicle_number }} - {{ $vehicle->driver_name }}</p>
            </div>
         </div>
         <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
               data-bs-target="#editVehicleModal">
               <i class="ri ri-settings-3-line me-1"></i> Config
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSessionModal">
               <i class="ri ri-add-line me-1"></i> Start New Session
            </button>
         </div>
      </div>

      <div class="row">
         {{-- Visual Layout Section --}}
         <div class="col-xl-4 col-lg-5 col-md-12 mb-4">
            <div class="card h-100">
               <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0">Vehicle Layout</h5>
                  <span class="badge bg-label-primary">Current View</span>
               </div>
               <div class="card-body p-4 text-center">
                  @if ($configuration)
                     @include('tyre-performance.movement._vehicle_layout', [
                         'configuration' => $configuration,
                         'assignedTyres' => $assignedTyres,
                     ])
                  @else
                     <div class="py-5">
                        <i class="ri-truck-line ri-4x text-light"></i>
                        <p class="text-muted mt-2">No master vehicle link or configuration found for this vehicle.</p>
                        <button class="btn btn-sm btn-label-primary" data-bs-toggle="modal"
                           data-bs-target="#editVehicleModal">Link Master Vehicle</button>
                     </div>
                  @endif
               </div>
            </div>
         </div>

         {{-- Status Table Section --}}
         <div class="col-xl-8 col-lg-7 col-md-12 mb-4">
            <div class="card h-100">
               <div class="card-header border-bottom">
                  <h5 class="card-title mb-0">Current Tyre Status</h5>
               </div>
               <div class="table-responsive">
                  <table class="table table-hover mb-0">
                     <thead class="table-light text-nowrap">
                        <tr>
                           <th>Pos</th>
                           <th>Serial Number</th>
                           <th>Spec (Brand/Pattern/Size)</th>
                           <th>Current RTD</th>
                           <th>Condition</th>
                        </tr>
                     </thead>
                     <tbody>
                        @forelse ($masterPositions as $p)
                           @php $t = $assignedTyres->get($p->id); @endphp
                           <tr>
                              <td>
                                 <span class="fw-bold">{{ $p->position_code }}</span>
                                 <small class="text-muted d-block">{{ $p->position_name }}</small>
                              </td>
                              <td>
                                 @if ($t)
                                    <span class="badge bg-label-dark">{{ $t->serial_number }}</span>
                                 @else
                                    <span class="text-muted italic small">Kosong</span>
                                 @endif
                              </td>
                              <td>
                                 @if ($t)
                                    {{ $t->brand->brand_name ?? '-' }} / {{ $t->pattern->name ?? '-' }} /
                                    {{ $t->size->size ?? '-' }}
                                 @else
                                    -
                                 @endif
                              </td>
                              <td>
                                 @if ($t)
                                    <span class="fw-bold">{{ $t->current_rtd ?? '-' }} mm</span>
                                 @else
                                    -
                                 @endif
                              </td>
                              <td>
                                 @if ($t)
                                    @php
                                       $orig = $t->original_rtd > 0 ? $t->original_rtd : 1;
                                       $curr = $t->current_rtd ?? 0;
                                       $perc = ($curr / $orig) * 100;
                                       $color = $perc < 20 ? 'danger' : ($perc < 50 ? 'warning' : 'success');
                                    @endphp
                                    <div class="d-flex align-items-center">
                                       <div class="progress w-100 me-2" style="height: 6px;">
                                          <div class="progress-bar bg-{{ $color }}"
                                             style="width: {{ $perc }}%"></div>
                                       </div>
                                       <small>{{ round($perc) }}%</small>
                                    </div>
                                 @else
                                    -
                                 @endif
                              </td>
                           </tr>
                        @empty
                           <tr>
                              <td colspan="5" class="text-center py-4">Link to Master Vehicle to see real-time tyre
                                 status table</td>
                           </tr>
                        @endforelse
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>

      {{-- Sessions History Table --}}
      <div class="card">
         <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Monitoring Period History</h5>
         </div>
         <div class="card-datatable table-responsive">
            <table class="datatables-sessions table table-hover">
               <thead>
                  <tr>
                     <th>Period Start</th>
                     <th>Tyre Size (Label)</th>
                     <th>Odo Start</th>
                     <th>Checks</th>
                     <th>Status</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($sessions as $session)
                     <tr>
                        <td>{{ $session->install_date }}</td>
                        <td>{{ $session->tyre_size }}</td>
                        <td>{{ number_format($session->odometer_start) }} KM</td>
                        <td><span class="badge bg-label-info">{{ $session->checks_count }} Checks</span></td>
                        <td>
                           <span class="badge bg-label-{{ $session->status == 'active' ? 'success' : 'secondary' }}">
                              {{ ucfirst($session->status) }}
                           </span>
                        </td>
                        <td>
                           <div class="d-flex gap-2">
                              <a href="{{ route('monitoring.sessions.show', $session->session_id) }}"
                                 class="btn btn-sm btn-icon btn-outline-primary" title="View Detailed Examination">
                                 <i class="ri ri-eye-line"></i>
                              </a>
                              <a href="{{ route('monitoring.sessions.export', $session->session_id) }}"
                                 class="btn btn-sm btn-icon btn-outline-success" title="Export Excel">
                                 <i class="ri ri-file-excel-2-line"></i>
                              </a>
                           </div>
                        </td>
                     </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
      </div>
   </div>

   <!-- Add Session Modal (Integrated with Cek 1) -->
   <div class="modal fade" id="addSessionModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered">
         <div class="modal-content border-top border-primary border-3">
            <div class="modal-header">
               <h5 class="modal-title">Start New Session & Record First Check (Cek 1)</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('monitoring.sessions.store') }}" method="POST">
               @csrf
               <input type="hidden" name="vehicle_id" value="{{ $vehicle->vehicle_id }}">
               <input type="hidden" name="master_vehicle_id" value="{{ $vehicle->master_vehicle_id }}">
               <div class="modal-body">
                  <div class="row g-4 mb-4">
                     <div class="col-md-3">
                        <label class="form-label fw-bold text-primary">Start Date</label>
                        <input type="date" name="install_date" class="form-control" required
                           value="{{ date('Y-m-d') }}">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold text-primary">Current Odometer</label>
                        <input type="number" name="odometer_start" class="form-control" required
                           placeholder="KM at start">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold text-primary">Original RTD (Ref)</label>
                        <input type="number" name="original_rtd" class="form-control" step="0.1" required
                           placeholder="E.g. 14.5">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold text-primary">Tyre Size Label</label>
                        <select name="tyre_size" class="form-select select2-setup" required>
                           <option value="">-- Select Size --</option>
                           @foreach ($sizes as $s)
                              <option value="{{ $s->size }}">{{ $s->size }}</option>
                           @endforeach
                        </select>
                     </div>
                  </div>

                  @if (!$vehicle->master_vehicle_id)
                     <div class="alert alert-warning">
                        <i class="ri-error-warning-line me-1"></i>
                        This vehicle is not linked to Master Data. Please link it first to automatically record
                        measurements for all tyres.
                     </div>
                  @else
                     <h6 class="fw-bold mb-3 mt-4"><i class="ri-list-check me-1"></i> Initial Tire Measurements (Cek 1)
                     </h6>
                     <div class="table-responsive border rounded">
                        <table class="table table-sm table-striped align-middle mb-0">
                           <thead class="table-light">
                              <tr>
                                 <th width="40">Pos</th>
                                 <th>Serial Number</th>
                                 <th width="80">PSI</th>
                                 <th width="80">RTD 1</th>
                                 <th width="80">RTD 2</th>
                                 <th width="80">RTD 3</th>
                                 <th>Condition</th>
                                 <th>Notes</th>
                              </tr>
                           </thead>
                           <tbody>
                              @foreach ($assignedTyres as $posId => $tyre)
                                 @php $pos = $masterPositions->where('id', $posId)->first(); @endphp
                                 <tr>
                                    <td class="fw-bold text-center">{{ $pos->position_code ?? $posId }}</td>
                                    <td>
                                       <div class="d-flex flex-column">
                                          <span class="fw-bold">{{ $tyre->serial_number }}</span>
                                          <small class="text-muted">{{ $tyre->brand->brand_name }} /
                                             {{ $tyre->pattern->name }}</small>
                                       </div>
                                    </td>
                                    <td>
                                       <input type="number" name="checks[{{ $tyre->serial_number }}][psi]"
                                          class="form-control form-control-sm" placeholder="Psi">
                                    </td>
                                    <td>
                                       <input type="number" name="checks[{{ $tyre->serial_number }}][rtd_1]"
                                          class="form-control form-control-sm" step="0.1"
                                          value="{{ $tyre->current_tread_depth }}">
                                    </td>
                                    <td>
                                       <input type="number" name="checks[{{ $tyre->serial_number }}][rtd_2]"
                                          class="form-control form-control-sm" step="0.1"
                                          value="{{ $tyre->current_tread_depth }}">
                                    </td>
                                    <td>
                                       <input type="number" name="checks[{{ $tyre->serial_number }}][rtd_3]"
                                          class="form-control form-control-sm" step="0.1"
                                          value="{{ $tyre->current_tread_depth }}">
                                    </td>
                                    <td>
                                       <select name="checks[{{ $tyre->serial_number }}][condition]"
                                          class="form-select form-select-sm">
                                          <option value="ok">OK</option>
                                          <option value="warning">Warning</option>
                                          <option value="critical">Critical</option>
                                       </select>
                                    </td>
                                    <td>
                                       <input type="text" name="checks[{{ $tyre->serial_number }}][notes]"
                                          class="form-control form-control-sm" placeholder="Catatan">
                                    </td>
                                 </tr>
                              @endforeach
                           </tbody>
                        </table>
                        <div class="bg-light p-2 small text-muted">
                           <i class="ri-information-line me-1"></i> Data di atas akan otomatis tercatat sebagai <b>Cek
                              #1</b> dalam sesi monitoring yang baru.
                        </div>
                     </div>
                  @endif
               </div>
               <div class="modal-footer bg-light p-3">
                  <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary btn-lg">
                     <i class="ri-checkbox-circle-line me-1"></i> Start Session & Save Cek 1
                  </button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <!-- Re-use Edit Vehicle Modal for Config -->
   <div class="modal fade" id="editVehicleModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Vehicle Configuration</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('monitoring.vehicle.update', $vehicle->vehicle_id) }}" method="POST">
               @csrf
               @method('PUT')
               <div class="modal-body">
                  <div class="row g-3">
                     <div class="col-md-12">
                        <label class="form-label text-primary fw-bold">Link to Master Vehicle</label>
                        <select name="master_vehicle_id" class="form-select select2-setup">
                           <option value="">-- No Link --</option>
                           @foreach (\App\Models\MasterImportKendaraan::all() as $m)
                              <option value="{{ $m->id }}"
                                 {{ $vehicle->master_vehicle_id == $m->id ? 'selected' : '' }}>
                                 {{ $m->no_polisi }} ({{ $m->kode_kendaraan }})
                              </option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-md-6">
                        <label class="form-label">Fleet Name</label>
                        <input type="text" name="fleet_name" class="form-control"
                           value="{{ $vehicle->fleet_name }}" required>
                     </div>
                     <div class="col-md-6">
                        <label class="form-label">Plate Number</label>
                        <input type="text" name="vehicle_number" class="form-control"
                           value="{{ $vehicle->vehicle_number }}" required>
                     </div>
                     <div class="col-md-6">
                        <label class="form-label">Driver</label>
                        <input type="text" name="driver_name" class="form-control"
                           value="{{ $vehicle->driver_name }}">
                     </div>
                     <div class="col-md-6">
                        <label class="form-label">Load Capacity</label>
                        <input type="text" name="load_capacity" class="form-control"
                           value="{{ $vehicle->load_capacity }}">
                     </div>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Update Config</button>
               </div>
            </form>
         </div>
      </div>
   </div>

@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(function() {
         $('.datatables-sessions').DataTable({
            order: [
               [0, 'desc']
            ]
         });

         $('.select2-setup').select2({
            dropdownParent: $('#addSessionModal')
         });

         $('#editVehicleModal .select2-setup').select2({
            dropdownParent: $('#editVehicleModal')
         });

         // Initialize tooltips for the layout
         const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"], [title]'))
         tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
         })
      });
   </script>
@endsection
