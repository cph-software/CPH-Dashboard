<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameOdometerToOdometerReadingInMonitoringTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_monitoring_installation', function (Blueprint $table) {
            $table->renameColumn('odometer', 'odometer_reading');
        });

        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            $table->renameColumn('odometer', 'odometer_reading');
        });

        Schema::table('tyre_monitoring_removal', function (Blueprint $table) {
            $table->renameColumn('odometer', 'odometer_reading');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyre_monitoring_installation', function (Blueprint $table) {
            $table->renameColumn('odometer_reading', 'odometer');
        });

        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            $table->renameColumn('odometer_reading', 'odometer');
        });

        Schema::table('tyre_monitoring_removal', function (Blueprint $table) {
            $table->renameColumn('odometer_reading', 'odometer');
        });
    }
}
