@extends('layouts.admin')

@section('title', 'Tyre Monitoring')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Operations /</span> Tyre Monitoring</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
            <i class="ri-add-line me-1"></i> Add Vehicle for Monitoring
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
                    @foreach($vehicles as $vehicle)
                    <tr>
                        <td>{{ $vehicle->fleet_name }}</td>
                        <td>{{ $vehicle->vehicle_number }}</td>
                        <td>{{ $vehicle->driver_name }}</td>
                        <td>{{ $vehicle->tire_positions }}</td>
                        <td>
                            @if($vehicle->sessions_count > 0)
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
                                <a href="{{ route('monitoring.vehicle.show', $vehicle->vehicle_id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="View Sessions">
                                    <i class="ri-eye-line"></i>
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

<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Monitoring Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('monitoring.vehicle.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Fleet Name</label>
                            <input type="text" name="fleet_name" class="form-control" required placeholder="example: FL-01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vehicle Number (Plate)</label>
                            <input type="text" name="vehicle_number" class="form-control" required placeholder="B 1234 ABC">
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
                            <input type="text" name="application" class="form-control" placeholder="example: Truk Semen Maros-Toraja">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Load Capacity</label>
                            <input type="text" name="load_capacity" class="form-control" placeholder="30 Ton">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tire Positions</label>
                            <input type="number" name="tire_positions" class="form-control" value="6" required min="1">
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
            order: [[0, 'desc']]
        });
    });
</script>
@endsection
