@extends('layouts.admin')

@section('title', 'Logistics Dashboard')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
<link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/css/pages/app-logistics-dashboard.css') }}" />
@endsection

@section('content')
<div class="row g-6 mb-6">
  <!-- Card Border Shadow -->
  <div class="col-sm-6 col-lg-3">
    <div class="card card-border-shadow-primary h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded-3 bg-label-primary">
              <i class="icon-base ri ri-car-line icon-24px"></i>
            </span>
          </div>
          <h4 class="mb-0">42</h4>
        </div>
        <h6 class="mb-0 fw-normal">On route vehicles</h6>
        <p class="mb-0">
          <span class="me-1 fw-medium">+18.2%</span>
          <small class="text-body-secondary">than last week</small>
        </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card card-border-shadow-warning h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded-3 bg-label-warning">
              <i class="icon-base ri ri-alert-line icon-24px"></i>
            </span>
          </div>
          <h4 class="mb-0">8</h4>
        </div>
        <h6 class="mb-0 fw-normal">Vehicles with errors</h6>
        <p class="mb-0">
          <span class="me-1 fw-medium">-8.7%</span>
          <small class="text-body-secondary">than last week</small>
        </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card card-border-shadow-danger h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded-3 bg-label-danger">
              <i class="icon-base ri ri-route-line icon-24px"></i>
            </span>
          </div>
          <h4 class="mb-0">27</h4>
        </div>
        <h6 class="mb-0 fw-normal">Deviated from route</h6>
        <p class="mb-0">
          <span class="me-1 fw-medium">+4.3%</span>
          <small class="text-body-secondary">than last week</small>
        </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card card-border-shadow-info h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded-3 bg-label-info">
              <i class="icon-base ri ri-time-line icon-24px"></i>
            </span>
          </div>
          <h4 class="mb-0">13</h4>
        </div>
        <h6 class="mb-0 fw-normal">Late vehicles</h6>
        <p class="mb-0">
          <span class="me-1 fw-medium">-2.5%</span>
          <small class="text-body-secondary">than last week</small>
        </p>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Vehicles overview -->
  <div class="col-xxl-6 order-5 order-xxl-0">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Vehicles overview</h5>
        </div>
      </div>
      <div class="card-body pb-2">
          <p>Selamat datang di CPH Dashboard Tyre. Sistem manajemen ban terpadu.</p>
          {{-- Add charts and other content here --}}
      </div>
    </div>
  </div>
</div>
@endsection

@section('vendor-script')
<script src="{{ asset('template/full-version/assets/vendor/libs/apex-charts/apex-charts.js') }}"></script>
<script src="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')
<script src="{{ asset('template/full-version/assets/js/app-logistics-dashboard.js') }}"></script>
@endsection