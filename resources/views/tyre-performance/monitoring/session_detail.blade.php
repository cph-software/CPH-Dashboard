@extends('layouts.admin')

@section('title', 'Session Detail #' . $session->session_id)

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <style>
      .timeline-item {
         position: relative;
         padding-left: 30px;
         padding-bottom: 24px;
         border-left: 2px solid #e0e0e0;
      }

      .timeline-item:last-child {
         border-left-color: transparent;
         padding-bottom: 0;
      }

      .timeline-item::before {
         content: '';
         position: absolute;
         left: -7px;
         top: 4px;
         width: 12px;
         height: 12px;
         border-radius: 50%;
         background: #7367f0;
         border: 2px solid #fff;
         box-shadow: 0 0 0 2px #7367f0;
      }

      .timeline-item.installation::before {
         background: #ff9f43;
         box-shadow: 0 0 0 2px #ff9f43;
      }

      .timeline-item.check::before {
         background: #00cfe8;
         box-shadow: 0 0 0 2px #00cfe8;
      }

      .timeline-item.removal::before {
         background: #ea5455;
         box-shadow: 0 0 0 2px #ea5455;
      }

      .v-chassis {
         position: relative;
         width: 100%;
         max-width: 300px;
         margin: 0 auto;
         background: #fafafa;
         border-radius: 16px;
         padding: 30px 15px;
         border: 2px solid #eee;
      }

      .v-cabin {
         width: 80px;
         height: 35px;
         background: #333;
         margin: 0 auto 20px;
         border-radius: 6px;
         text-align: center;
         line-height: 35px;
         font-size: 10px;
         color: #fff;
         font-weight: bold;
      }

      .v-axle {
         display: flex;
         justify-content: space-between;
         margin-bottom: 20px;
         position: relative;
      }

      .v-axle::after {
         content: '';
         position: absolute;
         top: 50%;
         left: 50%;
         transform: translate(-50%, -50%);
         width: 60%;
         height: 3px;
         background: #e0e0e0;
         z-index: 1;
      }

      .v-tyre {
         width: 28px;
         height: 48px;
         background: #fff;
         border: 2px solid #ddd;
         border-radius: 5px;
         z-index: 2;
         position: relative;
         display: flex;
         flex-direction: column;
         justify-content: center;
         align-items: center;
      }

      .v-tyre.filled {
         background: #2d2d2d !important;
         border-color: #1a1a1a;
      }

      .v-tyre-code {
         font-size: 8px;
         font-weight: 800;
         color: #666;
      }

      .v-tyre.filled .v-tyre-code {
         color: #fff;
      }

      .v-tyre.front {
         border-top: 3px solid #ff9f43;
      }

      .v-tyre.rear {
         border-top: 3px solid #28c76f;
      }

      .v-tyre.middle {
         border-top: 3px solid #7367f0;
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
         border-top: 2px dashed #eee;
      }

      .v-tyre.spare {
         width: 50px;
         height: 28px;
         border-top: none;
         border-right: 3px solid #00cfe8;
      }
   </style>
@endsection

