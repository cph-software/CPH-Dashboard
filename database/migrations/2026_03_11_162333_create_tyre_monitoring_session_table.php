<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreMonitoringSessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_monitoring_session', function (Blueprint $table) {
            $table->bigIncrements('session_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->date('install_date');
            $table->string('tyre_size');
            $table->decimal('original_rtd', 5, 2); // rtd awal saat instalasi (mm)
            $table->integer('odometer_start');
            $table->string('pattern')->nullable();
            $table->integer('retase')->nullable(); // tekanan angin rekomendasi, Psi
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->timestamps();

            $table->foreign('vehicle_id')->references('vehicle_id')->on('tyre_monitoring_vehicle')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tyre_monitoring_session');
    }
}
