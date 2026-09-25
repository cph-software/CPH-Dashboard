<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Executive Summary Report - CPH Tyre Performance</title>
    <style>
        @page {
            margin: 10mm 12mm 10mm 12mm;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            line-height: 1.3;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        /* HEADER AREA */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            border-bottom: 2px solid #0f2942;
            padding-bottom: 6px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-img {
            max-height: 48px;
            max-width: 150px;
        }
        .doc-title {
            font-size: 15pt;
            font-weight: bold;
            color: #0f2942;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .doc-subtitle {
            font-size: 8.5pt;
            font-weight: 600;
            color: #2563eb;
            margin: 2px 0 0 0;
            letter-spacing: 0.2px;
        }

        /* METADATA BAR */
        .meta-container {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .meta-container td {
            padding: 5px 8px;
            font-size: 8pt;
            vertical-align: middle;
        }
        .meta-label {
            color: #64748b;
            font-weight: bold;
            width: 17%;
        }
        .meta-val {
            color: #0f172a;
            font-weight: 600;
            width: 33%;
        }

        /* SECTION TITLES */
        .section-header {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 5px 0;
        }
        .section-header td {
            padding: 0;
            vertical-align: middle;
        }
        .section-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f2942;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-left: 3.5px solid #2563eb;
            padding-left: 6px;
            margin: 0;
        }

        /* KPI METRIC CARDS */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px 0;
            margin-bottom: 10px;
        }
        .kpi-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #2563eb;
            border-radius: 4px;
            padding: 6px 4px;
            text-align: center;
        }
        .kpi-card.emerald { border-top-color: #059669; }
        .kpi-card.indigo  { border-top-color: #4f46e5; }
        .kpi-card.purple  { border-top-color: #7c3aed; }
        .kpi-card.amber   { border-top-color: #d97706; }

        .kpi-label {
            font-size: 7pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }
        .kpi-number {
            font-size: 13.5pt;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            line-height: 1.1;
        }
        .kpi-unit {
            font-size: 7.5pt;
            font-weight: normal;
            color: #64748b;
        }
        .kpi-footnote {
            font-size: 6.8pt;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* DUAL COLUMN SECTION */
        .dual-col-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .dual-col-table td {
            vertical-align: top;
        }

        /* TABLES */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 8px;
        }
        .report-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 5px 6px;
            border: 1px solid #0f172a;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .report-table td {
            padding: 4px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .report-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }

        /* PERCENTAGE BAR */
        .progress-track {
            background-color: #e2e8f0;
            border-radius: 3px;
            width: 100%;
            height: 8px;
            overflow: hidden;
            display: inline-block;
            margin-top: 2px;
        }
        .progress-fill {
            background-color: #2563eb;
            height: 8px;
            border-radius: 3px;
        }

        /* RECOMMENDATION HIGHLIGHT BOXES */
        .recom-cards-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 8px;
        }
        .recom-box {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 8px;
            vertical-align: top;
        }
        .recom-box.best-cpk {
            background-color: #f0fdf4;
            border-color: #86efac;
            border-left: 3.5px solid #16a34a;
        }
        .recom-box.best-life {
            background-color: #eff6ff;
            border-color: #93c5fd;
            border-left: 3.5px solid #2563eb;
        }
        .recom-tag {
            font-size: 6.8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }
        .recom-title {
            font-size: 9pt;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 2px 0;
        }
        .recom-desc {
            font-size: 7.5pt;
            color: #334155;
            margin: 0;
            line-height: 1.25;
        }

        /* STATUS BADGES */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 6.8pt;
            font-weight: bold;
            border-radius: 3px;
            text-align: center;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-info    { background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-warning { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-secondary { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        /* SIGNATURE BOXES */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .sig-cell {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }
        .sig-role {
            font-size: 7.8pt;
            font-weight: bold;
            color: #475569;
            margin-bottom: 45px;
            line-height: 1.25;
        }
        .sig-line {
            border-bottom: 1px solid #334155;
            margin-bottom: 3px;
        }
        .sig-name {
            font-size: 8pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .sig-sub {
            font-size: 7pt;
            color: #64748b;
        }

        /* FOOTER */
        .footer-note {
            margin-top: 12px;
            padding-top: 4px;
            border-top: 1px solid #e2e8f0;
            font-size: 6.8pt;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- HEADER BRANDING -->
    <table class="header-table">
        <tr>
            <td style="width: 28%;">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo-img" alt="CPH Tyre">
                @else
                    <div style="font-size: 16pt; font-weight: bold; color: #0f2942; letter-spacing: 0.5px;">CPH TYRE</div>
                @endif
            </td>
            <td style="width: 72%; text-align: right;">
                <div class="doc-title">Executive Performance Report</div>
                <div class="doc-subtitle">Analisis Pergerakan, Umur Pakai &amp; Rekomendasi Efektivitas Ban</div>
            </td>
        </tr>
    </table>

    <!-- METADATA BOX -->
    <table class="meta-container">
        <tr>
            <td class="meta-label">Entitas / Perusahaan:</td>
            <td class="meta-val">{{ $companyName }}</td>
            <td class="meta-label">Periode Analisis:</td>
            <td class="meta-val">{{ $startDate->format('d/m/Y') }} &ndash; {{ $endDate->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Waktu Unduh:</td>
            <td class="meta-val">{{ $printDate }}</td>
            <td class="meta-label">Disiapkan Oleh:</td>
            <td class="meta-val">{{ $user->name ?? 'User Sistem' }} ({{ $user->role->name ?? 'Admin' }})</td>
        </tr>
    </table>

    <!-- 1. KEY PERFORMANCE INDICATORS (KPI) -->
    <table class="section-header">
        <tr>
            <td><div class="section-title">1. Ringkasan Eksekutif &amp; Key Performance Indicators (KPI)</div></td>
        </tr>
    </table>
    <table class="kpi-table">
        <tr>
            <td style="width: 20%;">
                <div class="kpi-card">
                    <div class="kpi-label">Populasi Ban</div>
                    <div class="kpi-number">{{ number_format($totalTyres, 0, ',', '.') }}</div>
                    <div class="kpi-footnote">{{ $installedTyres }} Pasang &bull; {{ $inStockTyres }} Gudang</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="kpi-card emerald">
                    <div class="kpi-label">Rata-rata Lifetime</div>
                    <div class="kpi-number">{{ number_format($avgKm, 0, ',', '.') }} <span class="kpi-unit">KM</span></div>
                    <div class="kpi-footnote">atau {{ number_format($avgHm, 0, ',', '.') }} HM</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="kpi-card indigo">
                    <div class="kpi-label">Cost Per KM (CPK)</div>
                    <div class="kpi-number"><span style="font-size: 9pt; font-weight: normal;">Rp</span> {{ number_format($avgCpk, 0, ',', '.') }}</div>
                    <div class="kpi-footnote">Rata-rata Biaya Operasional/KM</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="kpi-card purple">
                    <div class="kpi-label">Total Investasi Ban</div>
                    <div class="kpi-number"><span style="font-size: 9pt; font-weight: normal;">Rp</span> {{ number_format($totalInvestment / 1000000, 1, ',', '.') }}<span class="kpi-unit"> Jt</span></div>
                    <div class="kpi-footnote">Nilai Perolehan Seluruh Ban</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="kpi-card amber">
                    <div class="kpi-label">Status Kritis (RTD &lt; 5mm)</div>
                    <div class="kpi-number" style="color: {{ $criticalTyres->count() > 0 ? '#dc2626' : '#15803d' }};">
                        {{ $criticalTyres->count() }} <span class="kpi-unit">Ban</span>
                    </div>
                    <div class="kpi-footnote">{{ $criticalTyres->count() > 0 ? 'Perlu Penggantian Segera' : 'Kondisi Tapak Terjaga Baik' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- 2. KONDISI PERGERAKAN BAN & KERUSAKAN -->
    <table class="section-header">
        <tr>
            <td><div class="section-title">2. Kondisi Pergerakan Ban &amp; Analisis Kerusakan (Pelepasan)</div></td>
        </tr>
    </table>
    <table class="dual-col-table">
        <tr>
            <!-- Left Panel: Aktivitas Pergerakan -->
            <td style="width: 48%; padding-right: 5px;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Aktivitas Pergerakan Periode Ini</th>
                            <th class="text-center" style="width: 32%;">Jumlah Ban</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong style="color: #15803d;">&bull; Pemasangan (Installation)</strong></td>
                            <td class="text-center fw-bold">{{ number_format($totalPemasangan) }} ban</td>
                        </tr>
                        <tr>
                            <td><strong style="color: #dc2626;">&bull; Pelepasan (Removal)</strong></td>
                            <td class="text-center fw-bold">{{ number_format($totalPelepasan) }} ban</td>
                        </tr>
                        <tr>
                            <td><strong style="color: #d97706;">&bull; Rotasi Posisi (Rotation)</strong></td>
                            <td class="text-center fw-bold">{{ number_format($totalRotasi) }} ban</td>
                        </tr>
                        <tr>
                            <td><strong style="color: #2563eb;">&bull; Inspeksi / Monitoring Berkala</strong></td>
                            <td class="text-center fw-bold">{{ number_format($totalInspeksi) }} ban</td>
                        </tr>
                        <tr style="background-color: #f1f5f9;">
                            <td class="fw-bold">Total Transaksi Pergerakan</td>
                            <td class="text-center fw-bold">{{ number_format($totalPemasangan + $totalPelepasan + $totalRotasi + $totalInspeksi) }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>

            <!-- Right Panel: Analisis Kerusakan -->
            <td style="width: 52%; padding-left: 5px;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Penyebab Pelepasan Ban Terbanyak</th>
                            <th class="text-center" style="width: 20%;">Kasus</th>
                            <th class="text-center" style="width: 24%;">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topFailures as $fail)
                            <tr>
                                <td>
                                    <strong>{{ $fail['code'] }}</strong> &ndash; {{ $fail['name'] }}
                                    <div class="progress-track">
                                        <div class="progress-fill" style="width: {{ min(100, $fail['percentage']) }}%;"></div>
                                    </div>
                                </td>
                                <td class="text-center fw-bold">{{ $fail['count'] }}</td>
                                <td class="text-center fw-bold">{{ $fail['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center" style="color: #64748b; padding: 12px;">
                                    Tidak ada catatan pelepasan karena kerusakan pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- 3. REKOMENDASI BAN PALING EFEKTIF -->
    <table class="section-header">
        <tr>
            <td><div class="section-title">3. Rekomendasi Ban Paling Efektif Berdasarkan Data Pemakaian</div></td>
        </tr>
    </table>

    <!-- HIGHLIGHT REKOMENDASI UTAMA (DUAL TILES) -->
    @if ($bestCpkTyre || $bestLifeTyre)
        <table class="recom-cards-table">
            <tr>
                <td style="width: 50%;">
                    <div class="recom-box best-cpk">
                        <div class="recom-tag" style="color: #16a34a;">[REKOMENDASI BIAYA TERBAIK]</div>
                        <div class="recom-title">
                            {{ $bestCpkTyre['brand'] ?? '-' }} &bull; Pattern {{ $bestCpkTyre['pattern'] ?? '-' }}
                        </div>
                        <div class="recom-desc">
                            Ukuran <strong>{{ $bestCpkTyre['size'] ?? '-' }}</strong> membukukan biaya operasional terendah sebesar 
                            <strong style="color: #15803d;">Rp {{ number_format($bestCpkTyre['cpk'] ?? 0, 0, ',', '.') }}/KM</strong> 
                            dengan laju keausan yang sangat ekonomis.
                        </div>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="recom-box best-life">
                        <div class="recom-tag" style="color: #2563eb;">[REKOMENDASI DURABILITAS TERTINGGI]</div>
                        <div class="recom-title">
                            {{ $bestLifeTyre['brand'] ?? '-' }} &bull; Pattern {{ $bestLifeTyre['pattern'] ?? '-' }}
                        </div>
                        <div class="recom-desc">
                            Tercatat sebagai ban paling awet dengan rata-rata umur pakai mencapai 
                            <strong style="color: #1d4ed8;">{{ number_format($bestLifeTyre['avg_km'] ?? 0, 0, ',', '.') }} KM</strong>, 
                            sangat direkomendasikan untuk armada operasional jarak jauh.
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    @endif

    <!-- TABEL PERINGKAT & REKOMENDASI -->
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">No</th>
                <th style="width: 17%;">Merk Ban</th>
                <th style="width: 15%;">Pola Tapak (Pattern)</th>
                <th style="width: 14%;">Ukuran (Size)</th>
                <th style="width: 8%;" class="text-center">Populasi</th>
                <th style="width: 14%;" class="text-right">Rata-rata KM</th>
                <th style="width: 12%;" class="text-right">Biaya/KM (CPK)</th>
                <th style="width: 16%;" class="text-center">Status Rekomendasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($effectiveTyres as $idx => $tyre)
                <tr>
                    <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                    <td><strong>{{ $tyre['brand'] }}</strong></td>
                    <td>{{ $tyre['pattern'] }}</td>
                    <td>{{ $tyre['size'] }}</td>
                    <td class="text-center fw-bold">{{ $tyre['count'] }}</td>
                    <td class="text-right fw-bold">{{ $tyre['avg_km'] > 0 ? number_format($tyre['avg_km'], 0, ',', '.') . ' KM' : '-' }}</td>
                    <td class="text-right fw-bold" style="color: {{ $tyre['cpk'] > 0 && $tyre['cpk'] <= ($avgCpk ?: 50) ? '#15803d' : '#0f172a' }};">
                        {{ $tyre['cpk'] > 0 ? 'Rp ' . number_format($tyre['cpk'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $tyre['badge'] }}">{{ $tyre['status'] }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 12px; color: #64748b;">
                        Belum ada data ban dengan rekaman operasional untuk dianalisis.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- LEMBAR PENGESAHAN & APPROVAL -->
    <table class="sig-table">
        <tr>
            <td class="sig-cell">
                <div class="sig-role">Dibuat &amp; Dianalisis Oleh,<br><strong>PIC Tyreman / Staff</strong></div>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $user->name ?? 'PIC TYRE' }}</div>
                <div class="sig-sub">Staff Operasional</div>
            </td>
            <td class="sig-cell">
                <div class="sig-role">Diperiksa &amp; Diverifikasi Oleh,<br><strong>Fleet / Workshop Supervisor</strong></div>
                <div class="sig-line"></div>
                <div class="sig-name">( ............................................ )</div>
                <div class="sig-sub">Supervisor Lapangan</div>
            </td>
            <td class="sig-cell">
                <div class="sig-role">Disetujui Oleh,<br><strong>Management / Direksi</strong></div>
                <div class="sig-line"></div>
                <div class="sig-name">( ............................................ )</div>
                <div class="sig-sub">Fleet Management / Owner</div>
            </td>
        </tr>
    </table>

    <!-- REPORT FOOTER -->
    <div class="footer-note">
        CPH Tyre Performance System &bull; Laporan Resmi Eksekutif &bull; Digenerate pada {{ $printDate }} &bull; Dokumen Rahasia Internal
    </div>

</body>
</html>
