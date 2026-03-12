@extends('layouts.admin')

@section('title', 'Vehicle Monitoring Sessions')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-0"><span class="text-muted fw-light">Operations / Monitoring /</span> {{ $vehicle->fleet_name }}</h4>
            <p class="text-muted mb-0">{{ $vehicle->vehicle_number }} - {{ $vehicle->driver_name }}</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSessionModal">
            <i class="ri-add-line me-1"></i> Start New Session
        </button>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Monitoring Sessions History</h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-sessions table table-hover">
                <thead>
                    <tr>
                        <th>Install Date</th>
                        <th>Tyre Size</th>
                        <th>Original RTD</th>
                        <th>Odo Start</th>
                        <th>Checks</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $session)
                    <tr>
                        <td>{{ $session->install_date }}</td>
                        <td>{{ $session->tyre_size }}</td>
                        <td>{{ $session->original_rtd }} mm</td>
                        <td>{{ number_format($session->odometer_start) }}</td>
                        <td><span class="badge bg-label-info">{{ $session->checks_count }}</span></td>
                        <td>
                            <span class="badge bg-label-{{ $session->status == 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($session->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('monitoring.sessions.show', $session->session_id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="View Detail">
                                    <i class="ri-eye-line"></i>
                                </a>
                                <a href="{{ route('monitoring.sessions.export', $session->session_id) }}" class="btn btn-sm btn-icon btn-outline-success" title="Export Excel">
                                    <i class="ri-file-excel-2-line"></i>
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

<!-- Add Session Modal -->
<div class="modal fade" id="addSessionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Start New Monitoring Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('monitoring.sessions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="vehicle_id" value="{{ $vehicle->vehicle_id }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Install Date</label>
                            <input type="date" name="install_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tyre Size</label>
                            <input type="text" name="tyre_size" class="form-control" required placeholder="example: 11 R22.5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Original RTD (mm)</label>
                            <input type="number" name="original_rtd" class="form-control" step="0.1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Odometer Start</label>
                            <input type="number" name="odometer_start" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pattern (Optional)</label>
                            <input type="text" name="pattern" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Retase/Psi (Rcmd)</label>
                            <input type="number" name="retase" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Start Session</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    $(function() {
        $('.datatables-sessions').DataTable({
            order: [[0, 'desc']]
        });
    });
</script>
@endsection
