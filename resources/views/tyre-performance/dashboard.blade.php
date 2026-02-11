@extends('layouts.admin')

@section('title', 'Tyre Performance Dashboard')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
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
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">

      {{-- Page Header --}}
      <div class="d-flex justify-content-between align-items-center mb-4">
         <div>
            <h4 class="fw-bold mb-1"><i class="icon-base ri ri-dashboard-3-line me-2 text-primary"></i>Tyre Performance
               Dashboard</h4>
            <p class="text-muted mb-0 small">Overview real-time performa ban di seluruh unit kendaraan</p>
         </div>
         <div>
            <span class="badge bg-label-secondary rounded-pill">
               <i class="icon-base ri ri-time-line me-1"></i>Data per {{ now()->format('d M Y H:i') }}
            </span>
         </div>
      </div>

      {{-- ============================================== --}}
      {{-- ROW 1: KPI SUMMARY CARDS (6 cards) --}}
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
                  <h6 class="mb-1"><i class="icon-base ri ri-pie-chart-line me-1 text-primary"></i> Distribusi Status Ban
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
      {{-- ROW 3: PERFORMANCE ANALYSIS --}}
      {{-- ============================================== --}}
      <div class="row g-4 mb-4">
         {{-- 3a. Brand Performance Comparison --}}
         <div class="col-xl-6">
            <div class="card chart-card h-100">
               <div class="card-header pb-0">
                  <h6 class="mb-1"><i class="icon-base ri ri-bar-chart-horizontal-line me-1 text-primary"></i> Performa
                     Brand (Avg
                     Lifetime KM)</h6>
                  <p class="kpi-sub mb-0">Perbandingan umur rata-rata ban per brand</p>
               </div>
               <div class="card-body">
                  <div id="brandPerformanceChart" style="min-height:280px;"></div>
               </div>
            </div>
         </div>

         {{-- 3b. Stock by Location --}}
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
      {{-- ROW 4: ALERTS & OPERATIONAL TABLE --}}
      {{-- ============================================== --}}
      <div class="row g-4 mb-4">
         {{-- 4a. Low RTD Alert --}}
         <div class="col-xl-5">
            <div class="card h-100">
               <div class="card-header pb-2 d-flex justify-content-between align-items-center">
                  <div>
                     <h6 class="mb-1"><i class="icon-base ri ri-alarm-warning-line me-1 text-danger"></i> Ban Perlu
                        Perhatian</h6>
                     <p class="kpi-sub mb-0">Ban dengan RTD terendah (terpasang)</p>
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
                                 <th>Wear</th>
                              </tr>
                           </thead>
                           <tbody>
                              @foreach ($lowRtdTyres as $t)
                                 @php
                                    $otd = $t->initial_tread_depth ?? 20;
                                    $rtd = $t->current_tread_depth ?? 0;
                                    $wearPct = $otd > 0 ? round((($otd - $rtd) / $otd) * 100, 0) : 0;
                                    $barColor = $wearPct > 80 ? '#ea5455' : ($wearPct > 60 ? '#ff9f43' : '#28c76f');
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
                                          class="fw-bold {{ $rtd < 5 ? 'text-danger' : ($rtd < 8 ? 'text-warning' : 'text-success') }}">
                                          {{ $rtd }} mm
                                       </span>
                                    </td>
                                    <td style="min-width:80px">
                                       <div class="d-flex align-items-center">
                                          <span class="small me-2">{{ $wearPct }}%</span>
                                          <div class="rtd-bar flex-grow-1">
                                             <div class="rtd-bar-inner"
                                                style="width:{{ $wearPct }}%;background:{{ $barColor }}"></div>
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
         </div>

         {{-- 4b. Failure Code Distribution + Recent Movements --}}
         <div class="col-xl-7">
            <div class="card h-100">
               <div class="card-header pb-0">
                  <ul class="nav nav-tabs card-header-tabs" role="tablist">
                     <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#recentTab" role="tab">
                           <i class="icon-base ri ri-history-line me-1"></i> Aktivitas Terbaru
                        </a>
                     </li>
                     <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#failureTab" role="tab">
                           <i class="icon-base ri ri-error-warning-line me-1"></i> Penyebab Lepas
                        </a>
                     </li>
                  </ul>
               </div>
               <div class="card-body">
                  <div class="tab-content">
                     {{-- Recent Movements Tab --}}
                     <div class="tab-pane fade show active" id="recentTab" role="tabpanel">
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
                                             {{ \Carbon\Carbon::parse($m->movement_date)->format('d/m/Y') }}</td>
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

                     {{-- Failure Code Tab --}}
                     <div class="tab-pane fade" id="failureTab" role="tabpanel">
                        @if ($failureDistribution->count() > 0)
                           <div id="failureDonutChart" style="min-height: 280px;"></div>
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

         // ==========================================
         // DRILL-DOWN HELPER FUNCTION
         // ==========================================
         const drillDownUrl = '{{ route('tyre_performance.drill-down') }}';
         let drillDownDT = null;

         function openDrillDown(type, value) {
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

            $.ajax({
               url: drillDownUrl,
               data: {
                  type: type,
                  value: value
               },
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
                  // Add action column for types with tyre id
                  if (res.data[0] && res.data[0].id) headHtml += '<th>Aksi</th>';
                  headHtml += '</tr>';
                  document.getElementById('drillDownHead').innerHTML = headHtml;

                  // Build table body
                  let bodyHtml = '';
                  res.data.forEach(row => {
                     bodyHtml += '<tr>';
                     res.keys.forEach(key => {
                        let val = row[key] ?? '-';
                        // Color code status badges
                        if (key === 'status') {
                           let cls = val === 'Installed' ? 'success' : (val === 'New' ? 'info' :
                              (val === 'Scrap' ? 'danger' : 'warning'));
                           val = '<span class="badge bg-label-' + cls + ' rounded-pill">' +
                              val + '</span>';
                        }
                        bodyHtml += '<td>' + val + '</td>';
                     });
                     // View detail link
                     if (row.id) {
                        bodyHtml += '<td><a href="/tyre_performance/master_tyre/' + row.id +
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
            switch (s) {
               case 'Installed':
                  return colors.success;
               case 'New':
                  return colors.info;
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
                           formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0) + ' ban'
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
                  text: 'Jumlah',
                  style: {
                     fontSize: '12px'
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
         // 3. BRAND PERFORMANCE CHART (Horizontal Bar)
         // ==========================================
         const brandData = @json($brandPerformance);

         if (brandData.length > 0) {
            new ApexCharts(document.querySelector('#brandPerformanceChart'), {
               chart: {
                  type: 'bar',
                  height: 280,
                  toolbar: {
                     show: false
                  },
                  events: {
                     dataPointSelection: function(event, chartContext, config) {
                        const brandName = brandData[config.dataPointIndex].brand;
                        openDrillDown('brand', brandName);
                     }
                  }
               },
               series: [{
                  name: 'Avg KM',
                  data: brandData.map(b => b.avg_km)
               }],
               xaxis: {
                  categories: brandData.map(b => b.brand),
                  labels: {
                     style: {
                        fontSize: '12px'
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
                  formatter: val => val.toLocaleString() + ' km',
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
                     formatter: val => val.toLocaleString() + ' km'
                  }
               }
            }).render();
         } else {
            document.querySelector('#brandPerformanceChart').innerHTML =
               '<div class="text-center text-muted py-5"><i class="icon-base ri ri-bar-chart-line ri-3x opacity-25 d-block mb-2"></i><p>Belum ada data lifetime</p></div>';
         }

         // ==========================================
         // 4. LOCATION STOCK CHART (Grouped Bar)
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
               labels: {
                  style: {
                     fontSize: '11px'
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
         // 5. FAILURE CODE DONUT CHART
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

      });
   </script>
@endsection
