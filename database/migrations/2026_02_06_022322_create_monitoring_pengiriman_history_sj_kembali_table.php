<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitoringPengirimanHistorySjKembaliTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitoring_pengiriman_history_sj_kembali', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('id_surat_jalan');
            $table->string('invoice_number');
            $table->string('ket');
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
        Schema::dropIfExists('monitoring_pengiriman_history_sj_kembali');
    }
}
