<?php

namespace App\Services;

use App\Models\MasterImportKendaraan;
use App\Models\Tyre;
use App\Models\TyreMovement;
use App\Models\TyrePositionDetail;
use App\Models\TyreMonitoringVehicle;
use App\Models\TyreMonitoringSession;
use App\Models\TyreMonitoringInstallation;
use App\Models\TyreMonitoringCheck;
use App\Models\TyreMonitoringRemoval;
use Illuminate\Support\Facades\Log;

class TyreMonitoringSyncService
{
    /**
     * Get or create a TyreMonitoringVehicle record linked to MasterImportKendaraan.
     */
    public static function getOrCreateMonitoringVehicle($vehicleId)
    {
        $masterVehicle = MasterImportKendaraan::find($vehicleId);
        if (!$masterVehicle) {
            return null;
        }

        $monVehicle = TyreMonitoringVehicle::withoutGlobalScopes()
            ->where('master_vehicle_id', $vehicleId)
            ->first();

        if (!$monVehicle) {
            // Also try matching by vehicle_number (no_polisi)
            $monVehicle = TyreMonitoringVehicle::withoutGlobalScopes()
                ->where('vehicle_number', $masterVehicle->no_polisi)
                ->first();

            if ($monVehicle) {
                $monVehicle->update(['master_vehicle_id' => $vehicleId]);
            }
        }

        if (!$monVehicle) {
            $monVehicle = TyreMonitoringVehicle::create([
                'master_vehicle_id' => $masterVehicle->id,
                'vehicle_number' => $masterVehicle->no_polisi,
                'fleet_name' => $masterVehicle->kode_kendaraan,
                'driver_name' => '-',
                'phone_number' => '-',
                'application' => $masterVehicle->area ?? 'General',
                'load_capacity' => $masterVehicle->payload_capacity ?? 0,
                'tire_positions' => $masterVehicle->total_tyre_position ?? 10,
                'is_trail' => false,
                'status' => 'active',
                'tyre_company_id' => $masterVehicle->tyre_company_id,
                'measurement_unit' => $masterVehicle->measurement_unit ?? 'KM',
            ]);
        } else {
            // Ensure status is active
            if ($monVehicle->status !== 'active') {
                $monVehicle->update(['status' => 'active']);
            }
        }

        return $monVehicle;
    }

    /**
     * Get or create the active monitoring session for a vehicle.
     */
    public static function getOrCreateActiveSession($vehicleId, $movementDate = null, $odometer = 0, $hourMeter = 0)
    {
        $monVehicle = self::getOrCreateMonitoringVehicle($vehicleId);
        if (!$monVehicle) {
            return null;
        }

        $session = TyreMonitoringSession::withoutGlobalScopes()
            ->where('vehicle_id', $monVehicle->vehicle_id)
            ->where('status', 'active')
            ->latest('session_id')
            ->first();

        if (!$session) {
            $masterVehicle = MasterImportKendaraan::find($vehicleId);
            $companyId = $masterVehicle ? $masterVehicle->tyre_company_id : $monVehicle->tyre_company_id;

            $session = TyreMonitoringSession::create([
                'vehicle_id' => $monVehicle->vehicle_id,
                'master_vehicle_id' => $vehicleId,
                'install_date' => $movementDate ?? date('Y-m-d'),
                'tyre_size' => '-',
                'original_rtd' => 0,
                'odometer_start' => (int)($odometer ?? 0),
                'hm_start' => (int)($hourMeter ?? 0),
                'status' => 'active',
                'tyre_company_id' => $companyId,
            ]);
        }

        return $session;
    }

