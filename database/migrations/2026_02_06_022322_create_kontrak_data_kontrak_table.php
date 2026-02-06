<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKontrakDataKontrakTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kontrak_data_kontrak', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('kontrak_id')->index('kontrak_data_kontrak_kontrak_id_foreign');
            $table->string('cabang');
            $table->string('no_kontrak');
            $table->string('kode_lang');
            $table->string('target');
            $table->string('hadiah');
            $table->string('sales');
            $table->string('pencapaian')->nullable();
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
        Schema::dropIfExists('kontrak_data_kontrak');
    }
}
