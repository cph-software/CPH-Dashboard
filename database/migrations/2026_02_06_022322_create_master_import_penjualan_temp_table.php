<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterImportPenjualanTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_import_penjualan_temp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cabang');
            $table->string('no_invoice');
            $table->date('tanggal_invoice');
            $table->string('id_sales');
            $table->string('sales');
            $table->string('id_customer');
            $table->string('nama_customer');
            $table->string('id_produk');
            $table->string('nama_produk');
            $table->integer('qty');
            $table->bigInteger('dpp');
            $table->bigInteger('net_sales');
            $table->string('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_import_penjualan_temp');
    }
}
