@extends('layouts.admin')

@section('title', 'Detail Kendaraan — ' . $kendaraan->kode_kendaraan)

@section('page-style')
    <style>
        :root {
            --primary: #696cff;
            --success: #71dd37;
            --warning: #ffab00;
            --danger: #ff3e1d;
            --info: #03c3ec;
        }

        .vehicle-hero {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            border-radius: 1rem;
            position: relative;
            overflow: hidden;
        }

        .vehicle-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(105, 108, 255, 0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .vehicle-hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(3, 195, 236, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .stat-card {
            border-radius: .875rem;
            border: 1px solid rgba(255, 255, 255, .08);
            background: #fff;
            transition: transform .2s, box-shadow .2s;
            overflow: hidden;
            position: relative;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(105, 108, 255, .15);
        }

        .stat-card .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .info-grid dt {
            font-size: .75rem;
            font-weight: 600;
            color: #8592a3;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: .2rem;
        }

        .info-grid dd {
            font-size: .925rem;
            font-weight: 600;
            color: #384551;
            margin-bottom: 0;
        }

        .info-block {
            border-radius: .75rem;
            padding: 1rem 1.25rem;
            border-left: 4px solid;
        }

        .info-block.blue {
            background: rgba(105, 108, 255, .06);
            border-color: var(--primary);
        }

        .info-block.teal {
            background: rgba(3, 195, 236, .06);
            border-color: var(--info);
        }

        .info-block.green {
            background: rgba(113, 221, 55, .06);
            border-color: var(--success);
        }

        .info-block.orange {
            background: rgba(255, 171, 0, .06);
            border-color: var(--warning);
        }

        .badge-status {
            font-size: .8rem;
            padding: .35em .75em;
            border-radius: 50px;
            font-weight: 600;
        }

        .tyre-slot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: .75rem;
        }

        .tyre-slot {
            border-radius: .75rem;
            padding: 1rem;
            border: 2px dashed #d4d8dd;
            text-align: center;
            background: #f8f9fa;
            transition: border-color .2s, background .2s;
        }

        .tyre-slot.occupied {
            border: 2px solid rgba(113, 221, 55, 0.4);
            background: rgba(113, 221, 55, 0.04);
        }

        .tyre-slot .ri {
            font-size: 2rem;
            display: block;
            margin-bottom: .4rem;
        }

        .timeline-item {
            position: relative;
            padding-left: 2.5rem;
            padding-bottom: 1.5rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: .85rem;
            top: 1.5rem;
            bottom: 0;
            width: 2px;
            background: #e7eaf0;
        }

        .timeline-item:last-child::before {
            display: none;
        }

        .timeline-dot {
            position: absolute;
            left: 0;
            top: .25rem;
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
        }

        .section-title {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #8592a3;
        }

        .back-btn {
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .15);
            color: #fff;
            border-radius: .6rem;
            transition: background .2s;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        @media (max-width: 768px) {
            .vehicle-hero {
                padding: 1.5rem !important;
            }

            .tyre-slot-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1">
                <li class="breadcrumb-item"><a href="{{ route('tyre-kendaraan.index') }}">Vehicle Master</a></li>
                <li class="breadcrumb-item active">{{ $kendaraan->kode_kendaraan }}</li>
            </ol>
        </nav>

        {{-- ═══════════════════ HERO HEADER ═══════════════════ --}}
        <div class="vehicle-hero p-4 mb-4">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 position-relative"
                style="z-index:1">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                        style="width:68px;height:68px;background:rgba(255,255,255,.1);">
                        <i class="ri-truck-line text-white" style="font-size:2.2rem;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h4 class="text-white fw-bold mb-0">{{ $kendaraan->kode_kendaraan }}</h4>
                            @php
                                $statusColor = ['Active' => 'success', 'Inactive' => 'secondary', 'Maintenance' => 'warning'][$kendaraan->tyre_unit_status] ?? 'secondary';
                              @endphp
                            <span class="badge bg-{{ $statusColor }} badge-status">{{ $kendaraan->tyre_unit_status }}</span>
                        </div>
                        <p class="text-white-50 mb-0" style="font-size:.9rem;">
                            {{ $kendaraan->no_polisi ?? '—' }}
                            @if($kendaraan->vehicle_brand)
                                &nbsp;·&nbsp; {{ $kendaraan->vehicle_brand }}
                            @endif
                            @if($kendaraan->jenis_kendaraan)
                                &nbsp;·&nbsp; {{ $kendaraan->jenis_kendaraan }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('tyre-kendaraan.index') }}" class="btn back-btn btn-sm">
                        <i class="ri-arrow-left-line me-1"></i>Kembali
                    </a>
                    @if(hasPermission('Vehicle Master', 'update'))
                        <button class="btn btn-sm btn-light"
                            onclick="window.location='{{ route('tyre-kendaraan.edit', $kendaraan->id) }}'">
                            <i class="ri-pencil-line me-1"></i>Edit
                        </button>
                    @endif
                </div>
            </div>

            {{-- Quick Stats Row --}}
            <div class="row g-3 mt-3 position-relative" style="z-index:1">
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded-3" style="background:rgba(255,255,255,.07)">
                        <div class="text-white fw-bold" style="font-size:1.8rem;">{{ $installedCount }}</div>
                        <div class="text-white-50 small">Ban Terpasang</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded-3" style="background:rgba(255,255,255,.07)">
                        <div class="text-white fw-bold" style="font-size:1.8rem;">{{ $totalPositions }}</div>
                        <div class="text-white-50 small">Total Posisi</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded-3" style="background:rgba(255,255,255,.07)">
                        <div class="text-white fw-bold" style="font-size:1.8rem;">{{ $installCount }}</div>
                        <div class="text-white-50 small">Riwayat Pasang</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded-3" style="background:rgba(255,255,255,.07)">
                        <div class="text-white fw-bold" style="font-size:1.8rem;">{{ $removalCount }}</div>
                        <div class="text-white-50 small">Riwayat Lepas</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════ INFO CARD ═══════════════════ --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent border-0 pb-0">
                <p class="section-title mb-0"><i class="ri-file-list-3-line me-1"></i>Informasi Kendaraan</p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4 col-6">
                        <div class="info-block blue">
                            <dt class="info-label">Unit Code</dt>
                            <dd class="info-value">{{ $kendaraan->kode_kendaraan }}</dd>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="info-block blue">
                            <dt class="info-label">No. Polisi</dt>
                            <dd class="info-value">{{ $kendaraan->no_polisi ?? '—' }}</dd>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="info-block teal">
                            <dt class="info-label">Vehicle Type</dt>
                            <dd class="info-value">{{ $kendaraan->jenis_kendaraan ?? '—' }}</dd>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="info-block teal">
                            <dt class="info-label">Merk Kendaraan</dt>
                            <dd class="info-value">{{ $kendaraan->vehicle_brand ?? '—' }}</dd>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="info-block blue">
                            <dt class="info-label">Operational Area</dt>
                            <dd class="info-value">{{ $kendaraan->area ?? '—' }}</dd>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="info-block blue">
                            <dt class="info-label">Default Working Segment</dt>
                            <dd class="info-value">{{ $kendaraan->segment->segment_name ?? '—' }}</dd>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="info-block orange">
                            <dt class="info-label">Curb Weight</dt>
                            <dd class="info-value">
                                {{ $kendaraan->curb_weight ? number_format($kendaraan->curb_weight) . ' kg' : '—' }}
                            </dd>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="info-block orange">
                            <dt class="info-label">Payload Capacity</dt>
                            <dd class="info-value">
                                {{ $kendaraan->payload_capacity ? number_format($kendaraan->payload_capacity, 1) . ' ton' : '—' }}
                            </dd>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="info-block green">
                            <dt class="info-label">Axle Layout</dt>
                            <dd class="info-value">{{ $kendaraan->tyrePositionConfiguration->name ?? '—' }}</dd>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="info-block green">
                            <dt class="info-label">Total Wheels</dt>
                            <dd class="info-value">{{ $kendaraan->total_tyre_position ?? '—' }}</dd>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div
                            class="info-block {{ ['Active' => 'green', 'Maintenance' => 'orange', 'Inactive' => 'teal'][$kendaraan->tyre_unit_status] ?? 'teal' }}">
                            <dt class="info-label">Status</dt>
                            <dd class="info-value">
                                @php $sc = ['Active' => 'success', 'Maintenance' => 'warning', 'Inactive' => 'secondary'][$kendaraan->tyre_unit_status] ?? 'secondary'; @endphp
                                <span class="badge bg-label-{{ $sc }}">{{ $kendaraan->tyre_unit_status }}</span>
                            </dd>
                        </div>
                    </div>

                    {{-- GVW bar — only if both weight fields are filled --}}
                    @if($kendaraan->curb_weight && $kendaraan->payload_capacity)
                        @php $grossWeight = ($kendaraan->curb_weight / 1000) + $kendaraan->payload_capacity; @endphp
                        <div class="col-12">
                            <hr class="my-1">
                            <p class="section-title mb-2 mt-1"><i class="ri-scales-3-line me-1"></i>Estimasi GVW (Gross Vehicle
                                Weight)</p>
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="small text-muted">
                                    Curb {{ number_format($kendaraan->curb_weight / 1000, 1) }} t + Payload
                                    {{ $kendaraan->payload_capacity }} t
                                </span>
                                <span class="fw-bold text-primary">= {{ number_format($grossWeight, 1) }} ton</span>
                            </div>
                            <div class="progress" style="height:8px;border-radius:10px;">
                                <div class="progress-bar bg-primary"
                                    style="width:{{ min(($kendaraan->curb_weight / 1000) / $grossWeight * 100, 100) }}%;border-radius:10px 0 0 10px;">
                                </div>
                                <div class="progress-bar bg-success"
                                    style="width:{{ min($kendaraan->payload_capacity / $grossWeight * 100, 100) }}%;border-radius:0 10px 10px 0;">
                                </div>
                            </div>
                            <div class="d-flex gap-3 mt-1">
                                <small><span class="badge bg-label-primary me-1">■</span>Berat Kosong</small>
                                <small><span class="badge bg-label-success me-1">■</span>Muatan</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>


        {{-- ═══════════════════ BAN TERPASANG ═══════════════════ --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between">
                <p class="section-title mb-0"><i class="ri-circle-line me-1"></i>Ban Terpasang Saat Ini</p>
                <span
                    class="badge bg-label-{{ $installedCount == $totalPositions && $totalPositions > 0 ? 'success' : ($installedCount > 0 ? 'warning' : 'secondary') }}">
                    {{ $installedCount }} / {{ $totalPositions }} Posisi Terisi
                </span>
            </div>
            <div class="card-body">
                @if($kendaraan->tyres->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Serial Number</th>
                                    <th>Posisi</th>
                                    <th>Brand</th>
                                    <th>Size</th>
                                    <th>Pattern</th>
                                    <th>Status</th>
                                    <th>RTD (mm)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kendaraan->tyres as $tyre)
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary">{{ $tyre->serial_number }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-primary">
                                                {{ $tyre->currentPosition->position_name ?? ('Pos #' . $tyre->current_position_id) }}
                                            </span>
                                        </td>
                                        <td>{{ $tyre->brand->brand_name ?? '—' }}</td>
                                        <td>{{ $tyre->size->size ?? '—' }}</td>
                                        <td>{{ $tyre->pattern->name ?? '—' }}</td>
                                        <td>
                                            @php
                                                $sc = ['New' => 'success', 'Used' => 'warning', 'Retreaded' => 'info', 'Scrap' => 'danger'][$tyre->tyre_status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-label-{{ $sc }}">{{ $tyre->tyre_status ?? '—' }}</span>
                                        </td>
                                        <td>
                                            @if($tyre->current_tread_depth)
                                                <span
                                                    class="fw-semibold {{ $tyre->current_tread_depth < 3 ? 'text-danger' : ($tyre->current_tread_depth < 6 ? 'text-warning' : 'text-success') }}">
                                                    {{ $tyre->current_tread_depth }} mm
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="ri-circle-line ri-3x mb-3 d-block opacity-25"></i>
                        <p class="mb-0">Belum ada ban yang terpasang pada unit ini.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ═══════════════════ RIWAYAT PERGERAKAN ═══════════════════ --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between">
                <p class="section-title mb-0"><i class="ri-history-line me-1"></i>Riwayat Pergerakan Ban</p>
                <span class="badge bg-label-secondary">{{ $movements->count() }} entri terbaru</span>
            </div>
            <div class="card-body">
                @if($movements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Tipe</th>
                                    <th>Serial Number</th>
                                    <th>Brand / Size</th>
                                    <th>Posisi</th>
                                    <th>Odometer</th>
                                    <th>HM</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movements as $mv)
                                    @php
                                        $typeColor = [
                                            'Installation' => 'success',
                                            'Removal' => 'danger',
                                            'Rotation' => 'info',
                                            'Inspection' => 'warning',
                                        ][$mv->movement_type] ?? 'secondary';
                                        $typeIcon = [
                                            'Installation' => 'ri-arrow-down-circle-line',
                                            'Removal' => 'ri-arrow-up-circle-line',
                                            'Rotation' => 'ri-refresh-line',
                                            'Inspection' => 'ri-search-eye-line',
                                        ][$mv->movement_type] ?? 'ri-circle-line';
                                      @endphp
                                    <tr>
                                        <td>
                                            <span
                                                class="fw-semibold">{{ \Carbon\Carbon::parse($mv->movement_date)->format('d M Y') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-{{ $typeColor }} d-inline-flex align-items-center gap-1">
                                                <i class="{{ $typeIcon }}"></i> {{ $mv->movement_type }}
                                            </span>
                                        </td>
                                        <td class="fw-bold text-primary">{{ $mv->tyre->serial_number ?? '—' }}</td>
                                        <td>
                                            {{ $mv->tyre->brand->brand_name ?? '—' }}
                                            <span class="text-muted small">/ {{ $mv->tyre->size->size ?? '—' }}</span>
                                        </td>
                                        <td>{{ $mv->position->position_name ?? ($mv->position->position_code ?? '—') }}</td>
                                        <td>{{ $mv->odometer_reading ? number_format($mv->odometer_reading, 0, ',', '.') . ' km' : '—' }}
                                        </td>
                                        <td>{{ $mv->hour_meter_reading ? number_format($mv->hour_meter_reading, 0, ',', '.') . ' HM' : '—' }}
                                        </td>
                                        <td>
                                            <span class="text-muted small">{{ Str::limit($mv->notes, 40) ?: '—' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="ri-history-line ri-3x mb-3 d-block opacity-25"></i>
                        <p class="mb-0">Belum ada riwayat pergerakan pada unit ini.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection