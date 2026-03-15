<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHourMeterToMonitoringTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_monitoring_session', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_monitoring_session', 'hm_start')) {
                $table->integer('hm_start')->nullable()->after('odometer_start');
            }
        });

        Schema::table('tyre_monitoring_installation', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_monitoring_installation', 'hm_reading')) {
                $table->integer('hm_reading')->nullable()->after('odometer_reading');
            }
        });

        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_monitoring_check', 'hm_reading')) {
                $table->integer('hm_reading')->nullable()->after('odometer_reading');
            }
            if (!Schema::hasColumn('tyre_monitoring_check', 'operation_hm')) {
                $table->integer('operation_hm')->nullable()->after('operation_mileage');
            }
        });

        Schema::table('tyres', function (Blueprint $table) {
            if (!Schema::hasColumn('tyres', 'total_lifetime_hm')) {
                $table->integer('total_lifetime_hm')->default(0)->after('total_lifetime_km');
            }
            if (!Schema::hasColumn('tyres', 'last_hm_reading')) {
                $table->integer('last_hm_reading')->nullable();
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
        // ... handled by drop table usually or specific drops
    }
}
