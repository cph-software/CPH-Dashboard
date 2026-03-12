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
        $rtd1 = is_object($checkData) ? $checkData->rtd_1 : $checkData['rtd_1'];
        $rtd2 = is_object($checkData) ? $checkData->rtd_2 : $checkData['rtd_2'];
        $rtd3 = is_object($checkData) ? $checkData->rtd_3 : $checkData['rtd_3'];
        $checkDate = is_object($checkData) ? $checkData->check_date : $checkData['check_date'];
        $operationMileage = is_object($checkData) ? $checkData->operation_mileage : $checkData['operation_mileage'];

        $avgRtd = ($rtd1 + $rtd2 + $rtd3) / 3;
        
        // Avoid division by zero
        $wearAmount = $originalRtd - $avgRtd;
        if ($wearAmount <= 0) {
            $wearAmount = 0.01; // Small value to avoid division by zero
        }
        
        $wornPct = ($wearAmount / $originalRtd) * 100;
        $kmPerMm = $operationMileage / $wearAmount;
        $projLifeKm = $kmPerMm * ($avgRtd - 3);
        
        $installDateCarbon = Carbon::parse($installDate);
        $checkDateCarbon = Carbon::parse($checkDate);
        $daysElapsed = $installDateCarbon->diffInDays($checkDateCarbon);
        
        if ($daysElapsed <= 0) {
            $daysElapsed = 1;
        }
        
        $kmPerDay = $operationMileage / $daysElapsed;
        
        $projLifeDay = 0;
        if ($kmPerDay > 0) {
            $projLifeDay = $projLifeKm / $kmPerDay;
        }
        
        $projLifeMonth = $projLifeDay / 30;

        return [
            'avg_rtd' => round($avgRtd, 2),
            'worn_pct' => round($wornPct, 2),
            'km_per_mm' => round($kmPerMm, 2),
            'proj_life_km' => round($projLifeKm, 0),
            'days_elapsed' => $daysElapsed,
            'km_per_day' => round($kmPerDay, 2),
            'proj_life_day' => round($projLifeDay, 0),
            'proj_life_month' => round($projLifeMonth, 1),
        ];
    }
}
