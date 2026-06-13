<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMeasurementUnitToVehiclesAndMonitoringChecks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_import_kendaraan', function (Blueprint $table) {
            if (!Schema::hasColumn('master_import_kendaraan', 'measurement_unit')) {
                $table->enum('measurement_unit', ['KM', 'HM'])->default('KM')->after('no_polisi');
            }
        });

        Schema::table('tyre_monitoring_vehicle', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_monitoring_vehicle', 'measurement_unit')) {
                $table->enum('measurement_unit', ['KM', 'HM'])->default('KM')->after('vehicle_number');
            }
        });

        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_monitoring_check', 'hm_per_mm')) {
                $table->decimal('hm_per_mm', 12, 2)->nullable()->after('km_per_mm');
            }
            if (!Schema::hasColumn('tyre_monitoring_check', 'projected_life_hm')) {
                $table->decimal('projected_life_hm', 15, 2)->nullable()->after('projected_life_km');
            }
        });

        // Backfill existing records
        try {
            $checks = \DB::table('tyre_monitoring_check')->get();
            foreach ($checks as $c) {
                // Find installation to get original RTD
                $inst = \DB::table('tyre_monitoring_installation')
                    ->where('session_id', $c->session_id)
                    ->where('serial_number', $c->serial_number)
                    ->first();
                
                $session = \DB::table('tyre_monitoring_session')
                    ->where('session_id', $c->session_id)
                    ->first();

                $origRtd = 1;
                if ($inst && $inst->original_rtd > 0) {
                    $origRtd = $inst->original_rtd;
                } elseif ($session && $session->original_rtd > 0) {
                    $origRtd = $session->original_rtd;
                }

                $rtds = array_filter([$c->rtd_1, $c->rtd_2, $c->rtd_3, $c->rtd_4], function($v) { return $v > 0; });
                $avgRtd = count($rtds) > 0 ? array_sum($rtds) / count($rtds) : 0;
                $lossRtd = $origRtd - $avgRtd;

                $updateData = [];

                // Backfill HM metrics if operation_hm exists
                if ($c->operation_hm > 0 && $lossRtd >= 0.1) {
                    $hmPerMm = $c->operation_hm / $lossRtd;
                    $remainingTread = max(0, $avgRtd - 3);
                    $projectedLifeHm = $hmPerMm * $remainingTread;

                    $updateData['hm_per_mm'] = round($hmPerMm, 2);
                    $updateData['projected_life_hm'] = round($projectedLifeHm, 2);
                }

                // Backfill KM metrics if they were not calculated correctly or are zero/null
                if ($c->operation_mileage > 0 && $lossRtd >= 0.1 && (empty($c->km_per_mm) || $c->km_per_mm == 0)) {
                    $kmPerMm = $c->operation_mileage / $lossRtd;
                    $remainingTread = max(0, $avgRtd - 3);
                    $projectedLifeKm = $kmPerMm * $remainingTread;

                    $updateData['km_per_mm'] = round($kmPerMm, 2);
                    $updateData['projected_life_km'] = round($projectedLifeKm, 2);
                }

                if (!empty($updateData)) {
                    \DB::table('tyre_monitoring_check')
                        ->where('check_id', $c->check_id)
                        ->update($updateData);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to backfill tyre monitoring metrics: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_import_kendaraan', function (Blueprint $table) {
            if (Schema::hasColumn('master_import_kendaraan', 'measurement_unit')) {
                $table->dropColumn('measurement_unit');
            }
        });

        Schema::table('tyre_monitoring_vehicle', function (Blueprint $table) {
            if (Schema::hasColumn('tyre_monitoring_vehicle', 'measurement_unit')) {
                $table->dropColumn('measurement_unit');
            }
        });

        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            if (Schema::hasColumn('tyre_monitoring_check', 'hm_per_mm')) {
                $table->dropColumn('hm_per_mm');
            }
            if (Schema::hasColumn('tyre_monitoring_check', 'projected_life_hm')) {
                $table->dropColumn('projected_life_hm');
            }
        });
    }
}
