<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKanvasingPenjualanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kanvasing_penjualan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id');
            $table->string('customer_type')->nullable();
            $table->string('kode_penjualan')->unique();
            $table->string('kode_langganan')->nullable();
            $table->string('jenis_transaksi')->nullable();
            $table->string('nama')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('foto_invoice')->nullable();
            $table->string('foto_ktp')->nullable();
            $table->string('foto_bangunan')->nullable();
            $table->string('foto_owner')->nullable();
            $table->string('video')->nullable();
            $table->string('status');
            $table->string('invoice_number');
            $table->string('verifikasi_atasan')->nullable();
            $table->string('verifikasi_manager')->nullable();
            $table->string('verified_by')->nullable();
            $table->string('verified_by_manager')->nullable();
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
        Schema::dropIfExists('kanvasing_penjualan');
    }
}
