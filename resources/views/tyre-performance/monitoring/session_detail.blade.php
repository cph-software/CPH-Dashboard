@extends('layouts.admin')

@section('title', 'Monitoring Session Details')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('content')
@php
    use App\Services\TyreMonitoringCalculator;
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('monitoring.vehicle.show', $session->vehicle_id) }}" class="btn btn-icon btn-outline-secondary me-3">
                <i class="ri ri-arrow-left-line"></i>
            </a>
            <div>
                <h4 class="fw-bold py-1 mb-0"><span class="text-muted fw-light">Operations / Monitoring /</span> Session #{{ $session->session_id }}</h4>
                <p class="text-muted mb-0">{{ $session->vehicle->fleet_name }} - {{ $session->tyre_size }} ({{ $session->install_date }})</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('monitoring.sessions.export', $session->session_id) }}" class="btn btn-outline-success">
                <i class="ri ri-file-excel-2-line me-1"></i> Export Excel
            </a>
            @if($session->status == 'active')
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
                        <h4 class="mb-0 {{ $session->status == 'active' ? 'text-success' : 'text-secondary' }}">{{ ucfirst($session->status) }}</h4>
                    </div>
                    @if($session->status == 'active')
                        <form action="{{ route('monitoring.sessions.update', $session->session_id) }}" method="POST" class="d-inline ms-2">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Selesaikan sesi monitoring ini?')">Finish</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($lastCheck)
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

    <!-- Installation Records -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Installation Records</h5>
            @if($session->status == 'active')
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addInstallationModal">
                    <i class="ri ri-add-line me-1"></i> Add Installation
                </button>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Pos</th>
                        <th>Serial Number</th>
                        <th>Brand/Pattern</th>
                        <th>Size</th>
                        <th>Baseline Avg RTD</th>
                        <th>Odometer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($session->installations as $inst)
                    <tr>
                        <td>{{ $inst->position }}</td>
                        <td>{{ $inst->serial_number }}</td>
                        <td>{{ $inst->brand }} / {{ $inst->pattern }}</td>
                        <td>{{ $inst->size }}</td>
                        <td>{{ $inst->avg_rtd }} mm</td>
                        <td>{{ number_format($inst->odometer) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Check History -->
    @if($session->checks->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Monitoring Checks History</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr class="table-primary text-white">
                            <th>#</th>
                            <th>Date</th>
                            <th>Ser# (Pos)</th>
                            <th>Avg RTD</th>
                            <th>Mileage</th>
                            <th>Worn%</th>
                            <th>KM/mm</th>
                            <th>Proj. (KM)</th>
                            <th>Proj. (Mth)</th>
                            <th>Cond.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($session->checks->sortByDesc('check_number') as $check)
                            @php
                                $calc = TyreMonitoringCalculator::calculate($session->original_rtd, $session->install_date, $check);
                            @endphp
                            <tr>
                                <td>{{ $check->check_number }}</td>
                                <td>{{ $check->check_date }}</td>
                                <td>{{ $check->serial_number }} ({{ $check->position }})</td>
                                <td>{{ $calc['avg_rtd'] }} mm</td>
                                <td>{{ number_format($check->operation_mileage) }}</td>
                                <td>{{ $calc['worn_pct'] }}%</td>
                                <td>{{ $calc['km_per_mm'] }}</td>
                                <td>{{ number_format($calc['proj_life_km']) }}</td>
                                <td>{{ $calc['proj_life_month'] }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $check->condition == 'ok' ? 'success' : ($check->condition == 'warning' ? 'warning' : 'danger') }}">
                                        {{ strtoupper($check->condition) }}
                                    </span>
                                </td>
                            </tr>
                            @if($check->recommendation)
                            <tr class="table-warning">
                                <td colspan="10" class="small"><b>Rec:</b> {{ $check->recommendation }}</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Removal Record -->
    @if($session->removal)
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0 text-white">Removal Record</h5>
            </div>
            <div class="card-body pt-3">
                <div class="row">
                    <div class="col-md-3"><p class="mb-1"><b>Date:</b> {{ $session->removal->removal_date }}</p></div>
                    <div class="col-md-3"><p class="mb-1"><b>Total KM:</b> {{ number_format($session->removal->total_mileage) }}</p></div>
                    <div class="col-md-3"><p class="mb-1"><b>Final RTD:</b> {{ $session->removal->final_rtd }} mm</p></div>
                    <div class="col-md-3"><p class="mb-1"><b>Reason:</b> {{ $session->removal->removal_reason }}</p></div>
                </div>
                <p class="mt-2 mb-0"><b>Condition After:</b> {{ $session->removal->tyre_condition_after }}</p>
                <p class="mb-0"><b>Notes:</b> {{ $session->removal->notes }}</p>
            </div>
        </div>
    @elseif($session->status == 'active' && $session->installations->count() > 0)
        <div class="d-grid mb-4">
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#addRemovalModal">
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
                            <input type="text" name="serial_number" class="form-control" placeholder="Enter Serial Number" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Position</label>
                            <input type="number" name="position" class="form-control" required min="1" max="{{ $session->vehicle->tire_positions }}" placeholder="E.g. 1">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Odometer</label>
                            <input type="number" name="odometer" class="form-control" required value="{{ $session->odometer_start }}" placeholder="Odo at install">
                        </div>
                        <div class="col-4">
                            <label class="form-label">RTD 1</label>
                            <input type="number" name="rtd_1" class="form-control" step="0.1" required placeholder="mm">
                        </div>
                        <div class="col-4">
                            <label class="form-label">RTD 2</label>
                            <input type="number" name="rtd_2" class="form-control" step="0.1" required placeholder="mm">
                        </div>
                        <div class="col-4">
                            <label class="form-label">RTD 3</label>
                            <input type="number" name="rtd_3" class="form-control" step="0.1" required placeholder="mm">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Psi Rcmd</label>
                            <input type="number" name="inf_press_recommended" class="form-control" value="{{ $session->retase }}" placeholder="E.g. 110">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Psi Actual</label>
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

<!-- Modal Periodic Check -->
<div class="modal fade" id="addCheckModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Periodic Check</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('monitoring.check.store') }}" method="POST">
                @csrf
                <input type="hidden" name="session_id" value="{{ $session->session_id }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Tire In Test</label>
                            <select name="serial_number" class="form-select" required>
                                @foreach($session->installations as $inst)
                                    <option value="{{ $inst->serial_number }}">{{ $inst->serial_number }} (Pos {{ $inst->position }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Check Date</label>
                            <input type="date" name="check_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Current Odometer</label>
                            <input type="number" name="odometer" class="form-control" required placeholder="KM at check">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Condition</label>
                            <select name="condition" class="form-select">
                                <option value="ok">OK</option>
                                <option value="warning">Warning</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label">RTD 1</label>
                            <input type="number" name="rtd_1" class="form-control" step="0.1" required placeholder="mm">
                        </div>
                        <div class="col-4">
                            <label class="form-label">RTD 2</label>
                            <input type="number" name="rtd_2" class="form-control" step="0.1" required placeholder="mm">
                        </div>
                        <div class="col-4">
                            <label class="form-label">RTD 3</label>
                            <input type="number" name="rtd_3" class="form-control" step="0.1" required placeholder="mm">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Recommendation</label>
                            <textarea name="recommendation" class="form-control" rows="2" placeholder="E.g. Rotate, Repair, Continue, etc"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Check</button>
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
                            <select name="serial_number" class="form-select" required>
                                @foreach($session->installations as $inst)
                                    <option value="{{ $inst->serial_number }}">{{ $inst->serial_number }} (Pos {{ $inst->position }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Removal Date</label>
                            <input type="date" name="removal_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Odometer</label>
                            <input type="number" name="odometer" class="form-control" required placeholder="Final KM">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Final RTD (mm)</label>
                            <input type="number" name="final_rtd" class="form-control" step="0.1" required placeholder="E.g. 3.0">
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
                        <div class="col-12">
                            <label class="form-label">Tyre Condition After</label>
                            <input type="text" name="tyre_condition_after" class="form-control" placeholder="example: Buffable, Scrapped">
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="ri ri-error-warning-line me-1"></i> Removing the last tyre will not automatically close the session. Update session status manually if needed.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Record Removal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
