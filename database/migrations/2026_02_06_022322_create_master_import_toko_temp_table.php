<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterImportTokoTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_import_toko_temp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_toko')->index();
            $table->string('nama_toko');
            $table->string('alamat1')->nullable();
            $table->string('alamat2')->nullable();
            $table->string('distrik')->nullable();
            $table->string('kota')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('no_hp2')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('sold_to')->nullable();
            $table->string('kat_1')->nullable();
            $table->string('kat_2')->nullable();
            $table->string('kat_3')->nullable();
            $table->string('kat_4')->nullable();
            $table->string('kat_5')->nullable();
            $table->string('kat_6')->nullable();
            $table->string('kat_7')->nullable();
            $table->string('kat_8')->nullable();
            $table->string('kat_9')->nullable();
            $table->string('kat_10')->nullable();
            $table->string('kat_11')->nullable();
            $table->string('kat_12')->nullable();
            $table->string('kat_13')->nullable();
            $table->integer('limit_toko')->nullable();
            $table->string('top')->nullable();
            $table->date('tanggal_join')->nullable();
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
        Schema::dropIfExists('master_import_toko_temp');
    }
}
