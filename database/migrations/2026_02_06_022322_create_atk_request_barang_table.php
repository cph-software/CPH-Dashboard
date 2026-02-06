<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtkRequestBarangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('atk_request_barang', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('master_barang_id')->index('atk_request_barang_master_barang_id_foreign');
            $table->string('user_id')->index('atk_request_barang_user_id_foreign');
            $table->integer('jumlah');
            $table->longText('keterangan')->nullable();
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
        Schema::dropIfExists('atk_request_barang');
    }
}
