<?php

namespace App\Http\Controllers\TyrePerformance;

use App\Http\Controllers\Controller;
use App\Models\Tyre;
use App\Models\TyreMovement;
use App\Models\TyreBrand;
use App\Models\TyreLocation;
use App\Models\TyreFailureCode;
use App\Models\MasterImportKendaraan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ========================================
        // ROW 1: KPI Summary Cards
        // ========================================
        $totalTyres    = Tyre::count();
        $installedTyres = Tyre::where('status', 'Installed')->count();
        $inStockTyres   = Tyre::whereIn('status', ['New', 'Repaired'])->count();
        $scrappedTyres  = Tyre::where('status', 'Scrap')->count();

        // Total investment value
        $totalInvestment = Tyre::sum('price');

        // Average lifetime KM (only from tyres that have run)
        $avgLifetimeKm = Tyre::where('total_lifetime_km', '>', 0)->avg('total_lifetime_km') ?? 0;
        $avgLifetimeHm = Tyre::where('total_lifetime_hm', '>', 0)->avg('total_lifetime_hm') ?? 0;

        // Average Cost Per KM
        $tyresWithCpk = Tyre::where('total_lifetime_km', '>', 0)
            ->whereNotNull('price')
            ->where('price', '>', 0)
            ->select(DB::raw('SUM(price) as total_price, SUM(total_lifetime_km) as total_km'))
            ->first();
        $avgCpk = ($tyresWithCpk && $tyresWithCpk->total_km > 0)
            ? $tyresWithCpk->total_price / $tyresWithCpk->total_km
            : 0;

        // Scrap Rate %
        $scrapRate = $totalTyres > 0 ? round(($scrappedTyres / $totalTyres) * 100, 1) : 0;

        // Movement counts this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $installationsThisMonth = TyreMovement::where('movement_type', 'Installation')
            ->where('movement_date', '>=', $thisMonthStart)->count();
        $removalsThisMonth = TyreMovement::where('movement_type', 'Removal')
            ->where('movement_date', '>=', $thisMonthStart)->count();

        // ========================================
        // ROW 2: Charts Data
        // ========================================

        // 2a. Tyre Status Distribution (Donut Chart)
        $statusDistribution = Tyre::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // 2b. Monthly Movement Trend (last 6 months - Bar Chart)
        $monthlyMovements = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd   = Carbon::now()->subMonths($i)->endOfMonth();
            $monthLabel = $monthStart->format('M Y');

            $installs = TyreMovement::where('movement_type', 'Installation')
                ->whereBetween('movement_date', [$monthStart, $monthEnd])->count();
            $removals = TyreMovement::where('movement_type', 'Removal')
                ->whereBetween('movement_date', [$monthStart, $monthEnd])->count();

            $monthlyMovements[] = [
                'month'         => $monthLabel,
                'installations' => $installs,
                'removals'      => $removals,
            ];
        }

        // ========================================
        // ROW 3: Performance Analysis
        // ========================================

        // 3a. Top Brands by Average Lifetime KM
        $brandPerformance = Tyre::select(
                'tyre_brand_id',
                DB::raw('AVG(total_lifetime_km) as avg_km'),
                DB::raw('AVG(total_lifetime_hm) as avg_hm'),
                DB::raw('COUNT(*) as tyre_count')
            )
            ->where('total_lifetime_km', '>', 0)
            ->groupBy('tyre_brand_id')
            ->with('brand:id,brand_name')
            ->get()
            ->map(function ($item) {
                return [
                    'brand'    => $item->brand->brand_name ?? 'Unknown',
                    'avg_km'   => round($item->avg_km, 0),
                    'avg_hm'   => round($item->avg_hm, 0),
                    'count'    => $item->tyre_count,
                ];
            });

        // 3b. Cost Per KM by Brand
        $cpkByBrand = Tyre::select(
                'tyre_brand_id',
                DB::raw('SUM(price) as total_price'),
                DB::raw('SUM(total_lifetime_km) as total_km')
            )
            ->where('total_lifetime_km', '>', 0)
            ->whereNotNull('price')
            ->where('price', '>', 0)
            ->groupBy('tyre_brand_id')
            ->with('brand:id,brand_name')
            ->get()
            ->map(function ($item) {
                return [
                    'brand' => $item->brand->brand_name ?? 'Unknown',
                    'cpk'   => $item->total_km > 0 ? round($item->total_price / $item->total_km, 0) : 0,
                ];
            });

        // 3c. Tread Depth Analysis - Tyres nearing end of life (RTD < 5mm)
        $criticalTread = Tyre::where('status', 'Installed')
            ->whereNotNull('current_tread_depth')
            ->where('current_tread_depth', '<', 5)
            ->with(['brand', 'currentVehicle'])
            ->get();

        // ========================================
        // ROW 4: Inventory & Operational
        // ========================================

        // 4a. Stock by Location (Bar Chart)
        $locationStock = TyreLocation::select('location_name', 'current_stock', 'capacity')
            ->get();

        // 4b. Failure Code Distribution (Pie Chart)
        $failureDistribution = TyreMovement::where('movement_type', 'Removal')
            ->whereNotNull('failure_code_id')
            ->select('failure_code_id', DB::raw('count(*) as total'))
            ->groupBy('failure_code_id')
            ->with('failureCode:id,failure_code,failure_name')
            ->get()
            ->map(function ($item) {
                $fc = $item->failureCode;
                return [
                    'label' => $fc ? ($fc->failure_code . ' - ' . $fc->failure_name) : 'Unknown',
                    'total' => $item->total,
                ];
            });

        // 4c. Recent Movements (last 10)
        $recentMovements = TyreMovement::with(['tyre', 'vehicle', 'position'])
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // 4d. Tyres approaching replacement (top 5 lowest RTD installed)
        $lowRtdTyres = Tyre::where('status', 'Installed')
            ->whereNotNull('current_tread_depth')
            ->orderBy('current_tread_depth', 'asc')
            ->with(['brand', 'currentVehicle', 'currentPosition'])
            ->limit(5)
            ->get();

        // Total vehicles
        $totalVehicles = MasterImportKendaraan::count();

        return view('tyre-performance.dashboard', compact(
            // KPI
            'totalTyres', 'installedTyres', 'inStockTyres', 'scrappedTyres',
            'totalInvestment', 'avgLifetimeKm', 'avgLifetimeHm', 'avgCpk', 'scrapRate',
            'installationsThisMonth', 'removalsThisMonth',
            // Charts
            'statusDistribution', 'monthlyMovements',
            // Performance
            'brandPerformance', 'cpkByBrand', 'criticalTread',
            // Inventory & Operational
            'locationStock', 'failureDistribution', 'recentMovements', 'lowRtdTyres',
            'totalVehicles'
        ));
    }
}
