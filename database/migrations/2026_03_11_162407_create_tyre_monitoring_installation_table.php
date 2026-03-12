<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreMonitoringInstallationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_monitoring_installation', function (Blueprint $table) {
            $table->bigIncrements('install_id');
            $table->unsignedBigInteger('session_id');
            $table->integer('position'); // posisi ban (1 s/d tire_positions)
            $table->string('serial_number');
            $table->string('brand');
            $table->string('pattern');
            $table->string('size');
            $table->integer('inf_press_recommended')->comment('Psi');
            $table->integer('inf_press_actual')->comment('Psi');
            $table->date('install_date');
            $table->decimal('rtd_1', 5, 2);
            $table->decimal('rtd_2', 5, 2);
            $table->decimal('rtd_3', 5, 2);
            $table->decimal('avg_rtd', 5, 2);
            $table->integer('odometer');
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
        Schema::dropIfExists('tyre_monitoring_installation');
    }
}
