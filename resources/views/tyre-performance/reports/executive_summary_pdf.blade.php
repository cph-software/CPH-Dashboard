<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Executive Performance Report - CPH Tyre Performance</title>
    <style>
        @page {
            margin: 12mm 15mm 12mm 15mm;
            size: A4 portrait;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }

        /* HEADER */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2px solid #c0392b;
            padding-bottom: 8px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo-img {
            max-height: 44px;
            max-width: 150px;
        }

        .doc-title {
            margin: 0;
            color: #000;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-subtitle {
            margin: 3px 0 0 0;
            font-size: 9px;
            color: #c0392b;
            font-weight: bold;
        }

        .doc-module {
            margin: 2px 0 0 0;
            font-size: 8px;
            color: #777;
        }

        /* INFO TABLE */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .info-table td {
            padding: 5px 8px;
            vertical-align: top;
            border: 1px solid #e0e0e0;
            background: #fafafa;
        }

        .label {
            color: #666;
            font-size: 7.5px;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }

        .value {
            font-weight: bold;
            font-size: 9.5px;
            color: #111;
        }

        /* SECTION TITLE */
        .section-title {
            background: #c0392b;
            color: #fff;
            padding: 4px 8px;
            font-weight: bold;
            margin: 10px 0 6px 0;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* KPI METRIC TABLE */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .kpi-table td {
            width: 20%;
            border: 1px solid #ccc;
            background: #fff;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }

        .kpi-label {
            color: #666;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .kpi-value {
            font-size: 13px;
            font-weight: bold;
            color: #111;
            line-height: 1.2;
        }

        .kpi-unit {
            font-size: 8px;
            font-weight: normal;
            color: #666;
        }

        .kpi-sub {
            font-size: 7px;
            color: #888;
            margin-top: 2px;
        }

        /* TABLES */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .details-table th {
            background-color: #2c3e50;
            color: #fff;
            padding: 5px 4px;
            text-align: center;
            font-size: 8.5px;
            border: 1px solid #2c3e50;
            text-transform: uppercase;
        }

        .details-table td {
            padding: 4.5px 5px;
            border: 1px solid #ccc;
            font-size: 8.5px;
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

        /* DUAL COLUMN CONTAINER */
        .dual-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .dual-table td {
            vertical-align: top;
            padding: 0;
        }

        /* RECOMMENDATION HIGHLIGHT STRIP */
        .recom-highlight {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .recom-highlight td {
            padding: 6px 8px;
            vertical-align: top;
            background: #fdfdfd;
            border: 1px solid #ddd;
        }

        /* STATUS BADGES */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 7.5px;
            font-weight: bold;
            border-radius: 2px;
        }

        .badge-success   { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .badge-info      { background-color: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }
        .badge-warning   { background-color: #fff8e1; color: #f57f17; border: 1px solid #ffecb3; }
        .badge-secondary { background-color: #f5f5f5; color: #616161; border: 1px solid #e0e0e0; }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: -15px;
            left: 0;
            right: 0;
            font-size: 7.5px;
            color: #888;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 4px;
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
                    <div style="font-size: 16px; font-weight: bold; color: #c0392b;">CPH TYRE</div>
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

    <!-- 4. KONDISI PERGERAKAN BAN & ANALISIS PELEPASAN -->
    <div class="section-title">2. KONDISI PERGERAKAN BAN &amp; ANALISIS PELEPASAN</div>
    <table class="dual-table">
        <tr>
            <!-- Left: Aktivitas Pergerakan -->
            <td style="width: 48%; padding-right: 5px;">
                <table class="details-table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="text-align: left; padding-left: 8px;">Aktivitas Pergerakan</th>
                            <th style="width: 32%;">Jumlah Ban</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-left" style="padding-left: 8px;">&bull; Pemasangan (Installation)</td>
                            <td><strong>{{ number_format($totalPemasangan) }}</strong> ban</td>
                        </tr>
                        <tr class="bg-light">
                            <td class="text-left" style="padding-left: 8px;">&bull; Pelepasan (Removal)</td>
                            <td><strong>{{ number_format($totalPelepasan) }}</strong> ban</td>
                        </tr>
                        <tr>
                            <td class="text-left" style="padding-left: 8px;">&bull; Rotasi Posisi (Rotation)</td>
                            <td><strong>{{ number_format($totalRotasi) }}</strong> ban</td>
                        </tr>
                        <tr class="bg-light">
                            <td class="text-left" style="padding-left: 8px;">&bull; Inspeksi / Monitoring Berkala</td>
                            <td><strong>{{ number_format($totalInspeksi) }}</strong> ban</td>
                        </tr>
                        <tr style="background: #eef2f5;">
                            <td class="text-left" style="padding-left: 8px; font-weight: bold;">Total Transaksi Pergerakan</td>
                            <td><strong>{{ number_format($totalPemasangan + $totalPelepasan + $totalRotasi + $totalInspeksi) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </td>

            <!-- Right: Analisis Kerusakan Pelepasan -->
            <td style="width: 52%; padding-left: 5px;">
                <table class="details-table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="text-align: left; padding-left: 8px;">Penyebab Pelepasan Ban Terbanyak</th>
                            <th style="width: 20%;">Kasus</th>
                            <th style="width: 25%;">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topFailures as $idx => $fail)
                            <tr class="{{ $idx % 2 == 1 ? 'bg-light' : '' }}">
                                <td class="text-left" style="padding-left: 8px;">
                                    <strong>{{ $fail['code'] }}</strong> &ndash; {{ $fail['name'] }}
                                </td>
                                <td><strong>{{ $fail['count'] }}</strong></td>
                                <td><strong>{{ $fail['percentage'] }}%</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="color: #888; padding: 10px;">Tidak ada pelepasan ban karena kerusakan pada periode ini.</td>
                            </tr>
                        @endforelse
                        @if (count($topFailures) > 0 && count($topFailures) < 4)
                            @for ($i = count($topFailures); $i < 4; $i++)
                                <tr class="{{ $i % 2 == 1 ? 'bg-light' : '' }}">
                                    <td class="text-left" style="color: #bbb; padding-left: 8px;">-</td>
                                    <td style="color: #bbb;">-</td>
                                    <td style="color: #bbb;">-</td>
                                </tr>
                            @endfor
                        @endif
                    </tbody>
                </table>
            </td>
        </tr>
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
                        <div style="font-size: 8px; color: #555; margin-top: 2px;">
                            Biaya operasional terendah <strong>Rp {{ number_format($bestCpkTyre['cpk'], 0, ',', '.') }}/KM</strong> dengan laju keausan yang ekonomis.
                        </div>
                    </td>
                @endif
                @if ($bestLifeTyre)
                    <td style="width: 50%; border-left: 3px solid #2980b9;">
                        <span class="label" style="color: #2980b9;">[REKOMENDASI DURABILITAS TERTINGGI / BEST LIFETIME]</span>
                        <span class="value">{{ $bestLifeTyre['brand'] }} &bull; Pattern {{ $bestLifeTyre['pattern'] }} ({{ $bestLifeTyre['size'] }})</span>
                        <div style="font-size: 8px; color: #555; margin-top: 2px;">
                            Daya tahan paling awet dengan rata-rata umur pakai mencapai <strong>{{ number_format($bestLifeTyre['avg_km'], 0, ',', '.') }} KM</strong>.
                        </div>
                    </td>
                @endif
            </tr>
        </table>
    @endif

    <!-- Tabel Rincian Rekomendasi Ban -->
    <table class="details-table" style="margin-bottom: 6px;">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 17%; text-align: left; padding-left: 6px;">MERK BAN</th>
                <th style="width: 16%; text-align: left; padding-left: 6px;">POLA TAPAK (PATTERN)</th>
                <th style="width: 14%;">UKURAN (SIZE)</th>
                <th style="width: 8%;">POPULASI</th>
                <th style="width: 14%; text-align: right; padding-right: 6px;">RATA-RATA KM</th>
                <th style="width: 12%; text-align: right; padding-right: 6px;">BIAYA/KM (CPK)</th>
                <th style="width: 15%;">STATUS REKOMENDASI</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($effectiveTyres as $idx => $tyre)
                <tr class="{{ $idx % 2 == 1 ? 'bg-light' : '' }}">
                    <td>{{ $idx + 1 }}</td>
                    <td class="text-left" style="padding-left: 6px;"><strong>{{ $tyre['brand'] }}</strong></td>
                    <td class="text-left" style="padding-left: 6px;">{{ $tyre['pattern'] }}</td>
                    <td>{{ $tyre['size'] }}</td>
                    <td><strong>{{ $tyre['count'] }}</strong></td>
                    <td class="text-right" style="padding-right: 6px;">
                        <strong>{{ $tyre['avg_km'] > 0 ? number_format($tyre['avg_km'], 0, ',', '.') . ' KM' : '-' }}</strong>
                    </td>
                    <td class="text-right" style="padding-right: 6px; color: {{ $tyre['cpk'] > 0 && $tyre['cpk'] <= ($avgCpk ?: 50) ? '#27ae60' : '#222' }};">
                        <strong>{{ $tyre['cpk'] > 0 ? 'Rp ' . number_format($tyre['cpk'], 0, ',', '.') : '-' }}</strong>
                    </td>
                    <td>
                        <span class="badge {{ $tyre['badge'] }}">{{ $tyre['status'] }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="padding: 10px; color: #888;">Belum ada data ban dengan rekaman operasional untuk dianalisis.</td>
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
