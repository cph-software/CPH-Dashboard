@extends('layouts.admin')

@section('title', 'Global Overview')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
<style>
    .hover-elevate-up {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .hover-elevate-up:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    }
    .stats-icon-wrapper {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
    .company-card-header {
        background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    }
    .progress-bar-thin {
        height: 6px;
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #696cff 0%, #3e41ff 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #71dd37 0%, #46bd08 100%);
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffab00 0%, #cc8900 100%);
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Dashboard /</span> Global Overview
            </h4>
            <p class="text-muted mb-0">Bird-eye view of all company assets and tyre performance.</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex align-items-center">
            <span class="badge bg-label-primary px-3 py-2 rounded-pill fs-6 shadow-sm">
                <i class="icon-base ri-building-line me-1"></i> All Companies Active
            </span>
        </div>
    </div>

    <!-- Global Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="content-left">
                            <span class="text-muted mb-1 d-block">Total Companies</span>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="mb-0 me-2">{{ count($companyStats) }}</h4>
                            </div>
                        </div>
                        <div class="stats-icon-wrapper bg-label-primary">
                            <i class="icon-base ri-building-4-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="content-left">
                            <span class="text-muted mb-1 d-block">Total Vehicles</span>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="mb-0 me-2">{{ number_format($totalSystemVehicles) }}</h4>
                            </div>
                        </div>
                        <div class="stats-icon-wrapper bg-label-success">
                            <i class="icon-base ri-truck-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="content-left">
                            <span class="text-muted mb-1 d-block">Total Tyres (All)</span>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="mb-0 me-2">{{ number_format($totalSystemTyres) }}</h4>
                            </div>
                        </div>
                        <div class="stats-icon-wrapper bg-label-info">
                            <i class="icon-base ri-steering-2-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm h-100 border-0 bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="content-left">
                            <span class="text-white-50 mb-1 d-block">Total Asset Value</span>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="mb-0 text-white me-2">Rp {{ number_format($totalSystemInvestment, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                        <div class="stats-icon-wrapper bg-white text-primary">
                            <i class="icon-base ri-money-dollar-circle-line fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h5 class="fw-semibold mb-3 border-bottom pb-2">Company Breakdown</h5>

    <!-- Company Cards Grid -->
    <div class="row g-4">
        @foreach($companyStats as $stat)
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card h-100 shadow-sm border-0 hover-elevate-up">
                <div class="card-header d-flex justify-content-between align-items-center pb-2 company-card-header bg-lighter">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-2">
                            <span class="avatar-initial rounded-circle bg-label-dark fw-bold">
                                {{ substr($stat['company']->company_name, 0, 2) }}
                            </span>
                        </div>
                        <h6 class="mb-0 fw-bold">{{ $stat['company']->company_name }}</h6>
                    </div>
                    <button class="btn btn-sm btn-outline-primary rounded-pill btn-view-details" 
                            data-id="{{ $stat['company']->id }}" 
                            data-name="{{ $stat['company']->company_name }}">
                        View Details
                    </button>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex justify-content-around mb-4 text-center">
                        <div>
                            <h5 class="mb-0 fw-bold text-primary">{{ number_format($stat['vehicles_count']) }}</h5>
                            <small class="text-muted">Vehicles</small>
                        </div>
                        <div class="border-end"></div>
                        <div>
                            <h5 class="mb-0 fw-bold text-info">{{ number_format($stat['total_tyres']) }}</h5>
                            <small class="text-muted">Total Tyres</small>
                        </div>
                    </div>

                    <div class="mt-3">
                        @php
                            $installedPct = $stat['total_tyres'] > 0 ? ($stat['installed_count'] / $stat['total_tyres']) * 100 : 0;
                            $stockPct = $stat['total_tyres'] > 0 ? ($stat['in_stock_count'] / $stat['total_tyres']) * 100 : 0;
                            $scrapPct = $stat['total_tyres'] > 0 ? ($stat['scrap_count'] / $stat['total_tyres']) * 100 : 0;
                        @endphp
                        
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-medium">Tyre Distribution</small>
                        </div>
                        <div class="progress progress-bar-thin mb-3">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $installedPct }}%" title="Installed: {{ $stat['installed_count'] }}"></div>
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $stockPct }}%" title="In Stock: {{ $stat['in_stock_count'] }}"></div>
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $scrapPct }}%" title="Scrap: {{ $stat['scrap_count'] }}"></div>
                        </div>

                        <div class="d-flex justify-content-between text-muted small">
                            <span><i class="icon-base ri-checkbox-circle-fill text-success me-1"></i>Installed: {{ $stat['installed_count'] }}</span>
                            <span><i class="icon-base ri-checkbox-circle-fill text-warning me-1"></i>Stock: {{ $stat['in_stock_count'] }}</span>
                            <span><i class="icon-base ri-checkbox-circle-fill text-danger me-1"></i>Scrap: {{ $stat['scrap_count'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer border-top bg-transparent py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Asset Value</small>
                        <span class="fw-semibold text-dark">Rp {{ number_format($stat['investment'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal for Drilldown Details -->
<div class="modal fade" id="companyDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-lighter pb-3 border-bottom">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-3">
                        <span class="avatar-initial rounded-circle bg-primary text-white fw-bold" id="modalCompanyInitials">
                            CP
                        </span>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalCompanyName">Company Name</h5>
                        <small class="text-muted">Tyre Asset Breakdown</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body position-relative">
                <div id="modalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Fetching data...</p>
                </div>

                <div id="modalContent" class="d-none">
                    <div class="row g-4">
                        <!-- Brand Breakdown -->
                        <div class="col-md-4">
                            <div class="card shadow-none bg-lighter h-100 border">
                                <div class="card-header pb-2">
                                    <h6 class="mb-0"><i class="icon-base ri-price-tag-3-line text-primary me-2"></i>By Brand</h6>
                                </div>
                                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                    <ul class="list-group list-group-flush" id="listBrand">
                                        <!-- Rendered via JS -->
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Size Breakdown -->
                        <div class="col-md-4">
                            <div class="card shadow-none bg-lighter h-100 border">
                                <div class="card-header pb-2">
                                    <h6 class="mb-0"><i class="icon-base ri-ruler-line text-info me-2"></i>By Size</h6>
                                </div>
                                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                    <ul class="list-group list-group-flush" id="listSize">
                                        <!-- Rendered via JS -->
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Pattern Breakdown -->
                        <div class="col-md-4">
                            <div class="card shadow-none bg-lighter h-100 border">
                                <div class="card-header pb-2">
                                    <h6 class="mb-0"><i class="icon-base ri-git-commit-line text-warning me-2"></i>By Pattern</h6>
                                </div>
                                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                    <ul class="list-group list-group-flush" id="listPattern">
                                        <!-- Rendered via JS -->
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-lighter border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('vendor-script')
<script src="{{ asset('template/full-version/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        $('.btn-view-details').on('click', function() {
            let companyId = $(this).data('id');
            let companyName = $(this).data('name');
            
            // Set modal headers
            $('#modalCompanyName').text(companyName);
            $('#modalCompanyInitials').text(companyName.substring(0, 2).toUpperCase());
            
            // UI state
            $('#modalLoading').removeClass('d-none');
            $('#modalContent').addClass('d-none');
            $('#companyDetailModal').modal('show');

            // Fetch Data
            $.ajax({
                url: '{{ route("master_data.super-admin-company-detail") }}',
                type: 'GET',
                data: { company_id: companyId },
                success: function(res) {
                    renderList('#listBrand', res.by_brand, res.total);
                    renderList('#listSize', res.by_size, res.total);
                    renderList('#listPattern', res.by_pattern, res.total);
                    
                    $('#modalLoading').addClass('d-none');
                    $('#modalContent').removeClass('d-none');
                },
                error: function() {
                    $('#modalLoading').html('<p class="text-danger"><i class="icon-base ri-error-warning-line mb-2 fs-3 d-block"></i>Failed to fetch data.</p>');
                }
            });
        });

        function renderList(selector, data, total) {
            let html = '';
            if (!data || data.length === 0) {
                html = '<li class="list-group-item text-center text-muted py-4 border-0">No data available</li>';
            } else {
                data.forEach(item => {
                    let pct = total > 0 ? Math.round((item.count / total) * 100) : 0;
                    html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                            <div class="text-truncate me-2" title="${item.name}">${item.name}</div>
                            <span class="badge bg-label-dark rounded-pill">${item.count}</span>
                        </li>
                    `;
                });
            }
            $(selector).html(html);
        }
    });
</script>
@endsection
