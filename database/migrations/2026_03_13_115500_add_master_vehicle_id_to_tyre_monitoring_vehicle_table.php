<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMasterVehicleIdToTyreMonitoringVehicleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_monitoring_vehicle', function (Blueprint $table) {
            $table->unsignedBigInteger('master_vehicle_id')->nullable()->after('vehicle_id');
            $table->foreign('master_vehicle_id')->references('id')->on('master_import_kendaraan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyre_monitoring_vehicle', function (Blueprint $table) {
            $table->dropForeign(['master_vehicle_id']);
            $table->dropColumn('master_vehicle_id');
        });
    }
}
