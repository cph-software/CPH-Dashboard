<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreMonitoringVehicleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_monitoring_vehicle', function (Blueprint $table) {
            $table->bigIncrements('vehicle_id');
            $table->string('fleet_name');
            $table->string('vehicle_number'); // nomor polisi
            $table->string('driver_name');
            $table->string('phone_number')->nullable();
            $table->string('application')->nullable(); // contoh: Truk Semen Maros-Toraja
            $table->string('load_capacity')->nullable(); // contoh: 30 Ton
            $table->integer('tire_positions'); // jumlah posisi ban
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tyre_monitoring_vehicle');
    }
}
