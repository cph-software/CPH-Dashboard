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
        $companyId = \App\Helpers\SessionCompanyHelper::getActiveCompanyId() ?? ($user->tyre_company_id ?? 0);

        // Global Klien (array): use the user's own company for context settings
        if (is_array($companyId)) {
            $companyId = $user->tyre_company_id ?? 0;
        }

        $company = TyreCompany::find($companyId);
        $mode = optional($company)->measurement_mode ?? 'BOTH';

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
        $km = $tyre->total_lifetime_km ?: ($tyre->current_km ?: 0);
        if (!$km && isset($tyre->movements)) {
            $km = $tyre->movements->sum('running_km');
        }
        $hm = $tyre->total_lifetime_hm ?: ($tyre->current_hm ?: 0);
        if (!$hm && isset($tyre->movements)) {
            $hm = $tyre->movements->sum('running_hm');
        }

        $d = [];
        if ($mode !== 'HM') {
            $d['lifetime_km'] = $km > 0 ? number_format($km) : '-';
        }
        if ($mode !== 'KM') {
            $d['lifetime_hm'] = $hm > 0 ? number_format($hm) : '-';
        }
        return $d;
    }

    /**
     * Resolve a human-readable location for a tyre (warehouse name or vehicle code).
     */
    public static function resolveLocation($tyre): string
    {
        if ($tyre->location) {
            return $tyre->location->location_name;
        }
        if ($tyre->currentVehicle) {
            return 'Di Kendaraan: ' . $tyre->currentVehicle->kode_kendaraan;
        }
        return '-';
    }


    /**
     * Apply lifetime WHERE clause to a query builder based on mode.
     */
    public static function applyLifetimeFilter($query, string $mode)
    {
        $effectiveKmExpr = "GREATEST(COALESCE(tyres.total_lifetime_km, 0), COALESCE(tyres.current_km, 0), COALESCE((SELECT SUM(running_km) FROM tyre_movements WHERE tyre_movements.tyre_id = tyres.id), 0))";
        $effectiveHmExpr = "GREATEST(COALESCE(tyres.total_lifetime_hm, 0), COALESCE(tyres.current_hm, 0), COALESCE((SELECT SUM(running_hm) FROM tyre_movements WHERE tyre_movements.tyre_id = tyres.id), 0))";

        if ($mode === 'BOTH') {
            $query->where(function ($q) use ($effectiveKmExpr, $effectiveHmExpr) {
                $q->whereRaw("{$effectiveKmExpr} > 0")->orWhereRaw("{$effectiveHmExpr} > 0");
            });
        } elseif ($mode === 'HM') {
            $query->whereRaw("{$effectiveHmExpr} > 0");
        } else {
            $query->whereRaw("{$effectiveKmExpr} > 0");
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