    /**
     * Sync Installation event from Movement to Monitoring Installation baseline and Check 1.
     */
    public static function syncInstallation($vehicleId, $positionId, $tyreId, array $data)
    {
        try {
            $session = self::getOrCreateActiveSession(
                $vehicleId,
                $data['movement_date'] ?? null,
                $data['odometer_reading'] ?? ($data['odometer'] ?? 0),
                $data['hour_meter_reading'] ?? ($data['hour_meter'] ?? 0)
            );

            if (!$session) {
                return;
            }

            $tyre = Tyre::withoutGlobalScopes()->with(['brand', 'size', 'pattern'])->find($tyreId);
            if (!$tyre) {
                return;
            }

            $posDetail = TyrePositionDetail::find($positionId);
            $posCode = $posDetail ? $posDetail->position_code : "Pos-{$positionId}";

            // Calculate baseline RTD
            $r1 = isset($data['rtd_1']) && $data['rtd_1'] !== '' ? (float)$data['rtd_1'] : null;
            $r2 = isset($data['rtd_2']) && $data['rtd_2'] !== '' ? (float)$data['rtd_2'] : null;
            $r3 = isset($data['rtd_3']) && $data['rtd_3'] !== '' ? (float)$data['rtd_3'] : null;
            $r4 = isset($data['rtd_4']) && $data['rtd_4'] !== '' ? (float)$data['rtd_4'] : null;

            $rtdValues = array_filter([$r1, $r2, $r3, $r4], function($v) { return $v !== null && $v > 0; });
            if (!empty($rtdValues)) {
                $avgRtd = array_sum($rtdValues) / count($rtdValues);
            } else {
                $avgRtd = (float)($data['rtd_reading'] ?? $data['rtd'] ?? $tyre->current_tread_depth ?? $tyre->initial_tread_depth ?? 0);
            }

            $originalRtd = (float)($tyre->initial_tread_depth ?? $avgRtd);

            // Update session tyre_size if default '-'
            if ($session->tyre_size === '-' && $tyre->size) {
                $session->update([
                    'tyre_size' => $tyre->size->size,
                    'original_rtd' => $originalRtd
                ]);
            }

            // Remove previous tyre in this position in this session if replacing
            TyreMonitoringInstallation::withoutGlobalScopes()
                ->where('session_id', $session->session_id)
                ->where('position_id', $positionId)
                ->where('serial_number', '!=', $tyre->serial_number)
                ->delete();

            // 1. Create or update installation snapshot
            TyreMonitoringInstallation::withoutGlobalScopes()->updateOrCreate(
                [
                    'session_id' => $session->session_id,
                    'position_id' => $positionId,
                ],
                [
                    'position' => $posCode,
                    'serial_number' => $tyre->serial_number,
                    'tyre_id' => $tyre->id,
                    'brand' => $tyre->brand->brand_name ?? '-',
                    'pattern' => $tyre->pattern->name ?? '-',
                    'size' => $tyre->size->size ?? '-',
                    'inf_press_recommended' => 0,
                    'inf_press_actual' => (int)($data['psi_reading'] ?? ($data['psi'] ?? 0)),
                    'install_date' => $data['movement_date'] ?? date('Y-m-d'),
                    'rtd_1' => (float)($r1 ?? $avgRtd),
                    'rtd_2' => (float)($r2 ?? $avgRtd),
                    'rtd_3' => (float)($r3 ?? $avgRtd),
                    'rtd_4' => $r4,
                    'avg_rtd' => round($avgRtd, 2),
                    'original_rtd' => round($originalRtd, 2),
                    'odometer_reading' => (int)($data['odometer_reading'] ?? ($data['odometer'] ?? 0)),
                    'hm_reading' => (int)($data['hour_meter_reading'] ?? ($data['hour_meter'] ?? 0)),
                    'notes' => $data['notes'] ?? 'Auto-synced from Installation Movement',
                    'tyre_company_id' => $session->tyre_company_id,
                ]
            );

            // 2. Create or update Check 1 (Baseline Check)
            TyreMonitoringCheck::withoutGlobalScopes()->updateOrCreate(
                [
                    'session_id' => $session->session_id,
                    'check_number' => 1,
                    'position_id' => $positionId,
                ],
                [
                    'serial_number' => $tyre->serial_number,
                    'tyre_id' => $tyre->id,
                    'position' => $posCode,
                    'check_date' => $data['movement_date'] ?? date('Y-m-d'),
                    'odometer_reading' => (int)($data['odometer_reading'] ?? ($data['odometer'] ?? 0)),
                    'hm_reading' => (int)($data['hour_meter_reading'] ?? ($data['hour_meter'] ?? 0)),
                    'operation_mileage' => 0,
                    'operation_hm' => 0,
                    'inf_press_recommended' => 0,
                    'inf_press_actual' => (int)($data['psi_reading'] ?? ($data['psi'] ?? 0)),
                    'date_inspection' => $data['movement_date'] ?? date('Y-m-d'),
                    'rtd_1' => (float)($r1 ?? $avgRtd),
                    'rtd_2' => (float)($r2 ?? $avgRtd),
                    'rtd_3' => (float)($r3 ?? $avgRtd),
                    'rtd_4' => $r4,
                    'worn_percentage' => 0,
                    'km_per_mm' => 0,
                    'projected_life_km' => 0,
                    'condition' => 'ok',
                    'notes' => 'Baseline Cek 1 (Auto-created from Installation Movement)',
                    'approval_status' => 'Approved',
                    'approved_by' => auth()->id() ?? 1,
                    'tyre_company_id' => $session->tyre_company_id,
                ]
            );
        } catch (\Exception $e) {
            Log::error("TyreMonitoringSyncService::syncInstallation error: " . $e->getMessage());
        }
    }

