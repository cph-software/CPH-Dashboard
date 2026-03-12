<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreMonitoringRemovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_monitoring_removal', function (Blueprint $table) {
            $table->bigIncrements('removal_id');
            $table->unsignedBigInteger('session_id');
            $table->integer('position');
            $table->string('serial_number');
            $table->date('removal_date');
            $table->integer('odometer');
            $table->integer('total_mileage'); // total KM sejak install
            $table->decimal('final_rtd', 5, 2); // rtd akhir saat dilepas
            $table->string('removal_reason')->nullable(); // contoh: Worn Out, Damage, Rotation
            $table->string('tyre_condition_after')->nullable(); // kondisi ban setelah dilepas
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('session_id')->on('tyre_monitoring_session')->onDelete('cascade');
            $table->foreign('serial_number')->references('serial_number')->on('tyres')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tyre_monitoring_removal');
    }
}
