<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoRetailRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('po_retail_request', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transid');
            $table->string('cabang_id');
            $table->string('request_by');
            $table->string('status');
            $table->dateTime('terima_tanggal')->nullable();
            $table->string('terima_oleh')->nullable();
            $table->dateTime('batal_tanggal')->nullable();
            $table->string('batal_oleh')->nullable();
            $table->text('keterangan')->nullable();
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
        Schema::dropIfExists('po_retail_request');
    }
}
