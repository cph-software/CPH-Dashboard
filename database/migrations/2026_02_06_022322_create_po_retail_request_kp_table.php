<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoRetailRequestKpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('po_retail_request_kp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('urut');
            $table->string('transid');
            $table->string('id_barang_rtl');
            $table->string('kode_produksi')->nullable();
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
        Schema::dropIfExists('po_retail_request_kp');
    }
}
