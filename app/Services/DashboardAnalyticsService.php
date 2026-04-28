<?php

namespace App\Services;

use App\Models\TyreCompany;

class DashboardAnalyticsService
{
    /**
     * Get the measurement mode for the current user's company.
     * Returns ['mode' => 'KM'|'HM'|'BOTH', 'companyId' => int, 'company' => TyreCompany|null]
     */
    public static function getCompanyContext(): array
    {
        $user = auth()->user();
        $companyId = $user->tyre_company_id ?? 0;
        $isInternal = ($user->role_id == 1) || ($user->tyre_company_id == 1);
        if ($isInternal && session()->has('active_company_id')) {
            $companyId = session('active_company_id');
        }
        $company = TyreCompany::find($companyId);
        $mode = $company?->measurement_mode ?? 'BOTH';

        return ['mode' => $mode, 'companyId' => $companyId, 'company' => $company];
    }

    /**
     * Build lifetime column headers and keys based on mode.
     * Returns [['Lifetime KM', ...], ['lifetime_km', ...]]
     */
    public static function lifetimeCols(string $mode): array
    {
        if ($mode === 'KM') return [['Lifetime KM'], ['lifetime_km']];
        if ($mode === 'HM') return [['Lifetime HM'], ['lifetime_hm']];
        return [['Lifetime KM', 'Lifetime HM'], ['lifetime_km', 'lifetime_hm']];
    }

    /**
     * Build lifetime data values for a tyre based on mode.
     */
    public static function lifetimeData($tyre, string $mode): array
    {
        $d = [];
        if ($mode !== 'HM') $d['lifetime_km'] = number_format($tyre->total_lifetime_km ?? 0);
        if ($mode !== 'KM') $d['lifetime_hm'] = number_format($tyre->total_lifetime_hm ?? 0);
        return $d;
    }

    /**
     * Apply lifetime WHERE clause to a query builder based on mode.
     */
    public static function applyLifetimeFilter($query, string $mode)
    {
        if ($mode === 'BOTH') {
            $query->where(function ($q) {
                $q->where('total_lifetime_km', '>', 0)->orWhere('total_lifetime_hm', '>', 0);
            });
        } elseif ($mode === 'HM') {
            $query->where('total_lifetime_hm', '>', 0);
        } else {
            $query->where('total_lifetime_km', '>', 0);
        }
        return $query;
    }

    /**
     * Get the primary sort field based on mode.
     */
    public static function primaryField(string $mode): string
    {
        return $mode === 'HM' ? 'total_lifetime_hm' : 'total_lifetime_km';
    }
}
