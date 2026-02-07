@extends('layouts.admin')

@section('title', 'Tyre Performance Dashboard')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
@endsection

@section('content')
   <div class="row g-6 mb-6">
      <!-- Tyre Specific Stats -->
      <div class="col-sm-6 col-lg-3">
         <div class="card card-border-shadow-primary h-100">
            <div class="card-body">
               <div class="d-flex align-items-center mb-2">
                  <div class="avatar me-4">
                     <span class="avatar-initial rounded-3 bg-label-primary">
                        <i class="icon-base ri ri-focus-2-line icon-24px"></i>
                     </span>
                  </div>
                  <h4 class="mb-0">1,248</h4>
               </div>
               <h6 class="mb-0 fw-normal">Total Tyres Tracked</h6>
               <p class="mb-0">
                  <span class="me-1 fw-medium">+12%</span>
                  <small class="text-body-secondary">this month</small>
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
                        <i class="icon-base ri ri-history-line icon-24px"></i>
                     </span>
                  </div>
                  <h4 class="mb-0">85%</h4>
               </div>
               <h6 class="mb-0 fw-normal">Average Tyre Life</h6>
               <p class="mb-0">
                  <span class="me-1 fw-medium">+2.4%</span>
                  <small class="text-body-secondary">efficiency</small>
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
                        <i class="icon-base ri ri-error-warning-line icon-24px"></i>
                     </span>
                  </div>
                  <h4 class="mb-0">24</h4>
               </div>
               <h6 class="mb-0 fw-normal">Critical Maintenance</h6>
               <p class="mb-0">
                  <span class="me-1 fw-medium">Needs Attention</span>
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
                        <i class="icon-base ri ri-exchange-line icon-24px"></i>
                     </span>
                  </div>
                  <h4 class="mb-0">156</h4>
               </div>
               <h6 class="mb-0 fw-normal">Pending Inspections</h6>
               <p class="mb-0">
                  <span class="me-1 fw-medium">Scheduled</span>
               </p>
            </div>
         </div>
      </div>
   </div>

   <div class="row">
      <div class="col-12">
         <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
               <h5 class="mb-0">Tyre Performance Overview</h5>
               <small class="text-muted">Real-time data from all vehicles</small>
            </div>
            <div class="card-body">
               <div class="alert alert-primary d-flex align-items-center" role="alert">
                  <i class="ri-information-line me-2"></i>
                  <div>
                     Selamat datang di Dashboard <strong>Tyre Performance</strong>. Pantau kondisi dan performa ban unit
                     Anda di sini.
                  </div>
               </div>
               <p>Konten grafik dan tabel performa ban akan muncul di sini.</p>
            </div>
         </div>
      </div>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/apex-charts/apex-charts.js') }}"></script>
@endsection
