<?php

namespace App\Services;

use App\Models\TyreMovement;
use App\Models\TyreExamination;
use Carbon\Carbon;

/**
 * Service untuk logic ODO/HM yang sebelumnya terduplikasi di:
 * - TyreMovementController
 * - TyreExaminationController
 * - MonitoringController
 *
 * Centralized agar perubahan logic cukup di 1 tempat.
 */
class VehicleReadingService
{
    /**
     * Hitung selisih lifetime (KM atau HM) dengan handling reset meter.
     * Jika diff negatif, anggap meter sudah di-reset dan pakai current reading.
     *
     * @param float|null $currentReading  Pembacaan meter saat ini
     * @param float|null $lastReading     Pembacaan meter terakhir yang tercatat
     * @return float
     */
    public static function calculateLifetimeDiff($currentReading, $lastReading)
    {
        if (!$currentReading || !$lastReading)
            return 0;

        $diff = $currentReading - $lastReading;

        if ($diff < 0) {
            // Odometer reset or replaced.
            // Logic: Assume the current reading is the distance covered since reset.
            return (float) $currentReading;
        }

        return (float) $diff;
    }

    /**
     * Ambil pembacaan ODO & HM terakhir dari kendaraan,
     * dengan membandingkan tanggal terbaru dari Movement dan Examination (jika ada).
     *
     * @param int $vehicleId
     * @return array ['odometer' => int, 'hour_meter' => int]
     */
    public static function getLastVehicleReadings($vehicleId)
    {
        $lastMovement = TyreMovement::where('vehicle_id', $vehicleId)
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $lastExamination = TyreExamination::where('vehicle_id', $vehicleId)
            ->orderBy('examination_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $lastOdo = 0;
        $lastHm = 0;

        if ($lastMovement && $lastExamination) {
            $movDate = Carbon::parse($lastMovement->movement_date);
            $examDate = Carbon::parse($lastExamination->examination_date);

            if ($movDate->gt($examDate)) {
                $lastOdo = $lastMovement->odometer_reading;
                $lastHm = $lastMovement->hour_meter_reading;
            } else {
                $lastOdo = $lastExamination->odometer;
                $lastHm = $lastExamination->hour_meter;
            }
        } elseif ($lastMovement) {
            $lastOdo = $lastMovement->odometer_reading;
            $lastHm = $lastMovement->hour_meter_reading;
        } elseif ($lastExamination) {
            $lastOdo = $lastExamination->odometer;
            $lastHm = $lastExamination->hour_meter;
        }

        return [
            'odometer' => $lastOdo ?? 0,
            'hour_meter' => $lastHm ?? 0,
        ];
    }

    /**
     * Deteksi anomali ODO/HM dibandingkan dengan catatan movement terakhir kendaraan.
     * Return array pesan warning (kosong = tidak ada anomali).
     *
     * @param int $vehicleId
     * @param string $vehicleCode  Kode kendaraan untuk pesan error
     * @param float|null $odometer      Odometer input baru
     * @param float|null $hourMeter     Hour meter input baru
     * @param bool $isMeterReset        Apakah user menandai sebagai reset meter
     * @return array  Array of warning messages
     */
    public static function detectOdoAnomalies($vehicleId, $vehicleCode, $odometer, $hourMeter, $isMeterReset = false)
    {
        $warnings = [];

        if ($isMeterReset) {
            return $warnings; // Skip detection if meter reset is acknowledged
        }

        $lastVehicleMov = TyreMovement::where('vehicle_id', $vehicleId)
            ->whereIn('movement_type', ['Installation', 'Removal', 'Inspection', 'Rotation'])
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastVehicleMov && $odometer) {
            if ($odometer < $lastVehicleMov->odometer_reading) {
                $warnings[] = "Odometer Unit " . $vehicleCode . " ({$odometer}) menurun drastis dari catatan terakhir ({$lastVehicleMov->odometer_reading}). Hilangkan centang 'Reset Meter' jika ini adalah kesalahan ketik.";
            }
        }

        if ($lastVehicleMov && $hourMeter) {
            if ($hourMeter < $lastVehicleMov->hour_meter_reading) {
                $warnings[] = "Hour Meter Unit " . $vehicleCode . " ({$hourMeter}) menurun drastis dari catatan terakhir ({$lastVehicleMov->hour_meter_reading}). Hilangkan centang 'Reset Meter' jika ini adalah kesalahan ketik.";
            }
        }

        return $warnings;
    }
}
