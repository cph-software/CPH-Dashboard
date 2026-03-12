<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SyncMonitoringWithMasterData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Sync tyre_monitoring_session with master_import_kendaraan
        Schema::table('tyre_monitoring_session', function (Blueprint $table) {
            $table->unsignedBigInteger('master_vehicle_id')->nullable()->after('vehicle_id');
            $table->foreign('master_vehicle_id')->references('id')->on('master_import_kendaraan')->onDelete('set null');
        });

        // 2. Sync tyre_monitoring_installation with master tyre data and position details
        Schema::table('tyre_monitoring_installation', function (Blueprint $table) {
            $table->unsignedBigInteger('tyre_id')->nullable()->after('serial_number');
            $table->unsignedBigInteger('position_id')->nullable()->after('position');
            
            $table->foreign('tyre_id')->references('id')->on('tyres')->onDelete('set null');
            $table->foreign('position_id')->references('id')->on('tyre_position_details')->onDelete('set null');
        });

        // 3. Sync tyre_monitoring_check with master position details
        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            $table->unsignedBigInteger('position_id')->nullable()->after('position');
            $table->foreign('position_id')->references('id')->on('tyre_position_details')->onDelete('set null');
        });

        // 4. Sync tyre_monitoring_removal with master position details
        Schema::table('tyre_monitoring_removal', function (Blueprint $table) {
            $table->unsignedBigInteger('position_id')->nullable()->after('position');
            $table->foreign('position_id')->references('id')->on('tyre_position_details')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyre_monitoring_removal', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
        });

        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
        });

        Schema::table('tyre_monitoring_installation', function (Blueprint $table) {
            $table->dropForeign(['tyre_id']);
            $table->dropForeign(['position_id']);
            $table->dropColumn(['tyre_id', 'position_id']);
        });

        Schema::table('tyre_monitoring_session', function (Blueprint $table) {
            $table->dropForeign(['master_vehicle_id']);
            $table->dropColumn('master_vehicle_id');
        });
    }
}