@section('content')
   @php
      use App\Services\TyreMonitoringCalculator;
      $lastCheck = $session->checks->sortByDesc('check_number')->first();
      $runningKm = $lastCheck ? $lastCheck->operation_mileage : 0;
      $checkGroups = $session->checks->groupBy('check_number')->sortKeys();
   @endphp

   {{-- Page Header --}}
   <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
      <div class="d-flex align-items-center">
         <a href="{{ route('monitoring.vehicle.show', $session->vehicle_id) }}"
            class="btn btn-icon btn-outline-secondary me-3">
            <i class="ri ri-arrow-left-line"></i>
         </a>
         <div>
            <h4 class="fw-bold py-1 mb-0"><span class="text-muted fw-light">Monitoring /</span> Session
               #{{ $session->session_id }}</h4>
            <p class="text-muted mb-0">{{ $session->vehicle->fleet_name }} · {{ $session->tyre_size }}
               · {{ \Carbon\Carbon::parse($session->install_date)->format('d M Y') }}</p>
         </div>
      </div>
      <a href="{{ route('monitoring.sessions.export', $session->session_id) }}" class="btn btn-outline-success">
         <i class="ri ri-file-excel-2-line me-1"></i> Export Excel
      </a>
   </div>

   {{-- Session Overview Cards --}}
   <div class="row mb-4 g-3">
      <div class="col-6 col-md-3">
         <div class="card h-100 border-start border-primary border-3">
            <div class="card-body py-3">
               <p class="text-muted small mb-1">Baseline RTD</p>
               <h4 class="mb-0 text-primary fw-bold">{{ $session->original_rtd }} mm</h4>
            </div>
         </div>
      </div>
      <div class="col-6 col-md-3">
         <div class="card h-100">
            <div class="card-body py-3">
               <p class="text-muted small mb-1">Checks Done</p>
               <h4 class="mb-0 fw-bold">{{ $checkGroups->count() }} <small class="text-muted fw-normal">kali</small></h4>
            </div>
         </div>
      </div>
      <div class="col-6 col-md-3">
         <div class="card h-100">
            <div class="card-body py-3">
               <p class="text-muted small mb-1">Running Mileage</p>
               <h4 class="mb-0 fw-bold">{{ number_format($runningKm) }} <small class="text-muted fw-normal">KM</small></h4>
            </div>
         </div>
      </div>
      <div class="col-6 col-md-3">
         <div class="card h-100">
            <div class="card-body py-3">
               <p class="text-muted small mb-1">Status</p>
               <h4 class="mb-0">
                  <span class="badge bg-label-{{ $session->status == 'active' ? 'success' : 'secondary' }} fs-6">
                     {{ ucfirst($session->status) }}
                  </span>
               </h4>
            </div>
         </div>
      </div>
   </div>

   {{-- Wear Calculation Summary --}}
   @if ($lastCheck)
      @php $summary = TyreMonitoringCalculator::calculate($session->original_rtd, $session->install_date, $lastCheck); @endphp
      <div class="card mb-4 bg-primary text-white">
         <div class="card-body py-3">
            <div class="row text-center g-3">
               <div class="col-md-2 col-6 border-end border-white border-opacity-25">
                  <p class="mb-0 opacity-75 small">Current Avg RTD</p>
                  <h4 class="mb-0 text-white fw-bold">{{ $summary['avg_rtd'] }} mm</h4>
               </div>
               <div class="col-md-2 col-6 border-end border-white border-opacity-25">
                  <p class="mb-0 opacity-75 small">Worn %</p>
                  <h4 class="mb-0 text-white fw-bold">{{ $summary['worn_pct'] }}%</h4>
               </div>
               <div class="col-md-2 col-6 border-end border-white border-opacity-25">
                  <p class="mb-0 opacity-75 small">KM / mm</p>
                  <h4 class="mb-0 text-white fw-bold">{{ number_format($summary['km_per_mm']) }}</h4>
               </div>
               <div class="col-md-2 col-6 border-end border-white border-opacity-25">
                  <p class="mb-0 opacity-75 small">KM / Day</p>
                  <h4 class="mb-0 text-white fw-bold">{{ number_format($summary['km_per_day']) }}</h4>
               </div>
               <div class="col-md-2 col-6 border-end border-white border-opacity-25">
                  <p class="mb-0 opacity-75 small">Proj. Life (KM)</p>
                  <h4 class="mb-0 text-white fw-bold">{{ number_format($summary['proj_life_km']) }}</h4>
               </div>
               <div class="col-md-2 col-6">
                  <p class="mb-0 opacity-75 small">Proj. Remain</p>
                  <h4 class="mb-0 text-white fw-bold">{{ $summary['proj_life_month'] }} Mo</h4>
               </div>
            </div>
         </div>
      </div>
   @endif

   <div class="row mb-4">
      {{-- Vehicle Visual Layout --}}
      @if (count($masterPositions) > 0)
         <div class="col-lg-4 col-md-12 mb-4 mb-lg-0">
            <div class="card h-100">
               <div class="card-header border-bottom">
                  <h6 class="card-title mb-0"><i class="ri-truck-line me-1"></i> Vehicle Layout</h6>
               </div>
               <div class="card-body pt-3">
                  <div class="v-chassis">
                     <div class="v-cabin">FRONT</div>
                     @php
                        $frontAxles = $masterPositions->where('axle_type', 'Front')->groupBy('axle_number');
                        $middleAxles = $masterPositions->where('axle_type', 'Middle')->groupBy('axle_number');
                        $rearAxles = $masterPositions->where('axle_type', 'Rear')->groupBy('axle_number');
                        $spares = $masterPositions->where('is_spare', true);
                     @endphp

                     @foreach ($frontAxles as $positions)
                        <div class="v-axle">
                           @php
                              $left = $positions->where('side', 'Left')->first();
                              $right = $positions->where('side', 'Right')->first();
                           @endphp
                           @if ($left)
                              @php $t = $assignedTyres->get($left->id); @endphp
                              <div class="v-tyre front {{ $t ? 'filled' : '' }}" title="{{ $left->position_name }}">
                                 <span class="v-tyre-code">{{ $left->position_code }}</span>
                              </div>
                           @endif
                           @if ($right)
                              @php $t = $assignedTyres->get($right->id); @endphp
                              <div class="v-tyre front {{ $t ? 'filled' : '' }}" title="{{ $right->position_name }}">
                                 <span class="v-tyre-code">{{ $right->position_code }}</span>
                              </div>
                           @endif
                        </div>
                     @endforeach

                     @foreach ($middleAxles as $positions)
                        <div class="v-axle">
                           <div class="v-group">
                              @foreach ($positions->where('side', 'Left')->sortBy('display_order') as $p)
                                 @php $t = $assignedTyres->get($p->id); @endphp
                                 <div class="v-tyre middle {{ $t ? 'filled' : '' }}" title="{{ $p->position_name }}">
                                    <span class="v-tyre-code">{{ $p->position_code }}</span>
                                 </div>
                              @endforeach
                           </div>
                           <div class="v-group">
                              @foreach ($positions->where('side', 'Right')->sortBy('display_order') as $p)
                                 @php $t = $assignedTyres->get($p->id); @endphp
                                 <div class="v-tyre middle {{ $t ? 'filled' : '' }}" title="{{ $p->position_name }}">
                                    <span class="v-tyre-code">{{ $p->position_code }}</span>
                                 </div>
                              @endforeach
                           </div>
                        </div>
                     @endforeach

                     @foreach ($rearAxles as $positions)
                        <div class="v-axle">
                           <div class="v-group">
                              @foreach ($positions->where('side', 'Left')->sortBy('display_order') as $p)
                                 @php $t = $assignedTyres->get($p->id); @endphp
                                 <div class="v-tyre rear {{ $t ? 'filled' : '' }}" title="{{ $p->position_name }}">
                                    <span class="v-tyre-code">{{ $p->position_code }}</span>
                                 </div>
                              @endforeach
                           </div>
                           <div class="v-group">
                              @foreach ($positions->where('side', 'Right')->sortBy('display_order') as $p)
                                 @php $t = $assignedTyres->get($p->id); @endphp
                                 <div class="v-tyre rear {{ $t ? 'filled' : '' }}" title="{{ $p->position_name }}">
                                    <span class="v-tyre-code">{{ $p->position_code }}</span>
                                 </div>
                              @endforeach
                           </div>
                        </div>
                     @endforeach

                     @if ($spares->count() > 0)
                        <div class="v-spare-list">
                           @foreach ($spares as $s)
                              @php $t = $assignedTyres->get($s->id); @endphp
                              <div class="v-tyre spare {{ $t ? 'filled' : '' }}" title="{{ $s->position_name }}">
                                 <span class="v-tyre-code">{{ $s->position_code }}</span>
                              </div>
                           @endforeach
                        </div>
                     @endif
                  </div>
               </div>
            </div>
         </div>
      @endif

      {{-- Installed Tyres Table --}}
      <div class="{{ count($masterPositions) > 0 ? 'col-lg-8' : 'col-md-12' }}">
         <div class="card h-100">
            <div class="card-header border-bottom">
               <h6 class="card-title mb-0"><i class="ri-list-check me-1"></i> Installed Tyres</h6>
            </div>
            <div class="table-responsive">
               <table class="table table-hover mb-0">
                  <thead class="table-light">
                     <tr>
                        <th class="text-center" width="60">Pos</th>
                        <th>Tyre Details</th>
                        <th>Status</th>
                        <th>Current RTD</th>
                        <th>Last Inspection</th>
                     </tr>
                  </thead>
                  <tbody>
                     @forelse($masterPositions as $pos)
                        @php
                           $tyre = $assignedTyres->get($pos->id);
                           $inst = $session->installations->where('position_id', $pos->id)->first();
                        @endphp
                        <tr>
                           <td class="text-center fw-bold align-middle bg-light">
                              <span class="badge bg-label-primary rounded-circle"
                                 style="width: 32px; height: 32px; line-height: 22px;">{{ $pos->position_code }}</span>
                           </td>
                           <td class="align-middle">
                              @if ($tyre)
                                 <div class="d-flex flex-column">
                                    <span class="fw-bold text-primary">{{ $tyre->serial_number }}</span>
                                    <small class="text-muted">{{ $tyre->brand->brand_name ?? '-' }} /
                                       {{ $tyre->pattern->name ?? '-' }} / {{ $tyre->size->size ?? '-' }}</small>
                                 </div>
                              @else
                                 <span class="text-muted fst-italic">Posisi Kosong</span>
                              @endif
                           </td>
                           <td class="align-middle">
                              @if ($tyre)
                                 <span class="badge bg-label-success">Installed</span>
                              @else
                                 <span class="badge bg-label-secondary">Available</span>
                              @endif
                           </td>
                           <td class="align-middle">
                              @if ($tyre)
                                 <span class="fw-bold">{{ $tyre->current_tread_depth }} mm</span>
                              @else
                                 -
                              @endif
                           </td>
                           <td class="align-middle">
                              @if ($tyre)
                                 @php
                                    $latestCheck = $session->checks
                                        ->where('serial_number', $tyre->serial_number)
                                        ->sortByDesc('check_date')
                                        ->first();
                                 @endphp
                                 @if ($latestCheck)
                                    <div class="d-flex flex-column">
                                       <small
                                          class="fw-bold">{{ \Carbon\Carbon::parse($latestCheck->check_date)->format('d/m/Y') }}</small>
                                       <small class="text-info">{{ number_format($latestCheck->operation_mileage) }}
                                          KM</small>
                                    </div>
                                 @else
                                    <small class="text-muted">No check yet</small>
                                 @endif
                              @else
                                 -
                              @endif
                           </td>
                        </tr>
                     @empty
                        @foreach ($session->installations as $inst)
                           <tr>
                              <td class="text-center fw-bold align-middle">{{ $inst->position }}</td>
                              <td>{{ $inst->serial_number }} ({{ $inst->brand }} / {{ $inst->pattern }})</td>
                              <td><span class="badge bg-label-success">Installed</span></td>
                              <td>-</td>
                              <td>-</td>
                           </tr>
                        @endforeach
                     @endforelse
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>

   {{-- Event Timeline --}}
   <div class="card mb-4">
      <div class="card-header border-bottom">
         <h6 class="card-title mb-0"><i class="ri-timeline-view me-1"></i> Monitoring Timeline</h6>
      </div>
      <div class="card-body pt-4">
         <div class="timeline-wrapper">
            {{-- Installation Event --}}
            @if ($session->installations->count() > 0)
               <div class="timeline-item installation">
                  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start">
                     <div>
                        <h6 class="fw-bold mb-1">
                           <span class="badge bg-warning me-1">Installation</span>
                           {{ \Carbon\Carbon::parse($session->install_date)->format('d M Y') }}
                        </h6>
                        <p class="text-muted small mb-1">{{ $session->installations->count() }} ban terpasang · Odometer:
                           {{ number_format($session->odometer_start) }} KM</p>
                        <div class="d-flex flex-wrap gap-1">
                           @foreach ($session->installations as $inst)
                              <span class="badge bg-label-dark"
                                 title="{{ $inst->position }}">{{ $inst->serial_number }}</span>
                           @endforeach
                        </div>
                     </div>
                  </div>
               </div>
            @endif

            {{-- Check Events --}}
            @foreach ($checkGroups as $checkNumber => $group)
               @php
                  $first = $group->first();
                  $avgRtd = $group->avg(function ($c) {
                      return ($c->rtd_1 + $c->rtd_2 + $c->rtd_3 + ($c->rtd_4 ?? 0)) / ($c->rtd_4 ? 4 : 3);
                  });
                  $startDate = \Carbon\Carbon::parse($session->install_date);
                  $checkDate = \Carbon\Carbon::parse($first->check_date);
                  $days = $startDate->diffInDays($checkDate);
               @endphp
               <div class="timeline-item check">
                  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2">
                     <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">
                           <span class="badge bg-info me-1">Check #{{ $checkNumber }}</span>
                           {{ $checkDate->format('d M Y') }}
                           <small class="text-muted ms-1">({{ $days }} days)</small>
                        </h6>
                        <p class="text-muted small mb-2">{{ $group->count() }} posisi diperiksa · Op. Mileage:
                           {{ number_format($first->operation_mileage) }} KM · Avg RTD: {{ number_format($avgRtd, 2) }}
                           mm</p>
                        {{-- Detail Table --}}
                        <div class="table-responsive">
                           <table class="table table-sm table-bordered bg-white mb-0">
                              <thead class="table-light">
                                 <tr class="small">
                                    <th>Pos</th>
                                    <th>Serial</th>
                                    <th>PSI (R/A)</th>
                                    <th>RTD</th>
                                    <th>Avg</th>
                                    <th>Worn%</th>
                                    <th>KM/mm</th>
                                    <th>Proj.Life</th>
                                    <th>Condition</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 @foreach ($group as $c)
                                    @php $avgVal = ($c->rtd_1 + $c->rtd_2 + $c->rtd_3 + ($c->rtd_4 ?? 0)) / ($c->rtd_4 ? 4 : 3); @endphp
                                    <tr class="small">
                                       <td class="fw-bold">{{ $c->position }}</td>
                                       <td>{{ $c->serial_number }}</td>
                                       <td>{{ $c->inf_press_recommended ?? '-' }}/{{ $c->inf_press_actual ?? '-' }}</td>
                                       <td>
                                          {{ $c->rtd_1 }}/{{ $c->rtd_2 }}/{{ $c->rtd_3 }}{{ $c->rtd_4 ? '/' . $c->rtd_4 : '' }}
                                       </td>
                                       <td class="fw-bold">{{ number_format($avgVal, 1) }}</td>
                                       <td>{{ number_format($c->worn_percentage, 0) }}%</td>
                                       <td>{{ number_format($c->km_per_mm, 0) }}</td>
                                       <td class="fw-bold text-primary">{{ number_format($c->projected_life_km, 0) }}
                                       </td>
                                       <td>
                                          <span
                                             class="badge bg-{{ $c->condition == 'ok' ? 'success' : ($c->condition == 'warning' ? 'warning' : 'danger') }}">
                                             {{ strtoupper($c->condition) }}
                                          </span>
                                       </td>
                                    </tr>
                                 @endforeach
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            @endforeach

            {{-- Removal Event --}}
            @if ($session->removal)
               <div class="timeline-item removal">
                  <h6 class="fw-bold mb-1">
                     <span class="badge bg-danger me-1">Removal</span>
                     {{ \Carbon\Carbon::parse($session->removal->removal_date)->format('d M Y') }}
                  </h6>
                  <div class="row g-2 small">
                     <div class="col-md-3"><strong>Serial:</strong> {{ $session->removal->serial_number }}</div>
                     <div class="col-md-3"><strong>Total KM:</strong>
                        {{ number_format($session->removal->total_mileage) }}</div>
                     <div class="col-md-3"><strong>Final RTD:</strong> {{ $session->removal->final_rtd }} mm</div>
                     <div class="col-md-3"><strong>Reason:</strong> {{ $session->removal->removal_reason }}</div>
                  </div>
                  <p class="small mt-1 mb-0"><strong>Condition:</strong> {{ $session->removal->tyre_condition_after }} ·
                     <strong>Notes:</strong> {{ $session->removal->notes }}</p>
               </div>
            @endif

            {{-- No events --}}
            @if ($session->installations->count() == 0 && $session->checks->count() == 0 && !$session->removal)
               <div class="text-center py-4 text-muted">
                  <i class="ri-information-line ri-xl me-1"></i> Belum ada data monitoring pada sesi ini.
               </div>
            @endif
         </div>
      </div>
   </div>
@endsection
