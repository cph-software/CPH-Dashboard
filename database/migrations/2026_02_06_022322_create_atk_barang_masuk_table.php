<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtkBarangMasukTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('atk_barang_masuk', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('tanggal');
            $table->unsignedBigInteger('master_barang_id')->index('atk_barang_masuk_master_barang_id_foreign');
            $table->integer('qty');
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
        Schema::dropIfExists('atk_barang_masuk');
    }
}
