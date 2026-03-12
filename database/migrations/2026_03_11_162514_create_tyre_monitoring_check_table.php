<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreMonitoringCheckTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_monitoring_check', function (Blueprint $table) {
            $table->bigIncrements('check_id');
            $table->unsignedBigInteger('session_id');
            $table->integer('check_number'); // urutan cek: 1, 2, 3, ...n
            $table->date('check_date');
            $table->integer('odometer');
            $table->integer('operation_mileage'); // total KM sejak install_date
            $table->integer('position');
            $table->string('serial_number');
            $table->integer('inf_press_recommended')->comment('Psi');
            $table->integer('inf_press_actual')->comment('Psi');
            $table->decimal('rtd_1', 5, 2);
            $table->decimal('rtd_2', 5, 2);
            $table->decimal('rtd_3', 5, 2);
            $table->enum('condition', ['ok', 'warning', 'critical'])->nullable();
            $table->text('recommendation')->nullable(); // contoh: Swap ke posisi #2, balik ban
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
        Schema::dropIfExists('tyre_monitoring_check');
    }
}
