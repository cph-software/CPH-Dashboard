<?php

namespace App\Services;

use Carbon\Carbon;

class TyreMonitoringCalculator
{
    /**
     * Calculate monitoring metrics for a specific check record.
     *
     * @param float $originalRtd
     * @param string $installDate
     * @param object|array $checkData
     * @return array
     */
    public static function calculate($originalRtd, $installDate, $checkData)
    {
        // Handle object or array
        $r1 = (float) (is_object($checkData) ? $checkData->rtd_1 : ($checkData['rtd_1'] ?? 0));
        $r2 = (float) (is_object($checkData) ? $checkData->rtd_2 : ($checkData['rtd_2'] ?? 0));
        $r3 = (float) (is_object($checkData) ? $checkData->rtd_3 : ($checkData['rtd_3'] ?? 0));
        $r4 = (float) (is_object($checkData) ? $checkData->rtd_4 : ($checkData['rtd_4'] ?? 0));
        $checkDate = is_object($checkData) ? $checkData->check_date : $checkData['check_date'];
        $operationMileage = (float) (is_object($checkData) ? $checkData->operation_mileage : $checkData['operation_mileage']);

        $rtdCount = ($r4 > 0) ? 4 : 3;
        $avgRtd = ($r1 + $r2 + $r3 + $r4) / $rtdCount;
        
        $wearAmount = $originalRtd - $avgRtd;
        
        // Threshold: If wear is less than 0.1mm, we don't calculate performance yet
        // to avoid "immortal tyre" numbers (huge values)
        if ($wearAmount < 0.1) {
            $kmPerMm = 0;
            $projLifeKm = 0;
            $wornPct = ($wearAmount > 0) ? ($wearAmount / $originalRtd) * 100 : 0;
        } else {
            $wornPct = ($wearAmount / $originalRtd) * 100;
            $kmPerMm = $operationMileage / $wearAmount;
            $projLifeKm = $kmPerMm * ($originalRtd - 3);
        }
        
        $installDateCarbon = Carbon::parse($installDate);
        $checkDateCarbon = Carbon::parse($checkDate);
        
        // Elapsed days since INSTALLATION (for KM/Day)
        $daysSinceInstall = $installDateCarbon->diffInDays($checkDateCarbon);
        if ($daysSinceInstall <= 0) $daysSinceInstall = 1;
        
        // Elapsed days since ASSEMBLY (for the user's Day/Month columns)
        $asmDateRaw = is_object($checkData) ? ($checkData->date_assembly ?? null) : ($checkData['date_assembly'] ?? null);
        $asmDateCarbon = $asmDateRaw ? Carbon::parse($asmDateRaw) : $installDateCarbon;
        $daysSinceAsm = $asmDateCarbon->diffInDays($checkDateCarbon);

        $kmPerDay = $operationMileage / $daysSinceInstall;
        $projLifeDay = ($kmPerDay > 0) ? ($projLifeKm / $kmPerDay) : 0;

        return [
            'avg_rtd' => round($avgRtd, 2),
            'worn_pct' => round($wornPct, 1),
            'km_per_mm' => round($kmPerMm, 1),
            'proj_life_km' => round($projLifeKm, 0),
            'days_elapsed' => $daysSinceAsm, // Now matches assembly to inspection
            'months_elapsed' => round($daysSinceAsm / 30, 1),
            'km_per_day' => round($kmPerDay, 1),
            'proj_life_day' => round($projLifeDay, 0),
            'proj_life_month' => round($projLifeDay / 30, 1),
        ];
    }
}
