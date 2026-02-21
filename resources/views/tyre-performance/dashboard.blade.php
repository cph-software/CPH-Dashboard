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
                     <i class="ri-filter-3-line me-1"></i> Filter
                  </button>
                  @if (request()->filled('start_date') || request()->filled('end_date'))
                     <a href="{{ route('master_data.dashboard') }}" class="btn btn-label-secondary btn-sm px-3 ms-1 py-2"
                        style="border-radius: 6px;" title="Reset Filter">
                        <i class="ri-refresh-line me-1"></i> Reset
                     </a>
                  @endif
               </div>
               <div class="btn-group ms-2">
                  <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle py-2 px-3 shadow-none"
                     style="border-radius: 6px;" data-bs-toggle="dropdown" aria-expanded="false">
                     <i class="ri-download-2-line me-1"></i> Export
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                     <li>
                        <h6 class="dropdown-header small text-muted text-uppercase">Raw Data Export</h6>
                     </li>
                     <li><a class="dropdown-item"
                           href="{{ route('master_data.export', ['type' => 'movements', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"><i
                              class="ri-history-line me-1"></i> Movements Raw Data</a></li>
                     <li><a class="dropdown-item"
                           href="{{ route('master_data.export', ['type' => 'failures', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"><i
                              class="ri-error-warning-line me-1"></i> Failure Analysis Data</a></li>
                     <li><a class="dropdown-item" href="{{ route('master_data.export', ['type' => 'assets']) }}"><i
                              class="ri-disc-line me-1"></i> Tyre Master List</a></li>
                     <li>
                        <hr class="dropdown-divider">
                     </li>
                     <li><a class="dropdown-item text-primary fw-bold" href="javascript:void(0);" data-bs-toggle="modal"
                           data-bs-target="#importModal"><i class="ri-upload-2-line me-1"></i>
                           Admin: Import Data</a></li>
                  </ul>
               </div>
            </form>
         </div>
      </div>

      {{-- ============================================== --}}
      {{-- ROW 1: KPI CARDS --}}
      {{-- ============================================== --}}
      <div class="row g-4 mb-4">
         {{-- Total Tyres --}}
         <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card kpi-card card-border-shadow-primary h-100">
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
            <div class="card kpi-card card-border-shadow-success h-100">
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
            <div class="card kpi-card card-border-shadow-info h-100">
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

         {{-- Avg Lifetime KM --}}
         <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card kpi-card card-border-shadow-warning h-100">
               <div class="card-body py-3">
                  <div class="d-flex align-items-center mb-2">
                     <div class="avatar avatar-sm me-2">
                        <span class="avatar-initial rounded-3 bg-label-warning"><i
                              class="icon-base ri ri-road-map-line"></i></span>
                     </div>
                     <span class="kpi-sub">Avg Lifetime</span>
                  </div>
                  <div class="kpi-number text-warning">{{ number_format($avgLifetimeKm, 0) }}</div>
                  <div class="kpi-sub mt-1">KM rata-rata</div>
               </div>
            </div>
         </div>

         {{-- Cost Per KM --}}
         <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card kpi-card card-border-shadow-secondary h-100">
               <div class="card-body py-3">
                  <div class="d-flex align-items-center mb-2">
                     <div class="avatar avatar-sm me-2">
                        <span class="avatar-initial rounded-3 bg-label-secondary"><i
                              class="icon-base ri ri-money-dollar-circle-line"></i></span>
                     </div>
                     <span class="kpi-sub">Cost / KM</span>
                  </div>
                  <div class="kpi-number">Rp {{ number_format($avgCpk, 0, ',', '.') }}</div>
                  <div class="kpi-sub mt-1">Rata-rata CPK</div>
               </div>
            </div>
         </div>

         {{-- Scrap Rate --}}
         <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card kpi-card card-border-shadow-danger h-100">
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

      {{-- ============================================== --}}
      {{-- ROW 2: CHARTS --}}
      {{-- ============================================== --}}
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
                     <span class="badge bg-label-danger rounded-pill">
                        <i class="icon-base ri ri-arrow-up-line"></i> {{ $removalsThisMonth }} Lepas
                     </span>
                  </div>
               </div>
               <div class="card-body">
                  <div id="movementTrendChart" style="min-height:300px;"></div>
               </div>
            </div>
         </div>
      </div>

      {{-- ============================================== --}}
      {{-- ROW 3: PERFORMANCE ANALYSIS (with Filters) --}}
      {{-- ============================================== --}}
      <div class="row g-4 mb-4">
         {{-- 3a. Brand Performance Comparison (with Filters) --}}
         <div class="col-xl-6">
            <div class="card chart-card h-100">
               <div class="card-header pb-0">
                  <h6 class="mb-1"><i class="icon-base ri ri-bar-chart-horizontal-line me-1 text-primary"></i> Performa
                     Brand (Avg Lifetime KM)</h6>
                  <p class="kpi-sub mb-0">Perbandingan umur rata-rata ban per brand</p>
               </div>
               <div class="card-body">
                  {{-- Filter Bar --}}
                  <div class="chart-filter-bar rounded p-3 mb-3 shadow-sm">
                     <div class="row g-3">
                        <div class="col-md-6">
                           <label class="filter-label mb-1 d-block text-primary"><i class="ri-ruler-2-line me-1"></i>
                              Size</label>
                           <select id="brandFilterSize" class="form-select select2">
                              <option value="">Semua Size</option>
                              @foreach ($filterSizes as $s)
                                 <option value="{{ $s->id }}">{{ $s->size }}</option>
                              @endforeach
                           </select>
                        </div>

                        <div class="col-md-6">
                           <label class="filter-label mb-1 d-block text-primary"><i class="ri-road-map-line me-1"></i>
                              Pattern</label>
                           <select id="brandFilterPattern" class="form-select select2">
                              <option value="">Semua Pattern</option>
                              @foreach ($filterPatterns as $p)
                                 <option value="{{ $p->id }}">{{ $p->name }}</option>
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

         {{-- 3b. Cost Per KM by Brand (Dedicated Chart with Filters) --}}
         <div class="col-xl-6">
            <div class="card chart-card h-100">
               <div class="card-header pb-0">
                  <h6 class="mb-1"><i class="icon-base ri ri-money-dollar-circle-line me-1 text-warning"></i> Cost Per
                     KM by Brand (CPK)</h6>
                  <p class="kpi-sub mb-0">Perbandingan biaya per KM antar brand</p>
               </div>
               <div class="card-body">
                  {{-- Filter Bar --}}
                  <div class="chart-filter-bar rounded p-3 mb-3 shadow-sm">
                     <div class="row g-3">
                        <div class="col-md-6">
                           <label class="filter-label mb-1 d-block text-warning"><i class="ri-ruler-2-line me-1"></i>
                              Size</label>
                           <select id="cpkFilterSize" class="form-select select2">
                              <option value="">Semua Size</option>
                              @foreach ($filterSizes as $s)
                                 <option value="{{ $s->id }}">{{ $s->size }}</option>
                              @endforeach
                           </select>
                        </div>

                        <div class="col-md-6">
                           <label class="filter-label mb-1 d-block text-warning"><i class="ri-road-map-line me-1"></i>
                              Pattern</label>
                           <select id="cpkFilterPattern" class="form-select select2">
                              <option value="">Semua Pattern</option>
                              @foreach ($filterPatterns as $p)
                                 <option value="{{ $p->id }}">{{ $p->name }}</option>
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

      {{-- ============================================== --}}
      {{-- ROW 4: FLEET HEALTH & STOCK --}}
      {{-- ============================================== --}}
      <div class="row g-4 mb-4">
         {{-- 4a. Fleet Health (Percentage Based) --}}
         <div class="col-xl-6">
            <div class="card chart-card h-100">
               <div class="card-header pb-0">
                  <div class="d-flex justify-content-between align-items-start">
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
               </div>
               <div class="card-body">
                  <div id="fleetHealthChart" style="min-height:280px;"></div>
                  <div class="d-flex justify-content-between mt-2">
                     <span class="badge bg-label-primary sample-badge">
                        <i class="ri-database-2-line me-1"></i>{{ $fleetHealthData['totalWithData'] }} ban terukur
                     </span>
                     @if ($fleetHealthData['noDataCount'] > 0)
                        <span class="badge bg-label-secondary sample-badge">
                           <i class="ri-question-line me-1"></i>{{ $fleetHealthData['noDataCount'] }} ban belum
                           terukur
                        </span>
                     @endif
                  </div>
               </div>
            </div>
         </div>

         {{-- 4b. Stock by Location --}}
         <div class="col-xl-6">
            <div class="card chart-card h-100">
               <div class="card-header pb-0">
                  <h6 class="mb-1"><i class="icon-base ri ri-building-2-line me-1 text-primary"></i> Stok Ban per
                     Lokasi</h6>
                  <p class="kpi-sub mb-0">Kapasitas vs Stok Terisi</p>
               </div>
               <div class="card-body">
                  <div id="locationStockChart" style="min-height:280px;"></div>
               </div>
            </div>
         </div>
      </div>

      {{-- ============================================== --}}
      {{-- ROW 5: AXLE ANALYSIS & FAILURE --}}
      {{-- ============================================== --}}
      <div class="row g-4 mb-4">
         {{-- 5a. Axle Analysis (Scrap Frequency) --}}
         <div class="col-xl-6">
            <div class="card chart-card h-100">
               <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                  <div>
                     <h6 class="mb-1"><i class="icon-base ri ri-error-warning-line me-1 text-danger"></i> Scrap by
                        Position
                        Analysis</h6>
                     <p class="kpi-sub mb-0">Frekuensi Scrap Ban per Posisi</p>
                  </div>
                  <div class="ms-auto" id="axleTotalScrapBadge"></div>
               </div>
               <div class="card-body">
                  {{-- Filter Bar --}}
                  <div class="chart-filter-bar rounded p-3 mb-3 shadow-sm">
                     <div class="row g-3">
                        <div class="col-md-6">
                           <label class="filter-label mb-1 d-block text-danger"><i class="ri-ruler-2-line me-1"></i>
                              Size</label>
                           <select id="axleFilterSize" class="form-select select2">
                              <option value="">Semua Size</option>
                              @foreach ($filterSizes as $s)
                                 <option value="{{ $s->id }}">{{ $s->size }}</option>
                              @endforeach
                           </select>
                        </div>

                        <div class="col-md-6">
                           <label class="filter-label mb-1 d-block text-danger"><i class="ri-road-map-line me-1"></i>
                              Pattern</label>
                           <select id="axleFilterPattern" class="form-select select2">
                              <option value="">Semua Pattern</option>
                              @foreach ($filterPatterns as $p)
                                 <option value="{{ $p->id }}">{{ $p->name }}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                  </div>
                  <div id="axleAnalysisChart" style="min-height:280px;"></div>
                  <div id="axleAnalysisSample" class="text-center mt-1"></div>
               </div>
            </div>
         </div>

         {{-- 5b. Failure Code Distribution --}}
         <div class="col-xl-6">
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

      {{-- ============================================== --}}
      {{-- ROW 6: ALERTS & OPERATIONAL TABLE --}}
      {{-- ============================================== --}}
      <div class="row g-4 mb-4">
         {{-- 6a. Low RTD Alert --}}
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
                                                style="width:{{ $pctRemaining }}%;background:{{ $barColor }}">
                                             </div>
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

            {{-- ADMIN ACTION GUIDE --}}
            <div class="card border-primary shadow-none bg-label-primary">
               <div class="card-body">
                  <h6 class="fw-bold mb-3"><i class="ri-lightbulb-flash-line me-2"></i> Panduan Tindakan Admin</h6>
                  <div class="d-flex flex-column gap-3">
                     <div class="d-flex align-items-start">
                        <div class="badge bg-primary rounded p-1 me-2"><i class="ri-tools-line"></i></div>
                        <div>
                           <p class="mb-0 small fw-bold">1. Diagnosa & Pelepasan</p>
                           <p class="mb-0 kpi-sub">Jika % sisa < 20%, segera lakukan <strong>Pelepasan</strong>
                                 melalui menu
                                 <em>Movement</em>. Pilih <strong>Failure Code</strong> yang sesuai.
                           </p>
                        </div>
                     </div>
                     <div class="d-flex align-items-start border-top pt-2">
                        <div class="badge bg-primary rounded p-1 me-2"><i class="ri-refresh-line"></i></div>
                        <div>
                           <p class="mb-0 small fw-bold">2. Update Status Ban</p>
                           <p class="mb-0 kpi-sub">Setelah dilepas, admin <strong>WAJIB</strong> mengupdate status ban
                              di
                              <em>Master Tyre</em> menjadi <strong>Scrap</strong> atau
                              <strong>Repaired</strong>.
                           </p>
                        </div>
                     </div>
                     <div class="d-flex align-items-start border-top pt-2">
                        <div class="badge bg-primary rounded p-1 me-2"><i class="ri-history-line"></i></div>
                        <div>
                           <p class="mb-0 small fw-bold">3. Pencatatan Otomatis</p>
                           <p class="mb-0 kpi-sub">Setiap pergerakan dan update status akan <strong>tercatat
                                 otomatis</strong> di log <em>Tyre Performance</em>.</p>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         {{-- 6b. Recent Movements --}}
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
                                    <td class="small">
                                       {{ \Carbon\Carbon::parse($m->movement_date)->format('d/m/Y') }}
                                    </td>
                                    <td>
                                       <span
                                          class="badge bg-label-{{ $m->movement_type === 'Installation' ? 'success' : 'danger' }} rounded-pill"
                                          style="font-size:.65rem">
                                          {{ $m->movement_type === 'Installation' ? 'Pasang' : 'Lepas' }}
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

   {{-- ============================================== --}}
   {{-- DRILL-DOWN MODAL (Universal) --}}
   {{-- ============================================== --}}
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

         // ==========================================
         // Color Palette
         // ==========================================
         const colors = {
            primary: '#7367f0',
            success: '#28c76f',
            danger: '#ea5455',
            warning: '#ff9f43',
            info: '#00cfe8',
            secondary: '#a8aaae',
            dark: '#4b4b4b'
         };

         // Initialize Select2
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

         // ==========================================
         // DRILL-DOWN HELPER FUNCTION
         // ==========================================
         const drillDownUrl = '{{ route('master_data.drill-down') }}';
         let drillDownDT = null;

         function openDrillDown(type, value, extraParams) {
            const modal = new bootstrap.Modal(document.getElementById('drillDownModal'));
            document.getElementById('drillDownLoading').style.display = 'block';
            document.getElementById('drillDownContent').style.display = 'none';
            document.getElementById('drillDownEmpty').style.display = 'none';
            document.getElementById('drillDownTitleText').textContent = 'Memuat...';

            // Destroy previous DataTable instance
            if (drillDownDT) {
               drillDownDT.destroy();
               drillDownDT = null;
            }

            modal.show();

            // Merge base params with extra filter params
            let ajaxData = {
               type: type,
               value: value
            };
            if (extraParams) {
               Object.assign(ajaxData, extraParams);
            }

            $.ajax({
               url: drillDownUrl,
               data: ajaxData,
               dataType: 'json',
               success: function(res) {
                  document.getElementById('drillDownLoading').style.display = 'none';

                  if (!res.data || res.data.length === 0) {
                     document.getElementById('drillDownEmpty').style.display = 'block';
                     document.getElementById('drillDownTitleText').textContent = res.title ||
                        'Detail Data';
                     return;
                  }

                  document.getElementById('drillDownContent').style.display = 'block';
                  document.getElementById('drillDownTitleText').textContent = res.title ||
                     'Detail Data';
                  document.getElementById('drillDownCount').textContent = res.total +
                     ' data ditemukan';

                  // Link
                  const linkEl = document.getElementById('drillDownLink');
                  if (res.link) {
                     linkEl.href = res.link;
                     linkEl.style.display = 'inline-block';
                  } else {
                     linkEl.style.display = 'none';
                  }

                  // Build table header
                  let headHtml = '<tr>';
                  res.columns.forEach(col => headHtml += '<th>' + col + '</th>');
                  if (res.data[0] && res.data[0].id) headHtml += '<th>Aksi</th>';
                  headHtml += '</tr>';
                  document.getElementById('drillDownHead').innerHTML = headHtml;

                  // Build table body
                  let bodyHtml = '';
                  res.data.forEach(row => {
                     bodyHtml += '<tr>';
                     res.keys.forEach(key => {
                        let val = row[key] ?? '-';
                        if (key === 'status') {
                           let cls = 'secondary';
                           if (val === 'Installed') cls = 'success';
                           else if (val === 'New') cls = 'primary';
                           else if (val === 'Retread') cls = 'info';
                           else if (val === 'Scrap') cls = 'danger';
                           else if (val === 'Repaired') cls = 'warning';
                           val =
                              '<span class="badge bg-label-' + cls +
                              ' rounded-pill">' +
                              val + '</span>';
                        }
                        bodyHtml += '<td>' + val + '</td>';
                     });
                     if (row.id) {
                        bodyHtml +=
                           '<td><a href="/master_data_tyre/master_tyre/' + row.id +
                           '" class="btn btn-sm btn-icon btn-text-primary" title="Detail"><i class="icon-base ri ri-eye-line"></i></a></td>';
                     }
                     bodyHtml += '</tr>';
                  });
                  document.getElementById('drillDownBody').innerHTML = bodyHtml;

                  // Init DataTable
                  drillDownDT = $('#drillDownTable').DataTable({
                     paging: true,
                     pageLength: 10,
                     searching: true,
                     ordering: true,
                     info: true,
                     language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                        paginate: {
                           previous: '&laquo;',
                           next: '&raquo;'
                        },
                        zeroRecords: 'Tidak ada data ditemukan',
                     }
                  });
               },
               error: function() {
                  document.getElementById('drillDownLoading').style.display = 'none';
                  document.getElementById('drillDownEmpty').style.display = 'block';
                  document.getElementById('drillDownTitleText').textContent = 'Error memuat data';
               }
            });
         }

         // ==========================================
         // 1. STATUS DONUT CHART
         // ==========================================
         const statusData = @json($statusDistribution);
         const statusLabels = Object.keys(statusData);
         const statusValues = Object.values(statusData);
         const statusColors = statusLabels.map(s => {
            if (s.startsWith('New')) return colors.primary;
            if (s.startsWith('Retread')) return colors.info;

            switch (s) {
               case 'Installed':
                  return colors.success;
               case 'Repaired':
                  return colors.warning;
               case 'Scrap':
                  return colors.danger;
               default:
                  return colors.secondary;
            }
         });

         new ApexCharts(document.querySelector('#statusDonutChart'), {
            chart: {
               type: 'donut',
               height: 280,
               events: {
                  dataPointSelection: function(event, chartContext, config) {
                     const label = statusLabels[config.dataPointIndex];
                     openDrillDown('status', label);
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
                        name: {
                           show: true
                        },
                        value: {
                           show: true,
                           fontSize: '1.5rem',
                           fontWeight: 700,
                           formatter: val => val + ' ban'
                        },
                        total: {
                           show: true,
                           label: 'Total',
                           fontSize: '0.8rem',
                           formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b,
                              0) + ' ban'
                        }
                     }
                  }
               }
            },
            legend: {
               position: 'bottom',
               fontSize: '12px'
            },
            dataLabels: {
               enabled: false
            }
         }).render();

         // ==========================================
         // 2. MONTHLY MOVEMENT TREND CHART
         // ==========================================
         const monthlyData = @json($monthlyMovements);

         new ApexCharts(document.querySelector('#movementTrendChart'), {
            chart: {
               type: 'bar',
               height: 300,
               toolbar: {
                  show: false
               },
               events: {
                  dataPointSelection: function(event, chartContext, config) {
                     const monthLabel = monthlyData[config.dataPointIndex].month;
                     const seriesName = config.seriesIndex === 0 ? 'Installation' : 'Removal';
                     openDrillDown('movement', monthLabel + '|' + seriesName);
                  }
               }
            },
            series: [{
                  name: 'Pemasangan',
                  data: monthlyData.map(m => m.installations)
               },
               {
                  name: 'Pelepasan',
                  data: monthlyData.map(m => m.removals)
               }
            ],
            xaxis: {
               categories: monthlyData.map(m => m.month),
               title: {
                  text: 'Bulan',
                  style: {
                     fontWeight: 600
                  }
               },
               labels: {
                  style: {
                     fontSize: '11px'
                  }
               }
            },
            yaxis: {
               labels: {
                  style: {
                     fontSize: '11px'
                  }
               },
               title: {
                  text: 'Jumlah Ban',
                  style: {
                     fontSize: '12px',
                     fontWeight: 600
                  }
               }
            },
            colors: [colors.success, colors.danger],
            plotOptions: {
               bar: {
                  columnWidth: '40%',
                  borderRadius: 4,
                  dataLabels: {
                     position: 'top'
                  }
               }
            },
            dataLabels: {
               enabled: true,
               offsetY: -18,
               style: {
                  fontSize: '11px',
                  fontWeight: 600
               }
            },
            legend: {
               position: 'top',
               horizontalAlign: 'right',
               fontSize: '12px'
            },
            grid: {
               borderColor: '#f1f1f1',
               strokeDashArray: 3
            }
         }).render();

         // ==========================================
         // 3. BRAND PERFORMANCE CHART (with AJAX Filters)
         // ==========================================
         const brandPerformanceUrl = '{{ route('master_data.brand-performance') }}';
         let brandChart = null;

         function renderBrandChart(data) {
            if (brandChart) {
               brandChart.destroy();
               brandChart = null;
            }

            const container = document.querySelector('#brandPerformanceChart');
            const sampleContainer = document.querySelector('#brandPerformanceSample');

            if (!data || data.length === 0) {
               container.innerHTML =
                  '<div class="text-center text-muted py-5"><i class="icon-base ri ri-bar-chart-line ri-3x opacity-25 d-block mb-2"></i><p>Belum ada data lifetime untuk filter ini</p></div>';
               sampleContainer.innerHTML = '';
               return;
            }

            const totalSample = data.reduce((sum, b) => sum + b.count, 0);
            sampleContainer.innerHTML =
               '<span class="badge bg-label-primary sample-badge"><i class="ri-database-2-line me-1"></i>Total Entry: ' +
               totalSample + ' ban</span>';

            brandChart = new ApexCharts(container, {
               chart: {
                  type: 'bar',
                  height: 280,
                  toolbar: {
                     show: false
                  },
                  events: {
                     dataPointSelection: function(event, chartContext, config) {
                        const brandName = data[config.dataPointIndex].brand;
                        openDrillDown('brand_performance', brandName, {
                           size_id: $('#brandFilterSize').val(),
                           pattern_id: $('#brandFilterPattern').val()
                        });
                     }
                  }
               },
               series: [{
                  name: 'Avg KM',
                  data: data.map(b => b.avg_km)
               }],
               xaxis: {
                  categories: data.map(b => b.brand),
                  title: {
                     text: 'Rata-rata Lifetime (KM)',
                     style: {
                        fontWeight: 600
                     }
                  },
                  labels: {
                     style: {
                        fontSize: '12px'
                     }
                  }
               },
               yaxis: {
                  title: {
                     text: 'Brand',
                     style: {
                        fontWeight: 600
                     }
                  }
               },
               colors: [colors.primary],
               plotOptions: {
                  bar: {
                     horizontal: true,
                     borderRadius: 6,
                     barHeight: '50%',
                     dataLabels: {
                        position: 'center'
                     }
                  }
               },
               dataLabels: {
                  enabled: true,
                  formatter: function(val, opt) {
                     const count = data[opt.dataPointIndex].count;
                     return val.toLocaleString() + ' km (' + count + ' ban)';
                  },
                  style: {
                     fontSize: '11px',
                     fontWeight: 600,
                     colors: ['#fff']
                  }
               },
               grid: {
                  borderColor: '#f1f1f1',
                  strokeDashArray: 3
               },
               tooltip: {
                  y: {
                     formatter: function(val, opt) {
                        const idx = opt.dataPointIndex;
                        const count = data[idx].count;
                        return val.toLocaleString() + ' km (sample: ' + count + ' ban)';
                     }
                  }
               }
            });
            brandChart.render();
         }

         // Initial render
         renderBrandChart(@json($brandPerformance));

         // Filter event listeners for Brand Performance
         function loadBrandPerformance() {
            const sizeId = $('#brandFilterSize').val();
            const patternId = $('#brandFilterPattern').val();

            $.ajax({
               url: brandPerformanceUrl,
               data: {
                  size_id: sizeId,
                  type: type,
                  pattern_id: patternId
               },
               dataType: 'json',
               success: function(res) {
                  if (res.success) {
                     renderBrandChart(res.data);
                  }
               }
            });
         }

         $('#brandFilterSize, #brandFilterPattern').on('change', loadBrandPerformance);

         // ==========================================
         // 4. CPK BY BRAND CHART (Dedicated, with AJAX Filters)
         // ==========================================
         const cpkByBrandUrl = '{{ route('master_data.cpk-by-brand') }}';
         let cpkChart = null;

         function renderCpkChart(data) {
            if (cpkChart) {
               cpkChart.destroy();
               cpkChart = null;
            }

            const container = document.querySelector('#cpkByBrandChart');
            const sampleContainer = document.querySelector('#cpkBrandSample');

            if (!data || data.length === 0) {
               container.innerHTML =
                  '<div class="text-center text-muted py-5"><i class="icon-base ri ri-money-dollar-circle-line ri-3x opacity-25 d-block mb-2"></i><p>Belum ada data CPK untuk filter ini</p></div>';
               sampleContainer.innerHTML = '';
               return;
            }

            // Sort by CPK ascending (cheapest first)
            data.sort((a, b) => a.cpk - b.cpk);

            const totalSample = data.reduce((sum, b) => sum + b.count, 0);
            sampleContainer.innerHTML =
               '<span class="badge bg-label-warning sample-badge"><i class="ri-database-2-line me-1"></i>Total Entry: ' +
               totalSample + ' ban</span>';

            cpkChart = new ApexCharts(container, {
               chart: {
                  type: 'bar',
                  height: 280,
                  toolbar: {
                     show: false
                  },
                  events: {
                     dataPointSelection: function(event, chartContext, config) {
                        const brandName = data[config.dataPointIndex].brand;
                        openDrillDown('brand_cpk', brandName, {
                           size_id: $('#cpkFilterSize').val(),
                           pattern_id: $('#cpkFilterPattern').val()
                        });
                     }
                  }
               },
               series: [{
                  name: 'CPK (Rp/km)',
                  data: data.map(b => b.cpk)
               }],
               xaxis: {
                  categories: data.map(b => b.brand),
                  title: {
                     text: 'Biaya per KM (Rp/km)',
                     style: {
                        fontWeight: 600
                     }
                  },
                  labels: {
                     style: {
                        fontSize: '12px'
                     }
                  }
               },
               yaxis: {
                  title: {
                     text: 'Brand',
                     style: {
                        fontWeight: 600
                     }
                  }
               },
               colors: [colors.warning],
               plotOptions: {
                  bar: {
                     horizontal: true,
                     borderRadius: 6,
                     barHeight: '50%',
                     dataLabels: {
                        position: 'center'
                     }
                  }
               },
               dataLabels: {
                  enabled: true,
                  formatter: function(val, opt) {
                     const count = data[opt.dataPointIndex].count;
                     return 'Rp ' + val.toLocaleString() + '/km (' + count + ' ban)';
                  },
                  style: {
                     fontSize: '11px',
                     fontWeight: 600,
                     colors: ['#fff']
                  }
               },
               grid: {
                  borderColor: '#f1f1f1',
                  strokeDashArray: 3
               },
               tooltip: {
                  y: {
                     formatter: function(val, opt) {
                        const idx = opt.dataPointIndex;
                        const count = data[idx].count;
                        return 'Rp ' + val.toLocaleString() + '/km (sample: ' + count + ' ban)';
                     }
                  }
               }
            });
            cpkChart.render();
         }

         // Initial render
         renderCpkChart(@json($cpkByBrand));

         // Filter event listeners for CPK
         function loadCpkByBrand() {
            const sizeId = $('#cpkFilterSize').val();
            const patternId = $('#cpkFilterPattern').val();

            $.ajax({
               url: cpkByBrandUrl,
               data: {
                  size_id: sizeId,
                  type: type,
                  pattern_id: patternId
               },
               dataType: 'json',
               success: function(res) {
                  if (res.success) {
                     renderCpkChart(res.data);
                  }
               }
            });
         }

         $('#cpkFilterSize, #cpkFilterPattern').on('change', loadCpkByBrand);

         // ==========================================
         // 5. FLEET HEALTH CHART (Percentage Based)
         // ==========================================
         const fleetHealthData = @json($fleetHealthData);
         const healthCategories = fleetHealthData.categories;
         const healthLabels = Object.keys(healthCategories);
         const healthValues = Object.values(healthCategories);

         new ApexCharts(document.querySelector('#fleetHealthChart'), {
            chart: {
               type: 'donut',
               height: 280,
               events: {
                  dataPointSelection: function(event, chartContext, config) {
                     const label = healthLabels[config.dataPointIndex];
                     openDrillDown('rtd', label);
                  }
               }
            },
            series: healthValues,
            labels: healthLabels,
            colors: [colors.danger, colors.warning, colors.info, colors.success],
            plotOptions: {
               pie: {
                  donut: {
                     size: '65%',
                     labels: {
                        show: true,
                        name: {
                           show: true,
                           fontSize: '12px'
                        },
                        value: {
                           show: true,
                           fontSize: '1.5rem',
                           fontWeight: 700,
                           formatter: val => val + ' ban'
                        },
                        total: {
                           show: true,
                           label: 'Total Terukur',
                           fontSize: '0.75rem',
                           formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b,
                              0) + ' ban'
                        }
                     }
                  }
               }
            },
            legend: {
               position: 'bottom',
               fontSize: '11px'
            },
            dataLabels: {
               enabled: false
            }
         }).render();

         // ==========================================
         // 6. LOCATION STOCK CHART (Grouped Bar)
         // ==========================================
         const locationData = @json($locationStock);

         new ApexCharts(document.querySelector('#locationStockChart'), {
            chart: {
               type: 'bar',
               height: 280,
               toolbar: {
                  show: false
               },
               events: {
                  dataPointSelection: function(event, chartContext, config) {
                     const locName = locationData[config.dataPointIndex].location_name;
                     openDrillDown('location', locName);
                  }
               }
            },
            series: [{
                  name: 'Current Stock',
                  data: locationData.map(l => l.current_stock)
               },
               {
                  name: 'Capacity',
                  data: locationData.map(l => l.capacity)
               }
            ],
            xaxis: {
               categories: locationData.map(l => l.location_name),
               title: {
                  text: 'Lokasi Gudang',
                  style: {
                     fontWeight: 600
                  }
               },
               labels: {
                  style: {
                     fontSize: '11px'
                  }
               }
            },
            yaxis: {
               title: {
                  text: 'Jumlah Ban',
                  style: {
                     fontWeight: 600
                  }
               }
            },
            colors: [colors.info, '#82868b'],
            plotOptions: {
               bar: {
                  columnWidth: '50%',
                  borderRadius: 4,
               }
            },
            dataLabels: {
               enabled: false
            },
            legend: {
               position: 'top',
               horizontalAlign: 'right',
               fontSize: '12px'
            },
            grid: {
               borderColor: '#f1f1f1',
               strokeDashArray: 3
            }
         }).render();

         // ==========================================
         // 7. FAILURE CODE DONUT CHART
         // ==========================================
         const failureData = @json($failureDistribution);

         if (failureData.length > 0) {
            new ApexCharts(document.querySelector('#failureDonutChart'), {
               chart: {
                  type: 'donut',
                  height: 280,
                  events: {
                     dataPointSelection: function(event, chartContext, config) {
                        const label = failureData[config.dataPointIndex].label;
                        openDrillDown('failure', label);
                     }
                  }
               },
               series: failureData.map(f => f.total),
               labels: failureData.map(f => f.label),
               colors: [colors.danger, colors.warning, colors.info, colors.primary, colors.secondary],
               plotOptions: {
                  pie: {
                     donut: {
                        size: '60%',
                        labels: {
                           show: true,
                           name: {
                              show: true,
                              fontSize: '11px'
                           },
                           value: {
                              show: true,
                              fontSize: '1.2rem',
                              fontWeight: 700
                           },
                           total: {
                              show: true,
                              label: 'Total Removal',
                              formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                           }
                        }
                     }
                  }
               },
               legend: {
                  position: 'bottom',
                  fontSize: '11px'
               },
               dataLabels: {
                  enabled: false
               }
            }).render();
         }

         // ==========================================
         // 7. AXLE ANALYSIS CHART (Scrap by Position)
         // ==========================================
         const axleAnalysisUrl = '{{ route('master_data.scrap-by-position') }}';
         let axleChart = null;

         function renderAxleChart(data) {
            if (axleChart) {
               axleChart.destroy();
               axleChart = null;
            }

            const container = document.querySelector('#axleAnalysisChart');
            const totalBadgeContainer = document.querySelector('#axleTotalScrapBadge');

            if (!data || data.length === 0) {
               container.innerHTML =
                  '<div class="text-center text-muted py-5"><i class="icon-base ri ri-error-warning-line ri-3x opacity-25 d-block mb-2"></i><p>Belum ada data scrap untuk filter ini</p></div>';
               totalBadgeContainer.innerHTML = '';
               return;
            }

            const totalScrap = data.reduce((sum, item) => sum + item.total, 0);
            totalBadgeContainer.innerHTML =
               '<span class="badge bg-danger rounded-pill shadow-sm py-2 px-3 fw-bold"><i class="ri-delete-bin-line me-1"></i> Total: ' +
               totalScrap + ' Ban Scrap</span>';

            axleChart = new ApexCharts(container, {
               chart: {
                  type: 'bar',
                  height: 350,
                  toolbar: {
                     show: false
                  },
                  events: {
                     dataPointSelection: function(event, chartContext, config) {
                        const position = data[config.dataPointIndex].position;
                        openDrillDown('scrap_position', position, {
                           size_id: $('#axleFilterSize').val(),
                           pattern_id: $('#axleFilterPattern').val(),
                           start_date: $('input[name="start_date"]').val(),
                           end_date: $('input[name="end_date"]').val()
                        });
                     }
                  }
               },
               series: [{
                  name: 'Jumlah Scrap',
                  data: data.map(item => item.total)
               }],
               xaxis: {
                  categories: data.map(item => item.position),
                  title: {
                     text: 'Scrap Quantity',
                     style: {
                        fontWeight: 600
                     }
                  },
                  labels: {
                     style: {
                        fontSize: '12px'
                     }
                  }
               },
               yaxis: {
                  title: {
                     text: 'Wheel Position',
                     style: {
                        fontWeight: 600
                     }
                  }
               },
               colors: [colors.danger],
               plotOptions: {
                  bar: {
                     horizontal: true,
                     borderRadius: 6,
                     barHeight: '70%',
                     dataLabels: {
                        position: 'center'
                     }
                  }
               },
               dataLabels: {
                  enabled: true,
                  formatter: (val) => val + ' ban',
                  style: {
                     fontSize: '11px',
                     fontWeight: 600,
                     colors: ['#fff']
                  }
               },
               grid: {
                  borderColor: '#f1f1f1',
                  strokeDashArray: 3
               },
               tooltip: {
                  y: {
                     formatter: (val) => val + ' ban'
                  }
               }
            });
            axleChart.render();
         }

         // Initial render
         renderAxleChart(@json($axleAnalysis));

         // Filter event listeners for Axle
         function loadAxleAnalysis() {
            const sizeId = $('#axleFilterSize').val();
            const patternId = $('#axleFilterPattern').val();
            const startDate = $('input[name="start_date"]').val();
            const endDate = $('input[name="end_date"]').val();

            $.ajax({
               url: axleAnalysisUrl,
               data: {
                  size_id: sizeId,
                  type: type,
                  pattern_id: patternId,
                  start_date: startDate,
                  end_date: endDate
               },
               dataType: 'json',
               success: function(res) {
                  if (res.success) {
                     renderAxleChart(res.data);
                  }
               }
            });
         }

         $('#axleFilterSize, #axleFilterPattern').on('change', loadAxleAnalysis);

      });
   </script>
@endsection
