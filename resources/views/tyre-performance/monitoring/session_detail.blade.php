@extends('layouts.admin')

@section('title', 'Monitoring Session Details')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
   <style>
      .v-chassis {
         position: relative;
         width: 100%;
         max-width: 350px;
         margin: 0 auto;
         background: #fafafa;
         border-radius: 20px;
         padding: 40px 20px;
         border: 2px solid #eee;
         box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.05);
      }

      .v-cabin {
         width: 100px;
         height: 45px;
         background: #333;
         margin: 0 auto 30px auto;
         border-radius: 8px 8px 4px 4px;
         border-bottom: 5px solid #111;
         text-align: center;
         line-height: 40px;
         font-size: 11px;
         color: #fff;
         font-weight: bold;
         letter-spacing: 2px;
      }

      .v-axle {
         display: flex;
         justify-content: space-between;
         margin-bottom: 30px;
         position: relative;
      }

      .v-axle::after {
         content: '';
         position: absolute;
         top: 50%;
         left: 50%;
         transform: translate(-50%, -50%);
         width: 65%;
         height: 4px;
         background: #e0e0e0;
         z-index: 1;
         border-radius: 2px;
      }

      .v-tyre {
         width: 35px;
         height: 60px;
         background: #fff;
         border: 2px solid #ddd;
         border-radius: 6px;
         z-index: 2;
         position: relative;
         display: flex;
         flex-direction: column;
         justify-content: center;
         align-items: center;
         cursor: pointer;
         transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      }

      .v-tyre-code {
         font-size: 9px;
         font-weight: 800;
         color: #666;
      }

      .v-tyre-sn-hint {
         position: absolute;
         top: -20px;
         font-size: 8px;
         color: #7367f0;
         white-space: nowrap;
         font-weight: bold;
         opacity: 0;
         transition: opacity 0.3s;
      }

      .v-tyre:hover .v-tyre-sn-hint {
         opacity: 1;
      }

      .v-tyre.filled {
         background: #2d2d2d !important;
         border-color: #1a1a1a;
      }

      .v-tyre.filled .v-tyre-code {
         color: #fff;
      }

      .v-tyre.front {
         border-top: 4px solid #ff9f43;
      }

      .v-tyre.rear {
         border-top: 4px solid #28c76f;
      }

      .v-tyre.middle {
         border-top: 4px solid #7367f0;
      }

      .v-tyre:hover {
         transform: scale(1.15) translateY(-2px);
         box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
         z-index: 10;
      }

      .v-group {
         display: flex;
         gap: 5px;
      }

      .v-spare-list {
         display: flex;
         justify-content: center;
         gap: 15px;
         margin-top: 20px;
         padding-top: 20px;
         border-top: 2px dashed #eee;
      }

      .v-tyre.spare {
         width: 60px;
         height: 35px;
         border-top: none;
         border-right: 4px solid #00cfe8;
      }

      .pos-empty-card {
         background-color: #f8f9fa;
         border: 2px dashed #dee2e6;
      }
   </style>
@endsection

