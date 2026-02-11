@extends('layouts.admin')

@section('title', 'Tyre Detail')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master / Tyres /</span> Detail</h4>
         <a href="{{ route('tyre-master.index') }}" class="btn btn-outline-secondary">
            <i class="icon-base ri ri-arrow-left-line me-1"></i> Back to List
         </a>
      </div>

      <div class="row">
         <!-- Main Info Card -->
         <div class="col-xl-4 col-lg-5 col-md-5">
            <div class="card mb-4">
               <div class="card-body">
                  <div class="text-center mb-4">
                     <div class="avatar avatar-xl mx-auto mb-3" style="width: 100px; height: 100px;">
                        <span class="avatar-initial rounded-circle bg-label-primary" style="font-size: 2.5rem;">
                           <i class="icon-base ri ri-steering-2-line"></i>
                        </span>
                     </div>
                     <h4 class="mb-1">{{ $tyre->serial_number }}</h4>
                     <span
                        class="badge bg-label-{{ $tyre->status === 'Installed' ? 'success' : ($tyre->status === 'New' ? 'info' : ($tyre->status === 'Scrap' ? 'danger' : 'warning')) }} rounded-pill">
                        {{ $tyre->status }}
                     </span>
                  </div>

                  <div class="info-container">
                     <h6 class="pb-2 border-bottom mb-3 text-uppercase small fw-bold text-muted">Basic Information</h6>
                     <ul class="list-unstyled mb-4">
                        <li class="mb-2">
                           <span class="fw-medium text-muted">Brand:</span>
                           <span class="float-end">{{ $tyre->brand->brand_name ?? '-' }}</span>
                        </li>
                        <li class="mb-2">
                           <span class="fw-medium text-muted">Size:</span>
                           <span class="float-end">{{ $tyre->size->size ?? '-' }}</span>
                        </li>
                        <li class="mb-2">
                           <span class="fw-medium text-muted">Pattern:</span>
                           <span class="float-end">{{ $tyre->pattern->name ?? '-' }}</span>
                        </li>
                        <li class="mb-2">
                           <span class="fw-medium text-muted">Type:</span>
                           <span class="float-end">{{ $tyre->tyre_type }}</span>
                        </li>
                        <li class="mb-2">
                           <span class="fw-medium text-muted">Segment:</span>
                           <span class="float-end">{{ $tyre->segment->segment_name ?? '-' }}</span>
                        </li>
                        <li class="mb-2">
                           <span class="fw-medium text-muted">Location:</span>
                           <span class="float-end">{{ $tyre->location->location_name ?? '-' }}</span>
                        </li>
                     </ul>

                     @if ($tyre->status === 'Installed')
                        <h6 class="pb-2 border-bottom mb-3 text-uppercase small fw-bold text-muted">Current Installation
                        </h6>
                        <ul class="list-unstyled mb-4">
                           <li class="mb-2">
                              <span class="fw-medium text-muted">Vehicle:</span>
                              <span class="float-end">{{ $tyre->currentVehicle->kode_kendaraan ?? '-' }}</span>
                           </li>
                           <li class="mb-2">
                              <span class="fw-medium text-muted">Position:</span>
                              <span class="float-end">{{ $tyre->currentPosition->position_code ?? '-' }}</span>
                           </li>
                        </ul>
                     @endif

                     <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('tyre-master.edit', $tyre->id) }}" class="btn btn-primary">
                           <i class="icon-base ri ri-edit-line me-1"></i> Edit Tyre
                        </a>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <!-- Performance Metrics -->
         <div class="col-xl-8 col-lg-7 col-md-7">
            <!-- Financial & Tread Info -->
            <div class="card mb-4">
               <div class="card-header">
                  <h5 class="card-title mb-0"><i class="icon-base ri ri-money-dollar-circle-line me-2"></i>Performance
                     Metrics</h5>
               </div>
               <div class="card-body">
                  <div class="row g-3">
                     <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                           <div class="d-flex align-items-center mb-2">
                              <div class="avatar avatar-sm me-2">
                                 <span class="avatar-initial rounded bg-label-success">
                                    <i class="icon-base ri ri-price-tag-3-line"></i>
                                 </span>
                              </div>
                              <h6 class="mb-0 text-muted small">Purchase Price</h6>
                           </div>
                           <h3 class="mb-0">{{ $tyre->price ? 'Rp ' . number_format($tyre->price, 0, ',', '.') : '-' }}
                           </h3>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                           <div class="d-flex align-items-center mb-2">
                              <div class="avatar avatar-sm me-2">
                                 <span class="avatar-initial rounded bg-label-info">
                                    <i class="icon-base ri ri-recycle-line"></i>
                                 </span>
                              </div>
                              <h6 class="mb-0 text-muted small">Retread Status</h6>
                           </div>
                           <h3 class="mb-0">{{ $tyre->retread_count == 0 ? 'New (R0)' : 'R' . $tyre->retread_count }}
                           </h3>
                        </div>
                     </div>
                  </div>

                  <div class="row g-3 mt-2">
                     <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light">
                           <small class="text-muted d-block mb-1">OTD (Original)</small>
                           <h4 class="mb-0 text-primary">
                              {{ $tyre->initial_tread_depth ? $tyre->initial_tread_depth . ' mm' : '-' }}</h4>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light">
                           <small class="text-muted d-block mb-1">RTD (Current)</small>
                           <h4 class="mb-0 text-warning">
                              {{ $tyre->current_tread_depth ? $tyre->current_tread_depth . ' mm' : '-' }}</h4>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light">
                           <small class="text-muted d-block mb-1">Wear</small>
                           <h4 class="mb-0 text-danger">
                              @if ($tyre->initial_tread_depth && $tyre->current_tread_depth)
                                 {{ number_format($tyre->initial_tread_depth - $tyre->current_tread_depth, 2) }} mm
                              @else
                                 -
                              @endif
                           </h4>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Lifetime Stats -->
            <div class="card mb-4">
               <div class="card-header">
                  <h5 class="card-title mb-0"><i class="icon-base ri ri-bar-chart-box-line me-2"></i>Lifetime Statistics
                  </h5>
               </div>
               <div class="card-body">
                  <div class="row g-3">
                     <div class="col-md-4">
                        <div class="d-flex align-items-center">
                           <div class="avatar avatar-md me-3">
                              <span class="avatar-initial rounded bg-label-primary">
                                 <i class="icon-base ri ri-roadster-line ri-lg"></i>
                              </span>
                           </div>
                           <div>
                              <small class="text-muted d-block">Total KM</small>
                              <h5 class="mb-0">{{ number_format($tyre->total_lifetime_km ?? 0, 0, ',', '.') }} km</h5>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="d-flex align-items-center">
                           <div class="avatar avatar-md me-3">
                              <span class="avatar-initial rounded bg-label-warning">
                                 <i class="icon-base ri ri-time-line ri-lg"></i>
                              </span>
                           </div>
                           <div>
                              <small class="text-muted d-block">Total HM</small>
                              <h5 class="mb-0">{{ number_format($tyre->total_lifetime_hm ?? 0, 0, ',', '.') }} hrs</h5>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="d-flex align-items-center">
                           <div class="avatar avatar-md me-3">
                              <span class="avatar-initial rounded bg-label-success">
                                 <i class="icon-base ri ri-calculator-line ri-lg"></i>
                              </span>
                           </div>
                           <div>
                              <small class="text-muted d-block">Cost/KM</small>
                              <h5 class="mb-0">
                                 @if ($tyre->price && $tyre->total_lifetime_km > 0)
                                    Rp {{ number_format($tyre->price / $tyre->total_lifetime_km, 0, ',', '.') }}
                                 @else
                                    -
                                 @endif
                              </h5>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Movement History -->
            <div class="card">
               <div class="card-header d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0"><i class="icon-base ri ri-history-line me-2"></i>Movement History</h5>
                  <span class="badge bg-label-secondary">{{ $tyre->movements->count() }} Records</span>
               </div>
               <div class="card-body">
                  @if ($tyre->movements->count() > 0)
                     <div class="table-responsive">
                        <table class="table table-sm table-hover">
                           <thead>
                              <tr>
                                 <th>Date</th>
                                 <th>Type</th>
                                 <th>Vehicle</th>
                                 <th>Position</th>
                                 <th>KM</th>
                                 <th>HM</th>
                                 <th>RTD</th>
                              </tr>
                           </thead>
                           <tbody>
                              @foreach ($tyre->movements->sortByDesc('movement_date') as $movement)
                                 <tr>
                                    <td>{{ \Carbon\Carbon::parse($movement->movement_date)->format('d M Y') }}</td>
                                    <td>
                                       <span
                                          class="badge bg-label-{{ $movement->movement_type === 'Installation' ? 'success' : 'danger' }} rounded-pill">
                                          {{ $movement->movement_type }}
                                       </span>
                                    </td>
                                    <td>{{ $movement->vehicle->kode_kendaraan ?? '-' }}</td>
                                    <td>{{ $movement->position->position_code ?? '-' }}</td>
                                    <td>
                                       {{ $movement->odometer_reading ? number_format($movement->odometer_reading, 0) : '-' }}
                                    </td>
                                    <td>
                                       {{ $movement->hour_meter_reading ? number_format($movement->hour_meter_reading, 0) : '-' }}
                                    </td>
                                    <td>{{ $movement->rtd_reading ? $movement->rtd_reading . ' mm' : '-' }}</td>
                                 </tr>
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                  @else
                     <div class="text-center text-muted py-4">
                        <i class="icon-base ri ri-file-list-line ri-3x mb-2 d-block opacity-25"></i>
                        <p class="mb-0">No movement history yet</p>
                     </div>
                  @endif
               </div>
            </div>
         </div>
      </div>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection
