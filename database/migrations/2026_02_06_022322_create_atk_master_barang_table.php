<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtkMasterBarangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('atk_master_barang', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_barang');
            $table->string('nama_barang');
            $table->string('spesifikasi_barang');
            $table->string('satuan');
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
        Schema::dropIfExists('atk_master_barang');
    }
}