@section('content')
   @php
      use App\Services\TyreMonitoringCalculator;
   @endphp
   <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="d-flex align-items-center">
         <a href="{{ route('monitoring.vehicle.show', $session->vehicle_id) }}"
            class="btn btn-icon btn-outline-secondary me-3">
            <i class="ri ri-arrow-left-line"></i>
         </a>
         <div>
            <h4 class="fw-bold py-1 mb-0"><span class="text-muted fw-light">Operations / Monitoring /</span> Session
               #{{ $session->session_id }}</h4>
            <p class="text-muted mb-0">{{ $session->vehicle->fleet_name }} - {{ $session->tyre_size }}
               ({{ $session->install_date }})</p>
         </div>
      </div>
      <div class="d-flex gap-2">
         <a href="{{ route('monitoring.sessions.export', $session->session_id) }}" class="btn btn-outline-success">
            <i class="ri ri-file-excel-2-line me-1"></i> Export Excel
         </a>
         @if ($session->status == 'active')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCheckModal">
               <i class="ri ri-add-line me-1"></i> Add Periodic Check
            </button>
         @endif
      </div>
   </div>

   <!-- Session Overview -->
   <div class="row mb-4">
      <div class="col-md-3">
         <div class="card h-100 border-start border-primary border-3">
            <div class="card-body">
               <h6 class="text-muted fw-normal mb-1">Baseline RTD</h6>
               <h4 class="mb-0 text-primary">{{ $session->original_rtd }} mm</h4>
            </div>
         </div>
      </div>
      <div class="col-md-3">
         <div class="card h-100">
            <div class="card-body">
               <h6 class="text-muted fw-normal mb-1">Checks Count</h6>
               <h4 class="mb-0">{{ $session->checks->count() }} Kali</h4>
            </div>
         </div>
      </div>
      <div class="col-md-3">
         <div class="card h-100">
            <div class="card-body">
               <h6 class="text-muted fw-normal mb-1">Running Mileage</h6>
               @php
                  $lastCheck = $session->checks->sortByDesc('check_number')->first();
                  $runningKm = $lastCheck ? $lastCheck->operation_mileage : 0;
               @endphp
               <h4 class="mb-0">{{ number_format($runningKm) }} KM</h4>
            </div>
         </div>
      </div>
      <div class="col-md-3">
         <div class="card h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
               <div>
                  <h6 class="text-muted fw-normal mb-1">Status</h6>
                  <h4 class="mb-0 {{ $session->status == 'active' ? 'text-success' : 'text-secondary' }}">
                     {{ ucfirst($session->status) }}</h4>
               </div>
               @if ($session->status == 'active')
                  <form action="{{ route('monitoring.sessions.update', $session->session_id) }}" method="POST"
                     class="d-inline ms-2">
                     @csrf
                     @method('PUT')
                     <input type="hidden" name="status" value="completed">
                     <button type="submit" class="btn btn-sm btn-outline-secondary"
                        onclick="return confirm('Selesaikan sesi monitoring ini?')">Finish</button>
                  </form>
               @endif
            </div>
         </div>
      </div>
   </div>

   @if ($lastCheck)
      @php
         $summary = TyreMonitoringCalculator::calculate($session->original_rtd, $session->install_date, $lastCheck);
      @endphp
      <!-- Wear Calculation Summary -->
      <div class="card mb-4 bg-primary text-white">
         <div class="card-body">
            <div class="row text-center g-3">
               <div class="col-md-2 border-end border-white border-opacity-25 col-6">
                  <p class="mb-0 opacity-75 small">Current Avg RTD</p>
                  <h3 class="mb-0 text-white fw-bold">{{ $summary['avg_rtd'] }} mm</h3>
               </div>
               <div class="col-md-2 border-end border-white border-opacity-25 col-6">
                  <p class="mb-0 opacity-75 small">Worn Percetage</p>
                  <h3 class="mb-0 text-white fw-bold">{{ $summary['worn_pct'] }}%</h3>
               </div>
               <div class="col-md-2 border-end border-white border-opacity-25 col-6">
                  <p class="mb-0 opacity-75 small">KM / mm</p>
                  <h3 class="mb-0 text-white fw-bold">{{ number_format($summary['km_per_mm']) }}</h3>
               </div>
               <div class="col-md-2 border-end border-white border-opacity-25 col-6">
                  <p class="mb-0 opacity-75 small">KM / Day</p>
                  <h3 class="mb-0 text-white fw-bold">{{ number_format($summary['km_per_day']) }}</h3>
               </div>
               <div class="col-md-2 border-end border-white border-opacity-25 col-6">
                  <p class="mb-0 opacity-75 small">Proj. Life (KM)</p>
                  <h3 class="mb-0 text-white fw-bold">{{ number_format($summary['proj_life_km']) }}</h3>
               </div>
               <div class="col-md-2 col-6">
                  <p class="mb-0 opacity-75 small">Proj. Remain (Months)</p>
                  <h3 class="mb-0 text-white fw-bold">{{ $summary['proj_life_month'] }} Mo</h3>
               </div>
            </div>
         </div>
      </div>
   @endif

   <!-- Vehicle Layout & Installation Records -->
   <div class="row mb-4">
      @if (count($masterPositions) > 0)
         <div class="col-lg-4 col-md-12 mb-4 mb-lg-0">
            <div class="card h-100">
               <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0"><i class="ri ri-truck-line me-1"></i> Visual Layout</h5>
                  <small class="text-muted">Master Config</small>
               </div>
               <div class="card-body pt-4">
                  <div class="v-chassis">
                     <div class="v-cabin">FRONT</div>

                     @php
                        $frontAxles = $masterPositions->where('axle_type', 'Front')->groupBy('axle_number');
                        $middleAxles = $masterPositions->where('axle_type', 'Middle')->groupBy('axle_number');
                        $rearAxles = $masterPositions->where('axle_type', 'Rear')->groupBy('axle_number');
                        $spares = $masterPositions->where('is_spare', true);
                     @endphp

                     {{-- Front Axles --}}
                     @foreach ($frontAxles as $positions)
                        <div class="v-axle">
                           @php
                              $left = $positions->where('side', 'Left')->first();
                              $right = $positions->where('side', 'Right')->first();
                              $leftTyre = $left ? $assignedTyres->get($left->id) : null;
                              $rightTyre = $right ? $assignedTyres->get($right->id) : null;
                           @endphp
                           @if ($left)
                              <div class="v-tyre front {{ $leftTyre ? 'filled' : '' }}"
                                 data-position-id="{{ $left->id }}"
                                 title="{{ $left->position_name }} {{ $leftTyre ? '[' . $leftTyre->serial_number . ']' : '(Kosong)' }}">
                                 <span class="v-tyre-code">{{ $left->position_code }}</span>
                                 @if ($leftTyre)
                                    <span class="v-tyre-sn-hint">{{ substr($leftTyre->serial_number, -4) }}</span>
                                 @endif
                              </div>
                           @endif
                           @if ($right)
                              <div class="v-tyre front {{ $rightTyre ? 'filled' : '' }}"
                                 data-position-id="{{ $right->id }}"
                                 title="{{ $right->position_name }} {{ $rightTyre ? '[' . $rightTyre->serial_number . ']' : '(Kosong)' }}">
                                 <span class="v-tyre-code">{{ $right->position_code }}</span>
                                 @if ($rightTyre)
                                    <span class="v-tyre-sn-hint">{{ substr($rightTyre->serial_number, -4) }}</span>
                                 @endif
                              </div>
                           @endif
                        </div>
                     @endforeach

                     {{-- Middle Axles --}}
                     @foreach ($middleAxles as $positions)
                        <div class="v-axle">
                           <div class="v-group">
                              @foreach ($positions->where('side', 'Left')->sortBy('display_order') as $p)
                                 @php $t = $assignedTyres->get($p->id); @endphp
                                 <div class="v-tyre middle {{ $t ? 'filled' : '' }}"
                                    data-position-id="{{ $p->id }}"
                                    title="{{ $p->position_name }} {{ $t ? '[' . $t->serial_number . ']' : '(Kosong)' }}">
                                    <span class="v-tyre-code">{{ $p->position_code }}</span>
                                    @if ($t)
                                       <span class="v-tyre-sn-hint">{{ substr($t->serial_number, -4) }}</span>
                                    @endif
                                 </div>
                              @endforeach
                           </div>
                           <div class="v-group">
                              @foreach ($positions->where('side', 'Right')->sortBy('display_order') as $p)
                                 @php $t = $assignedTyres->get($p->id); @endphp
                                 <div class="v-tyre middle {{ $t ? 'filled' : '' }}"
                                    data-position-id="{{ $p->id }}"
                                    title="{{ $p->position_name }} {{ $t ? '[' . $t->serial_number . ']' : '(Kosong)' }}">
                                    <span class="v-tyre-code">{{ $p->position_code }}</span>
                                    @if ($t)
                                       <span class="v-tyre-sn-hint">{{ substr($t->serial_number, -4) }}</span>
                                    @endif
                                 </div>
                              @endforeach
                           </div>
                        </div>
                     @endforeach

                     {{-- Rear Axles --}}
                     @foreach ($rearAxles as $positions)
                        <div class="v-axle">
                           <div class="v-group">
                              @foreach ($positions->where('side', 'Left')->sortBy('display_order') as $p)
                                 @php $t = $assignedTyres->get($p->id); @endphp
                                 <div class="v-tyre rear {{ $t ? 'filled' : '' }}"
                                    data-position-id="{{ $p->id }}"
                                    title="{{ $p->position_name }} {{ $t ? '[' . $t->serial_number . ']' : '(Kosong)' }}">
                                    <span class="v-tyre-code">{{ $p->position_code }}</span>
                                    @if ($t)
                                       <span class="v-tyre-sn-hint">{{ substr($t->serial_number, -4) }}</span>
                                    @endif
                                 </div>
                              @endforeach
                           </div>
                           <div class="v-group">
                              @foreach ($positions->where('side', 'Right')->sortBy('display_order') as $p)
                                 @php $t = $assignedTyres->get($p->id); @endphp
                                 <div class="v-tyre rear {{ $t ? 'filled' : '' }}"
                                    data-position-id="{{ $p->id }}"
                                    title="{{ $p->position_name }} {{ $t ? '[' . $t->serial_number . ']' : '(Kosong)' }}">
                                    <span class="v-tyre-code">{{ $p->position_code }}</span>
                                    @if ($t)
                                       <span class="v-tyre-sn-hint">{{ substr($t->serial_number, -4) }}</span>
                                    @endif
                                 </div>
                              @endforeach
                           </div>
                        </div>
                     @endforeach

                     {{-- Spares --}}
                     @if ($spares->count() > 0)
                        <div class="v-spare-list">
                           @foreach ($spares as $s)
                              @php $t = $assignedTyres->get($s->id); @endphp
                              <div class="v-tyre spare {{ $t ? 'filled' : '' }}" data-position-id="{{ $s->id }}"
                                 title="{{ $s->position_name }} {{ $t ? '[' . $t->serial_number . ']' : '(Kosong)' }}">
                                 <span class="v-tyre-code">{{ $s->position_code }}</span>
                                 @if ($t)
                                    <span class="v-tyre-sn-hint"
                                       style="bottom: -15px; top: auto; left: 0; width: 100%;">{{ substr($t->serial_number, -4) }}</span>
                                 @endif
                              </div>
                           @endforeach
                        </div>
                     @endif
                  </div>
               </div>
            </div>
         </div>
      @endif

      <div class="{{ count($masterPositions) > 0 ? 'col-lg-8' : 'col-md-12' }}">
         <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
               <h5 class="card-title mb-0"><i class="ri ri-list-check me-1"></i> Current Tyre Status (By Position)
               </h5>
               @if ($session->status == 'active')
                  <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                     data-bs-target="#addInstallationModal">
                     <i class="ri ri-add-line me-1"></i> Add Installation
                  </button>
               @endif
            </div>
            <div class="table-responsive">
               <table class="table table-hover mb-0">
                  <thead class="table-light">
                     <tr>
                        <th class="text-center" width="80">Pos</th>
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
                           // Get latest installation record for this pos from session
                           $inst = $session->installations->where('position_id', $pos->id)->first();
                        @endphp
                        <tr>
                           <td class="text-center fw-bold align-middle bg-light">
                              <span class="badge bg-label-primary rounded-circle"
                                 style="width: 35px; height: 35px; line-height: 25px;">{{ $pos->position_code }}</span>
                           </td>
                           <td class="align-middle">
                              @if ($tyre)
                                 <div class="d-flex flex-column">
                                    <span class="fw-bold text-primary">{{ $tyre->serial_number }}</span>
                                    <small class="text-muted">{{ $tyre->brand->brand_name ?? '-' }} /
                                       {{ $tyre->pattern->name ?? '-' }} / {{ $tyre->size->size ?? '-' }}</small>
                                 </div>
                              @else
                                 <span class="text-muted italic">Posisi Kosong</span>
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
                                       <small class="fw-bold">{{ $latestCheck->check_date }}</small>
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
                        {{-- Fallback jika master layout tidak ada, pakai data instalasi saja --}}
                        @foreach ($session->installations as $inst)
                           <tr>
                              <td class="text-center fw-bold align-middle">{{ $inst->position }}</td>
                              <td>{{ $inst->serial_number }} ({{ $inst->brand }} / {{ $inst->pattern }})</td>
                              <td><span class="badge bg-label-success">Installed</span></td>
                              <td>
                                 {{ $inst->position_id ? $assignedTyres->get($inst->position_id)->current_tread_depth ?? '-' : '-' }}
                              </td>
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

   <!-- Check History (Grouped) -->
   @if ($session->checks->count() > 0)
      <div class="card mb-4">
         <div class="card-header border-bottom">
            <h5 class="card-title mb-0"><i class="ri-history-line me-1"></i> Examination Events & Analysis</h5>
         </div>
         <div class="table-responsive">
            <table class="table table-hover align-middle">
               <thead class="table-light">
                  <tr class="text-nowrap">
                     <th>Event</th>
                     <th>Check Date</th>
                     <th>Odometer</th>
                     <th>Op. Mileage</th>
                     <th>Avg. RTD</th>
                     <th>Worn (%)</th>
                     <th>Km/mm</th>
                     <th>Proj. Life (3mm)</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($session->checks->groupBy('check_number')->sortByDesc(function ($item, $key) {
           return $key;
       }) as $checkNumber => $group)
                     @php
                        $first = $group->first();
                        $avgRtd = $group->avg(function ($c) {
                            return ($c->rtd_1 + $c->rtd_2 + $c->rtd_3 + $c->rtd_4) / 4;
                        });
                        $avgWorn = $group->avg('worn_percentage');
                        $avgKmPerMm = $group->avg('km_per_mm');
                        $avgProjLife = $group->avg('projected_life_km');

                        $startDate = \Carbon\Carbon::parse($session->install_date);
                        $checkDate = \Carbon\Carbon::parse($first->check_date);
                        $days = $startDate->diffInDays($checkDate);
                        $months = number_format($days / 30, 1);
                     @endphp
                     <tr>
                        <td><span class="badge bg-primary">CHECK #{{ $checkNumber }}</span></td>
                        <td>
                           <div class="d-flex flex-column">
                              <span>{{ $first->check_date }}</span>
                              <small class="text-muted">{{ $days }} Days / {{ $months }} Mo</small>
                           </div>
                        </td>
                        <td>{{ number_format($first->odometer) }} KM</td>
                        <td class="fw-bold">{{ number_format($first->operation_mileage) }} KM</td>
                        <td class="fw-bold">{{ number_format($avgRtd, 2) }} mm</td>
                        <td>
                           <div class="d-flex align-items-center">
                              <div class="progress w-100 me-2" style="height: 6px;">
                                 <div
                                    class="progress-bar bg-{{ $avgWorn > 80 ? 'danger' : ($avgWorn > 50 ? 'warning' : 'success') }}"
                                    style="width: {{ min(100, $avgWorn) }}%"></div>
                              </div>
                              <small>{{ round($avgWorn) }}%</small>
                           </div>
                        </td>
                        <td>{{ number_format($avgKmPerMm, 0) }}</td>
                        <td class="text-primary fw-bold">{{ number_format($avgProjLife, 0) }} KM</td>
                        <td>
                           <button class="btn btn-sm btn-icon btn-outline-primary" type="button"
                              data-bs-toggle="collapse" data-bs-target="#checkDetails{{ $checkNumber }}">
                              <i class="ri-arrow-down-s-line"></i>
                           </button>
                        </td>
                     </tr>
                     <tr class="collapse" id="checkDetails{{ $checkNumber }}">
                        <td colspan="9" class="p-0">
                           <div class="bg-light p-3">
                              <table class="table table-sm table-bordered bg-white mb-0">
                                 <thead class="table-dark">
                                    <tr class="small text-uppercase">
                                       <th>Pos</th>
                                       <th>Serial Number</th>
                                       <th>Psi (R/A)</th>
                                       <th>Assembly</th>
                                       <th>RTD(1-4)</th>
                                       <th>Avg</th>
                                       <th>Worn%</th>
                                       <th>Km/mm</th>
                                       <th>Proj. Life</th>
                                       <th>Condition</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                    @foreach ($group as $c)
                                       @php $avgVal = ($c->rtd_1 + $c->rtd_2 + $c->rtd_3 + $c->rtd_4) / 4; @endphp
                                       <tr class="small">
                                          <td class="fw-bold text-center">{{ $c->position }}</td>
                                          <td>{{ $c->serial_number }}</td>
                                          <td>{{ $c->inf_press_recommended ?? '-' }}/{{ $c->inf_press_actual ?? '-' }}
                                          </td>
                                          <td>{{ $c->date_assembly ?? '-' }}</td>
                                          <td>
                                             {{ $c->rtd_1 }}/{{ $c->rtd_2 }}/{{ $c->rtd_3 }}/{{ $c->rtd_4 }}
                                          </td>
                                          <td class="fw-bold">{{ number_format($avgVal, 1) }}</td>
                                          <td>{{ number_format($c->worn_percentage, 0) }}%</td>
                                          <td>{{ number_format($c->km_per_mm, 0) }}</td>
                                          <td class="fw-bold text-primary">
                                             {{ number_format($c->projected_life_km, 0) }}</td>
                                          <td>
                                             <span
                                                class="badge badge-dot bg-{{ $c->condition == 'ok' ? 'success' : ($c->condition == 'warning' ? 'warning' : 'danger') }}"></span>
                                             {{ strtoupper($c->condition) }}
                                          </td>
                                       </tr>
                                    @endforeach
                                 </tbody>
                              </table>
                           </div>
                        </td>
                     </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
      </div>
   @endif

   <!-- Removal Record -->
   @if ($session->removal)
      <div class="card mb-4 border-danger">
         <div class="card-header bg-danger text-white">
            <h5 class="card-title mb-0 text-white">Removal Record</h5>
         </div>
         <div class="card-body pt-3">
            <div class="row">
               <div class="col-md-3">
                  <p class="mb-1"><b>Date:</b> {{ $session->removal->removal_date }}</p>
               </div>
               <div class="col-md-3">
                  <p class="mb-1"><b>Total KM:</b> {{ number_format($session->removal->total_mileage) }}</p>
               </div>
               <div class="col-md-3">
                  <p class="mb-1"><b>Final RTD:</b> {{ $session->removal->final_rtd }} mm</p>
               </div>
               <div class="col-md-3">
                  <p class="mb-1"><b>Reason:</b> {{ $session->removal->removal_reason }}</p>
               </div>
            </div>
            <p class="mt-2 mb-0"><b>Condition After:</b> {{ $session->removal->tyre_condition_after }}</p>
            <p class="mb-0"><b>Notes:</b> {{ $session->removal->notes }}</p>
         </div>
      </div>
   @elseif($session->status == 'active' && $session->installations->count() > 0)
      <div class="d-grid mb-4">
         <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
            data-bs-target="#addRemovalModal">
            <i class="ri ri-close-circle-line me-1"></i> Close Session / Record Removal
         </button>
      </div>
   @endif
   </div>

   <!-- Modal Installation -->
   <div class="modal fade" id="addInstallationModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Record Installation</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('monitoring.installation.store') }}" method="POST">
               @csrf
               <input type="hidden" name="session_id" value="{{ $session->session_id }}">
               <div class="modal-body">
                  <div class="row g-3">
                     <div class="col-12">
                        <label class="form-label">Tyre Serial Number</label>
                        <select name="serial_number" id="install_serial" class="form-select select2-setup" required
                           data-tags="true" data-placeholder="Pilih atau Ketik SN Baru">
                           <option value=""></option>
                           @foreach ($availableTyres as $t)
                              <option value="{{ $t->serial_number }}">{{ $t->serial_number }}</option>
                           @endforeach
                        </select>

                        <div id="new_tyre_data" class="mt-3 p-3 bg-light border rounded" style="display:none;">
                           <h6 class="mb-2 small fw-bold text-primary"><i class="ri-information-line me-1"></i> New Tyre
                              Specifications</h6>
                           <div class="row g-2">
                              <div class="col-md-4">
                                 <label class="form-label small mb-1">Brand</label>
                                 <select name="tyre_brand_id" class="form-select form-select-sm select2-setup"
                                    data-placeholder="Brand">
                                    <option value=""></option>
                                    @foreach ($brands as $b)
                                       <option value="{{ $b->id }}">{{ $b->brand_name }}</option>
                                    @endforeach
                                 </select>
                              </div>
                              <div class="col-md-4">
                                 <label class="form-label small mb-1">Pattern</label>
                                 <select name="tyre_pattern_id" class="form-select form-select-sm select2-setup"
                                    data-placeholder="Pattern">
                                    <option value=""></option>
                                    @foreach ($patterns as $p)
                                       <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                 </select>
                              </div>
                              <div class="col-md-4">
                                 <label class="form-label small mb-1">Size</label>
                                 <select name="tyre_size_id" class="form-select form-select-sm select2-setup"
                                    data-placeholder="Size">
                                    <option value=""></option>
                                    @foreach ($sizes as $s)
                                       <option value="{{ $s->id }}">{{ $s->size }}</option>
                                    @endforeach
                                 </select>
                              </div>
                           </div>
                        </div>

                        <div id="tyre_info_box" class="mt-2 p-2 bg-label-info rounded small border"
                           style="display:none;">
                           <div class="row">
                              <div class="col-6">Brand: <span id="info_brand" class="fw-bold">-</span></div>
                              <div class="col-6">Size: <span id="info_size" class="fw-bold">-</span></div>
                              <div class="col-12 mt-1">Pattern: <span id="info_pattern" class="fw-bold">-</span></div>
                           </div>
                        </div>
                     </div>
                     <div class="col-12">
                        @if (count($masterPositions) > 0)
                           <label class="form-label">Position Code</label>
                           <select name="position_id" id="position_id_select" class="form-select" required>
                              <option value="">-- Select Position --</option>
                              @foreach ($masterPositions as $pos)
                                 @php $isFilled = $assignedTyres->has($pos->id); @endphp
                                 <option value="{{ $pos->id }}" {{ $isFilled ? 'disabled' : '' }}>
                                    {{ $pos->position_code }} - {{ $pos->position_name }}
                                    {{ $isFilled ? '(Sudah Terisi)' : '' }}
                                 </option>
                              @endforeach
                           </select>
                        @else
                           <label class="form-label">Position Number</label>
                           <input type="number" name="position" class="form-control" required min="1"
                              max="{{ $session->vehicle->tire_positions }}" placeholder="E.g. 1">
                        @endif
                     </div>
                     <div class="col-6">
                        <label class="form-label">Odometer</label>
                        <input type="number" name="odometer" class="form-control" required
                           value="{{ $lastCheck ? $lastCheck->operation_mileage + $session->odometer_start : $session->odometer_start }}"
                           placeholder="Odo at install">
                     </div>
                     <div class="col-6">
                        <label class="form-label">Rcmd. PSI</label>
                        <input type="number" name="inf_press_recommended" class="form-control"
                           value="{{ $session->retase }}" placeholder="E.g. 110">
                     </div>
                     <div class="col-4">
                        <label class="form-label">RTD 1</label>
                        <input type="number" name="rtd_1" class="form-control" step="0.1" required
                           placeholder="mm">
                     </div>
                     <div class="col-4">
                        <label class="form-label">RTD 2</label>
                        <input type="number" name="rtd_2" class="form-control" step="0.1" required
                           placeholder="mm">
                     </div>
                     <div class="col-4">
                        <label class="form-label">RTD 3</label>
                        <input type="number" name="rtd_3" class="form-control" step="0.1" required
                           placeholder="mm">
                     </div>
                     <div class="col-12">
                        <label class="form-label">Actual PSI</label>
                        <input type="number" name="inf_press_actual" class="form-control" placeholder="E.g. 105">
                     </div>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Save Installation</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <!-- Modal Periodic Check (Batch) -->
   <div class="modal fade" id="addCheckModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title shadow-sm">Record Vehicle Check (Snapshot)</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('monitoring.check.store') }}" method="POST">
               @csrf
               <input type="hidden" name="session_id" value="{{ $session->session_id }}">
               <div class="modal-body">
                  <div class="row g-3 mb-4 bg-light p-3 rounded border">
                     <div class="col-md-6">
                        <label class="form-label fw-bold">Check Date (Tgl Pemeriksaan)</label>
                        <input type="date" name="check_date" class="form-control" required
                           value="{{ date('Y-m-d') }}">
                     </div>
                     <div class="col-md-6">
                        <label class="form-label fw-bold">Current Odometer (KM)</label>
                        <input type="number" name="odometer" class="form-control" required placeholder="KM at check"
                           value="{{ $lastCheck ? $lastCheck->odometer : '' }}">
                     </div>
                  </div>

                  <div class="table-responsive">
                     <table class="table table-bordered table-sm align-middle">
                        <thead class="table-dark">
                           <tr class="text-center">
                              <th width="40">Pos</th>
                              <th>Tyre Info</th>
                              <th>Psi (Rec/Act)</th>
                              <th>Assembly Date</th>
                              <th>RTD 1</th>
                              <th>RTD 2</th>
                              <th>RTD 3</th>
                              <th>RTD 4</th>
                              <th width="100">Cond.</th>
                              <th>Notes</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach ($session->installations as $inst)
                              @php
                                 $currentTyre = $inst->tyre_id ? \App\Models\Tyre::find($inst->tyre_id) : null;
                                 $lastRtd = $currentTyre ? $currentTyre->current_tread_depth : $inst->avg_rtd;
                              @endphp
                              <tr>
                                 <td class="text-center fw-bold bg-light">
                                    {{ $inst->positionDetail ? $inst->positionDetail->position_code : $inst->position }}
                                 </td>
                                 <td>
                                    <div class="fw-bold">{{ $inst->serial_number }}</div>
                                    <small class="text-muted text-nowrap">Last: {{ number_format($lastRtd, 1) }}
                                       mm</small>
                                 </td>
                                 <td>
                                    <div class="input-group input-group-sm">
                                       <input type="number" name="checks[{{ $inst->serial_number }}][psi_recommended]"
                                          class="form-control" placeholder="Rec">
                                       <input type="number" name="checks[{{ $inst->serial_number }}][psi_actual]"
                                          class="form-control" placeholder="Act">
                                    </div>
                                 </td>
                                 <td>
                                    <input type="date" name="checks[{{ $inst->serial_number }}][date_assembly]"
                                       class="form-control form-control-sm">
                                 </td>
                                 <td>
                                    <input type="number" name="checks[{{ $inst->serial_number }}][rtd_1]"
                                       class="form-control form-control-sm" step="0.1"
                                       value="{{ $lastRtd }}">
                                 </td>
                                 <td>
                                    <input type="number" name="checks[{{ $inst->serial_number }}][rtd_2]"
                                       class="form-control form-control-sm" step="0.1"
                                       value="{{ $lastRtd }}">
                                 </td>
                                 <td>
                                    <input type="number" name="checks[{{ $inst->serial_number }}][rtd_3]"
                                       class="form-control form-control-sm" step="0.1"
                                       value="{{ $lastRtd }}">
                                 </td>
                                 <td>
                                    <input type="number" name="checks[{{ $inst->serial_number }}][rtd_4]"
                                       class="form-control form-control-sm" step="0.1"
                                       value="{{ $lastRtd }}">
                                 </td>
                                 <td>
                                    <select name="checks[{{ $inst->serial_number }}][condition]"
                                       class="form-select form-select-sm">
                                       <option value="ok" selected>OK</option>
                                       <option value="warning">Warning</option>
                                       <option value="critical">Critical</option>
                                    </select>
                                 </td>
                                 <td>
                                    <input type="text" name="checks[{{ $inst->serial_number }}][notes]"
                                       class="form-control form-control-sm" placeholder="...">
                                 </td>
                              </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>
               </div>
               <div class="modal-footer bg-light border-top">
                  <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary btn-lg">
                     <i class="ri-save-line me-1"></i> Save Examination Snapshot
                  </button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <!-- Modal Removal -->
   <div class="modal fade" id="addRemovalModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Record Removal / Close Session</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('monitoring.removal.store') }}" method="POST">
               @csrf
               <input type="hidden" name="session_id" value="{{ $session->session_id }}">
               <div class="modal-body">
                  <div class="row g-3">
                     <div class="col-12">
                        <label class="form-label">Tire to Remove</label>
                        <select name="serial_number" class="form-select select2-setup" required>
                           <option value="">-- Pilih Ban Dicabut --</option>
                           @foreach ($session->installations as $inst)
                              @php
                                 $currentTyre = $inst->tyre_id ? \App\Models\Tyre::find($inst->tyre_id) : null;
                                 $rtd1 = $currentTyre ? $currentTyre->current_tread_depth : $inst->rtd_1;
                              @endphp
                              <option value="{{ $inst->serial_number }}"
                                 data-pos="{{ $inst->positionDetail ? $inst->positionDetail->position_code : $inst->position }}"
                                 data-rtd1="{{ $rtd1 }}" data-rtd2="{{ $inst->rtd_2 }}"
                                 data-rtd3="{{ $inst->rtd_3 }}">
                                 {{ $inst->serial_number }} (Pos
                                 {{ $inst->positionDetail ? $inst->positionDetail->position_code : $inst->position }})
                              </option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-6">
                        <label class="form-label">Removal Date</label>
                        <input type="date" name="removal_date" class="form-control" required
                           value="{{ date('Y-m-d') }}">
                     </div>
                     <div class="col-6">
                        <label class="form-label">Final Odometer</label>
                        <input type="number" name="odometer" class="form-control" required placeholder="Final KM"
                           value="{{ $lastCheck ? $lastCheck->operation_mileage + $session->odometer_start : '' }}">
                     </div>
                     <div class="col-6">
                        <label class="form-label">Final RTD (mm)</label>
                        <input type="number" name="final_rtd" class="form-control" step="0.1" required
                           placeholder="E.g. 3.0">
                     </div>
                     <div class="col-6">
                        <label class="form-label">Removal Reason</label>
                        <select name="removal_reason" class="form-select">
                           <option value="Worn Out">Worn Out</option>
                           <option value="Damage">Damage</option>
                           <option value="Rotation">Rotation</option>
                           <option value="End of Test">End of Test</option>
                        </select>
                     </div>
                     <div class="col-6">
                        <label class="form-label">Target Status</label>
                        <select name="target_status" class="form-select" required>
                           <option value="Repaired">Repaired / Stock</option>
                           <option value="Scrap">Scrap</option>
                           <option value="New">Wait for Retread</option>
                        </select>
                     </div>
                     <div class="col-6">
                        <label class="form-label">Destination Location</label>
                        <select name="work_location_id" class="form-select select2-setup" required>
                           <option value="">-- Select Location --</option>
                           @foreach ($locations as $loc)
                              <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-12">
                        <label class="form-label">Notes / Detailed Condition</label>
                        <input type="text" name="tyre_condition_after" class="form-control"
                           placeholder="example: Buffable, Sidewall Cut, etc">
                     </div>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-danger">Record Removal</button>
               </div>
            </form>
         </div>
      </div>
