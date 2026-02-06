<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKontrakMasterKontrakTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kontrak_master_kontrak', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama_kontrak');
            $table->text('keterangan');
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir');
            $table->string('status');
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
        Schema::dropIfExists('kontrak_master_kontrak');
    }
}
