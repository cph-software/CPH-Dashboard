<?php

namespace App\Services;

use Carbon\Carbon;

class TyreMonitoringCalculator
{
    /**
     * Calculate monitoring metrics for a specific check record.
     *
     * @param float $originalRtd - Baseline RTD at session start (NOT brand-new OTD)
     * @param string $installDate
     * @param object|array $checkData
     * @param string $measurementMode - 'KM', 'HM', or 'BOTH'
     * @return array
     */
    public static function calculate($originalRtd, $installDate, $checkData, $measurementMode = 'BOTH')
    {
        // Handle object or array
        $r1 = (float) (is_object($checkData) ? $checkData->rtd_1 : ($checkData['rtd_1'] ?? 0));
        $r2 = (float) (is_object($checkData) ? $checkData->rtd_2 : ($checkData['rtd_2'] ?? 0));
        $r3 = (float) (is_object($checkData) ? $checkData->rtd_3 : ($checkData['rtd_3'] ?? 0));
        $r4 = (float) (is_object($checkData) ? $checkData->rtd_4 : ($checkData['rtd_4'] ?? 0));
        $checkDate = is_object($checkData) ? $checkData->check_date : $checkData['check_date'];
        $operationMileage = (float) (is_object($checkData) ? $checkData->operation_mileage : $checkData['operation_mileage']);
        $operationHm = (float) (is_object($checkData) ? ($checkData->operation_hm ?? 0) : ($checkData['operation_hm'] ?? 0));

        $rtdCount = ($r4 > 0) ? 4 : 3;
        $avgRtd = ($r1 + $r2 + $r3 + $r4) / $rtdCount;
        
        $wearAmount = $originalRtd - $avgRtd;

        // Determine the primary operation metric based on mode
        if ($measurementMode === 'HM') {
            $primaryOp = $operationHm;
        } else {
            $primaryOp = $operationMileage;
        }
        
        // Minimum usable tread depth (safety limit)
        $minSafeRtd = 3;
        
        // Remaining usable tread from CURRENT state to safety limit
        $remainingTread = max(0, $avgRtd - $minSafeRtd);
        
        // Threshold: If wear is less than 0.1mm, we don't have enough data yet
        if ($wearAmount < 0.1) {
            $perMm = 0;
            $projRemainingLife = 0;
            $wornPct = ($wearAmount > 0 && $originalRtd > 0) ? ($wearAmount / $originalRtd) * 100 : 0;
        } else {
            $wornPct = ($originalRtd > 0) ? ($wearAmount / $originalRtd) * 100 : 0;
            $perMm = $primaryOp / $wearAmount;
            
            // REMAINING life = rate × remaining usable tread
            // This DECREASES as tyre wears down (intuitive for operators)
            $projRemainingLife = $perMm * $remainingTread;
        }
        
        $installDateCarbon = Carbon::parse($installDate);
        $checkDateCarbon = Carbon::parse($checkDate);
        
        // Elapsed days since INSTALLATION
        $daysSinceInstall = $installDateCarbon->diffInDays($checkDateCarbon);
        if ($daysSinceInstall <= 0) $daysSinceInstall = 1;
        
        // Elapsed days since ASSEMBLY (for the user's Day/Month columns)
        $asmDateRaw = is_object($checkData) ? ($checkData->date_assembly ?? null) : ($checkData['date_assembly'] ?? null);
        $asmDateCarbon = $asmDateRaw ? Carbon::parse($asmDateRaw) : $installDateCarbon;
        $daysSinceAsm = $asmDateCarbon->diffInDays($checkDateCarbon);

        $perDay = $primaryOp / $daysSinceInstall;
        $projRemainingDays = ($perDay > 0) ? ($projRemainingLife / $perDay) : 0;

        // Build result with mode-aware keys
        $result = [
            'avg_rtd' => round($avgRtd, 2),
            'worn_pct' => round($wornPct, 1),
            'days_elapsed' => $daysSinceAsm,
            'months_elapsed' => round($daysSinceAsm / 30, 1),
            'proj_life_day' => round($projRemainingDays, 0),
            'proj_life_month' => round($projRemainingDays / 30, 1),
        ];

        if ($measurementMode === 'HM') {
            $result['hm_per_mm'] = round($perMm, 1);
            $result['proj_life_hm'] = round($projRemainingLife, 0);
            $result['hm_per_day'] = round($perDay, 1);
            // Also include KM keys with same values for backward compat
            $result['km_per_mm'] = round($perMm, 1);
            $result['proj_life_km'] = round($projRemainingLife, 0);
            $result['km_per_day'] = round($perDay, 1);
        } else {
            $result['km_per_mm'] = round($perMm, 1);
            $result['proj_life_km'] = round($projRemainingLife, 0);
            $result['km_per_day'] = round($perDay, 1);
            // Also include HM keys for BOTH mode
            if ($measurementMode === 'BOTH' && $operationHm > 0) {
                $hmPerMm = ($wearAmount >= 0.1) ? ($operationHm / $wearAmount) : 0;
                $result['hm_per_mm'] = round($hmPerMm, 1);
                $result['proj_life_hm'] = round($hmPerMm * $remainingTread, 0);
                $result['hm_per_day'] = round($operationHm / $daysSinceInstall, 1);
            }
        }

        return $result;
    }