@endsection
   @section('page-script')
      <script>
         $(function() {
            // --- 1. Initialize Select2 on Modals ---
            function initSelect2() {
               $('.select2-setup').each(function() {
                  $(this).select2({
                     dropdownParent: $(this).closest('.modal'),
                     tags: $(this).data('tags') || false,
                     placeholder: $(this).data('placeholder') || 'Silakan Pilih',
                     allowClear: true
                  });
               });
            }

            // Re-init on modal show
            $('.modal').on('shown.bs.modal', function() {
               initSelect2();
            });

            // --- 2. Installation Auto-fill ---
            $('#install_serial').on('change', function() {
               const serial = $(this).val();
               if (!serial) {
                  $('#tyre_info_box').fadeOut();
                  return;
               }

               $.get("{{ route('monitoring.tyre-by-serial') }}", {
                  serial_number: serial
               }, function(res) {
                  if (res.success) {
                     $('#tyre_info_box').fadeIn();
                     $('#new_tyre_data').hide();
                     $('#info_brand').text(res.data.brand);
                     $('#info_size').text(res.data.size);
                     $('#info_pattern').text(res.data.pattern);

                     // Fill RTD fields if empty
                     if (!$('input[name="rtd_1"]').val()) {
                        $('input[name="rtd_1"]').val(res.data.rtd);
                        $('input[name="rtd_2"]').val(res.data.rtd);
                        $('input[name="rtd_3"]').val(res.data.rtd);
                     }
                  } else {
                     $('#tyre_info_box').hide();
                     $('#new_tyre_data').fadeIn();
                     // Reset if it was filled
                     if ($('input[name="rtd_1"]').val() == res.data?.rtd) {
                        $('input[name="rtd_1"]').val('');
                        $('input[name="rtd_2"]').val('');
                        $('input[name="rtd_3"]').val('');
                     }
                  }
               });
            });

            // --- 3. Check/Removal Auto-fill RTD & Pos ---
            $(document).on('change', 'select[name="serial_number"]', function() {
               if ($(this).attr('id') === 'install_serial') return; // Skip for installation

               const selected = $(this).find(':selected');
               const modal = $(this).closest('.modal');

               if (selected.val() && selected.data('rtd1')) {
                  modal.find('input[name="rtd_1"]').val(selected.data('rtd1'));
                  modal.find('input[name="rtd_2"]').val(selected.data('rtd2'));
                  modal.find('input[name="rtd_3"]').val(selected.data('rtd3'));
                  modal.find('input[name="final_rtd"]').val(selected.data('rtd1')); // for removal
               }
            });

            // --- 4. Interactive Visual Layout ---
            $('.v-tyre').on('click', function() {
               const posId = $(this).data('position-id');
               if (posId) {
                  $('#position_id_select').val(posId).trigger('change');
                  // Trigger modal show
                  $('#addInstallationModal').modal('show');
               }
            });
         });
      </script>
   @endsection
