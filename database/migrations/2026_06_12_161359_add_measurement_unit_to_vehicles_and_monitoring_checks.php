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