    /**
     * Determine tyre wear health status, alert level, and actionable recommendation.
     *
     * @param float $avgRtd
     * @param float $originalRtd
     * @param float|null $psiActual
     * @param float|null $psiRecommended
     * @return array
     */
    public static function getWearHealthStatus($avgRtd, $originalRtd = 0, $psiActual = null, $psiRecommended = null): array
    {
        $avgRtd = (float)$avgRtd;
        $originalRtd = (float)$originalRtd;
        $minSafeRtd = 3.0;
        $warningRtd = 5.0;

        // 1. RTD Health Status
        if ($avgRtd <= 0) {
            $status = 'unknown';
            $statusLabel = 'Belum Diukur';
            $badgeClass = 'bg-label-secondary';
            $color = '#8592a3';
            $recommendation = 'Lakukan inspeksi RTD awal.';
        } elseif ($avgRtd <= $minSafeRtd) {
            $status = 'critical';
            $statusLabel = 'Kritis (Segera Ganti)';
            $badgeClass = 'bg-label-danger';
            $color = '#ea5455';
            $recommendation = "RTD ({$avgRtd}mm) telah mencapai batas batas aman ({$minSafeRtd}mm). Jadwalkan penggantian ban unit ini segera!";
        } elseif ($avgRtd <= $warningRtd) {
            $status = 'warning';
            $statusLabel = 'Perhatian (Waspada)';
            $badgeClass = 'bg-label-warning';
            $color = '#ff9f43';
            $recommendation = "RTD ({$avgRtd}mm) mendekati batas kritis. Evaluasi keausan dan pertimbangkan rotasi posisi.";
        } else {
            $status = 'good';
            $statusLabel = 'Kondisi Baik';
            $badgeClass = 'bg-label-success';
            $color = '#28c76f';
            $recommendation = 'Kondisi tapak ban prima. Lanjutkan jadwal pemantauan berkala.';
        }

        // 2. Pressure Health Status
        $pressureAlert = null;
        if ($psiActual !== null && $psiActual > 0) {
            $rec = ($psiRecommended !== null && $psiRecommended > 0) ? (float)$psiRecommended : 110.0;
            $diffPct = (($psiActual - $rec) / $rec) * 100;

            if ($diffPct < -15 || $psiActual < 90) {
                $pressureAlert = [
                    'status' => 'under_inflated',
                    'label' => 'Tekanan Kurang',
                    'badge' => 'bg-label-danger',
                    'message' => "Tekanan ban ({$psiActual} PSI) terlalu rendah (standar {$rec} PSI). Berisiko aus bahu & boros BBM."
                ];
            } elseif ($diffPct > 15 || $psiActual > 135) {
                $pressureAlert = [
                    'status' => 'over_inflated',
                    'label' => 'Tekanan Berlebih',
                    'badge' => 'bg-label-warning',
                    'message' => "Tekanan ban ({$psiActual} PSI) terlalu tinggi (standar {$rec} PSI). Berisiko aus tengah & benturan batu."
                ];
            } else {
                $pressureAlert = [
                    'status' => 'normal',
                    'label' => 'Tekanan Normal',
                    'badge' => 'bg-label-success',
                    'message' => "Tekanan angin ({$psiActual} PSI) ideal."
                ];
            }
        }

        return [
            'status' => $status,
            'status_label' => $statusLabel,
            'badge_class' => $badgeClass,
            'color' => $color,
            'recommendation' => $recommendation,
            'pressure_alert' => $pressureAlert,
            'is_critical' => ($status === 'critical'),
            'is_warning' => ($status === 'warning'),
        ];
    }
}
