<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMagangMasterDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('magang_master_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nomor_sertifikat');
            $table->string('nama_penerima');
            $table->string('nama_program');
            $table->string('lokasi');
            $table->string('tanggal_mulai');
            $table->string('tanggal_selesai');
            $table->string('tanggal_ttd');
            $table->string('nama_ttd');
            $table->string('posisi_ttd');
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
        Schema::dropIfExists('magang_master_data');
    }
}
