@extends('layouts.admin')

@section('title', 'Tyre Performance Dashboard')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <style>
      .kpi-card {
         transition: transform 0.2s ease, box-shadow 0.2s ease;
         cursor: pointer;
      }

      .kpi-card:hover {
         transform: translateY(-2px);
         box-shadow: 0 4px 18px rgba(0, 0, 0, .1);
      }

      .kpi-number {
         font-size: 1.75rem;
         font-weight: 700;
         line-height: 1.2;
      }

      .kpi-sub {
         font-size: 0.75rem;
         color: #a1acb8;
      }

      .chart-card {
         min-height: 380px;
      }

      .alert-tyre-row td {
         vertical-align: middle;
      }

      .rtd-bar {
         height: 6px;
         border-radius: 3px;
         background: #e9ecef;
         overflow: hidden;
      }

      .rtd-bar-inner {
         height: 100%;
         border-radius: 3px;
         transition: width .4s ease;
      }

      .section-label {
         font-size: 0.7rem;
         font-weight: 600;
         text-transform: uppercase;
         letter-spacing: .5px;
         color: #a1acb8;
         margin-bottom: 1rem;
      }

      .fleet-gauge-number {
         font-size: 2.5rem;
         font-weight: 800;
         line-height: 1;
      }

      .chart-filter-bar {
         padding: 0.5rem 0;
         border-bottom: 1px solid #f1f1f1;
         margin-bottom: 0.5rem;
      }

      .chart-filter-bar .form-select {
         font-size: 0.8rem;
         padding: 0.25rem 2rem 0.25rem 0.5rem;
         border-radius: 0.375rem;
      }

      .chart-filter-bar {
         transition: all 0.2s ease;
         background-color: #f1f0f2 !important;
         /* Elegant light grey/blue */
         border: 1px solid #dbdade !important;
      }

      .chart-filter-bar:hover {
         border-color: #7367f0 !important;
      }

      /* Loading spinner for AJAX charts */
      .chart-loading {
         display: flex;
         align-items: center;
         justify-content: center;
         min-height: 280px;
         flex-direction: column;
         gap: 0.75rem;
      }
      .chart-loading .spinner-border {
         width: 2rem;
         height: 2rem;
         border-width: 0.2rem;
      }
      .chart-loading-text {
         font-size: 0.75rem;
         color: #a1acb8;
         font-weight: 500;
      }

      /* Select2 Custom Styling - Solid, not transparent */
      .select2-container--default .select2-selection--single {
         border: 1px solid #dbdade !important;
         background-color: #ffffff !important;
         height: 38px !important;
         display: flex;
         align-items: center;
         border-radius: 0.375rem !important;
         transition: all 0.2s ease;
      }

      .select2-container--default.select2-container--focus .select2-selection--single {
         border-color: #7367f0 !important;
         box-shadow: 0 0.125rem 0.25rem rgba(115, 103, 240, 0.1);
      }

      .select2-container--default .select2-selection--single .select2-selection__rendered {
         line-height: normal !important;
         padding-left: 12px !important;
         font-size: 0.85rem;
         font-weight: 600;
         color: #5d596c !important;
      }

      .select2-container--default .select2-selection--single .select2-selection__arrow {
         height: 36px !important;
         right: 8px !important;
      }

      .select2-dropdown {
         border: 1px solid #dbdade !important;
         box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
         border-radius: 0.375rem !important;
      }

      .select2-container--default .select2-search--dropdown .select2-search__field {
         border: 1px solid #dbdade !important;
         border-radius: 0.375rem !important;
         padding: 0.4375rem 0.875rem !important;
      }
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">

      {{-- Page Header --}}
      <div class="row align-items-center mb-4 g-3">
         <div class="col-md-6 col-lg-5">
            <h4 class="fw-bold mb-1"><i class="icon-base ri ri-dashboard-3-line me-2 text-primary"></i>Tyre Performance
               Dashboard</h4>
            <p class="text-muted mb-0 small">Overview real-time performa ban di seluruh unit kendaraan</p>
         </div>
         <div class="col-md-6 col-lg-7 text-md-end">
            <form action="{{ route('master_data.dashboard') }}" method="GET"
               class="d-inline-flex align-items-center gap-2">
               <div class="d-flex align-items-center bg-white rounded shadow-sm border p-1"
                  style="border-color: #dbdade !important;">
                  <div class="px-2" style="border-right: 1px solid #eee;">
                     <label class="d-block small text-muted text-start fw-bold"
                        style="font-size: 0.6rem; text-transform: uppercase;">Mulai</label>
                     <input type="date" name="start_date"
                        class="form-control form-control-sm border-0 p-0 shadow-none fw-bold"
                        style="width: 115px; font-size: 0.85rem; color: #5d596c;"
                        value="{{ $startDate->format('Y-m-d') }}">
                  </div>
                  <div class="px-2">
                     <label class="d-block small text-muted text-start fw-bold"
                        style="font-size: 0.6rem; text-transform: uppercase;">Sampai</label>
                     <input type="date" name="end_date"
                        class="form-control form-control-sm border-0 p-0 shadow-none fw-bold"
                        style="width: 115px; font-size: 0.85rem; color: #5d596c;" value="{{ $endDate->format('Y-m-d') }}">
                  </div>
                  <button type="submit" class="btn btn-primary btn-sm px-3 ms-1 py-2" style="border-radius: 6px;"
                     title="Terapkan Filter">
                     <i class="icon-base ri ri-filter-3-line me-1"></i> Filter
                  </button>
                  @if (request()->filled('start_date') || request()->filled('end_date'))
                     <a href="{{ route('master_data.dashboard') }}" class="btn btn-label-secondary btn-sm px-3 ms-1 py-2"
                        style="border-radius: 6px;" title="Reset Filter">
                        <i class="icon-base ri ri-refresh-line me-1"></i> Reset
                     </a>
                  @endif
               </div>
               <div class="d-flex gap-2 ms-2">
                  @if (hasPermission('Import Approval', 'create'))
                     <button type="button" class="btn btn-primary btn-sm py-2 px-3 shadow-sm" style="border-radius: 6px;"
                        data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="icon-base ri ri-upload-2-line me-1"></i> Import Data
                     </button>
                  @endif
                  @if (hasPermission('Dashboard', 'export') || hasPermission('Import Approval', 'view'))
                  <div class="btn-group">
                     <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle py-2 px-3 shadow-none"
                        style="border-radius: 6px;" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon-base {{ hasPermission('Dashboard', 'export') ? 'ri-download-2-line' : 'ri-menu-line' }} me-1"></i> 
                        {{ hasPermission('Dashboard', 'export') ? 'Export' : 'More' }}
                     </button>
                     <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        @if (hasPermission('Dashboard', 'export'))
                        <li>
                           <h6 class="dropdown-header small text-muted text-uppercase">Raw Data Export</h6>
                        </li>
                        <li><a class="dropdown-item"
                              href="{{ route('master_data.export', ['type' => 'movements', 'format' => 'excel', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"><i
                                 class="ri-history-line me-1"></i> Movements Raw Data</a></li>
                        <li><a class="dropdown-item"
                              href="{{ route('master_data.export', ['type' => 'failures', 'format' => 'excel', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"><i
                                 class="ri-error-warning-line me-1"></i> Failure Analysis Data</a></li>
                        <li><a class="dropdown-item"
                              href="{{ route('master_data.export', ['type' => 'assets', 'format' => 'excel']) }}"><i
                                 class="ri-disc-line me-1"></i> Tyre Master List</a></li>
                        @endif

                        @if (hasPermission('Dashboard', 'export') && hasPermission('Import Approval', 'view'))
                        <li>
                           <hr class="dropdown-divider">
                        </li>
                        @endif

                        @if (hasPermission('Import Approval', 'view'))
                        <li><a class="dropdown-item text-success fw-bold" href="{{ route('import-approval.index') }}">
                              <i class="icon-base ri ri-check-double-line me-1"></i>
                              Log Approval Import</a></li>
                        @endif
                     </ul>
                  </div>
                  @endif
               </div>
            </form>
         </div>
      </div>

      {{-- KPI CARDS --}}
      <div class="row g-4 mb-4">
         {{-- Total Tyres --}}
         <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card kpi-card card-border-shadow-primary h-100" data-kpi-type="total_tyres">
               <div class="card-body py-3">
                  <div class="d-flex align-items-center mb-2">
                     <div class="avatar avatar-sm me-2">
                        <span class="avatar-initial rounded-3 bg-label-primary"><i
                              class="icon-base ri ri-stack-line"></i></span>
                     </div>
                     <span class="kpi-sub">Total Ban</span>
                  </div>
                  <div class="kpi-number text-primary">{{ number_format($totalTyres) }}</div>
                  <div class="kpi-sub mt-1">
                     <i class="icon-base ri ri-car-line"></i> {{ $totalVehicles }} kendaraan
                  </div>
               </div>
            </div>
         </div>

         {{-- Installed --}}
         <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card kpi-card card-border-shadow-success h-100" data-kpi-type="installed">
               <div class="card-body py-3">
                  <div class="d-flex align-items-center mb-2">
                     <div class="avatar avatar-sm me-2">
                        <span class="avatar-initial rounded-3 bg-label-success"><i
                              class="icon-base ri ri-checkbox-circle-line"></i></span>
                     </div>
                     <span class="kpi-sub">Terpasang</span>
                  </div>
                  <div class="kpi-number text-success">{{ number_format($installedTyres) }}</div>
                  <div class="kpi-sub mt-1">
                     {{ $totalTyres > 0 ? round(($installedTyres / $totalTyres) * 100, 0) : 0 }}% dari total
                  </div>
               </div>
            </div>
         </div>

         {{-- In Stock --}}
         <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card kpi-card card-border-shadow-info h-100" data-kpi-type="stock">
               <div class="card-body py-3">
                  <div class="d-flex align-items-center mb-2">
                     <div class="avatar avatar-sm me-2">
                        <span class="avatar-initial rounded-3 bg-label-info"><i
                              class="icon-base ri ri-archive-line"></i></span>
                     </div>
                     <span class="kpi-sub">Stok Tersedia</span>
                  </div>
                  <div class="kpi-number text-info">{{ number_format($inStockTyres) }}</div>
                  <div class="kpi-sub mt-1">New + Repaired</div>
               </div>
            </div>
         </div>

         {{-- Avg Lifetime --}}
         <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card kpi-card card-border-shadow-warning h-100" data-kpi-type="avg_lifetime">
               <div class="card-body py-3">
                  <div class="d-flex align-items-center mb-2">
                     <div class="avatar avatar-sm me-2">
                        <span class="avatar-initial rounded-3 bg-label-warning"><i
                              class="icon-base ri ri-road-map-line"></i></span>
                     </div>
                     <span class="kpi-sub">Avg Lifetime</span>
                  </div>
                  @if($measurementMode == 'KM')
                     <div class="kpi-number text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Rata-rata jarak tempuh (KM) yang dicapai ban sebelum dilepas/discrap">{{ number_format($avgLifetimeKm, 0, ',', '.') }}</div>
                     <div class="kpi-sub mt-1">KM rata-rata</div>
                  @elseif($measurementMode == 'HM')
                     <div class="kpi-number text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Rata-rata jam operasional (HM) yang dicapai ban sebelum dilepas/discrap">{{ number_format($avgLifetimeHm, 0, ',', '.') }}</div>
                     <div class="kpi-sub mt-1">HM rata-rata</div>
                  @else
                     <div class="d-flex flex-column mt-2">
                        <div class="d-flex justify-content-between align-items-end mb-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Rata-rata jarak tempuh (KM) yang dicapai ban">
                           <div class="kpi-sub" style="font-size: 0.65rem;">KM Rata-rata</div>
                           <div class="kpi-number text-warning fw-bold" style="font-size: 1.15rem; line-height: 1;">{{ number_format($avgLifetimeKm, 0, ',', '.') }}</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Rata-rata jam operasional (HM) yang dicapai ban">
                           <div class="kpi-sub" style="font-size: 0.65rem;">HM Rata-rata</div>
                           <div class="kpi-number text-warning fw-bold" style="font-size: 1.15rem; line-height: 1;">{{ number_format($avgLifetimeHm, 0, ',', '.') }}</div>
                        </div>
                     </div>
                  @endif
               </div>
            </div>
         </div>

         {{-- Avg Cost --}}
         <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card kpi-card card-border-shadow-secondary h-100" data-kpi-type="cost_per">
               <div class="card-body py-3">
                  <div class="d-flex align-items-center mb-2">
                     <div class="avatar avatar-sm me-2">
                        <span class="avatar-initial rounded-3 bg-label-secondary"><i
                              class="icon-base ri ri-money-dollar-circle-line"></i></span>
                     </div>
                     <span class="kpi-sub">
                        @if($measurementMode == 'KM') Cost / KM @elseif($measurementMode == 'HM') Cost / HM @else Cost / KM & HM @endif
                     </span>
                  </div>
                  @if($measurementMode == 'KM')
                     <div class="kpi-number" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Total Biaya seluruh ban dibagi Total KM tempuh seluruh ban">Rp {{ number_format($avgCpk, 0, ',', '.') }}</div>
                     <div class="kpi-sub mt-1">Rata-rata CPK</div>
                  @elseif($measurementMode == 'HM')
                     <div class="kpi-number" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Total Biaya seluruh ban dibagi Total HM operasional seluruh ban">Rp {{ number_format($avgCph, 0, ',', '.') }}</div>
                     <div class="kpi-sub mt-1">Rata-rata CPH</div>
                  @else
                     <div class="d-flex flex-column mt-2">
                        <div class="d-flex justify-content-between align-items-end mb-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Biaya Rata-rata per KM (Total Harga Ban / Total KM)">
                           <div class="kpi-sub text-secondary fw-bold" style="font-size: 0.65rem;">Cost/KM</div>
                           <div class="kpi-number fw-bold text-dark" style="font-size: 1.05rem; line-height: 1;">Rp {{ number_format($avgCpk, 0, ',', '.') }}</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Biaya Rata-rata per HM (Total Harga Ban / Total HM)">
                           <div class="kpi-sub text-secondary fw-bold" style="font-size: 0.65rem;">Cost/HM</div>
                           <div class="kpi-number fw-bold text-dark" style="font-size: 1.05rem; line-height: 1;">Rp {{ number_format($avgCph, 0, ',', '.') }}</div>
                        </div>
                     </div>
                  @endif
               </div>
            </div>
         </div>

         {{-- Scrap Rate --}}
         <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card kpi-card card-border-shadow-danger h-100" data-kpi-type="scrap_rate">
               <div class="card-body py-3">
                  <div class="d-flex align-items-center mb-2">
                     <div class="avatar avatar-sm me-2">
                        <span class="avatar-initial rounded-3 bg-label-danger"><i
                              class="icon-base ri ri-delete-bin-line"></i></span>
                     </div>
                     <span class="kpi-sub">Scrap Rate</span>
                  </div>
                  <div class="kpi-number text-danger">{{ $scrapRate }}%</div>
                  <div class="kpi-sub mt-1">{{ $scrappedTyres }} ban discrap</div>
               </div>
            </div>
         </div>
      </div>

      {{-- DATA QUALITY SCORE --}}
      <div class="row g-4 mb-4">
         <div class="col-12">
            <div class="card">
               <div class="card-body py-3">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                     <div class="d-flex align-items-center gap-2">
                        <i class="ri-shield-check-line text-{{ $dataQuality['score'] >= 60 ? 'success' : ($dataQuality['score'] >= 30 ? 'warning' : 'danger') }} ri-24px"></i>
                        <div>
                           <h6 class="mb-0 fw-bold">Data Quality Score</h6>
                           <small class="text-muted">Kelengkapan data mempengaruhi akurasi analitik</small>
                        </div>
                     </div>
                     <span class="badge bg-{{ $dataQuality['score'] >= 60 ? 'success' : ($dataQuality['score'] >= 30 ? 'warning' : 'danger') }} fs-6">{{ $dataQuality['score'] }}%</span>
                  </div>
                  <div class="progress mb-3" style="height: 8px;">
                     <div class="progress-bar bg-{{ $dataQuality['score'] >= 60 ? 'success' : ($dataQuality['score'] >= 30 ? 'warning' : 'danger') }}"
                        role="progressbar" style="width: {{ $dataQuality['score'] }}%"></div>
                  </div>
                  <div class="row g-3 text-center">
                     <div class="col-md-3 col-6">
                        <div class="small text-muted">Ban Terpasang</div>
                        <div class="fw-bold {{ $dataQuality['installed'] > 0 ? 'text-success' : 'text-danger' }}">{{ number_format($dataQuality['installed']) }} / {{ number_format($totalTyres) }}</div>
                     </div>
                     <div class="col-md-3 col-6">
                        <div class="small text-muted">Pernah Beroperasi <i class="ri-information-line" data-bs-toggle="tooltip" title="Ban yang punya data jarak tempuh (KM/HM > 0), dari semua status"></i></div>
                        <div class="fw-bold {{ $dataQuality['with_lifetime'] > 0 ? 'text-success' : 'text-danger' }}">{{ number_format($dataQuality['with_lifetime']) }} / {{ number_format($totalTyres) }}</div>
                     </div>
                     <div class="col-md-3 col-6">
                        <div class="small text-muted">Punya Harga</div>
                        <div class="fw-bold {{ $dataQuality['with_price'] > 0 ? 'text-success' : 'text-danger' }}">{{ number_format($dataQuality['with_price']) }} / {{ number_format($totalTyres) }}</div>
                     </div>
                     <div class="col-md-3 col-6">
                        <div class="small text-muted">Unit Punya Ban</div>
                        <div class="fw-bold {{ $dataQuality['vehicles_with_tyres'] > 0 ? 'text-success' : 'text-danger' }}">{{ $dataQuality['vehicles_with_tyres'] }} / {{ $totalVehicles }}</div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>


      <div class="row g-4 mb-4">
         <div class="col-12">
            <div class="section-label"><i class="ri-eye-line me-1"></i> MONITORING OVERVIEW</div>
         </div>
         <div class="col-xl-4 col-lg-4 col-sm-6">
            <div class="card kpi-card card-border-shadow-info h-100" data-kpi-type="monitoring_active">
               <div class="card-body py-3">
                  <div class="d-flex align-items-center justify-content-between">
                     <div>
                        <span class="kpi-sub d-block mb-1">Sesi Monitoring Aktif</span>
                        <div class="kpi-number text-info">{{ $activeMonitoringSessions }}</div>
                        <div class="kpi-sub mt-1">Kendaraan sedang dipantau</div>
                     </div>
                     <div class="avatar avatar-md">
                        <span class="avatar-initial rounded-3 bg-label-info"><i class="icon-base ri ri-radar-line ri-24px"></i></span>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-xl-4 col-lg-4 col-sm-6">
            <div class="card kpi-card card-border-shadow-warning h-100" data-kpi-type="pending_checks">
               <div class="card-body py-3">
                  <div class="d-flex align-items-center justify-content-between">
                     <div>
                        <span class="kpi-sub d-block mb-1">Pending Approval</span>
                        <div class="kpi-number text-warning">{{ $pendingChecks }}</div>
                        <div class="kpi-sub mt-1">Check menunggu persetujuan</div>
                     </div>
                     <div class="avatar avatar-md">
                        <span class="avatar-initial rounded-3 bg-label-warning"><i class="icon-base ri ri-time-line ri-24px"></i></span>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-xl-4 col-lg-4 col-sm-6">
            <div class="card kpi-card card-border-shadow-danger h-100" data-kpi-type="overdue_inspection">
               <div class="card-body py-3">
                  <div class="d-flex align-items-center justify-content-between">
                     <div>
                        <span class="kpi-sub d-block mb-1">Overdue Inspeksi (> 30 hari)</span>
                        <div class="kpi-number text-danger">{{ $overdueInspection }}</div>
                        <div class="kpi-sub mt-1">Ban belum diperiksa ulang</div>
                     </div>
                     <div class="avatar avatar-md">
                        <span class="avatar-initial rounded-3 bg-label-danger"><i class="icon-base ri ri-alarm-warning-line ri-24px"></i></span>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      {{-- CHARTS ROW --}}
      <div class="row g-4 mb-4">
         {{-- 2a. Tyre Status Distribution (Donut) --}}
         <div class="col-xl-4 col-lg-5">
            <div class="card chart-card h-100">
               <div class="card-header pb-0">
                  <h6 class="mb-1"><i class="icon-base ri ri-pie-chart-line me-1 text-primary"></i> Distribusi Status
                     Ban
                  </h6>
                  <p class="kpi-sub mb-0">Persentase ban berdasarkan status</p>
               </div>
               <div class="card-body d-flex align-items-center justify-content-center">
                  <div id="statusDonutChart" style="min-height:280px; width:100%;"></div>
               </div>
            </div>
         </div>

         {{-- 2b. Monthly Movement Trend (Mixed Bar) --}}
         <div class="col-xl-8 col-lg-7">
            <div class="card chart-card h-100">
               <div class="card-header pb-0 d-flex justify-content-between align-items-start">
                  <div>
                     <h6 class="mb-1"><i class="icon-base ri ri-bar-chart-grouped-line me-1 text-primary"></i> Tren
                        Pergerakan Bulanan
                     </h6>
                     <p class="kpi-sub mb-0">Pemasangan vs Pelepasan (6 bulan terakhir)</p>
                  </div>
                  <div class="text-end">
                     <span class="badge bg-label-success rounded-pill me-1">
                        <i class="icon-base ri ri-arrow-down-line"></i> {{ $installationsThisMonth }} Pasang
                     </span>
                     <span class="badge bg-label-danger rounded-pill me-1">
                        <i class="icon-base ri ri-arrow-up-line"></i> {{ $removalsThisMonth }} Lepas
                     </span>
                     <span class="badge bg-label-info rounded-pill">
                        <i class="icon-base ri ri-search-line"></i> {{ $inspectionsThisMonth }} Inspeksi
                     </span>
                     <span class="badge bg-label-warning rounded-pill ms-1">
                        <i class="icon-base ri ri-repeat-line"></i> {{ $rotationsThisMonth }} Rotasi
                     </span>
                     <span class="badge bg-label-secondary rounded-pill ms-1">
                        <i class="icon-base ri ri-file-list-3-line"></i> {{ $examinationsThisMonth }} Exam
                     </span>
                  </div>
               </div>
               <div class="card-body">
                  <div id="movementTrendChart" style="min-height:300px;"></div>
               </div>
            </div>
         </div>
      </div>

      {{-- PERFORMANCE ROW --}}
      <div class="row g-4 mb-4">
         <div class="col-xl-6 col-lg-6">
            <div class="card chart-card h-100">
               <div class="card-header pb-0">
                  <h6 class="mb-1"><i class="icon-base ri ri-bar-chart-horizontal-line me-1 text-primary"></i> Performa
                     Brand (Avg Lifetime {{ $measurementMode === 'BOTH' ? 'KM & HM' : $measurementMode }})</h6>
                  <p class="kpi-sub mb-0">Perbandingan umur rata-rata ban per brand</p>
               </div>
               <div class="card-body">
                  <div class="chart-filter-bar rounded p-3 mb-3 shadow-sm">
                     <div class="row g-3">
                        <div class="col-md-4">
                           <label class="filter-label mb-1 d-block text-primary"><i class="ri-ruler-2-line me-1"></i>
                              Size</label>
                           <select id="brandFilterSize" class="form-select select2">
                              <option value="">Semua Size</option>
                              @foreach ($filterSizes as $s)
                                 <option value="{{ $s->size }}">{{ $s->size }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-4">
                           <label class="filter-label mb-1 d-block text-primary"><i class="ri-price-tag-3-line me-1"></i>
                              Brand</label>
                           <select id="brandFilterBrand" class="form-select select2">
                              <option value="">Semua Brand</option>
                              @foreach ($filterBrands as $b)
                                 <option value="{{ $b->id }}">{{ $b->brand_name }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-4">
                           <label class="filter-label mb-1 d-block text-primary"><i class="ri-road-map-line me-1"></i>
                              Pattern</label>
                           <select id="brandFilterPattern" class="form-select select2">
                              <option value="">Semua Pattern</option>
                              @foreach ($filterPatterns as $p)
                                 <option value="{{ $p->name }}">{{ $p->name }}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                  </div>
                  <div id="brandPerformanceChart" style="min-height:280px;"></div>
                  <div id="brandPerformanceSample" class="text-center mt-1"></div>
               </div>
            </div>
         </div>
         <div class="col-xl-6 col-lg-6">
            <div class="card chart-card h-100">
               <div class="card-header pb-0">
                  <h6 class="mb-1"><i class="icon-base ri ri-money-dollar-circle-line me-1 text-warning"></i> Cost Per
                     {{ $measurementMode === 'BOTH' ? 'KM & HM' : $measurementMode }} by Brand (CP{{ $measurementMode === 'HM' ? 'H' : 'K' }})</h6>
                  <p class="kpi-sub mb-0">Perbandingan biaya per {{ $measurementMode === 'BOTH' ? 'satuan' : $measurementMode }} antar brand</p>
               </div>
               <div class="card-body">
                  <div class="chart-filter-bar rounded p-3 mb-3 shadow-sm">
                     <div class="row g-3">
                        <div class="col-md-4">
                           <label class="filter-label mb-1 d-block text-warning"><i class="ri-ruler-2-line me-1"></i>
                              Size</label>
                           <select id="cpkFilterSize" class="form-select select2">
                              <option value="">Semua Size</option>
                              @foreach ($filterSizes as $s)
                                 <option value="{{ $s->size }}">{{ $s->size }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-4">
                           <label class="filter-label mb-1 d-block text-warning"><i class="ri-price-tag-3-line me-1"></i>
                              Brand</label>
                           <select id="cpkFilterBrand" class="form-select select2">
                              <option value="">Semua Brand</option>
                              @foreach ($filterBrands as $b)
                                 <option value="{{ $b->id }}">{{ $b->brand_name }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-4">
                           <label class="filter-label mb-1 d-block text-warning"><i class="ri-road-map-line me-1"></i>
                              Pattern</label>
                           <select id="cpkFilterPattern" class="form-select select2">
                              <option value="">Semua Pattern</option>
                              @foreach ($filterPatterns as $p)
                                 <option value="{{ $p->name }}">{{ $p->name }}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                  </div>
                  <div id="cpkByBrandChart" style="min-height:280px;"></div>
                  <div id="cpkBrandSample" class="text-center mt-1"></div>
               </div>
            </div>
         </div>
      </div>

      {{-- NEW SECTION: BRAND DETAIL COMPARISON --}}
      <div class="row g-4 mb-4">
         <div class="col-12">
            <div class="card">
               <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
                  <div class="d-flex align-items-center">
                     <div class="avatar avatar-sm me-2">
                        <span class="avatar-initial rounded bg-label-primary"><i
                              class="icon-base ri ri-search-eye-line"></i></span>
                     </div>
                     <div>
                        <h6 class="mb-0">Perbandingan Internal Brand</h6>
                        <p class="kpi-sub mb-0">Analisis performa per Pattern & Size untuk brand pilihan</p>
                     </div>
                  </div>
                  <div style="width: 200px;">
                     <select id="brandDetailSelector" class="form-select select2">
                        <option value="">Pilih Brand</option>
                        @foreach ($filterBrands as $b)
                           <option value="{{ $b->id }}">{{ $b->brand_name }}</option>
                        @endforeach
                     </select>
                  </div>
               </div>
               <div class="card-body pt-4">
                  <div id="brandDetailPlaceholder" class="text-center py-5">
                     <i class="icon-base ri ri-information-line ri-3x text-light mb-2 d-block"></i>
                     <p class="text-muted mb-0">Silakan pilih brand untuk melihat perbandingan pattern dan size.</p>
                  </div>
                  <div id="brandDetailContent" style="display: none;">
                     {{-- Summary Stats --}}
                     <div class="row g-3 mb-4" id="brandSummaryRow"></div>
                     <div class="row g-4">
                        <div class="col-xl-6 col-lg-6">
                           <h6 class="text-center mb-3 fw-bold"><i
                                 class="icon-base ri ri-road-map-line me-1 text-primary"></i> Avg
                              Lifetime per Pattern</h6>
                           <div id="brandPatternChart" style="min-height: 300px;"></div>
                        </div>
                        <div class="col-xl-6 col-lg-6">
                           <h6 class="text-center mb-3 fw-bold"><i
                                 class="icon-base ri ri-ruler-2-line me-1 text-primary"></i> Avg
                              Lifetime per Size</h6>
                           <div id="brandSizeChart" style="min-height: 300px;"></div>
                        </div>
                        <div class="col-12">
                           <h6 class="text-center mb-3 fw-bold"><i
                                 class="icon-base ri ri-map-pin-line me-1 text-success"></i> Performa per Lokasi</h6>
                           <div id="brandLocationChart" style="min-height: 280px;"></div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      {{-- HEALTH ROW --}}
      <div class="row g-4 mb-4">
         <div class="col-xl-6 col-lg-6">
            <div class="card chart-card h-100">
               <div class="card-header pb-0 d-flex justify-content-between align-items-start">
                  <div>
                     <h6 class="mb-1"><i class="icon-base ri ri-heart-pulse-line me-1 text-primary"></i> Kondisi
                        Ban Terpasang (RTD %)</h6>
                     <p class="kpi-sub mb-0">Distribusi kondisi ban berdasarkan persentase sisa tapak</p>
                  </div>
                  <div class="text-center">
                     @php
                        $healthColor =
                            $fleetHealthData['avgHealth'] >= 60
                                ? 'success'
                                : ($fleetHealthData['avgHealth'] >= 40
                                    ? 'info'
                                    : ($fleetHealthData['avgHealth'] >= 20
                                        ? 'warning'
                                        : 'danger'));
                     @endphp
                     <div class="fleet-gauge-number text-{{ $healthColor }}">
                        {{ $fleetHealthData['avgHealth'] }}%
                     </div>
                     <span class="kpi-sub">Rata-rata Fleet</span>
                  </div>
               </div>
               <div class="card-body">
                  <div id="fleetHealthChart" style="min-height:280px;"></div>
                  <div class="d-flex justify-content-between mt-2">
                     <span class="badge bg-label-primary sample-badge text-wrap">
                        <i class="ri-database-2-line me-1"></i>{{ $fleetHealthData['totalWithData'] }} ban terukur
                     </span>
                     @if ($fleetHealthData['noDataCount'] > 0)
                        <span class="badge bg-label-secondary sample-badge text-wrap">
                           <i class="ri-question-line me-1"></i>{{ $fleetHealthData['noDataCount'] }} ban belum
                           terukur
                        </span>
                     @endif
                  </div>
               </div>
            </div>
         </div>
         <div class="col-xl-6 col-lg-6">
            <div class="card chart-card h-100">
               <div class="card-header pb-0">
                  <h6 class="mb-1"><i class="icon-base ri ri-building-2-line me-1 text-primary"></i> Stok Ban per
                     Lokasi</h6>
                  <p class="kpi-sub mb-0">Kapasitas vs Stok Terisi</p>
               </div>
               <div class="card-body">
                  <div id="locationStockChart" style="min-height:280px; width: 100%;"></div>
               </div>
            </div>
         </div>
      </div>

      {{-- AXLE & FAILURE ROW --}}
      <div class="row g-4 mb-4">
         <div class="col-xl-6 col-lg-6">
            <div class="card chart-card h-100">
               <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                  <div>
                     <h6 class="mb-1"><i class="icon-base ri ri-error-warning-line me-1 text-danger"></i> Scrap by
                        Position
                     </h6>
                     <p class="kpi-sub mb-0">Frekuensi Scrap Ban per Posisi</p>
                  </div>
                  <div class="ms-auto" id="axleTotalScrapBadge"></div>
               </div>
               <div class="card-body">
                  <div class="chart-filter-bar rounded p-3 mb-3 shadow-sm">
                     <div class="row g-3">
                        <div class="col-md-4">
                           <label class="filter-label mb-1 d-block text-danger"><i class="ri-ruler-2-line me-1"></i>
                              Size</label>
                           <select id="axleFilterSize" class="form-select select2">
                              <option value="">Semua Size</option>
                              @foreach ($filterSizes as $s)
                                 <option value="{{ $s->size }}">{{ $s->size }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-4">
                           <label class="filter-label mb-1 d-block text-danger"><i class="ri-price-tag-3-line me-1"></i>
                              Brand</label>
                           <select id="axleFilterBrand" class="form-select select2">
                              <option value="">Semua Brand</option>
                              @foreach ($filterBrands as $b)
                                 <option value="{{ $b->id }}">{{ $b->brand_name }}</option>
                              @endforeach
                           </select>
                        </div>
                        <div class="col-md-4">
                           <label class="filter-label mb-1 d-block text-danger"><i class="ri-road-map-line me-1"></i>
                              Pattern</label>
                           <select id="axleFilterPattern" class="form-select select2">
                              <option value="">Semua Pattern</option>
                              @foreach ($filterPatterns as $p)
                                 <option value="{{ $p->name }}">{{ $p->name }}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                  </div>
                  <div id="axleAnalysisChart" style="min-height:280px;"></div>
               </div>
            </div>
         </div>
         <div class="col-xl-6 col-lg-6">
            <div class="card chart-card h-100">
               <div class="card-header pb-0">
                  <h6 class="mb-1"><i class="icon-base ri ri-error-warning-line me-1 text-danger"></i> Penyebab
                     Pelepasan
                  </h6>
                  <p class="kpi-sub mb-0">Distribusi Failure Code</p>
               </div>
               <div class="card-body d-flex align-items-center justify-content-center">
                  @if ($failureDistribution->count() > 0)
                     <div id="failureDonutChart" style="min-height: 280px; width: 100%;"></div>
                  @else
                     <div class="text-center py-5 text-muted">
                        <i class="icon-base ri ri-shield-check-line ri-3x mb-2 d-block text-success opacity-50"></i>
                        <p class="mb-0">Belum ada data pelepasan dengan failure code</p>
                     </div>
                  @endif
               </div>
            </div>
         </div>
      </div>

      {{-- ALERTS & RECENT --}}
      <div class="row g-4 mb-4">
         <div class="col-xl-5">
            <div class="card h-100 mb-4">
               <div class="card-header pb-2 d-flex justify-content-between align-items-center">
                  <div>
                     <h6 class="mb-1"><i class="icon-base ri ri-alarm-warning-line me-1 text-danger"></i> Ban Perlu
                        Perhatian</h6>
                     <p class="kpi-sub mb-0">Ban dengan sisa tread terendah (terpasang)</p>
                  </div>
                  <span class="badge bg-danger rounded-pill">{{ $lowRtdTyres->count() }}</span>
               </div>
               <div class="card-body p-0">
                  @if ($lowRtdTyres->count() > 0)
                     <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                           <thead class="table-light">
                              <tr>
                                 <th class="ps-3">Ban</th>
                                 <th>Kendaraan</th>
                                 <th>OTD</th>
                                 <th>RTD</th>
                                 <th>% Sisa</th>
                              </tr>
                           </thead>
                           <tbody>
                              @foreach ($lowRtdTyres as $t)
                                 @php
                                    $otd = $t->initial_tread_depth ?? 0;
                                    $rtd = $t->current_tread_depth ?? 0;
                                    $pctRemaining = $otd > 0 ? round(($rtd / $otd) * 100, 0) : 0;
                                    $barColor =
                                        $pctRemaining < 20
                                            ? '#ea5455'
                                            : ($pctRemaining < 40
                                                ? '#ff9f43'
                                                : ($pctRemaining < 60
                                                    ? '#00cfe8'
                                                    : '#28c76f'));
                                 @endphp
                                 <tr class="alert-tyre-row">
                                    <td class="ps-3">
                                       <strong class="d-block small">{{ $t->serial_number }}</strong>
                                       <span class="text-muted"
                                          style="font-size:.7rem">{{ $t->brand->brand_name ?? '' }}</span>
                                    </td>
                                    <td>
                                       <span
                                          class="badge bg-label-primary rounded-pill">{{ $t->currentVehicle->kode_kendaraan ?? '-' }}</span>
                                    </td>
                                    <td class="small">{{ $otd }} mm</td>
                                    <td>
                                       <span
                                          class="fw-bold {{ $pctRemaining < 20 ? 'text-danger' : ($pctRemaining < 40 ? 'text-warning' : 'text-success') }}">
                                          {{ $rtd }} mm
                                       </span>
                                    </td>
                                    <td style="min-width:80px">
                                       <div class="d-flex align-items-center">
                                          <span class="small me-2">{{ $pctRemaining }}%</span>
                                          <div class="rtd-bar flex-grow-1">
                                             <div class="rtd-bar-inner"
                                                style="width:{{ $pctRemaining }}%;background:{{ $barColor }}"></div>
                                          </div>
                                       </div>
                                    </td>
                                 </tr>
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                  @else
                     <div class="text-center py-5 text-muted">
                        <i class="icon-base ri ri-checkbox-circle-line ri-3x mb-2 d-block text-success opacity-50"></i>
                        <p class="mb-0">Semua ban dalam kondisi baik</p>
                     </div>
                  @endif
               </div>
            </div>

            <div class="card border-primary shadow-none bg-label-primary">
               <div class="card-body">
                  <h6 class="fw-bold mb-3"><i class="icon-base ri ri-lightbulb-flash-line me-2"></i> Panduan Tindakan
                     Admin</h6>
                  <div class="d-flex flex-column gap-3">
                     <div class="d-flex align-items-start">
                        <div class="badge bg-primary rounded p-1 me-2"><i class="ri-tools-line"></i></div>
                        <div>
                           <p class="mb-0 small fw-bold">1. Diagnosa & Pelepasan</p>
                           <p class="mb-0 kpi-sub text-wrap">Jika % sisa < 20%, segera lakukan <strong>Pelepasan</strong>
                                 melalui menu <em>Movement</em>. Pilih <strong>Failure Code</strong> yang sesuai.</p>
                        </div>
                     </div>
                     <div class="d-flex align-items-start border-top pt-2">
                        <div class="badge bg-primary rounded p-1 me-2"><i class="ri-refresh-line"></i></div>
                        <div>
                           <p class="mb-0 small fw-bold">2. Update Status Ban</p>
                           <p class="mb-0 kpi-sub text-wrap">Setelah dilepas, admin <strong>WAJIB</strong> mengupdate
                              status ban di <em>Master Tyre</em> menjadi <strong>Scrap</strong> atau
                              <strong>Repaired</strong>.
                           </p>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <div class="col-xl-7">
            <div class="card h-100">
               <div class="card-header pb-0">
                  <h6 class="mb-1"><i class="icon-base ri ri-history-line me-1 text-primary"></i> Aktivitas Terbaru
                  </h6>
                  <p class="kpi-sub mb-0">10 pergerakan terakhir</p>
               </div>
               <div class="card-body">
                  @if ($recentMovements->count() > 0)
                     <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                           <thead class="table-light">
                              <tr>
                                 <th>Tanggal</th>
                                 <th>Tipe</th>
                                 <th>Ban</th>
                                 <th>Kendaraan</th>
                                 <th>KM</th>
                                 <th>HM</th>
                              </tr>
                           </thead>
                           <tbody>
                              @foreach ($recentMovements as $m)
                                 <tr>
                                    <td class="small">{{ \Carbon\Carbon::parse($m->movement_date)->format('d/m/Y') }}
                                    </td>
                                    <td>
                                       <span
                                          class="badge bg-label-{{ $m->movement_type === 'Installation' ? 'success' : ($m->movement_type === 'Removal' ? 'danger' : ($m->movement_type === 'Rotation' ? 'warning' : 'info')) }} rounded-pill"
                                          style="font-size:.65rem">
                                          {{ $m->movement_type === 'Installation' ? 'Pasang' : ($m->movement_type === 'Removal' ? 'Lepas' : ($m->movement_type === 'Rotation' ? 'Rotasi' : 'Inspeksi')) }}
                                       </span>
                                    </td>
                                    <td class="small fw-medium">{{ $m->tyre->serial_number ?? '-' }}</td>
                                    <td class="small">{{ $m->vehicle->kode_kendaraan ?? '-' }}</td>
                                    <td class="small">
                                       {{ $m->odometer_reading ? number_format($m->odometer_reading, 0) : '-' }}
                                    </td>
                                    <td class="small">
                                       {{ $m->hour_meter_reading ? number_format($m->hour_meter_reading, 0) : '-' }}
                                    </td>
                                 </tr>
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                  @else
                     <div class="text-center py-5 text-muted">
                        <i class="icon-base ri ri-file-list-line ri-3x mb-2 d-block opacity-25"></i>
                        <p class="mb-0">Belum ada aktivitas pergerakan</p>
                     </div>
                  @endif
               </div>
            </div>
         </div>
      </div>
   </div>

   {{-- DRILL-DOWN MODAL --}}
   <div class="modal fade" id="drillDownModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
         <div class="modal-content">
            <div class="modal-header bg-primary">
               <h5 class="modal-title text-white" id="drillDownTitle">
                  <i class="icon-base ri ri-search-eye-line me-2"></i>
                  <span id="drillDownTitleText">Detail Data</span>
               </h5>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <div id="drillDownLoading" class="text-center py-5">
                  <div class="spinner-border text-primary" role="status">
                     <span class="visually-hidden">Loading...</span>
                  </div>
                  <p class="mt-2 text-muted">Memuat data...</p>
               </div>
               <div id="drillDownContent" style="display:none;">
                  <div class="mb-3 d-flex justify-content-between align-items-center">
                     <span class="badge bg-primary rounded-pill" id="drillDownCount"></span>
                     <a href="#" id="drillDownLink" class="btn btn-sm btn-outline-primary" style="display:none;">
                        <i class="icon-base ri ri-external-link-line me-1"></i>Lihat Semua
                     </a>
                  </div>
                  <div class="table-responsive">
                     <table class="table table-sm table-hover table-striped" id="drillDownTable" style="width:100%">
                        <thead class="table-light" id="drillDownHead"></thead>
                        <tbody id="drillDownBody"></tbody>
                     </table>
                  </div>
               </div>
               <div id="drillDownEmpty" class="text-center py-5" style="display:none;">
                  <i class="icon-base ri ri-file-search-line ri-3x mb-2 d-block opacity-25"></i>
                  <p class="text-muted mb-0">Tidak ada data untuk ditampilkan</p>
               </div>
            </div>
         </div>
      </div>
   </div>

   {{-- KPI DETAIL MODAL --}}
   <div class="modal fade" id="kpiDetailModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
         <div class="modal-content">
            <div class="modal-header border-bottom">
               <h5 class="modal-title fw-bold" id="kpiDetailTitle"><i class="ri-bar-chart-box-line me-2 text-primary"></i>Detail</h5>
               <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-label-primary rounded-pill" id="kpiDetailTotal"></span>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
               </div>
            </div>
            <div class="modal-body p-4">
               {{-- Loading --}}
               <div id="kpiDetailLoading" class="text-center py-5">
                  <div class="spinner-border text-primary" style="width:2.5rem;height:2.5rem;"></div>
                  <p class="text-muted mt-2 mb-0 small">Memuat data analitik...</p>
               </div>
               {{-- Content --}}
               <div id="kpiDetailContent" style="display:none;">
                  {{-- Summary Cards --}}
                  <div class="row g-3 mb-4" id="kpiSummaryCards"></div>
                  {{-- Charts --}}
                  <div class="row g-3 mb-4" id="kpiChartsRow">
                     <div class="col-md-6"><div class="card shadow-none border"><div class="card-header py-2"><h6 class="mb-0 small fw-bold" id="kpiChart1Title"></h6></div><div class="card-body p-2"><div id="kpiChart1" style="min-height:250px;"></div></div></div></div>
                     <div class="col-md-6"><div class="card shadow-none border"><div class="card-header py-2"><h6 class="mb-0 small fw-bold" id="kpiChart2Title"></h6></div><div class="card-body p-2"><div id="kpiChart2" style="min-height:250px;"></div></div></div></div>
                  </div>
                  {{-- Table --}}
                  <div class="card shadow-none border">
                     <div class="card-header py-2"><h6 class="mb-0 small fw-bold"><i class="ri-table-line me-1"></i>Data Detail</h6></div>
                     <div class="card-body p-0">
                        <div class="table-responsive">
                           <table class="table table-sm table-hover table-striped mb-0" id="kpiDetailTable" style="width:100%">
                              <thead class="table-light" id="kpiDetailHead"></thead>
                              <tbody id="kpiDetailBody"></tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      'use strict';

      document.addEventListener('DOMContentLoaded', function() {
         var colors = {
            primary: '#7367f0',
            success: '#28c76f',
            danger: '#ea5455',
            warning: '#ff9f43',
            info: '#00cfe8',
            secondary: '#a8aaae'
         };

         if ($.fn.select2) {
            $('.select2').each(function() {
               var $this = $(this);
               $this.select2({
                  placeholder: 'Pilih opsi',
                  dropdownParent: $this.parent(),
                  width: '100%'
               });
            });
         }

         var drillDownUrl = '{{ route('master_data.drill-down') }}';
         var brandPerformanceUrl = '{{ route('master_data.brand-performance') }}';
         var cpkByBrandUrl = '{{ route('master_data.cpk-by-brand') }}';
         var axleAnalysisUrl = '{{ route('master_data.scrap-by-position') }}';
         var filterOptionsUrl = '{{ route('master_data.filter-options') }}';
         var drillDownDT = null;

         // ===========================================
         // Cascading Dropdown Helper
         // ===========================================
         function updateCascadingDropdowns(sizeSelector, brandSelector, patternSelector, callback) {
            var size = $(sizeSelector).val();
            var brandId = $(brandSelector).val();

            $.ajax({
               url: filterOptionsUrl,
               data: { size: size, brand_id: brandId },
               success: function(res) {
                  if (!res.success) {
                     if (callback) callback();
                     return;
                  }

                  // Repopulate Brand dropdown (keep current selection if still valid)
                  var currentBrand = $(brandSelector).val();
                  $(brandSelector).empty().append('<option value="">Semua Brand</option>');
                  res.brands.forEach(function(b) {
                     $(brandSelector).append('<option value="' + b.id + '">' + b.brand_name + '</option>');
                  });
                  // Restore selection if still available
                  if (currentBrand && $(brandSelector).find('option[value="' + currentBrand + '"]').length) {
                     $(brandSelector).val(currentBrand);
                  } else {
                     $(brandSelector).val('');
                  }
                  $(brandSelector).trigger('change.select2');

                  // Repopulate Pattern dropdown
                  var currentPattern = $(patternSelector).val();
                  $(patternSelector).empty().append('<option value="">Semua Pattern</option>');
                  res.patterns.forEach(function(p) {
                     $(patternSelector).append('<option value="' + p.name + '">' + p.name + '</option>');
                  });
                  if (currentPattern && $(patternSelector).find('option[value="' + currentPattern + '"]').length) {
                     $(patternSelector).val(currentPattern);
                  } else {
                     $(patternSelector).val('');
                  }
                  $(patternSelector).trigger('change.select2');

                  if (callback) callback();
               },
               error: function() {
                  console.error('Filter options AJAX failed');
                  if (callback) callback();
               }
            });
         }

         // Global dates from PHP
         var globalStartDate = '{{ $startDate->format('Y-m-d') }}';
         var globalEndDate = '{{ $endDate->format('Y-m-d') }}';

         // Helper: tampilkan loading spinner di chart container
         function showChartLoading(containerId, text) {
            var el = document.querySelector(containerId);
            if (el) el.innerHTML = '<div class="chart-loading"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><span class="chart-loading-text">' + (text || 'Memuat data...') + '</span></div>';
         }

         function openDrillDown(type, value, extraParams) {
            var modal = new bootstrap.Modal(document.getElementById('drillDownModal'));
            document.getElementById('drillDownLoading').style.display = 'block';
            document.getElementById('drillDownContent').style.display = 'none';
            document.getElementById('drillDownEmpty').style.display = 'none';
            document.getElementById('drillDownTitleText').textContent = 'Memuat...';

            if (drillDownDT) {
               drillDownDT.destroy();
               drillDownDT = null;
            }
            modal.show();

            var ajaxData = {
               type: type,
               value: value
            };
            if (extraParams) {
               $.extend(ajaxData, extraParams);
            }

            $.ajax({
               url: drillDownUrl,
               data: ajaxData,
               dataType: 'json',
               success: function(res) {
                  document.getElementById('drillDownLoading').style.display = 'none';
                  if (!res.data || res.data.length === 0) {
                     document.getElementById('drillDownEmpty').style.display = 'block';
                     document.getElementById('drillDownTitleText').textContent = res.title || 'Detail Data';
                     return;
                  }
                  document.getElementById('drillDownContent').style.display = 'block';
                  document.getElementById('drillDownTitleText').textContent = res.title || 'Detail Data';
                  document.getElementById('drillDownCount').textContent = res.total + ' data ditemukan';

                  var headHtml = '<tr>';
                  res.columns.forEach(function(col) {
                     headHtml += '<th>' + col + '</th>';
                  });
                  if (res.data[0] && res.data[0].id) headHtml += '<th>Aksi</th>';
                  headHtml += '</tr>';
                  document.getElementById('drillDownHead').innerHTML = headHtml;

                  var bodyHtml = '';
                  res.data.forEach(function(row) {
                     bodyHtml += '<tr>';
                     res.keys.forEach(function(key) {
                        var val = (row[key] !== null) ? row[key] : '-';
                        if (key === 'status') {
                           var cls = 'secondary';
                           if (val === 'Installed') cls = 'success';
                           else if (val === 'New') cls = 'primary';
                           else if (val === 'Retread') cls = 'info';
                           else if (val === 'Scrap') cls = 'danger';
                           else if (val === 'Repaired') cls = 'warning';
                           val = '<span class="badge bg-label-' + cls + ' rounded-pill">' +
                              val + '</span>';
                        }
                        bodyHtml += '<td>' + val + '</td>';
                     });
                     if (row.id) {
                        bodyHtml += '<td><a href="/master_tyre/' + row.id +
                           '" class="btn btn-sm btn-icon btn-text-primary"><i class="icon-base ri ri-eye-line"></i></a></td>';
                     }
                     bodyHtml += '</tr>';
                  });
                  document.getElementById('drillDownBody').innerHTML = bodyHtml;
                  drillDownDT = $('#drillDownTable').DataTable({
                     paging: true,
                     pageLength: 10,
                     language: {
                        search: 'Cari:',
                        zeroRecords: 'Tidak ada data ditemukan'
                     }
                  });
               }
            });
         }

         var statusData = @json($statusDistribution);
         var statusLabels = Object.keys(statusData);
         var statusValues = Object.values(statusData);
         var statusColorsMap = {
            'Installed': colors.success,
            'Repaired': colors.warning,
            'Scrap': colors.danger
         };
         var statusColors = statusLabels.map(function(s) {
            if (s.indexOf('New') === 0) return colors.primary;
            if (s.indexOf('Retread') === 0) return colors.info;
            return statusColorsMap[s] || colors.secondary;
         });

         new ApexCharts(document.querySelector('#statusDonutChart'), {
            chart: {
               type: 'donut',
               height: 280,
               events: {
                  dataPointSelection: function(e, c, cfg) {
                     openDrillDown('status', statusLabels[cfg.dataPointIndex]);
                  }
               }
            },
            series: statusValues,
            labels: statusLabels,
            colors: statusColors,
            plotOptions: {
               pie: {
                  donut: {
                     size: '65%',
                     labels: {
                        show: true,
                        value: {
                           fontWeight: 700,
                           formatter: function(v) {
                              return v + ' ban';
                           }
                        },
                        total: {
                           show: true,
                           label: 'Total',
                           formatter: function(w) {
                              return w.globals.seriesTotals.reduce(function(a, b) {
                                 return a + b;
                              }, 0) + ' ban';
                           }
                        }
                     }
                  }
               }
            },
            tooltip: {
               custom: function({series, seriesIndex, dataPointIndex, w}) {
                  var label = w.globals.labels[seriesIndex];
                  var val = series[seriesIndex];
                  var desc = '';
                  if (label.indexOf('Installed') === 0) desc = 'Ban saat ini terpasang dan aktif di unit kendaraan.';
                  else if (label.indexOf('New') === 0) desc = 'Ban baru asli dari pabrik (R0) yang tersimpan atau terpasang.';
                  else if (label.indexOf('Retread') === 0) desc = 'Ban hasil vulkanisir di mana tapaknya telah diperbarui (R1, R2, dst).';
                  else if (label.indexOf('Repaired') === 0) desc = 'Ban yang sedang/telah diperbaiki akibat bocor atau robek ringan.';
                  else if (label.indexOf('Scrap') === 0) desc = 'Ban rusak permanen / afkir yang sudah tidak layak digunakan (Dibuang).';
                  
                  return '<div style="background-color: #ffffff; border: 1px solid #e9ecef; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 12px; max-width: 260px;">' +
                         '<div style="font-weight: 700; font-size: 13px; color: #2b3445; margin-bottom: 6px;">' + label + ' : ' + val + ' Ban</div>' +
                         '<div style="font-size: 12px; color: #7d879c; white-space: normal; line-height: 1.5;">' + desc + '</div>' +
                         '</div>';
               }
            }
         }).render();

         var monthlyData = @json($monthlyMovements);
         new ApexCharts(document.querySelector('#movementTrendChart'), {
            chart: {
               type: 'bar',
               height: 320,
               fontFamily: 'inherit',
               toolbar: {
                  show: false
               },
               events: {
                  dataPointSelection: function(e, c, cfg) {
                     var m = monthlyData[cfg.dataPointIndex].month;
                     var s = 'Installation';
                     if (cfg.seriesIndex === 1) s = 'Removal';
                     if (cfg.seriesIndex === 2) s = 'Inspection';
                     if (cfg.seriesIndex === 3) s = 'Rotation';
                     if (cfg.seriesIndex === 4) s = 'Examination';
                     openDrillDown('movement', m + '|' + s);
                  }
               }
            },
            series: [{
                  name: 'Pemasangan',
                  data: monthlyData.map(function(m) { return m.installations; })
               },
               {
                  name: 'Pelepasan',
                  data: monthlyData.map(function(m) { return m.removals; })
               },
               {
                  name: 'Inspeksi',
                  data: monthlyData.map(function(m) { return m.inspections || 0; })
               },
               {
                  name: 'Rotasi',
                  data: monthlyData.map(function(m) { return m.rotations || 0; })
               },
               {
                  name: 'Exam',
                  data: monthlyData.map(function(m) { return m.examinations || 0; })
               }
            ],
            xaxis: {
               categories: monthlyData.map(function(m) { return m.month; }),
               axisBorder: { show: false },
               axisTicks: { show: false },
               labels: {
                  style: { colors: '#a1acb8', fontSize: '13px' }
               }
            },
            yaxis: {
               labels: {
                  style: { colors: '#a1acb8', fontSize: '13px' }
               }
            },
            grid: {
               strokeDashArray: 7,
               borderColor: '#e9ecef',
               padding: { top: -20, bottom: -10, left: 10, right: 10 }
            },
            colors: [colors.success, colors.danger, colors.info, colors.warning, colors.secondary],
            plotOptions: {
               bar: {
                  columnWidth: '50%',
                  borderRadius: 6,
                  borderRadiusApplication: 'end',
                  dataLabels: {
                     position: 'top'
                  }
               }
            },
            dataLabels: {
               enabled: false
            },
            stroke: {
               show: true,
               width: 3,
               colors: ['transparent']
            },
            legend: {
               position: 'top',
               horizontalAlign: 'right',
               markers: { radius: 12 },
               itemMargin: { horizontal: 10 }
            },
            tooltip: {
               shared: true,
               intersect: false,
               y: {
                  formatter: function (val) {
                     return val === 0 ? "0 pergerakan" : val + " pergerakan";
                  }
               }
            }
         }).render();

         var brandChartInst = null;
         function renderBrandChart(data) {
            var container = document.querySelector('#brandPerformanceChart');
            if (brandChartInst) {
               brandChartInst.destroy();
               brandChartInst = null;
            }
            if (!data || data.length === 0) {
               container.innerHTML = '<div class="text-center py-5">Belum ada data</div>';
               return;
            }
            container.innerHTML = ''; // Clear loading spinner
            var hasBrand = $('#brandFilterBrand').val();
            brandChartInst = new ApexCharts(container, {
               chart: {
                  type: 'bar',
                  height: 280,
                  events: {
                     dataPointSelection: function(e, c, cfg) {
                        openDrillDown('brand_performance', data[cfg.dataPointIndex].label, {
                           size: $('#brandFilterSize').val(),
                           brand_id: $('#brandFilterBrand').val(),
                           pattern: $('#brandFilterPattern').val()
                        });
                     }
                  }
               },
               series: [{
                  name: hasBrand ? 'Avg by Pattern' : 'Avg {{ $measurementMode === 'BOTH' ? 'Life' : $measurementMode }}',
                  data: data.map(function(b) {
                     return b.avg_km;
                  })
               }],
               xaxis: {
                  categories: data.map(function(b) {
                     return b.label;
                  })
               },
               colors: [hasBrand ? colors.info : colors.primary],
               plotOptions: {
                  bar: {
                     horizontal: true,
                     borderRadius: 6,
                     dataLabels: {
                        position: 'center'
                     }
                  }
               },
               dataLabels: {
                  enabled: true,
                  formatter: function(v, o) {
                     return v.toLocaleString() + ' {{ $measurementMode === 'HM' ? 'hm' : 'km' }} (' + data[o.dataPointIndex].count + ' ban)';
                  }
               }
            });
            brandChartInst.render();
         }
         renderBrandChart(@json($brandPerformance));

         function triggerBrandPerformanceAjax() {
            if (brandChartInst) {
               brandChartInst.destroy();
               brandChartInst = null;
            }
            showChartLoading('#brandPerformanceChart', 'Memuat performa brand...');
            $.ajax({
               url: brandPerformanceUrl,
               data: {
                  size: $('#brandFilterSize').val(),
                  brand_id: $('#brandFilterBrand').val(),
                  pattern: $('#brandFilterPattern').val()
               },
               success: function(res) {
                  if (res.success && res.data && res.data.length > 0) {
                     renderBrandChart(res.data);
                  } else {
                     document.querySelector('#brandPerformanceChart').innerHTML = '<div class="text-center py-5 text-muted">Belum ada data untuk filter ini</div>';
                  }
               },
               error: function(xhr) {
                  console.error('Brand performance AJAX error:', xhr.status, xhr.responseText);
                  document.querySelector('#brandPerformanceChart').innerHTML = '<div class="text-center py-5 text-danger"><i class="ri-error-warning-line ri-lg me-1"></i>Gagal memuat data</div>';
               }
            });
         }
         $('#brandFilterSize').on('change', function() {
            updateCascadingDropdowns('#brandFilterSize', '#brandFilterBrand', '#brandFilterPattern', triggerBrandPerformanceAjax);
         });
         $('#brandFilterBrand').on('change', function() {
            // Only refresh patterns (not brands)
            var size = $('#brandFilterSize').val();
            var brandId = $(this).val();
            $.ajax({
               url: filterOptionsUrl,
               data: { size: size, brand_id: brandId },
               success: function(res) {
                  if (!res.success) { triggerBrandPerformanceAjax(); return; }
                  var currentPattern = $('#brandFilterPattern').val();
                  $('#brandFilterPattern').empty().append('<option value="">Semua Pattern</option>');
                  res.patterns.forEach(function(p) {
                     $('#brandFilterPattern').append('<option value="' + p.name + '">' + p.name + '</option>');
                  });
                  if (currentPattern && $('#brandFilterPattern').find('option[value="' + currentPattern + '"]').length) {
                     $('#brandFilterPattern').val(currentPattern);
                  } else {
                     $('#brandFilterPattern').val('');
                  }
                  $('#brandFilterPattern').trigger('change.select2');
                  triggerBrandPerformanceAjax();
               },
               error: function() { triggerBrandPerformanceAjax(); }
            });
         });
         $('#brandFilterPattern').on('change', triggerBrandPerformanceAjax);


         var cpkChartInst = null;
         function renderCpkChart(data) {
            var container = document.querySelector('#cpkByBrandChart');
            if (cpkChartInst) {
               cpkChartInst.destroy();
               cpkChartInst = null;
            }
            if (!data || data.length === 0) {
               container.innerHTML = '<div class="text-center py-5">Belum ada data</div>';
               return;
            }
            container.innerHTML = ''; // Clear loading spinner
            data.sort(function(a, b) {
               return a.cpk - b.cpk;
            });
            var hasBrand = $('#cpkFilterBrand').val();
            cpkChartInst = new ApexCharts(container, {
               chart: {
                  type: 'bar',
                  height: 280,
                  events: {
                     dataPointSelection: function(e, c, cfg) {
                        openDrillDown('brand_cpk', data[cfg.dataPointIndex].label, {
                           size: $('#cpkFilterSize').val(),
                           brand_id: $('#cpkFilterBrand').val(),
                           pattern: $('#cpkFilterPattern').val()
                        });
                     }
                  }
               },
               series: [{
                  name: hasBrand ? 'CP{{ $measurementMode === 'HM' ? 'H' : 'K' }} by Pattern' : 'CP{{ $measurementMode === 'HM' ? 'H' : 'K' }}',
                  data: data.map(function(b) {
                     return b.cpk;
                  })
               }],
               xaxis: {
                  categories: data.map(function(b) {
                     return b.label;
                  })
               },
               colors: [hasBrand ? colors.info : colors.warning],
               plotOptions: {
                  bar: {
                     horizontal: true,
                     borderRadius: 6,
                     dataLabels: {
                        position: 'center'
                     }
                  }
               },
               dataLabels: {
                  enabled: true,
                  formatter: function(v, o) {
                     return 'Rp ' + v.toLocaleString() + ' (' + data[o.dataPointIndex].count + ' ban)';
                  }
               }
            });
            cpkChartInst.render();
         }
         renderCpkChart(@json($cpkByBrand));

         function triggerCpkAjax() {
            if (cpkChartInst) {
               cpkChartInst.destroy();
               cpkChartInst = null;
            }
            showChartLoading('#cpkByBrandChart', 'Memuat data CPK...');
            $.ajax({
               url: cpkByBrandUrl,
               data: {
                  size: $('#cpkFilterSize').val(),
                  brand_id: $('#cpkFilterBrand').val(),
                  pattern: $('#cpkFilterPattern').val()
               },
               success: function(res) {
                  if (res.success && res.data && res.data.length > 0) {
                     renderCpkChart(res.data);
                  } else {
                     document.querySelector('#cpkByBrandChart').innerHTML = '<div class="text-center py-5 text-muted">Belum ada data untuk filter ini</div>';
                  }
               },
               error: function(xhr) {
                  console.error('CPK AJAX error:', xhr.status, xhr.responseText);
                  document.querySelector('#cpkByBrandChart').innerHTML = '<div class="text-center py-5 text-danger"><i class="ri-error-warning-line ri-lg me-1"></i>Gagal memuat data</div>';
               }
            });
         }
         $('#cpkFilterSize').on('change', function() {
            updateCascadingDropdowns('#cpkFilterSize', '#cpkFilterBrand', '#cpkFilterPattern', triggerCpkAjax);
         });
         $('#cpkFilterBrand').on('change', function() {
            var size = $('#cpkFilterSize').val();
            var brandId = $(this).val();
            $.ajax({
               url: filterOptionsUrl,
               data: { size: size, brand_id: brandId },
               success: function(res) {
                  if (!res.success) { triggerCpkAjax(); return; }
                  var currentPattern = $('#cpkFilterPattern').val();
                  $('#cpkFilterPattern').empty().append('<option value="">Semua Pattern</option>');
                  res.patterns.forEach(function(p) {
                     $('#cpkFilterPattern').append('<option value="' + p.name + '">' + p.name + '</option>');
                  });
                  if (currentPattern && $('#cpkFilterPattern').find('option[value="' + currentPattern + '"]').length) {
                     $('#cpkFilterPattern').val(currentPattern);
                  } else {
                     $('#cpkFilterPattern').val('');
                  }
                  $('#cpkFilterPattern').trigger('change.select2');
                  triggerCpkAjax();
               },
               error: function() { triggerCpkAjax(); }
            });
         });
         $('#cpkFilterPattern').on('change', triggerCpkAjax);

         // --- Brand Detail Section Logic ---
         var brandDetailUrl = '{{ route('master_data.brand-detail-performance') }}';
         var brandPatternChart = null, brandSizeChart = null, brandLocationChart = null;

         function buildBrandSeries(items, mode) {
            var series = [];
            if (mode !== 'HM') series.push({ name: 'Avg KM', data: items.map(i => i.avg_km || 0) });
            if (mode !== 'KM') series.push({ name: 'Avg HM', data: items.map(i => i.avg_hm || 0) });
            return series;
         }

         function brandBarOpts(items, mode, height) {
            return {
               chart: { type: 'bar', height: height || 300 },
               series: buildBrandSeries(items, mode),
               xaxis: { categories: items.map(i => i.label) },
               colors: [colors.primary, colors.info, colors.warning],
               plotOptions: { bar: { borderRadius: 4, dataLabels: { position: 'top' }, columnWidth: mode === 'BOTH' ? '60%' : '40%' } },
               dataLabels: { enabled: true, offsetY: -20, style: { fontSize: '10px' }, formatter: v => v ? v.toLocaleString() : '0' },
               tooltip: { y: { formatter: v => v ? v.toLocaleString() : '0' } }
            };
         }

         function renderBrandDetailCharts(data) {
            $('#brandDetailPlaceholder').hide();
            $('#brandDetailContent').show();
            var mode = data.mode || 'KM';
            var s = data.summary || {};

            // Summary cards
            var sh = '';
            sh += '<div class="col-sm-6 col-md-3"><div class="card shadow-none bg-label-primary"><div class="card-body py-3 text-center"><div class="small fw-bold text-uppercase opacity-75">Total Ban</div><div class="fw-bold fs-5">' + (s.total || 0) + '</div></div></div></div>';
            if (s.avg_km !== undefined) sh += '<div class="col-sm-6 col-md-3"><div class="card shadow-none bg-label-warning"><div class="card-body py-3 text-center"><div class="small fw-bold text-uppercase opacity-75">Avg KM</div><div class="fw-bold fs-5">' + (s.avg_km || 0).toLocaleString() + '</div></div></div></div>';
            if (s.avg_hm !== undefined) sh += '<div class="col-sm-6 col-md-3"><div class="card shadow-none bg-label-info"><div class="card-body py-3 text-center"><div class="small fw-bold text-uppercase opacity-75">Avg HM</div><div class="fw-bold fs-5">' + (s.avg_hm || 0).toLocaleString() + '</div></div></div></div>';
            if (s.cpk !== undefined) sh += '<div class="col-sm-6 col-md-3"><div class="card shadow-none bg-label-success"><div class="card-body py-3 text-center"><div class="small fw-bold text-uppercase opacity-75">CPK</div><div class="fw-bold fs-5">Rp ' + (s.cpk || 0).toLocaleString() + '</div></div></div></div>';
            if (s.cph !== undefined) sh += '<div class="col-sm-6 col-md-3"><div class="card shadow-none bg-label-secondary"><div class="card-body py-3 text-center"><div class="small fw-bold text-uppercase opacity-75">CPH</div><div class="fw-bold fs-5">Rp ' + (s.cph || 0).toLocaleString() + '</div></div></div></div>';
            $('#brandSummaryRow').html(sh);

            var pItems = Array.isArray(data.by_pattern) ? data.by_pattern : Object.values(data.by_pattern);
            var sItems = Array.isArray(data.by_size) ? data.by_size : Object.values(data.by_size);
            var lItems = Array.isArray(data.by_location) ? data.by_location : Object.values(data.by_location);

            if (brandPatternChart) brandPatternChart.destroy();
            brandPatternChart = new ApexCharts(document.querySelector('#brandPatternChart'), brandBarOpts(pItems, mode, 300));
            brandPatternChart.render();

            if (brandSizeChart) brandSizeChart.destroy();
            brandSizeChart = new ApexCharts(document.querySelector('#brandSizeChart'), brandBarOpts(sItems, mode, 300));
            brandSizeChart.render();

            if (brandLocationChart) brandLocationChart.destroy();
            if (lItems.length > 0) {
               var locOpts = brandBarOpts(lItems, mode, 280);
               locOpts.plotOptions.bar.horizontal = true;
               locOpts.dataLabels.offsetY = 0;
               locOpts.dataLabels.offsetX = 15;
               brandLocationChart = new ApexCharts(document.querySelector('#brandLocationChart'), locOpts);
               brandLocationChart.render();
            }
         }

         $('#brandDetailSelector').on('change', function() {
            var brandId = $(this).val();
            if (!brandId) {
               $('#brandDetailContent').hide();
               $('#brandDetailPlaceholder').show();
               return;
            }
            $.ajax({
               url: brandDetailUrl,
               data: { brand_id: brandId },
               success: function(res) {
                  if (res.success) renderBrandDetailCharts(res);
               }
            });
         });


         var fleetHealthData = @json($fleetHealthData);
         var healthLabels = Object.keys(fleetHealthData.categories);
         new ApexCharts(document.querySelector('#fleetHealthChart'), {
            chart: {
               type: 'donut',
               height: 280,
               events: {
                  dataPointSelection: function(e, c, cfg) {
                     openDrillDown('rtd', healthLabels[cfg.dataPointIndex]);
                  }
               }
            },
            series: Object.values(fleetHealthData.categories),
            labels: healthLabels,
            colors: [colors.danger, colors.warning, colors.info, colors.success],
            plotOptions: {
               pie: {
                  donut: {
                     size: '65%',
                     labels: {
                        show: true,
                        value: {
                           fontWeight: 700
                        },
                        total: {
                           show: true,
                           label: 'Total',
                           formatter: function(w) {
                              return w.globals.seriesTotals.reduce(function(a, b) {
                                 return a + b;
                              }, 0) + ' ban';
                           }
                        }
                     }
                  }
               }
            }
         }).render();

         var locationData = @json($locationStock);
         new ApexCharts(document.querySelector('#locationStockChart'), {
            chart: {
               type: 'bar',
               height: locationData.length > 5 ? (80 + locationData.length * 30) :
               280, // Dynamic height if many locations
               events: {
                  dataPointSelection: function(e, c, cfg) {
                     openDrillDown('location', locationData[cfg.dataPointIndex].location_name);
                  }
               }
            },
            series: [{
               name: 'Stock',
               data: locationData.map(function(l) {
                  return l.current_stock;
               })
            }, {
               name: 'Capacity',
               data: locationData.map(function(l) {
                  return l.capacity;
               })
            }],
            xaxis: {
               categories: locationData.map(function(l) {
                  return l.location_name;
               })
            },
            colors: [colors.info, '#82868b'],
            plotOptions: {
               bar: {
                  horizontal: locationData.length > 5, // Switch to horizontal if many locations
                  columnWidth: '50%',
                  borderRadius: 4
               }
            }
         }).render();

         var axleAnalysisUrl = '{{ route('master_data.scrap-by-position') }}';
         var axleChart = null;

         function renderAxleChart(data) {
            var container = document.querySelector('#axleAnalysisChart');
            if (axleChart) {
               axleChart.destroy();
               axleChart = null;
            }
            if (!data || data.length === 0) {
               container.innerHTML = '<div class="text-center py-5">Belum ada data scrap</div>';
               return;
            }
            axleChart = new ApexCharts(container, {
               chart: {
                  type: 'bar',
                  height: 280,
                  events: {
                     dataPointSelection: function(e, c, cfg) {
                        openDrillDown('scrap_position', data[cfg.dataPointIndex].position);
                     }
                  }
               },
               series: [{
                  name: 'Scrap',
                  data: data.map(function(a) {
                     return a.total;
                  })
               }],
               xaxis: {
                  categories: data.map(function(a) {
                     return a.position;
                  })
               },
               colors: [colors.danger],
               plotOptions: {
                  bar: {
                     horizontal: true,
                     borderRadius: 6
                  }
               }
            });
            axleChart.render();
         }
         renderAxleChart(@json($axleAnalysis));

         function triggerAxleAjax() {
            if (axleChart) {
               axleChart.destroy();
               axleChart = null;
            }
            showChartLoading('#axleAnalysisChart', 'Memuat data posisi...');
            $.ajax({
               url: axleAnalysisUrl,
               data: {
                  start_date: globalStartDate,
                  end_date: globalEndDate,
                  size: $('#axleFilterSize').val(),
                  brand_id: $('#axleFilterBrand').val(),
                  pattern: $('#axleFilterPattern').val()
               },
               success: function(res) {
                  if (res.success) renderAxleChart(res.data);
               }
            });
         }
         $('#axleFilterSize').on('change', function() {
            updateCascadingDropdowns('#axleFilterSize', '#axleFilterBrand', '#axleFilterPattern', triggerAxleAjax);
         });
         $('#axleFilterBrand').on('change', function() {
            var size = $('#axleFilterSize').val();
            var brandId = $(this).val();
            $.ajax({
               url: filterOptionsUrl,
               data: { size: size, brand_id: brandId },
               success: function(res) {
                  if (!res.success) return;
                  var currentPattern = $('#axleFilterPattern').val();
                  $('#axleFilterPattern').empty().append('<option value="">Semua Pattern</option>');
                  res.patterns.forEach(function(p) {
                     $('#axleFilterPattern').append('<option value="' + p.name + '">' + p.name + '</option>');
                  });
                  if (currentPattern && $('#axleFilterPattern').find('option[value="' + currentPattern + '"]').length) {
                     $('#axleFilterPattern').val(currentPattern);
                  } else {
                     $('#axleFilterPattern').val('');
                  }
                  $('#axleFilterPattern').trigger('change.select2');
                  triggerAxleAjax();
               }
            });
         });
         $('#axleFilterPattern').on('change', triggerAxleAjax);

         var failureData = @json($failureDistribution);
         if (failureData.length > 0) {
            new ApexCharts(document.querySelector('#failureDonutChart'), {
               chart: {
                  type: 'donut',
                  height: 280,
                  events: {
                     dataPointSelection: function(e, c, cfg) {
                        openDrillDown('failure', failureData[cfg.dataPointIndex].label);
                     }
                  }
               },
               series: failureData.map(function(f) {
                  return f.total;
               }),
               labels: failureData.map(function(f) {
                  return f.label;
               }),
               colors: [colors.danger, colors.warning, colors.info, colors.primary, colors.secondary],
               plotOptions: {
                  pie: {
                     donut: {
                        size: '60%',
                        labels: {
                           show: true,
                           total: {
                              show: true,
                              label: 'Total'
                           }
                        }
                     }
                  }
               }
            }).render();
         }

         // =============================================
         // KPI DETAIL MODAL — Click handler
         // =============================================
         var kpiDetailUrl = '{{ route('master_data.kpi-detail') }}';
         var kpiChart1Inst = null, kpiChart2Inst = null, kpiDT = null;
         var kpiColors = [colors.primary, colors.success, colors.info, colors.warning, colors.danger, colors.secondary, '#9b59b6', '#1abc9c', '#e67e22', '#2c3e50'];

         $('.kpi-card[data-kpi-type]').on('click', function() {
            var type = $(this).data('kpi-type');
            var modal = new bootstrap.Modal($('#kpiDetailModal')[0]);
            $('#kpiDetailLoading').show();
            $('#kpiDetailContent').hide();
            $('#kpiChartsRow').hide();
            modal.show();

            // Cleanup previous
            if (kpiChart1Inst) { kpiChart1Inst.destroy(); kpiChart1Inst = null; }
            if (kpiChart2Inst) { kpiChart2Inst.destroy(); kpiChart2Inst = null; }
            if (kpiDT) { kpiDT.destroy(); kpiDT = null; }

            $.ajax({
               url: kpiDetailUrl,
               data: { type: type },
               success: function(res) {
                  if (!res.success) { $('#kpiDetailLoading').html('<p class="text-danger">Gagal memuat data</p>'); return; }
                  $('#kpiDetailTitle').html('<i class="ri-bar-chart-box-line me-2 text-primary"></i>' + res.title);
                  $('#kpiDetailTotal').text(res.total + ' data');

                  // Summary Cards
                  var scHtml = '';
                  (res.summary || []).forEach(function(s) {
                     scHtml += '<div class="col-sm-6 col-md-3"><div class="card shadow-none border-0 bg-label-' + s.color + ' bg-opacity-10"><div class="card-body py-3 px-3 text-center">';
                     scHtml += '<div class="fw-bold" style="font-size:0.7rem;text-transform:uppercase;opacity:0.7;">' + s.label + '</div>';
                     scHtml += '<div class="fw-bold mt-1" style="font-size:1.25rem;">' + s.value + '</div>';
                     if (s.pct) scHtml += '<div class="small text-muted">' + s.pct + '</div>';
                     scHtml += '</div></div></div>';
                  });
                  $('#kpiSummaryCards').html(scHtml);

                  // Charts
                  if (res.charts && res.charts.length > 0) {
                     $('#kpiChartsRow').show();
                     res.charts.forEach(function(c, idx) {
                        var elId = idx === 0 ? '#kpiChart1' : '#kpiChart2';
                        var titleId = idx === 0 ? '#kpiChart1Title' : '#kpiChart2Title';
                        $(titleId).text(c.title);
                        $(elId).empty();

                        var opts = { chart: { height: 250 }, colors: kpiColors };
                        if (c.type === 'donut') {
                           opts.chart.type = 'donut';
                           opts.labels = Array.isArray(c.labels) ? c.labels : Object.values(c.labels);
                           opts.series = Array.isArray(c.series) ? c.series.map(Number) : Object.values(c.series).map(Number);
                           opts.plotOptions = { pie: { donut: { size: '60%', labels: { show: true, total: { show: true, label: 'Total' } } } } };
                        } else if (c.type === 'stacked_bar') {
                           opts.chart.type = 'bar';
                           opts.chart.stacked = true;
                           opts.xaxis = { categories: Array.isArray(c.labels) ? c.labels : Object.values(c.labels) };
                           opts.series = [
                              { name: 'New', data: Array.isArray(c.series_new) ? c.series_new.map(Number) : Object.values(c.series_new).map(Number) },
                              { name: 'Repaired', data: Array.isArray(c.series_repaired) ? c.series_repaired.map(Number) : Object.values(c.series_repaired).map(Number) }
                           ];
                           opts.plotOptions = { bar: { horizontal: true, borderRadius: 4 } };
                        } else {
                           opts.chart.type = 'bar';
                           opts.xaxis = { categories: Array.isArray(c.labels) ? c.labels : Object.values(c.labels) };
                           opts.series = [{ name: c.title, data: Array.isArray(c.series) ? c.series.map(Number) : Object.values(c.series).map(Number) }];
                           opts.plotOptions = { bar: { horizontal: true, borderRadius: 4, dataLabels: { position: 'center' } } };
                           opts.dataLabels = { enabled: true, formatter: function(v) { return v.toLocaleString(); } };
                        }

                        var inst = new ApexCharts(document.querySelector(elId), opts);
                        inst.render();
                        if (idx === 0) kpiChart1Inst = inst; else kpiChart2Inst = inst;
                     });
                     if (res.charts.length < 2) { $('#kpiChartsRow .col-md-6:last').hide(); } else { $('#kpiChartsRow .col-md-6:last').show(); }
                  } else {
                     $('#kpiChartsRow').hide();
                  }

                  // Table
                  var headHtml = '<tr>';
                  (res.columns || []).forEach(function(col) { headHtml += '<th>' + col + '</th>'; });
                  headHtml += '</tr>';
                  $('#kpiDetailHead').html(headHtml);

                  var bodyHtml = '';
                  var dataArr = Array.isArray(res.data) ? res.data : Object.values(res.data);
                  dataArr.forEach(function(row) {
                     bodyHtml += '<tr>';
                     (res.keys || []).forEach(function(k) { bodyHtml += '<td class="small">' + (row[k] ?? '-') + '</td>'; });
                     bodyHtml += '</tr>';
                  });
                  $('#kpiDetailBody').html(bodyHtml);

                  if (dataArr.length > 0) {
                     kpiDT = $('#kpiDetailTable').DataTable({
                        pageLength: 15,
                        order: [],
                        language: { search: 'Cari:', lengthMenu: 'Tampil _MENU_', info: '_START_-_END_ dari _TOTAL_', paginate: { previous: '‹', next: '›' } },
                        dom: '<"d-flex justify-content-between align-items-center mb-2"lf>t<"d-flex justify-content-between align-items-center mt-2"ip>'
                     });
                  }

                  $('#kpiDetailLoading').hide();
                  $('#kpiDetailContent').show();
               },
               error: function() {
                  $('#kpiDetailLoading').html('<p class="text-danger">Terjadi kesalahan saat memuat data</p>');
               }
            });
         });

         // Cleanup on modal close
         $('#kpiDetailModal').on('hidden.bs.modal', function() {
            if (kpiChart1Inst) { kpiChart1Inst.destroy(); kpiChart1Inst = null; }
            if (kpiChart2Inst) { kpiChart2Inst.destroy(); kpiChart2Inst = null; }
            if (kpiDT) { kpiDT.destroy(); kpiDT = null; }
            $('#kpiSummaryCards').empty();
            $('#kpiDetailHead').empty();
            $('#kpiDetailBody').empty();
            $('#kpiChart1').empty();
            $('#kpiChart2').empty();
         });
      });
   </script>
@endsection
