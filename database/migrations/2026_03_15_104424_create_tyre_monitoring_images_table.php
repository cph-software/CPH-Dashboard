<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreMonitoringImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_monitoring_images', function (Blueprint $table) {
            $table->id('image_id');
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('check_id')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('image_type')->comment('fleet, vehicle, map, odometer_km, odometer_hm, tyre_serial, tyre_psi, tyre_rtd_1, tyre_rtd_2, tyre_rtd_3, tyre_rtd_4');
            $table->string('image_path');
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('session_id')->on('tyre_monitoring_session')->onDelete('set null');
            $table->foreign('check_id')->references('check_id')->on('tyre_monitoring_check')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tyre_monitoring_images');
    }
}
