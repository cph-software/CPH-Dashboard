<?php

namespace App\Http\Controllers\TyrePerformance;

use App\Http\Controllers\Controller;
use App\Models\Tyre;
use App\Models\TyreMovement;
use App\Models\TyreBrand;
use App\Models\TyreSize;
use App\Models\TyrePattern;
use App\Models\TyreLocation;
use App\Models\TyreFailureCode;
use App\Models\MasterImportKendaraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user) {
                return redirect()->route('login');
            }

            // Check Access to Tyre Performance App (ID 20)
            $userApps = getAplikasiPerRole($user->role_id);
            if (!$userApps->contains('id', 20)) {
                if ($request->ajax()) {
                    return response()->json(['error' => 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses Tyre Monitoring.'], 403);
                }
                return redirect('/dashboard')->with('error', 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses Tyre Monitoring.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        // ========================================
        // Filters
        // ========================================
        // Logic filter tanggal (Default ke 6 bulan terakhir)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        } else {
            $startDate = Carbon::now()->subMonths(5)->startOfMonth();
            $endDate = Carbon::now()->endOfDay();
        }

        // ========================================
        // ROW 1: KPI Summary Cards
        // ========================================
        $totalTyres = Tyre::count();
        $installedTyres = Tyre::where('status', 'Installed')->count();
        $inStockTyres = Tyre::whereIn('status', ['New', 'Repaired'])->count();
        $scrappedTyres = Tyre::where('status', 'Scrap')->count();

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

        // Movement counts in filtered range
        $installationsThisMonth = TyreMovement::where('movement_type', 'Installation')
            ->whereBetween('movement_date', [$startDate, $endDate])->count();
        $removalsThisMonth = TyreMovement::where('movement_type', 'Removal')
            ->whereBetween('movement_date', [$startDate, $endDate])->count();

        // ========================================
        // ROW 2: Charts Data
        // ========================================

        // 2a. Tyre Status Distribution (Donut Chart) - Enhanced with Retread Count
        $statusDistribution = Tyre::select(
            DB::raw("CASE 
                WHEN status = 'New' THEN 'New (R0)'
                WHEN status = 'Retread' AND retread_count = 1 THEN 'Retread R1'
                WHEN status = 'Retread' AND retread_count = 2 THEN 'Retread R2'
                WHEN status = 'Retread' AND retread_count = 3 THEN 'Retread R3'
                WHEN status = 'Retread' AND retread_count >= 4 THEN 'Retread R4+'
                WHEN status = 'Installed' THEN 'Installed'
                WHEN status = 'Repaired' THEN 'Repaired'
                WHEN status = 'Scrap' THEN 'Scrap'
                ELSE status
            END as status_label"),
            DB::raw('count(*) as total')
        )
            ->groupBy('status_label')
            ->pluck('total', 'status_label')
            ->toArray();

        // 2b. Monthly Movement Trend - Filtered
        $monthlyMovements = [];
        // Gunakan startOfMonth untuk kedua tanggal agar perbandingan per bulan akurat
        $periodStart = $startDate->copy()->startOfMonth();
        $periodEnd = $endDate->copy()->startOfMonth();
        $period = \Carbon\CarbonPeriod::create($periodStart, '1 month', $periodEnd);

        foreach ($period as $date) {
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            $monthLabel = $monthStart->format('M Y');

            $installs = TyreMovement::where('movement_type', 'Installation')
                ->whereBetween('movement_date', [$monthStart, $monthEnd])->count();
            $removals = TyreMovement::where('movement_type', 'Removal')
                ->whereBetween('movement_date', [$monthStart, $monthEnd])->count();

            $monthlyMovements[] = [
                'month' => $monthLabel,
                'installations' => $installs,
                'removals' => $removals,
            ];
        }

        // ========================================
        // ROW 3: Performance Analysis (Initial data)
        // ========================================

        // 3a. Top Brands by Average Lifetime KM (initial - no filter)
        $brandPerformance = $this->getBrandPerformanceData();

        // 3b. Cost Per KM by Brand (initial - no filter)
        $cpkByBrand = $this->getCpkByBrandData();

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

        // 4b. Failure Code Distribution (Pie Chart) - Filtered
        $failureDistribution = TyreMovement::where('movement_type', 'Removal')
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->whereNotNull('failure_code_id')
            ->select('failure_code_id', DB::raw('count(*) as total'))
            ->groupBy('failure_code_id')
            ->with('failureCode:id,failure_code,failure_name')
            ->get()
            ->map(function ($item) {
                $fc = $item->failureCode;
                return [
                    'label' => $fc ? ($fc->failure_code . ' - ' . ($fc->display_name ?: $fc->failure_name)) : 'Unknown',
                    'total' => $item->total,
                ];
            });

        // 4c. Recent Movements - Filtered
        $recentMovements = TyreMovement::with(['tyre', 'vehicle', 'position'])
            ->whereBetween('movement_date', [$startDate, $endDate])
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

        // ========================================
        // 5a. Fleet Health (% Remaining - Percentage Based)
        // ========================================
        $fleetHealthData = $this->getFleetHealthData();

        // 5a. Axle Analysis (Scrap Frequency by Position) - Filtered
        $axleAnalysis = $this->getScrapByPositionData($startDate, $endDate);

        // ========================================
        // Filter Options for Brand Performance & CPK Charts
        // ========================================
        $filterSizes = TyreSize::select('id', 'size')->distinct()->orderBy('size')->get();
        $filterTypes = TyreSize::select('type')->whereNotNull('type')->distinct()->orderBy('type')->pluck('type');
        $filterPatterns = TyrePattern::select('id', 'name')->where('status', 'Active')->orderBy('name')->get();

        return view('tyre-performance.dashboard', compact(
            // Filters
            'startDate',
            'endDate',
            // KPI
            'totalTyres',
            'installedTyres',
            'inStockTyres',
            'scrappedTyres',
            'totalInvestment',
            'avgLifetimeKm',
            'avgLifetimeHm',
            'avgCpk',
            'scrapRate',
            'installationsThisMonth',
            'removalsThisMonth',
            // Charts
            'statusDistribution',
            'monthlyMovements',
            // Performance
            'brandPerformance',
            'cpkByBrand',
            'criticalTread',
            // Inventory & Operational
            'locationStock',
            'failureDistribution',
            'recentMovements',
            'lowRtdTyres',
            'totalVehicles',
            // Fleet Health (Percentage)
            'fleetHealthData',
            'axleAnalysis',
            // Filter Options
            'filterSizes',
            'filterTypes',
            'filterPatterns'
        ));
    }

    /**
     * Get Fleet Health data using percentage-based RTD/OTD calculation
     */
    private function getFleetHealthData()
    {
        $installedTyres = Tyre::where('status', 'Installed')
            ->whereNotNull('current_tread_depth')
            ->whereNotNull('initial_tread_depth')
            ->where('initial_tread_depth', '>', 0)
            ->select('id', 'serial_number', 'current_tread_depth', 'initial_tread_depth')
            ->get();

        $categories = [
            'Critical (< 20%)' => 0,
            'Warning (20-40%)' => 0,
            'Monitor (40-60%)' => 0,
            'Good (> 60%)' => 0,
        ];

        $totalPercent = 0;
        $countWithData = 0;
        $noDataCount = Tyre::where('status', 'Installed')
            ->where(function ($q) {
                $q->whereNull('current_tread_depth')
                    ->orWhereNull('initial_tread_depth')
                    ->orWhere('initial_tread_depth', '<=', 0);
            })
            ->count();

        foreach ($installedTyres as $tyre) {
            $pctRemaining = ($tyre->current_tread_depth / $tyre->initial_tread_depth) * 100;
            $pctRemaining = min($pctRemaining, 100); // cap at 100%
            $totalPercent += $pctRemaining;
            $countWithData++;

            if ($pctRemaining < 20) {
                $categories['Critical (< 20%)']++;
            } elseif ($pctRemaining < 40) {
                $categories['Warning (20-40%)']++;
            } elseif ($pctRemaining < 60) {
                $categories['Monitor (40-60%)']++;
            } else {
                $categories['Good (> 60%)']++;
            }
        }

        $avgFleetHealth = $countWithData > 0 ? round($totalPercent / $countWithData, 1) : 0;

        return [
            'categories' => $categories,
            'avgHealth' => $avgFleetHealth,
            'totalWithData' => $countWithData,
            'noDataCount' => $noDataCount,
        ];
    }

    /**
     * Get Brand Performance data with optional filters
     */
    private function getBrandPerformanceData($sizeId = null, $type = null, $patternId = null)
    {
        $query = Tyre::select(
            'tyre_brand_id',
            DB::raw('AVG(total_lifetime_km) as avg_km'),
            DB::raw('AVG(total_lifetime_hm) as avg_hm'),
            DB::raw('COUNT(*) as tyre_count')
        )
            ->where('total_lifetime_km', '>', 0);

        // Apply filters
        if ($sizeId) {
            $query->where('tyre_size_id', $sizeId);
        }
        if ($type) {
            $query->whereHas('size', function ($q) use ($type) {
                $q->where('type', $type);
            });
        }
        if ($patternId) {
            $query->where('tyre_pattern_id', $patternId);
        }

        return $query->groupBy('tyre_brand_id')
            ->with('brand:id,brand_name')
            ->get()
            ->map(function ($item) {
                return [
                    'brand' => $item->brand->brand_name ?? 'Unknown',
                    'avg_km' => round($item->avg_km, 0),
                    'avg_hm' => round($item->avg_hm, 0),
                    'count' => $item->tyre_count,
                ];
            });
    }

    /**
     * Get CPK by Brand data with optional filters
     */
    private function getCpkByBrandData($sizeId = null, $type = null, $patternId = null)
    {
        $query = Tyre::select(
            'tyre_brand_id',
            DB::raw('SUM(price) as total_price'),
            DB::raw('SUM(total_lifetime_km) as total_km'),
            DB::raw('COUNT(*) as tyre_count')
        )
            ->where('total_lifetime_km', '>', 0)
            ->whereNotNull('price')
            ->where('price', '>', 0);

        // Apply filters
        if ($sizeId) {
            $query->where('tyre_size_id', $sizeId);
        }
        if ($type) {
            $query->whereHas('size', function ($q) use ($type) {
                $q->where('type', $type);
            });
        }
        if ($patternId) {
            $query->where('tyre_pattern_id', $patternId);
        }

        return $query->groupBy('tyre_brand_id')
            ->with('brand:id,brand_name')
            ->get()
            ->map(function ($item) {
                return [
                    'brand' => $item->brand->brand_name ?? 'Unknown',
                    'cpk' => $item->total_km > 0 ? round($item->total_price / $item->total_km, 0) : 0,
                    'count' => $item->tyre_count,
                ];
            });
    }

    /**
     * AJAX: Brand Performance with filters
     */
    public function brandPerformanceAjax(Request $request)
    {
        $sizeId = $request->input('size_id');
        $type = $request->input('type');
        $patternId = $request->input('pattern_id');

        $data = $this->getBrandPerformanceData($sizeId, $type, $patternId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * AJAX: CPK by Brand with filters
     */
    public function cpkByBrandAjax(Request $request)
    {
        $sizeId = $request->input('size_id');
        $type = $request->input('type');
        $patternId = $request->input('pattern_id');

        $data = $this->getCpkByBrandData($sizeId, $type, $patternId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function scrapByPositionAjax(\Illuminate\Http\Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : null;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : null;
        $sizeId = $request->get('size_id');
        $type = $request->get('type');
        $patternId = $request->get('pattern_id');

        $data = $this->getScrapByPositionData($startDate, $endDate, $sizeId, $type, $patternId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Helper method to fetch scrap by position data
     */
    private function getScrapByPositionData($startDate = null, $endDate = null, $sizeId = null, $type = null, $patternId = null)
    {
        $query = TyreMovement::where('movement_type', 'Removal')
            ->where('target_status', 'Scrap')
            ->join('tyre_position_details', 'tyre_movements.position_id', '=', 'tyre_position_details.id')
            ->join('tyres', 'tyre_movements.tyre_id', '=', 'tyres.id');

        if ($startDate && $endDate) {
            $query->whereBetween('tyre_movements.movement_date', [$startDate, $endDate]);
        }

        if ($sizeId) {
            $query->where('tyres.size_id', $sizeId);
        }

        if ($type) {
            $query->join('tyre_sizes', 'tyres.size_id', '=', 'tyre_sizes.id')
                ->where('tyre_sizes.type', $type);
        }

        if ($patternId) {
            $query->where('tyres.pattern_id', $patternId);
        }

        return $query->select('tyre_position_details.position_name', DB::raw('count(*) as total'))
            ->groupBy('tyre_position_details.position_name')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'position' => $item->position_name,
                    'total' => (int) $item->total
                ];
            });
    }

    /**
     * Drill-down AJAX endpoint for dashboard charts
     */
    public function drillDown(\Illuminate\Http\Request $request)
    {
        $type = $request->get('type');
        $value = $request->get('value');

        // Extra filter params for filtered chart drill-downs
        $sizeId = $request->get('size_id');
        $filterType = $request->get('filter_type');
        $patternId = $request->get('pattern_id');
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : null;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : null;

        switch ($type) {
            // ==========================================
            // 1. STATUS DONUT → List tyres by status
            // ==========================================
            case 'status':
                $query = Tyre::query();

                if ($value === 'New (R0)') {
                    $query->where('status', 'New');
                } elseif (preg_match('/^Retread R(\d+)$/', $value, $matches)) {
                    $query->where('status', 'Retread')->where('retread_count', $matches[1]);
                } elseif ($value === 'Retread R4+') {
                    $query->where('status', 'Retread')->where('retread_count', '>=', 4);
                } else {
                    $query->where('status', $value);
                }

                $tyres = $query->with(['brand', 'size', 'pattern', 'location', 'currentVehicle'])
                    ->get()
                    ->map(function ($t) {
                        return [
                            'id' => $t->id,
                            'serial_number' => $t->serial_number,
                            'brand' => $t->brand->brand_name ?? '-',
                            'size' => $t->size->size ?? '-',
                            'pattern' => $t->pattern->name ?? '-',
                            'type' => $t->size->type ?? '-',
                            'location' => $t->location->location_name ?? '-',
                            'vehicle' => $t->currentVehicle->kode_kendaraan ?? '-',
                            'otd' => $t->initial_tread_depth ? $t->initial_tread_depth . ' mm' : '-',
                            'rtd' => $t->current_tread_depth ? $t->current_tread_depth . ' mm' : '-',
                            'lifetime_km' => $t->total_lifetime_km ? number_format($t->total_lifetime_km, 0) : '-',
                            'price' => $t->price ? 'Rp ' . number_format($t->price, 0, ',', '.') : '-',
                        ];
                    });

                return response()->json([
                    'title' => "Ban Status: {$value}",
                    'columns' => ['Serial Number', 'Brand', 'Size', 'Pattern', 'Type', 'Location', 'Kendaraan', 'OTD', 'RTD', 'Lifetime KM', 'Harga'],
                    'keys' => ['serial_number', 'brand', 'size', 'pattern', 'type', 'location', 'vehicle', 'otd', 'rtd', 'lifetime_km', 'price'],
                    'data' => $tyres,
                    'total' => $tyres->count(),
                    'link' => route('tyre-master.index') . '?status=' . urlencode($value),
                ]);

            // ==========================================
            // 2. BRAND PERFORMANCE → List tyres by brand
            // ==========================================
            case 'brand':
                $brand = TyreBrand::where('brand_name', $value)->first();
                if (!$brand)
                    return response()->json(['data' => [], 'total' => 0]);

                $tyres = Tyre::where('tyre_brand_id', $brand->id)
                    ->with(['size', 'pattern', 'location', 'currentVehicle'])
                    ->get()
                    ->map(function ($t) {
                        return [
                            'id' => $t->id,
                            'serial_number' => $t->serial_number,
                            'status' => $t->status,
                            'size' => $t->size->size ?? '-',
                            'pattern' => $t->pattern->name ?? '-',
                            'type' => $t->size->type ?? '-',
                            'location' => $t->location->location_name ?? '-',
                            'vehicle' => $t->currentVehicle->kode_kendaraan ?? '-',
                            'otd' => $t->initial_tread_depth ? $t->initial_tread_depth . ' mm' : '-',
                            'rtd' => $t->current_tread_depth ? $t->current_tread_depth . ' mm' : '-',
                            'lifetime_km' => $t->total_lifetime_km ? number_format($t->total_lifetime_km, 0) : '-',
                            'lifetime_hm' => $t->total_lifetime_hm ? number_format($t->total_lifetime_hm, 0) : '-',
                            'price' => $t->price ? 'Rp ' . number_format($t->price, 0, ',', '.') : '-',
                        ];
                    });

                return response()->json([
                    'title' => "Ban Brand: {$value}",
                    'columns' => ['Serial Number', 'Status', 'Size', 'Pattern', 'Type', 'Location', 'Kendaraan', 'OTD', 'RTD', 'KM', 'HM', 'Harga'],
                    'keys' => ['serial_number', 'status', 'size', 'pattern', 'type', 'location', 'vehicle', 'otd', 'rtd', 'lifetime_km', 'lifetime_hm', 'price'],
                    'data' => $tyres,
                    'total' => $tyres->count(),
                ]);

            // ==========================================
            // 3. LOCATION STOCK → List tyres at location
            // ==========================================
            case 'location':
                $location = TyreLocation::where('location_name', $value)->first();
                if (!$location)
                    return response()->json(['data' => [], 'total' => 0]);

                $tyres = Tyre::where('work_location_id', $location->id)
                    ->with(['brand', 'size', 'pattern', 'currentVehicle'])
                    ->get()
                    ->map(function ($t) {
                        return [
                            'id' => $t->id,
                            'serial_number' => $t->serial_number,
                            'brand' => $t->brand->brand_name ?? '-',
                            'status' => $t->status,
                            'size' => $t->size->size ?? '-',
                            'pattern' => $t->pattern->name ?? '-',
                            'type' => $t->size->type ?? '-',
                            'vehicle' => $t->currentVehicle->kode_kendaraan ?? '-',
                            'otd' => $t->initial_tread_depth ? $t->initial_tread_depth . ' mm' : '-',
                            'rtd' => $t->current_tread_depth ? $t->current_tread_depth . ' mm' : '-',
                            'retread' => 'R' . $t->retread_count,
                        ];
                    });

                return response()->json([
                    'title' => "Ban di Lokasi: {$value}",
                    'columns' => ['Serial Number', 'Brand', 'Status', 'Size', 'Pattern', 'Type', 'Kendaraan', 'OTD', 'RTD', 'Retread'],
                    'keys' => ['serial_number', 'brand', 'status', 'size', 'pattern', 'type', 'vehicle', 'otd', 'rtd', 'retread'],
                    'data' => $tyres,
                    'total' => $tyres->count(),
                ]);

            // ==========================================
            // 4. FAILURE CODE → Movement records
            // ==========================================
            case 'failure':
                $fc = TyreFailureCode::whereRaw("CONCAT(failure_code, ' - ', failure_name) = ?", [$value])->first();
                if (!$fc) {
                    // Try matching by failure_code only
                    $fc = TyreFailureCode::where('failure_code', $value)->first();
                }
                if (!$fc)
                    return response()->json(['data' => [], 'total' => 0]);

                $movements = TyreMovement::where('failure_code_id', $fc->id)
                    ->where('movement_type', 'Removal')
                    ->with(['tyre.size', 'tyre.pattern', 'vehicle'])
                    ->orderBy('movement_date', 'desc')
                    ->get()
                    ->map(function ($m) {
                        return [
                            'date' => Carbon::parse($m->movement_date)->format('d/m/Y'),
                            'serial' => $m->tyre->serial_number ?? '-',
                            'size' => $m->tyre->size->size ?? '-',
                            'pattern' => $m->tyre->pattern->name ?? '-',
                            'type' => $m->tyre->size->type ?? '-',
                            'vehicle' => $m->vehicle->kode_kendaraan ?? '-',
                            'km' => $m->odometer_reading ? number_format($m->odometer_reading, 0) : '-',
                            'hm' => $m->hour_meter_reading ? number_format($m->hour_meter_reading, 0) : '-',
                            'rtd' => $m->rtd_reading ? $m->rtd_reading . ' mm' : '-',
                            'notes' => $m->notes ?? '-',
                        ];
                    });

                return response()->json([
                    'title' => "Pelepasan: " . ($fc->display_name ?: "{$fc->failure_code} - {$fc->failure_name}"),
                    'columns' => ['Tanggal', 'Serial Ban', 'Size', 'Pattern', 'Type', 'Kendaraan', 'KM', 'HM', 'RTD', 'Notes'],
                    'keys' => ['date', 'serial', 'size', 'pattern', 'type', 'vehicle', 'km', 'hm', 'rtd', 'notes'],
                    'data' => $movements,
                    'total' => $movements->count(),
                ]);

            // ==========================================
            // 5. MONTHLY MOVEMENT → Movement records
            // ==========================================
            case 'movement':
                // value format: "Jan 2026|Installation" or "Jan 2026|Removal"
                $parts = explode('|', $value);
                if (count($parts) !== 2)
                    return response()->json(['data' => [], 'total' => 0]);

                $monthStr = $parts[0];
                $movType = $parts[1];

                try {
                    $monthDate = Carbon::createFromFormat('M Y', $monthStr);
                    $monthStart = $monthDate->copy()->startOfMonth();
                    $monthEnd = $monthDate->copy()->endOfMonth();
                } catch (\Exception $e) {
                    return response()->json(['data' => [], 'total' => 0]);
                }

                $movements = TyreMovement::where('movement_type', $movType)
                    ->whereBetween('movement_date', [$monthStart, $monthEnd])
                    ->with(['tyre.brand', 'tyre.size', 'tyre.pattern', 'vehicle'])
                    ->orderBy('movement_date', 'desc')
                    ->get()
                    ->map(function ($m) {
                        return [
                            'date' => Carbon::parse($m->movement_date)->format('d/m/Y'),
                            'serial' => $m->tyre->serial_number ?? '-',
                            'size' => $m->tyre->size->size ?? '-',
                            'pattern' => $m->tyre->pattern->name ?? '-',
                            'type' => $m->tyre->size->type ?? '-',
                            'brand' => $m->tyre->brand->brand_name ?? '-',
                            'vehicle' => $m->vehicle->kode_kendaraan ?? '-',
                            'km' => $m->odometer_reading ? number_format($m->odometer_reading, 0) : '-',
                            'hm' => $m->hour_meter_reading ? number_format($m->hour_meter_reading, 0) : '-',
                            'psi' => $m->psi_reading ?? '-',
                            'rtd' => $m->rtd_reading ? $m->rtd_reading . ' mm' : '-',
                        ];
                    });

                $typeLabel = $movType === 'Installation' ? 'Pemasangan' : 'Pelepasan';

                return response()->json([
                    'title' => "{$typeLabel} - {$monthStr}",
                    'columns' => ['Tanggal', 'Serial Ban', 'Size', 'Pattern', 'Type', 'Brand', 'Kendaraan', 'KM', 'HM', 'PSI', 'RTD'],
                    'keys' => ['date', 'serial', 'size', 'pattern', 'type', 'brand', 'vehicle', 'km', 'hm', 'psi', 'rtd'],
                    'data' => $movements,
                    'total' => $movements->count(),
                ]);

            // ==========================================
            // 6. RTD DISTRIBUTION → List installed tyres (Percentage based)
            // ==========================================
            case 'rtd':
                $query = Tyre::where('status', 'Installed')
                    ->whereNotNull('current_tread_depth')
                    ->whereNotNull('initial_tread_depth')
                    ->where('initial_tread_depth', '>', 0);

                $tyresAll = $query->with(['brand', 'size', 'pattern', 'currentVehicle'])->get();

                // Filter by percentage range
                $tyres = $tyresAll->filter(function ($t) use ($value) {
                    $pct = ($t->current_tread_depth / $t->initial_tread_depth) * 100;
                    if (str_contains($value, 'Critical')) {
                        return $pct < 20;
                    } elseif (str_contains($value, 'Warning')) {
                        return $pct >= 20 && $pct < 40;
                    } elseif (str_contains($value, 'Monitor')) {
                        return $pct >= 40 && $pct < 60;
                    } else {
                        return $pct >= 60;
                    }
                })->map(function ($t) {
                    $pct = $t->initial_tread_depth > 0
                        ? round(($t->current_tread_depth / $t->initial_tread_depth) * 100, 1)
                        : 0;
                    return [
                        'id' => $t->id,
                        'serial_number' => $t->serial_number,
                        'brand' => $t->brand->brand_name ?? '-',
                        'size' => $t->size->size ?? '-',
                        'pattern' => $t->pattern->name ?? '-',
                        'type' => $t->size->type ?? '-',
                        'vehicle' => $t->currentVehicle->kode_kendaraan ?? '-',
                        'otd' => $t->initial_tread_depth ? $t->initial_tread_depth . ' mm' : '-',
                        'rtd' => $t->current_tread_depth ? $t->current_tread_depth . ' mm' : '-',
                        'remaining' => $pct . '%',
                        'lifetime_km' => $t->total_lifetime_km ? number_format($t->total_lifetime_km, 0) : '-',
                    ];
                })->values();

                return response()->json([
                    'title' => "Fleet Health: {$value}",
                    'columns' => ['Serial Number', 'Brand', 'Size', 'Pattern', 'Type', 'Kendaraan', 'OTD', 'RTD', '% Sisa', 'Lifetime KM'],
                    'keys' => ['serial_number', 'brand', 'size', 'pattern', 'type', 'vehicle', 'otd', 'rtd', 'remaining', 'lifetime_km'],
                    'data' => $tyres,
                    'total' => $tyres->count(),
                ]);

            // ==========================================
            // 7. AXLE ANALYSIS → List removals by axle/pos
            // ==========================================
            case 'axle':
                $movements = TyreMovement::where('movement_type', 'Removal')
                    ->join('tyre_position_details', 'tyre_movements.position_id', '=', 'tyre_position_details.id')
                    ->where('tyre_position_details.position_name', $value)
                    ->with(['tyre.size', 'tyre.pattern', 'vehicle'])
                    ->orderBy('movement_date', 'desc')
                    ->get()
                    ->map(function ($m) {
                        return [
                            'date' => Carbon::parse($m->movement_date)->format('d/m/Y'),
                            'serial' => $m->tyre->serial_number ?? '-',
                            'size' => $m->tyre->size->size ?? '-',
                            'pattern' => $m->tyre->pattern->name ?? '-',
                            'type' => $m->tyre->size->type ?? '-',
                            'vehicle' => $m->vehicle->kode_kendaraan ?? '-',
                            'km' => $m->odometer_reading ? number_format($m->odometer_reading, 0) : '-',
                            'rtd' => $m->rtd_reading ? $m->rtd_reading . ' mm' : '-',
                            'notes' => $m->notes ?? '-',
                        ];
                    });

                return response()->json([
                    'title' => "Scrap Frequency: Posisi {$value}",
                    'columns' => ['Tanggal', 'Serial Ban', 'Size', 'Pattern', 'Type', 'Kendaraan', 'KM', 'RTD', 'Notes'],
                    'keys' => ['date', 'serial', 'size', 'pattern', 'type', 'vehicle', 'km', 'rtd', 'notes'],
                    'data' => $movements,
                    'total' => $movements->count(),
                ]);

            // ==========================================
            // 8. BRAND PERFORMANCE FILTERED → Drill-down
            // ==========================================
            case 'brand_performance':
                $brand = TyreBrand::where('brand_name', $value)->first();
                if (!$brand)
                    return response()->json(['data' => [], 'total' => 0]);

                $query = Tyre::where('tyre_brand_id', $brand->id)
                    ->where('total_lifetime_km', '>', 0);

                // Apply same filters as chart
                if ($sizeId) {
                    $query->where('tyre_size_id', $sizeId);
                }
                if ($filterType) {
                    $query->whereHas('size', function ($q) use ($filterType) {
                        $q->where('type', $filterType);
                    });
                }
                if ($patternId) {
                    $query->where('tyre_pattern_id', $patternId);
                }

                $filterLabel = '';
                if ($sizeId || $filterType || $patternId) {
                    $parts = [];
                    if ($sizeId)
                        $parts[] = 'Size: ' . (TyreSize::find($sizeId)->size ?? $sizeId);
                    if ($filterType)
                        $parts[] = 'Type: ' . $filterType;
                    if ($patternId)
                        $parts[] = 'Pattern: ' . (TyrePattern::find($patternId)->name ?? $patternId);
                    $filterLabel = ' | Filter: ' . implode(', ', $parts);
                }

                $tyres = $query->with(['size', 'pattern', 'location', 'currentVehicle'])
                    ->get()
                    ->map(function ($t) {
                        return [
                            'id' => $t->id,
                            'serial_number' => $t->serial_number,
                            'status' => $t->status,
                            'size' => $t->size->size ?? '-',
                            'pattern' => $t->pattern->name ?? '-',
                            'type' => $t->size->type ?? '-',
                            'location' => $t->location->location_name ?? '-',
                            'vehicle' => $t->currentVehicle->kode_kendaraan ?? '-',
                            'otd' => $t->initial_tread_depth ? $t->initial_tread_depth . ' mm' : '-',
                            'rtd' => $t->current_tread_depth ? $t->current_tread_depth . ' mm' : '-',
                            'lifetime_km' => $t->total_lifetime_km ? number_format($t->total_lifetime_km, 0) : '-',
                            'lifetime_hm' => $t->total_lifetime_hm ? number_format($t->total_lifetime_hm, 0) : '-',
                            'price' => $t->price ? 'Rp ' . number_format($t->price, 0, ',', '.') : '-',
                        ];
                    });

                return response()->json([
                    'title' => "Brand Performance: {$value}{$filterLabel}",
                    'columns' => ['Serial Number', 'Status', 'Size', 'Pattern', 'Type', 'Location', 'Kendaraan', 'OTD', 'RTD', 'KM', 'HM', 'Harga'],
                    'keys' => ['serial_number', 'status', 'size', 'pattern', 'type', 'location', 'vehicle', 'otd', 'rtd', 'lifetime_km', 'lifetime_hm', 'price'],
                    'data' => $tyres,
                    'total' => $tyres->count(),
                ]);

            // ==========================================
            // 9. CPK FILTERED → Drill-down
            // ==========================================
            case 'brand_cpk':
                $brand = TyreBrand::where('brand_name', $value)->first();
                if (!$brand)
                    return response()->json(['data' => [], 'total' => 0]);

                $query = Tyre::where('tyre_brand_id', $brand->id)
                    ->where('total_lifetime_km', '>', 0)
                    ->whereNotNull('price')
                    ->where('price', '>', 0);

                // Apply same filters as chart
                if ($sizeId) {
                    $query->where('tyre_size_id', $sizeId);
                }
                if ($filterType) {
                    $query->whereHas('size', function ($q) use ($filterType) {
                        $q->where('type', $filterType);
                    });
                }
                if ($patternId) {
                    $query->where('tyre_pattern_id', $patternId);
                }

                $filterLabel = '';
                if ($sizeId || $filterType || $patternId) {
                    $parts = [];
                    if ($sizeId)
                        $parts[] = 'Size: ' . (TyreSize::find($sizeId)->size ?? $sizeId);
                    if ($filterType)
                        $parts[] = 'Type: ' . $filterType;
                    if ($patternId)
                        $parts[] = 'Pattern: ' . (TyrePattern::find($patternId)->name ?? $patternId);
                    $filterLabel = ' | Filter: ' . implode(', ', $parts);
                }

                $tyres = $query->with(['size', 'pattern', 'location', 'currentVehicle'])
                    ->get()
                    ->map(function ($t) {
                        $cpkVal = ($t->total_lifetime_km > 0) ? round($t->price / $t->total_lifetime_km, 0) : 0;
                        return [
                            'id' => $t->id,
                            'serial_number' => $t->serial_number,
                            'status' => $t->status,
                            'size' => $t->size->size ?? '-',
                            'pattern' => $t->pattern->name ?? '-',
                            'type' => $t->size->type ?? '-',
                            'location' => $t->location->location_name ?? '-',
                            'vehicle' => $t->currentVehicle->kode_kendaraan ?? '-',
                            'lifetime_km' => number_format($t->total_lifetime_km, 0),
                            'price' => 'Rp ' . number_format($t->price, 0, ',', '.'),
                            'cpk' => 'Rp ' . number_format($cpkVal, 0, ',', '.') . '/km',
                        ];
                    });

                return response()->json([
                    'title' => "CPK Brand: {$value}{$filterLabel}",
                    'columns' => ['Serial Number', 'Status', 'Size', 'Pattern', 'Type', 'Location', 'Kendaraan', 'Lifetime KM', 'Harga', 'CPK'],
                    'keys' => ['serial_number', 'status', 'size', 'pattern', 'type', 'location', 'vehicle', 'lifetime_km', 'price', 'cpk'],
                    'data' => $tyres,
                    'total' => $tyres->count(),
                ]);

            // ==========================================
            // 11. SCRAP BY POSITION FILTERED → Drill-down
            // ==========================================
            case 'scrap_position':
                $query = TyreMovement::where('movement_type', 'Removal')
                    ->where('target_status', 'Scrap')
                    ->join('tyre_position_details', 'tyre_movements.position_id', '=', 'tyre_position_details.id')
                    ->join('tyres', 'tyre_movements.tyre_id', '=', 'tyres.id')
                    ->where('tyre_position_details.position_name', $value);

                if ($startDate && $endDate) {
                    $query->whereBetween('tyre_movements.movement_date', [$startDate, $endDate]);
                }
                if ($sizeId)
                    $query->where('tyres.size_id', $sizeId);
                if ($filterType) {
                    $query->join('tyre_sizes as ts_extra', 'tyres.size_id', '=', 'ts_extra.id')
                        ->where('ts_extra.type', $filterType);
                }
                if ($patternId)
                    $query->where('tyres.pattern_id', $patternId);

                $movements = $query->with(['tyre', 'tyre.size', 'tyre.pattern', 'vehicle', 'failureCode'])
                    ->select('tyre_movements.*') // Avoid column name collisions
                    ->get();

                return response()->json([
                    'success' => true,
                    'title' => 'Daftar Ban Scrap di Posisi: ' . $value,
                    'columns' => ['Tgl Pelepasan', 'Serial Number', 'Size', 'Pattern', 'Kendaraan', 'Penyebab Scrap'],
                    'keys' => ['date', 'serial', 'size', 'pattern', 'vehicle', 'failure'],
                    'data' => $movements->map(function ($m) {
                        return [
                            'date' => Carbon::parse($m->movement_date)->format('d/m/Y'),
                            'serial' => $m->tyre->serial_number ?? '-',
                            'size' => $m->tyre->size->size ?? '-',
                            'pattern' => $m->tyre->pattern->name ?? '-',
                            'vehicle' => $m->vehicle->kode_kendaraan ?? '-',
                            'failure' => $m->failureCode->display_name ?? ($m->failureCode->failure_name ?? '-'),
                        ];
                    }),
                    'total' => $movements->count(),
                ]);

            default:
                return response()->json(['data' => [], 'total' => 0]);
        }
    }
}
