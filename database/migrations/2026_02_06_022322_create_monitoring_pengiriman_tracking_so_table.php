<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitoringPengirimanTrackingSoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitoring_pengiriman_tracking_so', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('tanggal_so')->nullable()->index();
            $table->string('referensi')->nullable();
            $table->string('no_so')->nullable()->index();
            $table->string('status_so')->nullable();
            $table->string('trans_so')->nullable();
            $table->string('amount_so')->nullable();
            $table->string('no_do')->nullable();
            $table->dateTime('tanggal_do')->nullable();
            $table->string('amount_do')->nullable();
            $table->string('status_do')->nullable();
            $table->string('invoice')->nullable()->index();
            $table->string('inv_batal')->nullable();
            $table->string('id_cust')->nullable()->index();
            $table->string('id_sales')->nullable();
            $table->string('nama_sales')->nullable();
            $table->string('nama_customer')->nullable();
            $table->integer('id_surat_jalan')->index();
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
        Schema::dropIfExists('monitoring_pengiriman_tracking_so');
    }
}