    /**
     * Sync Removal event from Movement to Monitoring.
     */
    public static function syncRemoval($vehicleId, $positionId, $tyreId, array $data)
    {
        try {
            $monVehicle = self::getOrCreateMonitoringVehicle($vehicleId);
            if (!$monVehicle) return;

            $session = TyreMonitoringSession::withoutGlobalScopes()
                ->where('vehicle_id', $monVehicle->vehicle_id)
                ->where('status', 'active')
                ->latest('session_id')
                ->first();

            if (!$session) return;

            $tyre = Tyre::withoutGlobalScopes()->find($tyreId);
            $sn = $tyre ? $tyre->serial_number : null;

            // Remove from active installations for this session
            $query = TyreMonitoringInstallation::withoutGlobalScopes()
                ->where('session_id', $session->session_id);

            if ($sn) {
                $query->where(function($q) use ($sn, $positionId) {
                    $q->where('serial_number', $sn)->orWhere('position_id', $positionId);
                });
            } else {
                $query->where('position_id', $positionId);
            }

            $query->delete();
        } catch (\Exception $e) {
            Log::error("TyreMonitoringSyncService::syncRemoval error: " . $e->getMessage());
        }
    }

    /**
     * Sync Rotation event from Movement to Monitoring.
     */
    public static function syncRotation($vehicleId, $srcPosId, $tgtPosId, $srcTyreId, $tgtTyreId = null, array $data = [])
    {
        try {
            $monVehicle = self::getOrCreateMonitoringVehicle($vehicleId);
            if (!$monVehicle) return;

            $session = TyreMonitoringSession::withoutGlobalScopes()
                ->where('vehicle_id', $monVehicle->vehicle_id)
                ->where('status', 'active')
                ->latest('session_id')
                ->first();

            if (!$session) return;

            $srcPos = TyrePositionDetail::find($srcPosId);
            $tgtPos = TyrePositionDetail::find($tgtPosId);
            $srcPosCode = $srcPos ? $srcPos->position_code : "Pos-{$srcPosId}";
            $tgtPosCode = $tgtPos ? $tgtPos->position_code : "Pos-{$tgtPosId}";

            $srcTyre = Tyre::withoutGlobalScopes()->find($srcTyreId);
            $tgtTyre = $tgtTyreId ? Tyre::withoutGlobalScopes()->find($tgtTyreId) : null;

            // Update Source Tyre Installation & Checks in Session -> Target Position
            if ($srcTyre) {
                TyreMonitoringInstallation::withoutGlobalScopes()
                    ->where('session_id', $session->session_id)
                    ->where('serial_number', $srcTyre->serial_number)
                    ->update([
                        'position_id' => $tgtPosId,
                        'position' => $tgtPosCode
                    ]);

                TyreMonitoringCheck::withoutGlobalScopes()
                    ->where('session_id', $session->session_id)
                    ->where('serial_number', $srcTyre->serial_number)
                    ->update([
                        'position_id' => $tgtPosId,
                        'position' => $tgtPosCode
                    ]);
            }

            // Update Target Tyre Installation & Checks in Session (if Swap) -> Source Position
            if ($tgtTyre) {
                TyreMonitoringInstallation::withoutGlobalScopes()
                    ->where('session_id', $session->session_id)
                    ->where('serial_number', $tgtTyre->serial_number)
                    ->update([
                        'position_id' => $srcPosId,
                        'position' => $srcPosCode
                    ]);

                TyreMonitoringCheck::withoutGlobalScopes()
                    ->where('session_id', $session->session_id)
                    ->where('serial_number', $tgtTyre->serial_number)
                    ->update([
                        'position_id' => $srcPosId,
                        'position' => $srcPosCode
                    ]);
            }
        } catch (\Exception $e) {
            Log::error("TyreMonitoringSyncService::syncRotation error: " . $e->getMessage());
        }
    }
}
