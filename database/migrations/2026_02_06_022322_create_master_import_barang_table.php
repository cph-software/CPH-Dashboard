<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterImportBarangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_import_barang', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('product_id');
            $table->string('name');
            $table->string('satuan');
            $table->string('kategori1')->nullable();
            $table->string('kategori2')->nullable();
            $table->string('kategori3')->nullable();
            $table->string('kategori4')->nullable();
            $table->string('kategori5')->nullable();
            $table->string('kategori6')->nullable();
            $table->string('kategori7')->nullable();
            $table->string('kategori8')->nullable();
            $table->string('kategori9')->nullable();
            $table->string('mobil_jenis')->nullable();
            $table->string('mobil_kategori')->nullable();
            $table->string('mobil_type')->nullable();
            $table->string('mobil_segment')->nullable();
            $table->string('mobil_lob')->nullable();
            $table->string('mobil_pack')->nullable();
            $table->string('mobil_liter')->nullable();
            $table->string('mobil_satuan')->nullable();
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
        Schema::dropIfExists('master_import_barang');
    }
}
