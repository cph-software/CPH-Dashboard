@extends('layouts.admin')

@section('title', 'Vehicle Monitoring - ' . $vehicle->fleet_name)

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
   <style>
      /* Stepper */
      .monitoring-stepper {
         display: flex;
         align-items: center;
         overflow-x: auto;
         padding: 10px 0;
         gap: 0;
      }

      .step-item {
         display: flex;
         align-items: center;
         white-space: nowrap;
      }

      .step-badge {
         display: inline-flex;
         align-items: center;
         gap: 6px;
         padding: 6px 14px;
         border-radius: 50px;
         font-size: 0.8rem;
         font-weight: 600;
         border: 2px solid #e0e0e0;
         background: #f8f9fa;
         color: #999;
         transition: all 0.3s;
      }

      .step-badge.completed {
         background: #e8f5e9;
         border-color: #28c76f;
         color: #28c76f;
      }

      .step-badge.active {
         background: #7367f0;
         border-color: #7367f0;
         color: #fff;
         box-shadow: 0 3px 12px rgba(115, 103, 240, 0.4);
         transform: scale(1.05);
      }

      .step-badge.active .step-icon {
         animation: pulse 1.5s infinite;
      }

      .step-connector {
         width: 25px;
         height: 2px;
         background: #e0e0e0;
         margin: 0 2px;
         flex-shrink: 0;
      }

      .step-connector.completed {
         background: #28c76f;
      }

      @keyframes pulse {

         0%,
         100% {
            opacity: 1;
         }

         50% {
            opacity: 0.5;
         }
      }

      /* Vehicle chassis compact */
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
   @php
      // Determine the active session and its current stage
      $activeSession = $sessions->where('status', 'active')->first();
      $hasInstallation = $activeSession && $activeSession->installations_count > 0;

      // checkCount = jumlah grup check berbeda (check number), bukan jumlah row
      $checkCount = 0;
      if ($activeSession) {
          // We calculate unique check_numbers for the active session
          $uniqueChecks = \App\Models\TyreMonitoringCheck::where('session_id', $activeSession->session_id)
              ->distinct('check_number')
              ->pluck('check_number');
          $checkCount = $uniqueChecks->count();
      }

      // Current stage logic
      if (!$activeSession) {
          $currentStage = 'none';
      } elseif (!$hasInstallation) {
          $currentStage = 'installation';
      } else {
          $currentStage = 'check_' . ($checkCount + 1);
      }
   @endphp
   {{-- Page Header --}}
   <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
      <div class="d-flex align-items-center">
         <a href="{{ route('monitoring.index') }}" class="btn btn-icon btn-outline-secondary me-3">
            <i class="ri ri-arrow-left-line"></i>
         </a>
         <div>
            <h4 class="fw-bold py-1 mb-0"><span class="text-muted fw-light">Operations / Monitoring /</span>
               {{ $vehicle->fleet_name }}</h4>
            <p class="text-muted mb-0">{{ $vehicle->vehicle_number }} · {{ $vehicle->driver_name }}</p>
         </div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
         <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
            data-bs-target="#editVehicleModal">
            <i class="ri ri-settings-3-line me-1"></i> Config
         </button>
      </div>
   </div>

   {{-- Monitoring Progress Stepper --}}
   @if ($activeSession)
      <div class="card mb-4">
         <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
               <h6 class="mb-0 fw-bold text-primary">
                  <i class="ri ri-route-line me-1"></i>
                  @if (!$hasInstallation)
                     Status: Awaiting Initial Installation
                  @elseif ($checkCount == 0)
                     Status: Periodic Monitoring Started
                  @else
                     Status: Active Monitoring · Check #{{ $checkCount }}
                  @endif
               </h6>
               <span class="badge bg-label-{{ $activeSession->status == 'active' ? 'success' : 'secondary' }}">
                  {{ ucfirst($activeSession->status) }}
               </span>
            </div>
            <div class="monitoring-stepper">
               {{-- Installation Step --}}
               <div class="step-item">
                  <span class="step-badge {{ $hasInstallation ? 'completed' : 'active' }}">
                     <i class="ri ri-install-line step-icon"></i>
                     Installation
                     @if ($hasInstallation)
                        <i class="ri ri-check-line"></i>
                     @endif
                  </span>
               </div>

               {{-- Check Steps: only show recorded checks --}}
               @for ($i = 1; $i <= $checkCount; $i++)
                  <div class="step-connector completed"></div>
                  <div class="step-item">
                     <span class="step-badge completed">
                        <i class="ri ri-search-eye-line step-icon"></i>
                        Check {{ $i }}
                        <i class="ri ri-check-line"></i>
                     </span>
                  </div>
               @endfor
            </div>
         </div>
      </div>

      {{-- Smart Action Card --}}
      @if ($activeSession->status == 'active')
         <div class="card mb-4 border-primary shadow-sm">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
               <div class="d-flex align-items-center gap-3">
                  @if ($currentStage == 'installation')
                     <div
                        class="avatar avatar-xl bg-label-warning rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 70px; height: 70px;">
                        <i class="ri ri-install-line ri-2x"></i>
                     </div>
                     <div>
                        <h5 class="mb-1 fw-bold text-dark">Record Installation</h5>
                        <p class="text-muted mb-0 small">Mulai monitoring dengan mencatat pemasangan ban.</p>
                     </div>
                  @elseif (str_starts_with($currentStage, 'check_'))
                     <div
                        class="avatar avatar-xl bg-label-info rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 70px; height: 70px;">
                        <i class="ri ri-search-eye-line ri-2x"></i>
                     </div>
                     <div>
                        <h5 class="mb-1 fw-bold text-dark">Periodic Check #{{ $checkCount + 1 }}</h5>
                        <p class="text-muted mb-0 small">Lakukan pemeriksaan rutin untuk memantau performa ban.</p>
                     </div>
                  @endif
               </div>
               <div class="d-flex gap-2 flex-wrap">
                  @if ($currentStage == 'installation')
                     <a href="{{ route('monitoring.sessions.create', $vehicle->vehicle_id) }}"
                        class="btn btn-primary shadow-sm">
                        <i class="ri ri-add-line me-1"></i> Start Installation
                     </a>
                  @elseif (str_starts_with($currentStage, 'check_'))
                     <a href="{{ route('monitoring.check.create', $activeSession->session_id) }}"
                        class="btn btn-primary btn-lg shadow-sm px-4">
                        <i class="ri ri-add-line me-1"></i> Add Check #{{ $checkCount + 1 }}
                     </a>
                  @endif
               </div>
            </div>
         </div>
      @endif
   @else
      {{-- No Active Session --}}
      <div class="card mb-4 border-dashed border-2">
         <div class="card-body text-center py-4">
            <i class="ri ri-add-circle-line ri-3x text-primary mb-2"></i>
            <h5 class="fw-bold">Belum Ada Sesi Aktif</h5>
            <p class="text-muted mb-3">Mulai sesi monitoring baru untuk mencatat pemasangan dan pemeriksaan ban kendaraan
               ini.</p>
            <a href="{{ route('monitoring.sessions.create', $vehicle->vehicle_id) }}" class="btn btn-primary">
               <i class="ri ri-add-line me-1"></i> Start New Session
            </a>
         </div>
      </div>
   @endif

   {{-- Vehicle Layout & Status --}}
   <div class="row mb-4">
      {{-- Visual Layout Section --}}
      <div class="col-xl-4 col-lg-5 col-md-12 mb-4 mb-xl-0">
         <div class="card h-100">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
               <h5 class="card-title mb-0"><i class="ri ri-truck-line me-1"></i> Vehicle Layout</h5>
               <span class="badge bg-label-primary">Live</span>
            </div>
            <div class="card-body p-4 text-center">
               @if ($configuration)
                  @include('tyre-performance.movement._vehicle_layout', [
                      'configuration' => $configuration,
                      'assignedTyres' => $assignedTyres,
                  ])
               @else
                  <div class="py-5">
                     <i class="ri ri-truck-line ri-4x text-light"></i>
                     <p class="text-muted mt-2">Konfigurasi posisi ban belum tersedia.</p>
                     <button class="btn btn-sm btn-label-primary" data-bs-toggle="modal"
                        data-bs-target="#editVehicleModal">Link Master Vehicle</button>
                  </div>
               @endif
            </div>
         </div>
      </div>

      {{-- Current Tyre Status Table --}}
      <div class="col-xl-8 col-lg-7 col-md-12">
         <div class="card h-100">
            <div class="card-header border-bottom">
               <h5 class="card-title mb-0"><i class="ri ri-list-check me-1"></i> Current Tyre Status</h5>
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
                                 <span class="fw-bold">{{ $t->current_tread_depth ?? '-' }} mm</span>
                              @else
                                 -
                              @endif
                           </td>
                           <td>
                              @if ($t)
                                 @php
                                    $orig =
                                        $t->initial_tread_depth && $t->initial_tread_depth > 0
                                            ? $t->initial_tread_depth
                                            : 1;
                                    $curr = $t->current_tread_depth ?? 0;
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
                           <td colspan="5" class="text-center py-4">Link Master Vehicle untuk melihat status ban</td>
                        </tr>
                     @endforelse
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>

   {{-- Sessions History --}}
   <div class="card mb-4">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
         <h5 class="card-title mb-0"><i class="ri ri-history-line me-1"></i> Monitoring History</h5>
      </div>
      <div class="card-datatable table-responsive">
         <table class="datatables-sessions table table-hover">
            <thead>
               <tr>
                  <th>Period Start</th>
                  <th>Tyre Size</th>
                  <th>{{ $measurementMode === 'HM' ? 'HM Start' : 'Odo Start' }}</th>
                  <th>Progress</th>
                  <th>Status</th>
                  <th>Actions</th>
               </tr>
            </thead>
            <tbody>
               @foreach ($sessions as $session)
                  <tr>
                     <td>{{ \Carbon\Carbon::parse($session->install_date)->format('d/m/Y') }}</td>
                     <td>
                        @if ($session->tyre_size && $session->tyre_size !== '-')
                           {{ $session->tyre_size }}
                        @else
                           @php
                              $instTyre = $session->installations->first();
                              $tyreSizeDisplay = '-';
                              if ($instTyre) {
                                 $tyreObj = \App\Models\Tyre::where('serial_number', $instTyre->serial_number)->with('size')->first();
                                 $tyreSizeDisplay = $tyreObj && $tyreObj->size ? $tyreObj->size->size : '-';
                              }
                           @endphp
                           {{ $tyreSizeDisplay }}
                        @endif
                     </td>
                     <td>{{ $measurementMode === 'HM' ? number_format($session->hm_start) . ' HM' : number_format($session->odometer_start) . ' KM' }}</td>
                     <td>
                        <div class="d-flex align-items-center gap-1">
                           <span class="badge bg-label-warning" title="Installations">
                              <i class="ri ri-install-line me-1"></i>{{ $session->installations_count ?? 0 }}
                           </span>
                           <span class="badge bg-label-info" title="Checks">
                              <i class="ri ri-search-eye-line me-1"></i>{{ $session->checks_count }}
                           </span>
                           @if ($session->removal_count > 0)
                              <span class="badge bg-label-danger" title="Removal">
                                 <i class="ri ri-delete-bin-line"></i>
                              </span>
                           @endif
                        </div>
                     </td>
                     <td>
                        <span class="badge bg-label-{{ $session->status == 'active' ? 'success' : 'secondary' }}">
                           {{ ucfirst($session->status) }}
                        </span>
                     </td>
                     <td>
                        <div class="d-flex gap-1">
                           <a href="{{ route('monitoring.sessions.show', $session->session_id) }}"
                              class="btn btn-sm btn-icon btn-outline-primary" title="View Detail">
                              <i class="ri ri-eye-line"></i>
                           </a>
                           <a href="{{ route('monitoring.sessions.export', $session->session_id) }}"
                              class="btn btn-sm btn-icon btn-outline-success" title="Export Excel">
                              <i class="icon-base ri ri-file-excel-2-line"></i>
                           </a>
                           <a href="{{ route('monitoring.sessions.export-pdf', $session->session_id) }}"
                              class="btn btn-sm btn-icon btn-outline-danger" title="Export PDF" target="_blank">
                              <i class="icon-base ri ri-file-pdf-line"></i>
                           </a>
                           @if ($session->status == 'active')
                              <form action="{{ route('monitoring.sessions.update', $session->session_id) }}"
                                 method="POST" class="d-inline">
                                 @csrf @method('PUT')
                                 <input type="hidden" name="status" value="completed">
                                 <button type="submit" class="btn btn-sm btn-icon btn-outline-secondary" title="Finish"
                                    onclick="return confirm('Selesaikan sesi monitoring ini?')">
                                    <i class="ri ri-checkbox-circle-line"></i>
                                 </button>
                              </form>
                           @endif
                        </div>
                     </td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>
   </div>

   {{-- ======= MODALS ======= --}}


   {{-- Edit Vehicle Config Modal --}}
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
            ],
            pageLength: 10
         });

         // Init Select2 for modals
         $('.modal').on('shown.bs.modal', function() {
            $(this).find('.select2-setup').each(function() {
               $(this).select2({
                  dropdownParent: $(this).closest('.modal'),
                  placeholder: 'Silakan Pilih',
                  allowClear: true
               });
            });
         });

         $('#editVehicleModal .select2-setup').select2({
            dropdownParent: $('#editVehicleModal')
         });
      });
   </script>
@endsection
