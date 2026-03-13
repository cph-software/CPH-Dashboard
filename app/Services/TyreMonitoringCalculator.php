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

        $avgRtd = ($r1 + $r2 + $r3 + $r4) / 4;
        
        $wearAmount = $originalRtd - $avgRtd;
        if ($wearAmount <= 0) $wearAmount = 0.001;
        
        $wornPct = ($wearAmount / $originalRtd) * 100;
        $kmPerMm = $operationMileage / $wearAmount;
        $projLifeKm = $kmPerMm * ($originalRtd - 3);
        
        $installDateCarbon = Carbon::parse($installDate);
        $checkDateCarbon = Carbon::parse($checkDate);
        $daysElapsed = $installDateCarbon->diffInDays($checkDateCarbon);
        if ($daysElapsed <= 0) $daysElapsed = 1;
        
        $kmPerDay = $operationMileage / $daysElapsed;
        $projLifeDay = ($kmPerDay > 0) ? ($projLifeKm / $kmPerDay) : 0;
        $projLifeMonth = $projLifeDay / 30;

        return [
            'avg_rtd' => round($avgRtd, 2),
            'worn_pct' => round($wornPct, 1),
            'km_per_mm' => round($kmPerMm, 1),
            'proj_life_km' => round($projLifeKm, 0),
            'days_elapsed' => $daysElapsed,
            'km_per_day' => round($kmPerDay, 1),
            'proj_life_day' => round($projLifeDay, 0),
            'proj_life_month' => round($projLifeMonth, 1),
        ];
    }
}
