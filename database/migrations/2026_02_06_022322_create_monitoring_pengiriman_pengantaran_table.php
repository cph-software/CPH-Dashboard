<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitoringPengirimanPengantaranTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitoring_pengiriman_pengantaran', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('id_surat_jalan');
            $table->string('driver');
            $table->string('invoice_number');
            $table->string('status')->nullable();
            $table->string('status_pengantaran')->nullable();
            $table->string('foto')->nullable();
            $table->string('keterangan')->nullable();
            $table->dateTime('jam_berangkat')->nullable();
            $table->dateTime('jam_tiba')->nullable();
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
        Schema::dropIfExists('monitoring_pengiriman_pengantaran');
    }
}
