<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoRetailRequestDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('po_retail_request_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transid');
            $table->string('id_barang_rtl');
            $table->string('nama_barang_rtl');
            $table->string('id_barang_tol')->nullable();
            $table->string('nama_barang_tol')->nullable();
            $table->string('qty');
            $table->string('satuan')->nullable();
            $table->string('supply');
            $table->string('terima')->nullable();
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
        Schema::dropIfExists('po_retail_request_detail');
    }
}
