<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitoringPengirimanSuratJalanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitoring_pengiriman_surat_jalan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('id_surat_jalan')->index();
            $table->integer('no_surat_jalan')->index();
            $table->date('tanggal');
            $table->string('no_polisi');
            $table->string('driver');
            $table->string('helper');
            $table->string('gudang')->nullable();
            $table->string('keterangan')->nullable();
            $table->dateTime('tgl_jalan')->nullable();
            $table->dateTime('tgl_kembali')->nullable();
            $table->dateTime('tgl_terima_faktur')->nullable();
            $table->string('user_id')->nullable();
            $table->string('foto')->nullable();
            $table->string('pengantaran_driver')->nullable();
            $table->enum('saved', ['0', '1'])->default('0');
            $table->integer('cabang_id')->nullable();
            $table->string('rute')->nullable();
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
        Schema::dropIfExists('monitoring_pengiriman_surat_jalan');
    }
}
