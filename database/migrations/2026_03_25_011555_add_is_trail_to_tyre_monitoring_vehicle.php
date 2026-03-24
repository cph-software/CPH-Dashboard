<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsTrailToTyreMonitoringVehicle extends Migration
{
    public function up()
    {
        Schema::table('tyre_monitoring_vehicle', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_monitoring_vehicle', 'is_trail')) {
                $table->boolean('is_trail')->default(false)->after('tire_positions');
            }
        });
    }

    public function down()
    {
        Schema::table('tyre_monitoring_vehicle', function (Blueprint $table) {
            $table->dropColumn('is_trail');
        });
    }
}
