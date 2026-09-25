<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Executive Summary Report - CPH Tyre Performance</title>
    <style>
        @page {
            margin: 12mm 14mm 14mm 14mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.35;
            color: #2d3748;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2px solid #2b6cb0;
            padding-bottom: 8px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-img {
            max-height: 46px;
            max-width: 140px;
        }
        .header-title {
            font-size: 15pt;
            font-weight: bold;
            color: #1a365d;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-subtitle {
            font-size: 9pt;
            color: #4a5568;
            margin: 2px 0 0 0;
            font-weight: 500;
        }
        .meta-box {
            background-color: #ebf8ff;
            border: 1px solid #bee3f8;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 12px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            font-size: 8pt;
            padding: 2px 4px;
        }
        .meta-label {
            color: #4a5568;
            font-weight: bold;
            width: 16%;
        }
        .meta-value {
            color: #1a202c;
            width: 34%;
        }

        /* SECTION STYLING */
        .section-title {
            font-size: 10.5pt;
            font-weight: bold;
            color: #2b6cb0;
            margin: 12px 0 6px 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
        }

        /* KPI CARDS */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 12px;
        }
        .kpi-card {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #3182ce;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: center;
        }
        .kpi-card.green { border-top-color: #38a169; }
        .kpi-card.purple { border-top-color: #805ad5; }
        .kpi-card.orange { border-top-color: #dd6b20; }
        .kpi-card.teal { border-top-color: #319795; }
        .kpi-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #718096;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .kpi-value {
            font-size: 13pt;
            font-weight: bold;
            color: #1a202c;
            margin: 0;
        }
        .kpi-sub {
            font-size: 7pt;
            color: #a0aec0;
            margin-top: 2px;
        }

        /* TWO COLUMN LAYOUT */
        .columns-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .columns-table td {
            vertical-align: top;
        }

        /* GENERAL DATA TABLES */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 10px;
        }
        .data-table th {
            background-color: #edf2f7;
            color: #2d3748;
            font-weight: bold;
            text-align: left;
            padding: 5px 6px;
            border: 1px solid #cbd5e0;
            font-size: 7.5pt;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f7fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 7pt;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-success { background-color: #c6f6d5; color: #22543d; border: 1px solid #9ae6b4; }
        .badge-info { background-color: #bee3f8; color: #2a4365; border: 1px solid #90cdf4; }
        .badge-warning { background-color: #feebc8; color: #744210; border: 1px solid #fbd38d; }
        .badge-secondary { background-color: #edf2f7; color: #4a5568; border: 1px solid #cbd5e0; }

        /* HIGHLIGHT CALLOUT */
        .callout-box {
            background-color: #f0fff4;
            border: 1px solid #9ae6b4;
            border-left: 4px solid #38a169;
            padding: 7px 10px;
            border-radius: 3px;
            margin-bottom: 10px;
        }
        .callout-title {
            font-size: 8.5pt;
            font-weight: bold;
            color: #22543d;
            margin: 0 0 3px 0;
        }
        .callout-desc {
            font-size: 8pt;
            color: #276749;
            margin: 0;
        }

        /* SIGNATURE SECTION */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 32%;
            text-align: center;
            vertical-align: top;
            padding: 0 8px;
        }
        .signature-role {
            font-size: 8pt;
            font-weight: bold;
            color: #4a5568;
            margin-bottom: 50px;
        }
        .signature-line {
            border-bottom: 1px solid #4a5568;
            margin-bottom: 3px;
        }
        .signature-name {
            font-size: 8pt;
            font-weight: bold;
            color: #1a202c;
        }

        /* FOOTER */
        .report-footer {
            margin-top: 15px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 7pt;
            color: #a0aec0;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td style="width: 25%;">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo-img" alt="CPH Tyre">
                @else
                    <span style="font-size: 16pt; font-weight: bold; color: #2b6cb0;">CPH TYRE</span>
                @endif
            </td>
            <td style="width: 75%; text-align: right;">
                <div class="header-title">Executive Performance Report</div>
                <div class="header-subtitle">Analisis Pergerakan, Umur Pakai & Rekomendasi Efektivitas Ban</div>
            </td>
        </tr>
    </table>

    <!-- METADATA BOX -->
    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td class="meta-label">Entitas / Perusahaan:</td>
                <td class="meta-value"><strong>{{ $companyName }}</strong></td>
                <td class="meta-label">Periode Analisis:</td>
                <td class="meta-value">{{ $startDate->format('d/m/Y') }} &ndash; {{ $endDate->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="meta-label">Waktu Unduh:</td>
                <td class="meta-value">{{ $printDate }}</td>
                <td class="meta-label">Disiapkan Oleh:</td>
                <td class="meta-value">{{ $user->name ?? 'User Sistem' }} ({{ $user->role->name ?? 'Admin' }})</td>
            </tr>
        </table>
    </div>

    <!-- ROW 1: EXECUTIVE KPI SUMMARY -->
    <div class="section-title">1. Ringkasan Eksekutif & Key Performance Indicators (KPI)</div>
    <table class="kpi-table">
        <tr>
            <td style="width: 20%;">
                <div class="kpi-card">
                    <div class="kpi-title">Total Populasi Ban</div>
                    <div class="kpi-value">{{ number_format($totalTyres, 0, ',', '.') }}</div>
                    <div class="kpi-sub">{{ $installedTyres }} Pasang &bull; {{ $inStockTyres }} Gudang</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="kpi-card green">
                    <div class="kpi-title">Rata-rata Umur Pakai</div>
                    <div class="kpi-value">{{ number_format($avgKm, 0, ',', '.') }} <span style="font-size:8pt; font-weight:normal;">KM</span></div>
                    <div class="kpi-sub">atau {{ number_format($avgHm, 0, ',', '.') }} HM</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="kpi-card orange">
                    <div class="kpi-title">Cost Per KM (CPK)</div>
                    <div class="kpi-value">Rp {{ number_format($avgCpk, 0, ',', '.') }}</div>
                    <div class="kpi-sub">Rata-rata Biaya / KM</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="kpi-card purple">
                    <div class="kpi-title">Total Investasi Ban</div>
                    <div class="kpi-value">Rp {{ number_format($totalInvestment / 1000000, 1, ',', '.') }}<span style="font-size:8pt; font-weight:normal;"> Jt</span></div>
                    <div class="kpi-sub">Nilai Perolehan Aset Ban</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="kpi-card teal">
                    <div class="kpi-title">Kondisi Ban Kritis</div>
                    <div class="kpi-value" style="color: {{ $criticalTyres->count() > 0 ? '#c53030' : '#2f855a' }};">
                        {{ $criticalTyres->count() }} <span style="font-size:8pt; font-weight:normal;">Unit Ban</span>
                    </div>
                    <div class="kpi-sub">RTD &lt; 5 mm (Perlu Peremajaan)</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- ROW 2: KONDISI PERGERAKAN BAN & KERUSAKAN -->
    <div class="section-title">2. Kondisi Pergerakan Ban & Analisis Kerusakan</div>
    <table class="columns-table">
        <tr>
            <!-- Left Column: Aktivitas Pergerakan -->
            <td style="width: 48%; padding-right: 8px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Aktivitas Pergerakan Periode Ini</th>
                            <th class="text-center" style="width: 30%;">Jumlah Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong style="color: #2f855a;">&bull; Pemasangan (Installation)</strong></td>
                            <td class="text-center fw-bold">{{ number_format($totalPemasangan) }} ban</td>
                        </tr>
                        <tr>
                            <td><strong style="color: #c53030;">&bull; Pelepasan (Removal)</strong></td>
                            <td class="text-center fw-bold">{{ number_format($totalPelepasan) }} ban</td>
                        </tr>
                        <tr>
                            <td><strong style="color: #dd6b20;">&bull; Rotasi Posisi (Rotation)</strong></td>
                            <td class="text-center fw-bold">{{ number_format($totalRotasi) }} ban</td>
                        </tr>
                        <tr>
                            <td><strong style="color: #3182ce;">&bull; Inspeksi / Monitoring Berkala</strong></td>
                            <td class="text-center fw-bold">{{ number_format($totalInspeksi) }} ban</td>
                        </tr>
                        <tr style="background-color: #edf2f7;">
                            <td class="fw-bold">Total Transaksi Operasional</td>
                            <td class="text-center fw-bold">{{ number_format($totalPemasangan + $totalPelepasan + $totalRotasi + $totalInspeksi) }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>

            <!-- Right Column: Top Mode Kerusakan -->
            <td style="width: 52%; padding-left: 8px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Penyebab Kerusakan Terbanyak (Pelepasan)</th>
                            <th class="text-center" style="width: 22%;">Kasus</th>
                            <th class="text-center" style="width: 25%;">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topFailures as $fail)
                            <tr>
                                <td><strong>{{ $fail['code'] }}</strong> &ndash; {{ $fail['name'] }}</td>
                                <td class="text-center fw-bold">{{ $fail['count'] }}</td>
                                <td class="text-center">{{ $fail['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center" style="color:#718096; padding: 10px;">
                                    Tidak ada data kerusakan pelepasan ban pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- ROW 3: REKOMENDASI BAN PALING EFEKTIF -->
    <div class="section-title">3. Rekomendasi Ban Paling Efektif Berdasarkan Data Monitoring</div>

    @if ($bestCpkTyre || $bestLifeTyre)
        <div class="callout-box">
            <div class="callout-title">&#9733; Ringkasan Rekomendasi Utama (Executive Insight):</div>
            <p class="callout-desc">
                @if ($bestCpkTyre && $bestCpkTyre['cpk'] > 0)
                    &bull; <strong>Ban Paling Hemat Biaya (Best CPK):</strong> 
                    Merk <strong>{{ $bestCpkTyre['brand'] }}</strong> (Pattern: {{ $bestCpkTyre['pattern'] }}, Size: {{ $bestCpkTyre['size'] }}) dengan biaya operasional hanya <strong>Rp {{ number_format($bestCpkTyre['cpk'], 0, ',', '.') }}/KM</strong>.
                @endif
                <br>
                @if ($bestLifeTyre && $bestLifeTyre['avg_km'] > 0)
                    &bull; <strong>Ban Paling Tahan Lama (Best Durability):</strong> 
                    Merk <strong>{{ $bestLifeTyre['brand'] }}</strong> (Pattern: {{ $bestLifeTyre['pattern'] }}) mencapai umur pakai rata-rata <strong>{{ number_format($bestLifeTyre['avg_km'], 0, ',', '.') }} KM</strong>.
                @endif
            </p>
        </div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 18%;">Merk Ban</th>
                <th style="width: 15%;">Pola Tapak (Pattern)</th>
                <th style="width: 14%;">Ukuran (Size)</th>
                <th style="width: 8%;" class="text-center">Populasi</th>
                <th style="width: 14%;" class="text-right">Rata-rata KM</th>
                <th style="width: 12%;" class="text-right">Biaya/KM (CPK)</th>
                <th style="width: 14%;" class="text-center">Status Rekomendasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($effectiveTyres as $idx => $tyre)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $tyre['brand'] }}</strong></td>
                    <td>{{ $tyre['pattern'] }}</td>
                    <td>{{ $tyre['size'] }}</td>
                    <td class="text-center">{{ $tyre['count'] }}</td>
                    <td class="text-right fw-bold">{{ $tyre['avg_km'] > 0 ? number_format($tyre['avg_km'], 0, ',', '.') . ' KM' : '-' }}</td>
                    <td class="text-right fw-bold" style="color: {{ $tyre['cpk'] > 0 && $tyre['cpk'] <= ($avgCpk ?: 50) ? '#22543d' : '#2d3748' }};">
                        {{ $tyre['cpk'] > 0 ? 'Rp ' . number_format($tyre['cpk'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $tyre['badge'] }}">{{ $tyre['status'] }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 12px; color: #718096;">
                        Belum ada data ban dengan rekaman operasional untuk dianalisis.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- LEMBAR PENGESAHAN / APPROVAL -->
    <table class="signature-table">
        <tr>
            <td class="signature-box">
                <div class="signature-role">Dibuat & Dianalisis Oleh,<br><strong>PIC Tyreman / Staff</strong></div>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $user->name ?? 'PIC Tyre' }}</div>
            </td>
            <td class="signature-box">
                <div class="signature-role">Diperiksa & Diverifikasi Oleh,<br><strong>Fleet / Workshop Supervisor</strong></div>
                <div class="signature-line"></div>
                <div class="signature-name">( ............................................ )</div>
            </td>
            <td class="signature-box">
                <div class="signature-role">Disetujui Oleh,<br><strong>Management / Direksi</strong></div>
                <div class="signature-line"></div>
                <div class="signature-name">( ............................................ )</div>
            </td>
        </tr>
    </table>

    <!-- FOOTER -->
    <div class="report-footer">
        Laporan Eksekutif CPH Tyre Performance &bull; Dokumen resmi internal perusahaan &bull; Digenerate pada {{ $printDate }}
    </div>

</body>
</html>
