<?php

namespace App\Http\Controllers\TyrePerformance;

use App\Http\Controllers\Controller;
use App\Models\Tyre;
use App\Models\TyreMovement;
use App\Services\DashboardAnalyticsService as DAS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardKpiController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()) return redirect()->route('login');
            return $next($request);
        });
    }

    /**
     * AJAX: KPI Card Deep Analytics dispatcher
     */
    public function kpiDetailAjax(Request $request)
    {
        $type = $request->get('type');
        $ctx = DAS::getCompanyContext();
        $mode = $ctx['mode'];

        switch ($type) {
            case 'total_tyres':       return $this->kpiTotalTyres($mode);
            case 'installed':         return $this->kpiInstalled($mode);
            case 'stock':             return $this->kpiStock($mode);
            case 'avg_lifetime':      return $this->kpiAvgLifetime($mode);
            case 'cost_per':          return $this->kpiCostPer($mode);
            case 'scrap_rate':        return $this->kpiScrapRate($mode);
            case 'monitoring_active': return $this->kpiMonitoringActive();
            case 'pending_checks':    return $this->kpiPendingChecks();
            case 'overdue_inspection':return $this->kpiOverdueInspection();
            default:
                return response()->json(['success' => false, 'message' => 'Unknown type']);
        }
    }

    private function kpiTotalTyres($mode)
    {
        $total = Tyre::count();
        $installed = Tyre::where('status', 'Installed')->count();
        $newCount = Tyre::where('status', 'New')->count();
        $repaired = Tyre::where('status', 'Repaired')->count();
        $scrap = Tyre::where('status', 'Scrap')->count();
        $retread = Tyre::where('status', 'Retread')->count();

        $byBrand = Tyre::select('tyre_brand_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('tyre_brand_id')->with('brand:id,brand_name')
            ->orderByDesc('cnt')->limit(10)->get()
            ->map(fn($i) => ['label' => $i->brand->brand_name ?? '-', 'value' => $i->cnt]);

        $bySize = Tyre::select('tyre_size_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('tyre_size_id')->with('size:id,size')
            ->orderByDesc('cnt')->limit(10)->get()
            ->map(fn($i) => ['label' => $i->size->size ?? '-', 'value' => $i->cnt]);

        [$ltCols, $ltKeys] = DAS::lifetimeCols($mode);
        $table = Tyre::with(['brand', 'size', 'pattern', 'location', 'currentVehicle'])
            ->orderBy('status')->limit(500)->get()
            ->map(fn($t) => array_merge([
                'serial_number' => $t->serial_number,
                'brand' => $t->brand->brand_name ?? '-',
                'size' => $t->size->size ?? '-',
                'pattern' => $t->pattern->name ?? '-',
                'status' => $t->status,
                'location' => $t->location->location_name ?? '-',
                'vehicle' => $t->currentVehicle->kode_kendaraan ?? '-',
            ], DAS::lifetimeData($t, $mode), [
                'price' => $t->price ? 'Rp ' . number_format($t->price, 0, ',', '.') : '-',
                '_url' => route('tyre-master.show', $t->id),
            ]));

        return response()->json([
            'success' => true, 'title' => 'Detail Total Ban',
            'summary' => [
                ['label' => 'Installed', 'value' => $installed, 'pct' => $total > 0 ? round($installed/$total*100,1).'%' : '0%', 'color' => 'success'],
                ['label' => 'New', 'value' => $newCount, 'pct' => $total > 0 ? round($newCount/$total*100,1).'%' : '0%', 'color' => 'primary'],
                ['label' => 'Repaired', 'value' => $repaired, 'pct' => $total > 0 ? round($repaired/$total*100,1).'%' : '0%', 'color' => 'info'],
                ['label' => 'Scrap', 'value' => $scrap, 'pct' => $total > 0 ? round($scrap/$total*100,1).'%' : '0%', 'color' => 'danger'],
            ],
            'charts' => [
                ['type' => 'donut', 'title' => 'Distribusi Status', 'labels' => ['Installed','New','Repaired','Scrap','Retread'], 'series' => [$installed,$newCount,$repaired,$scrap,$retread]],
                ['type' => 'bar', 'title' => 'Top Brand (Jumlah Ban)', 'labels' => $byBrand->pluck('label'), 'series' => $byBrand->pluck('value')],
            ],
            'columns' => array_merge(['Serial','Brand','Size','Pattern','Status','Lokasi','Kendaraan'], $ltCols, ['Harga']),
            'keys' => array_merge(['serial_number','brand','size','pattern','status','location','vehicle'], $ltKeys, ['price']),
            'data' => $table, 'total' => $total,
        ]);
    }

    private function kpiInstalled($mode)
    {
        $tyres = Tyre::where('status', 'Installed')
            ->with(['brand', 'size', 'pattern', 'currentVehicle', 'currentPosition'])->get();

        $byVehicle = $tyres->groupBy(fn($t) => $t->currentVehicle->kode_kendaraan ?? 'Unknown')
            ->map(fn($g, $k) => ['label' => $k, 'value' => $g->count()])
            ->sortByDesc('value')->values()->take(10);

        $byBrand = $tyres->groupBy(fn($t) => $t->brand->brand_name ?? 'Unknown')
            ->map(fn($g, $k) => ['label' => $k, 'value' => $g->count()])
            ->sortByDesc('value')->values();

        $topBrand = $byBrand->first()['label'] ?? '-';
        $topSize = $tyres->groupBy(fn($t) => $t->size->size ?? '-')
            ->map->count()->sortDesc()->keys()->first() ?? '-';
        $vehicles = $tyres->pluck('current_vehicle_id')->unique()->count();

        [$ltCols, $ltKeys] = DAS::lifetimeCols($mode);
        $table = $tyres->map(fn($t) => array_merge([
            'serial_number' => $t->serial_number,
            'brand' => $t->brand->brand_name ?? '-',
            'size' => $t->size->size ?? '-',
            'pattern' => $t->pattern->name ?? '-',
            'vehicle' => $t->currentVehicle->kode_kendaraan ?? '-',
            'position' => $t->currentPosition->position_name ?? '-',
            'otd' => $t->initial_tread_depth ? $t->initial_tread_depth.' mm' : '-',
            'rtd' => $t->current_tread_depth ? $t->current_tread_depth.' mm' : '-',
        ], DAS::lifetimeData($t, $mode), [
            '_url' => route('tyre-master.show', $t->id),
        ]));

        return response()->json([
            'success' => true, 'title' => 'Detail Ban Terpasang',
            'summary' => [
                ['label' => 'Total Terpasang', 'value' => $tyres->count(), 'color' => 'success'],
                ['label' => 'Jumlah Unit', 'value' => $vehicles, 'color' => 'primary'],
                ['label' => 'Brand Terbanyak', 'value' => $topBrand, 'color' => 'info'],
                ['label' => 'Size Terbanyak', 'value' => $topSize, 'color' => 'warning'],
            ],
            'charts' => [
                ['type' => 'bar', 'title' => 'Ban per Kendaraan (Top 10)', 'labels' => $byVehicle->pluck('label'), 'series' => $byVehicle->pluck('value')],
                ['type' => 'donut', 'title' => 'Distribusi Brand', 'labels' => $byBrand->pluck('label'), 'series' => $byBrand->pluck('value')],
            ],
            'columns' => array_merge(['Serial','Brand','Size','Pattern','Kendaraan','Posisi','OTD','RTD'], $ltCols),
            'keys' => array_merge(['serial_number','brand','size','pattern','vehicle','position','otd','rtd'], $ltKeys),
            'data' => $table, 'total' => $tyres->count(),
        ]);
    }

    private function kpiStock($mode)
    {
        $tyres = Tyre::whereIn('status', ['New', 'Repaired'])
            ->with(['brand', 'size', 'pattern', 'location'])->get();
        $newC = $tyres->where('status', 'New')->count();
        $repC = $tyres->where('status', 'Repaired')->count();

        $byLoc = $tyres->groupBy(fn($t) => $t->location->location_name ?? 'Unknown');
        $locData = $byLoc->map(fn($g, $k) => [
            'label' => $k, 'new' => $g->where('status', 'New')->count(), 'repaired' => $g->where('status', 'Repaired')->count(),
        ])->sortByDesc(fn($i) => $i['new'] + $i['repaired'])->values();

        $topLoc = $locData->first()['label'] ?? '-';
        $byBrand = $tyres->groupBy(fn($t) => $t->brand->brand_name ?? '-')
            ->map(fn($g, $k) => ['label' => $k, 'value' => $g->count()])
            ->sortByDesc('value')->values();

        $table = $tyres->map(fn($t) => [
            'serial_number' => $t->serial_number, 'brand' => $t->brand->brand_name ?? '-',
            'size' => $t->size->size ?? '-', 'pattern' => $t->pattern->name ?? '-',
            'status' => $t->status, 'location' => $t->location->location_name ?? '-',
            'price' => $t->price ? 'Rp '.number_format($t->price, 0, ',', '.') : '-',
            '_url' => route('tyre-master.show', $t->id),
        ]);

        return response()->json([
            'success' => true, 'title' => 'Detail Stok Tersedia',
            'summary' => [
                ['label' => 'Total Stok', 'value' => $tyres->count(), 'color' => 'info'],
                ['label' => 'New', 'value' => $newC, 'color' => 'primary'],
                ['label' => 'Repaired', 'value' => $repC, 'color' => 'success'],
                ['label' => 'Lokasi Terbanyak', 'value' => $topLoc, 'color' => 'warning'],
            ],
            'charts' => [
                ['type' => 'stacked_bar', 'title' => 'Stok per Lokasi', 'labels' => $locData->pluck('label'), 'series_new' => $locData->pluck('new'), 'series_repaired' => $locData->pluck('repaired')],
                ['type' => 'donut', 'title' => 'Brand di Stok', 'labels' => $byBrand->pluck('label'), 'series' => $byBrand->pluck('value')],
            ],
            'columns' => ['Serial','Brand','Size','Pattern','Status','Lokasi','Harga'],
            'keys' => ['serial_number','brand','size','pattern','status','location','price'],
            'data' => $table, 'total' => $tyres->count(),
        ]);
    }

    private function kpiAvgLifetime($mode)
    {
        [$ltCols, $ltKeys] = DAS::lifetimeCols($mode);
        $field = DAS::primaryField($mode);
        $chartLabel = $mode === 'HM' ? 'HM' : 'KM';
        $query = Tyre::query()->with(['brand', 'size', 'pattern', 'currentVehicle']);
        DAS::applyLifetimeFilter($query, $mode);
        $tyres = $query->orderByDesc($field)->limit(500)->get();

        $summaryItems = [];
        if ($mode !== 'HM') {
            $avgKm = $tyres->where('total_lifetime_km', '>', 0)->avg('total_lifetime_km') ?? 0;
            $maxKm = $tyres->max('total_lifetime_km') ?? 0;
            $summaryItems[] = ['label' => 'Avg KM', 'value' => number_format($avgKm, 0).' KM', 'color' => 'warning'];
            $summaryItems[] = ['label' => 'Max KM', 'value' => number_format($maxKm, 0).' KM', 'color' => 'success'];
        }
        if ($mode !== 'KM') {
            $avgHm = $tyres->where('total_lifetime_hm', '>', 0)->avg('total_lifetime_hm') ?? 0;
            $maxHm = $tyres->max('total_lifetime_hm') ?? 0;
            $summaryItems[] = ['label' => 'Avg HM', 'value' => number_format($avgHm, 0).' HM', 'color' => 'info'];
            $summaryItems[] = ['label' => 'Max HM', 'value' => number_format($maxHm, 0).' HM', 'color' => 'primary'];
        }

        $table = $tyres->map(fn($t) => array_merge([
            'serial_number' => $t->serial_number, 'brand' => $t->brand->brand_name ?? '-',
            'size' => $t->size->size ?? '-', 'pattern' => $t->pattern->name ?? '-',
        ], DAS::lifetimeData($t, $mode), [
            'status' => $t->status, 'vehicle' => $t->currentVehicle->kode_kendaraan ?? '-',
            'price' => $t->price ? 'Rp '.number_format($t->price, 0, ',', '.') : '-',
            '_url' => route('tyre-master.show', $t->id),
        ]));

        $sortField = $tyres->avg('total_lifetime_hm') > $tyres->avg('total_lifetime_km') ? 'total_lifetime_hm' : 'total_lifetime_km';
        $sortLabel = $sortField === 'total_lifetime_hm' ? 'HM' : 'KM';
        $top10 = $tyres->sortByDesc($sortField)->take(10)->map(fn($t) => ['label' => $t->serial_number, 'value' => $t->{$sortField}]);
        $byBrand = $tyres->groupBy(fn($t) => $t->brand->brand_name ?? '-')
            ->map(fn($g, $k) => ['label' => $k, 'value' => round($g->avg($sortField))])
            ->sortByDesc('value')->values();

        return response()->json([
            'success' => true, 'title' => 'Detail Avg Lifetime' . ($mode === 'BOTH' ? ' (KM & HM)' : ' ('.$chartLabel.')'),
            'summary' => $summaryItems,
            'charts' => [
                ['type' => 'bar', 'title' => 'Top 10 Ban Terjauh ('.$sortLabel.')', 'labels' => $top10->pluck('label'), 'series' => $top10->pluck('value')],
                ['type' => 'bar', 'title' => 'Avg Lifetime per Brand ('.$sortLabel.')', 'labels' => $byBrand->pluck('label'), 'series' => $byBrand->pluck('value')],
            ],
            'columns' => array_merge(['Serial','Brand','Size','Pattern'], $ltCols, ['Status','Kendaraan','Harga']),
            'keys' => array_merge(['serial_number','brand','size','pattern'], $ltKeys, ['status','vehicle','price']),
            'data' => $table, 'total' => $tyres->count(),
        ]);
    }

    private function kpiCostPer($mode)
    {
        [$ltCols, $ltKeys] = DAS::lifetimeCols($mode);
        $field = DAS::primaryField($mode);
        $cpLabel = $mode === 'HM' ? 'CPH' : ($mode === 'KM' ? 'CPK' : 'CPK/CPH');
        $query2 = Tyre::query()->whereNotNull('price')->where('price', '>', 0)
            ->with(['brand', 'size', 'pattern', 'currentVehicle']);
        DAS::applyLifetimeFilter($query2, $mode);
        $tyres = $query2->get();

        $tyres = $tyres->map(function($t) use ($mode) {
            $t->cpk_val = $t->total_lifetime_km > 0 ? round($t->price / $t->total_lifetime_km, 2) : 0;
            $t->cph_val = $t->total_lifetime_hm > 0 ? round($t->price / $t->total_lifetime_hm, 2) : 0;
            if ($mode === 'HM') { $t->sort_val = $t->cph_val; }
            elseif ($mode === 'KM') { $t->sort_val = $t->cpk_val; }
            else { $t->sort_val = $t->cph_val > 0 ? $t->cph_val : $t->cpk_val; }
            return $t;
        })->sortBy('sort_val');

        $totalInv = $tyres->sum('price');
        $avgCpk = $tyres->avg('cpk_val') ?? 0;
        $best = $tyres->first();
        $worst = $tyres->last();

        $byBrand = $tyres->groupBy(fn($t) => $t->brand->brand_name ?? '-')
            ->map(fn($g, $k) => ['label' => $k, 'value' => round($g->sum('price') / max($g->sum($field), 1))])
            ->sortBy('value')->values();

        $tableData = $tyres->map(function($t) use ($mode) {
            $row = [
                'serial_number' => $t->serial_number, 'brand' => $t->brand->brand_name ?? '-',
                'size' => $t->size->size ?? '-', 'pattern' => $t->pattern->name ?? '-',
                'price' => 'Rp '.number_format($t->price, 0, ',', '.'),
            ];
            $row = array_merge($row, DAS::lifetimeData($t, $mode));
            $row['cpk'] = 'Rp '.number_format($t->cpk_val, 0, ',', '.');
            if ($mode === 'BOTH') $row['cph'] = 'Rp '.number_format($t->cph_val ?? 0, 0, ',', '.');
            $row['status'] = $t->status;
            return $row;
        });

        $cpCols = $mode === 'BOTH' ? ['CPK','CPH'] : [$cpLabel];
        $cpKeys = $mode === 'BOTH' ? ['cpk','cph'] : ['cpk'];

        return response()->json([
            'success' => true, 'title' => 'Detail ' . $cpLabel,
            'summary' => [
                ['label' => 'Avg '.$cpLabel, 'value' => 'Rp '.number_format($avgCpk, 0, ',', '.'), 'color' => 'secondary'],
                ['label' => 'Total Investasi', 'value' => 'Rp '.number_format($totalInv, 0, ',', '.'), 'color' => 'primary'],
                ['label' => 'Paling Efisien', 'value' => $best ? $best->serial_number : '-', 'color' => 'success'],
                ['label' => 'Paling Mahal', 'value' => $worst ? $worst->serial_number : '-', 'color' => 'danger'],
            ],
            'charts' => [
                ['type' => 'bar', 'title' => $cpLabel.' per Brand', 'labels' => $byBrand->pluck('label'), 'series' => $byBrand->pluck('value')],
                ['type' => 'bar', 'title' => 'Top 10 Paling Efisien', 'labels' => $tyres->take(10)->pluck('serial_number'), 'series' => $tyres->take(10)->pluck('cpk_val')],
            ],
            'columns' => array_merge(['Serial','Brand','Size','Pattern','Harga'], $ltCols, $cpCols, ['Status']),
            'keys' => array_merge(['serial_number','brand','size','pattern','price'], $ltKeys, $cpKeys, ['status']),
            'data' => $tableData, 'total' => $tyres->count(),
        ]);
    }

    private function kpiScrapRate($mode)
    {
        [$ltCols, $ltKeys] = DAS::lifetimeCols($mode);
        $field = DAS::primaryField($mode);
        $chartLabel = $mode === 'HM' ? 'HM' : 'KM';
        $scrapped = Tyre::where('status', 'Scrap')->with(['brand', 'size', 'pattern', 'location'])->get();
        $total = Tyre::count();

        $removals = TyreMovement::where('movement_type', 'Removal')->where('target_status', 'Scrap')
            ->whereNotNull('failure_code_id')
            ->select('failure_code_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('failure_code_id')->with('failureCode')->orderByDesc('cnt')->get();

        $failureLabels = $removals->map(fn($r) => $r->failureCode ? $r->failureCode->failure_code.' - '.$r->failureCode->failure_name : 'Unknown');
        $failureSeries = $removals->pluck('cnt');
        $topFailure = $failureLabels->first() ?? '-';

        $byBrand = $scrapped->groupBy(fn($t) => $t->brand->brand_name ?? '-')
            ->map(fn($g, $k) => ['label' => $k, 'value' => $g->count()])
            ->sortByDesc('value')->values();
        $avgLife = $scrapped->avg($field) ?? 0;

        $table = $scrapped->map(fn($t) => array_merge([
            'serial_number' => $t->serial_number, 'brand' => $t->brand->brand_name ?? '-',
            'size' => $t->size->size ?? '-', 'pattern' => $t->pattern->name ?? '-',
            'location' => $t->location->location_name ?? '-',
        ], DAS::lifetimeData($t, $mode), [
            '_url' => route('tyre-master.show', $t->id),
        ]));

        return response()->json([
            'success' => true, 'title' => 'Detail Scrap Rate',
            'summary' => [
                ['label' => 'Total Scrap', 'value' => $scrapped->count(), 'color' => 'danger'],
                ['label' => 'Scrap Rate', 'value' => ($total > 0 ? round($scrapped->count()/$total*100,1) : 0).'%', 'color' => 'warning'],
                ['label' => 'Penyebab #1', 'value' => $topFailure, 'color' => 'info'],
                ['label' => 'Avg Life (Scrap)', 'value' => number_format($avgLife).' '.$chartLabel, 'color' => 'secondary'],
            ],
            'charts' => [
                ['type' => 'donut', 'title' => 'Penyebab Scrap', 'labels' => $failureLabels->values(), 'series' => $failureSeries->values()],
                ['type' => 'bar', 'title' => 'Brand Paling Sering Scrap', 'labels' => $byBrand->pluck('label'), 'series' => $byBrand->pluck('value')],
            ],
            'columns' => array_merge(['Serial','Brand','Size','Pattern','Lokasi'], $ltCols),
            'keys' => array_merge(['serial_number','brand','size','pattern','location'], $ltKeys),
            'data' => $table, 'total' => $scrapped->count(),
        ]);
    }

    private function kpiMonitoringActive()
    {
        $sessions = \App\Models\TyreMonitoringSession::where('status', 'active')->with(['vehicle', 'masterVehicle'])->get();
        $table = $sessions->map(fn($s) => [
            'vehicle' => optional($s->masterVehicle)->kode_kendaraan ?? optional($s->vehicle)->fleet_name ?? optional($s->vehicle)->vehicle_number ?? '-', 'status' => $s->status,
            'created' => $s->created_at ? $s->created_at->format('d/m/Y') : '-',
            '_url' => route('monitoring.sessions.show', $s->session_id),
        ]);
        return response()->json([
            'success' => true, 'title' => 'Sesi Monitoring Aktif',
            'summary' => [['label' => 'Total Aktif', 'value' => $sessions->count(), 'color' => 'info']],
            'charts' => [], 'columns' => ['Kendaraan','Status','Tanggal Mulai'],
            'keys' => ['vehicle','status','created'], 'data' => $table, 'total' => $sessions->count(),
        ]);
    }

    private function kpiPendingChecks()
    {
        $checks = \App\Models\TyreMonitoringCheck::where('approval_status', 'Pending')
            ->with(['session.vehicle', 'session.masterVehicle'])->limit(200)->get();
        $table = $checks->map(fn($c) => [
            'vehicle' => optional(optional($c->session)->masterVehicle)->kode_kendaraan ?? optional(optional($c->session)->vehicle)->fleet_name ?? optional(optional($c->session)->vehicle)->vehicle_number ?? '-',
            'check_no' => 'Check #'.$c->check_number,
            'date' => $c->check_date ? Carbon::parse($c->check_date)->format('d/m/Y') : '-',
            '_url' => $c->session ? route('monitoring.sessions.show', $c->session->session_id) : '#',
        ]);
        return response()->json([
            'success' => true, 'title' => 'Pending Approval',
            'summary' => [['label' => 'Total Pending', 'value' => $checks->count(), 'color' => 'warning']],
            'charts' => [], 'columns' => ['Kendaraan','Check','Tanggal'],
            'keys' => ['vehicle','check_no','date'], 'data' => $table, 'total' => $checks->count(),
        ]);
    }

    private function kpiOverdueInspection()
    {
        $tyres = Tyre::where('status', 'Installed')
            ->where(fn($q) => $q->whereNull('last_inspection_date')->orWhere('last_inspection_date', '<', Carbon::now()->subDays(30)))
            ->with(['brand', 'currentVehicle'])->limit(500)->get();
        $over60 = $tyres->filter(fn($t) => !$t->last_inspection_date || Carbon::parse($t->last_inspection_date)->lt(Carbon::now()->subDays(60)))->count();
        $over90 = $tyres->filter(fn($t) => !$t->last_inspection_date || Carbon::parse($t->last_inspection_date)->lt(Carbon::now()->subDays(90)))->count();
        $table = $tyres->map(fn($t) => [
            'serial_number' => $t->serial_number, 'brand' => $t->brand->brand_name ?? '-',
            'vehicle' => $t->currentVehicle->kode_kendaraan ?? '-',
            'last_inspection' => $t->last_inspection_date ? Carbon::parse($t->last_inspection_date)->format('d/m/Y') : 'Belum pernah',
            'days' => $t->last_inspection_date ? Carbon::parse($t->last_inspection_date)->diffInDays(now()).' hari' : '∞',
            '_url' => route('tyre-master.show', $t->id),
        ]);
        return response()->json([
            'success' => true, 'title' => 'Overdue Inspeksi (> 30 Hari)',
            'summary' => [
                ['label' => 'Total Overdue', 'value' => $tyres->count(), 'color' => 'danger'],
                ['label' => '> 60 Hari', 'value' => $over60, 'color' => 'warning'],
                ['label' => '> 90 Hari', 'value' => $over90, 'color' => 'danger'],
            ],
            'charts' => [], 'columns' => ['Serial','Brand','Kendaraan','Inspeksi Terakhir','Overdue'],
            'keys' => ['serial_number','brand','vehicle','last_inspection','days'],
            'data' => $table, 'total' => $tyres->count(),
        ]);
    }
}
