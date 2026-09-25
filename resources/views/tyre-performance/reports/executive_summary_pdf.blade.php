<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Executive Performance Report - CPH Tyre Performance</title>
    <style>
        @page {
            margin: 10mm 12mm 10mm 12mm;
            size: A4 portrait;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5px;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.25;
        }

        /* HEADER */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            border-bottom: 2px solid #c0392b;
            padding-bottom: 6px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo-img {
            max-height: 40px;
            max-width: 140px;
        }

        .doc-title {
            margin: 0;
            color: #000;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-subtitle {
            margin: 2px 0 0 0;
            font-size: 8.5px;
            color: #c0392b;
            font-weight: bold;
        }

        .doc-module {
            margin: 1px 0 0 0;
            font-size: 7.5px;
            color: #777;
        }

        /* INFO TABLE */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
            border: 1px solid #e0e0e0;
            background: #fafafa;
        }

        .label {
            color: #666;
            font-size: 7px;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
            margin-bottom: 1px;
        }

        .value {
            font-weight: bold;
            font-size: 9px;
            color: #111;
        }

        /* SECTION TITLE */
        .section-title {
            background: #c0392b;
            color: #fff;
            padding: 3.5px 7px;
            font-weight: bold;
            margin: 8px 0 5px 0;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        /* KPI METRIC TABLE */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .kpi-table td {
            width: 20%;
            border: 1px solid #ccc;
            background: #fff;
            padding: 5px 3px;
            text-align: center;
            vertical-align: middle;
        }

        .kpi-label {
            color: #666;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .kpi-value {
            font-size: 12px;
            font-weight: bold;
            color: #111;
            line-height: 1.15;
        }

        .kpi-unit {
            font-size: 7.5px;
            font-weight: normal;
            color: #666;
        }

        .kpi-sub {
            font-size: 6.8px;
            color: #888;
            margin-top: 1px;
        }

        /* TABLES */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .details-table th {
            background-color: #2c3e50;
            color: #fff;
            padding: 4.5px 4px;
            text-align: center;
            font-size: 8px;
            border: 1px solid #2c3e50;
            text-transform: uppercase;
        }

        .details-table td {
            padding: 4px 5px;
            border: 1px solid #ccc;
            font-size: 8px;
            text-align: center;
            vertical-align: middle;
        }

        .details-table td.text-left {
            text-align: left;
        }

        .details-table td.text-right {
            text-align: right;
        }

        .bg-light {
            background-color: #f9f9f9;
        }

        /* RECOMMENDATION HIGHLIGHT STRIP */
        .recom-highlight {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .recom-highlight td {
            padding: 5px 7px;
            vertical-align: top;
            background: #fdfdfd;
            border: 1px solid #ddd;
        }

        /* STATUS BADGES */
        .badge {
            display: inline-block;
            padding: 1.5px 4px;
            font-size: 7px;
            font-weight: bold;
            border-radius: 2px;
            white-space: nowrap;
        }

        .badge-success   { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .badge-danger    { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .badge-info      { background-color: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }
        .badge-warning   { background-color: #fff8e1; color: #f57f17; border: 1px solid #ffecb3; }
        .badge-secondary { background-color: #f5f5f5; color: #616161; border: 1px solid #e0e0e0; }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: -15px;
            left: 0;
            right: 0;
            font-size: 7px;
            color: #888;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 3px;
        }
    </style>
</head>
<body>

    <!-- 1. HEADER BRANDING -->
    <table class="header-table">
        <tr>
            <td style="width: 25%;">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo-img" alt="CPH Tyre">
                @else
                    <div style="font-size: 15px; font-weight: bold; color: #c0392b;">CPH TYRE</div>
                @endif
            </td>
            <td style="width: 75%; text-align: right;">
                <h2 class="doc-title">EXECUTIVE PERFORMANCE REPORT</h2>
                <div class="doc-subtitle">Analisis Pergerakan, Umur Pakai &amp; Rekomendasi Efektivitas Ban</div>
                <div class="doc-module">CPH Dashboard &bull; Tyre Performance Module</div>
            </td>
        </tr>
    </table>

    <!-- 2. INFO METADATA -->
    <table class="info-table">
        <tr>
            <td style="width: 25%;">
                <span class="label">PERUSAHAAN / ENTITAS</span>
                <span class="value">{{ $companyName }}</span>
            </td>
            <td style="width: 25%;">
                <span class="label">PERIODE ANALISIS</span>
                <span class="value">{{ $startDate->format('d/m/Y') }} &ndash; {{ $endDate->format('d/m/Y') }}</span>
            </td>
            <td style="width: 25%;">
                <span class="label">WAKTU UNDUH</span>
                <span class="value">{{ $printDate }}</span>
            </td>
            <td style="width: 25%;">
                <span class="label">DISIAPKAN OLEH</span>
                <span class="value">{{ $user->name ?? 'User Sistem' }} ({{ $user->role->name ?? 'Admin' }})</span>
            </td>
        </tr>
    </table>

    <!-- 3. RINGKASAN EKSEKUTIF & KPI -->
    <div class="section-title">1. RINGKASAN EKSEKUTIF &amp; KEY PERFORMANCE INDICATORS (KPI)</div>
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-label">POPULASI BAN</div>
                <div class="kpi-value">{{ number_format($totalTyres, 0, ',', '.') }}</div>
                <div class="kpi-sub">{{ $installedTyres }} Pasang &bull; {{ $inStockTyres }} Gudang</div>
            </td>
            <td>
                <div class="kpi-label">RATA-RATA LIFETIME</div>
                <div class="kpi-value">{{ number_format($avgKm, 0, ',', '.') }} <span class="kpi-unit">KM</span></div>
                <div class="kpi-sub">atau {{ number_format($avgHm, 0, ',', '.') }} HM</div>
            </td>
            <td>
                <div class="kpi-label">COST PER KM (CPK)</div>
                <div class="kpi-value">Rp {{ number_format($avgCpk, 0, ',', '.') }}</div>
                <div class="kpi-sub">Rata-rata Biaya Operasional/KM</div>
            </td>
            <td>
                <div class="kpi-label">TOTAL INVESTASI BAN</div>
                <div class="kpi-value">Rp {{ number_format($totalInvestment / 1000000, 1, ',', '.') }} <span class="kpi-unit">Jt</span></div>
                <div class="kpi-sub">Nilai Perolehan Seluruh Ban</div>
            </td>
            <td>
                <div class="kpi-label">STATUS KRITIS (RTD &lt; 5MM)</div>
                <div class="kpi-value" style="color: {{ $criticalTyres->count() > 0 ? '#c0392b' : '#27ae60' }};">
                    {{ $criticalTyres->count() }} <span class="kpi-unit">Ban</span>
                </div>
                <div class="kpi-sub">{{ $criticalTyres->count() > 0 ? 'Perlu Penggantian Segera' : 'Kondisi Tapak Terjaga Baik' }}</div>
            </td>
        </tr>
    </table>

    <!-- 4. RIWAYAT PERGERAKAN BAN (MOVEMENT HISTORY) -->
    <div class="section-title">2. RIWAYAT PERGERAKAN BAN (MOVEMENT HISTORY)</div>

    <!-- Summary Bar Transaksi -->
    <table class="info-table" style="margin-bottom: 5px;">
        <tr>
            <td style="width: 25%; text-align: center;">
                <span class="label">TOTAL PERGERAKAN</span>
                <span class="value">{{ number_format($totalPemasangan + $totalPelepasan + $totalRotasi + $totalInspeksi) }} Transaksi</span>
            </td>
            <td style="width: 25%; text-align: center;">
                <span class="label">PEMASANGAN (INSTALL)</span>
                <span class="value" style="color: #27ae60;">{{ number_format($totalPemasangan) }} Ban</span>
            </td>
            <td style="width: 25%; text-align: center;">
                <span class="label">PELEPASAN (REMOVAL)</span>
                <span class="value" style="color: #c0392b;">{{ number_format($totalPelepasan) }} Ban</span>
            </td>
            <td style="width: 25%; text-align: center;">
                <span class="label">ROTASI POSISI</span>
                <span class="value" style="color: #d97706;">{{ number_format($totalRotasi) }} Ban</span>
            </td>
        </tr>
    </table>

    <!-- Tabel Riwayat Pergerakan Ban -->
    <table class="details-table" style="margin-bottom: 7px;">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 10%;">TANGGAL</th>
                <th style="width: 16%; text-align: left; padding-left: 5px;">SN BAN</th>
                <th style="width: 17%; text-align: left; padding-left: 5px;">MERK / UKURAN</th>
                <th style="width: 15%; text-align: left; padding-left: 5px;">KENDARAAN</th>
                <th style="width: 7%;">POSISI</th>
                <th style="width: 11%;">TIPE</th>
                <th style="width: 9%; text-align: right; padding-right: 5px;">ODOMETER</th>
                <th style="width: 11%; text-align: left; padding-left: 5px;">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $idx => $m)
                <tr class="{{ $idx % 2 == 1 ? 'bg-light' : '' }}">
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $m->movement_date ? \Carbon\Carbon::parse($m->movement_date)->format('d/m/Y') : '-' }}</td>
                    <td class="text-left" style="padding-left: 5px;">
                        <strong>{{ optional($m->tyre)->serial_number ?? '-' }}</strong>
                    </td>
                    <td class="text-left" style="padding-left: 5px;">
                        {{ optional(optional($m->tyre)->brand)->brand_name ?? '-' }}<br>
                        <span style="font-size: 7px; color: #666;">
                            {{ optional(optional($m->tyre)->size)->size ?? '' }}
                            @if (optional($m->tyre)->pattern)
                                &bull; {{ $m->tyre->pattern->name }}
                            @endif
                        </span>
                    </td>
                    <td class="text-left" style="padding-left: 5px;">
                        <strong>{{ optional($m->vehicle)->no_polisi ?? optional($m->vehicle)->kode_kendaraan ?? '-' }}</strong>
                    </td>
                    <td>
                        <strong>{{ optional($m->position)->position_code ?? '-' }}</strong>
                    </td>
                    <td>
                        @if ($m->movement_type === 'Installation')
                            <span class="badge badge-success">Pemasangan</span>
                        @elseif ($m->movement_type === 'Removal')
                            <span class="badge badge-danger">Pelepasan</span>
                        @elseif ($m->movement_type === 'Rotation')
                            <span class="badge badge-warning">Rotasi</span>
                        @else
                            <span class="badge badge-info">{{ $m->movement_type }}</span>
                        @endif
                    </td>
                    <td class="text-right" style="padding-right: 5px;">
                        @if ($m->odometer_reading > 0)
                            {{ number_format($m->odometer_reading, 0, ',', '.') }} KM
                        @elseif ($m->hour_meter_reading > 0)
                            {{ number_format($m->hour_meter_reading, 0, ',', '.') }} HM
                        @elseif (str_contains(strtolower($m->notes ?? ''), 'odometer rusak'))
                            <span style="color: #c0392b; font-size: 7px;">Odo Rusak</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-left" style="padding-left: 5px;">
                        @if ($m->failureCode)
                            <strong style="color: #c0392b;">{{ $m->failureCode->failure_code }}</strong>
                            <span style="font-size: 7px; color: #555;">({{ $m->failureCode->failure_name }})</span>
                        @elseif ($m->notes)
                            <span style="font-size: 7px; color: #555;">{{ Str::limit($m->notes, 25) }}</span>
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="padding: 8px; color: #888;">Tidak ada riwayat pergerakan ban pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 5. REKOMENDASI BAN PALING EFEKTIF -->
    <div class="section-title">3. REKOMENDASI BAN PALING EFEKTIF BERDASARKAN DATA PEMAKAIAN</div>

    <!-- Highlight Rangkuman Rekomendasi -->
    @if ($bestCpkTyre || $bestLifeTyre)
        <table class="recom-highlight">
            <tr>
                @if ($bestCpkTyre)
                    <td style="width: 50%; border-left: 3px solid #27ae60;">
                        <span class="label" style="color: #27ae60;">[REKOMENDASI BIAYA TERBAIK / BEST CPK]</span>
                        <span class="value">{{ $bestCpkTyre['brand'] }} &bull; Pattern {{ $bestCpkTyre['pattern'] }} ({{ $bestCpkTyre['size'] }})</span>
                        <div style="font-size: 7.5px; color: #555; margin-top: 1px;">
                            Biaya operasional terendah <strong>Rp {{ number_format($bestCpkTyre['cpk'], 0, ',', '.') }}/KM</strong> dengan laju keausan ekonomis.
                        </div>
                    </td>
                @endif
                @if ($bestLifeTyre)
                    <td style="width: 50%; border-left: 3px solid #2980b9;">
                        <span class="label" style="color: #2980b9;">[REKOMENDASI DURABILITAS TERTINGGI / BEST LIFETIME]</span>
                        <span class="value">{{ $bestLifeTyre['brand'] }} &bull; Pattern {{ $bestLifeTyre['pattern'] }} ({{ $bestLifeTyre['size'] }})</span>
                        <div style="font-size: 7.5px; color: #555; margin-top: 1px;">
                            Daya tahan paling awet dengan rata-rata umur pakai mencapai <strong>{{ number_format($bestLifeTyre['avg_km'], 0, ',', '.') }} KM</strong>.
                        </div>
                    </td>
                @endif
            </tr>
        </table>
    @endif

    <!-- Tabel Rincian Rekomendasi Ban -->
    <table class="details-table" style="margin-bottom: 5px;">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 17%; text-align: left; padding-left: 5px;">MERK BAN</th>
                <th style="width: 16%; text-align: left; padding-left: 5px;">POLA TAPAK (PATTERN)</th>
                <th style="width: 14%;">UKURAN (SIZE)</th>
                <th style="width: 8%;">POPULASI</th>
                <th style="width: 14%; text-align: right; padding-right: 5px;">RATA-RATA KM</th>
                <th style="width: 12%; text-align: right; padding-right: 5px;">BIAYA/KM (CPK)</th>
                <th style="width: 15%;">STATUS REKOMENDASI</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($effectiveTyres as $idx => $tyre)
                <tr class="{{ $idx % 2 == 1 ? 'bg-light' : '' }}">
                    <td>{{ $idx + 1 }}</td>
                    <td class="text-left" style="padding-left: 5px;"><strong>{{ $tyre['brand'] }}</strong></td>
                    <td class="text-left" style="padding-left: 5px;">{{ $tyre['pattern'] }}</td>
                    <td>{{ $tyre['size'] }}</td>
                    <td><strong>{{ $tyre['count'] }}</strong></td>
                    <td class="text-right" style="padding-right: 5px;">
                        <strong>{{ $tyre['avg_km'] > 0 ? number_format($tyre['avg_km'], 0, ',', '.') . ' KM' : '-' }}</strong>
                    </td>
                    <td class="text-right" style="padding-right: 5px; color: {{ $tyre['cpk'] > 0 && $tyre['cpk'] <= ($avgCpk ?: 50) ? '#27ae60' : '#222' }};">
                        <strong>{{ $tyre['cpk'] > 0 ? 'Rp ' . number_format($tyre['cpk'], 0, ',', '.') : '-' }}</strong>
                    </td>
                    <td>
                        <span class="badge {{ $tyre['badge'] }}">{{ $tyre['status'] }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="padding: 8px; color: #888;">Belum ada data ban dengan rekaman operasional untuk dianalisis.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        Generated on: {{ $printDate }} &bull; CPH Dashboard - Tyre Performance Module &bull; Laporan Resmi Eksekutif &bull; Dokumen Rahasia Internal
    </div>

</body>
</html>
