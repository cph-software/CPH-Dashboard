<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengajuanTopDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengajuan_top_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_langganan');
            $table->string('nama_langganan');
            $table->string('avg_revenue')->nullable();
            $table->string('payment_term')->nullable();
            $table->string('top_sebelumnya');
            $table->string('top_permintaan');
            $table->string('status_top');
            $table->string('lama_top')->nullable();
            $table->longText('alasan_penambahan')->nullable();
            $table->string('tempat_usaha')->nullable();
            $table->string('foto_toko');
            $table->string('foto_ar');
            $table->date('tgl_bergabung_langganan');
            $table->string('diajukan_oleh');
            $table->date('tgl_diajukan');
            $table->string('spv_oleh')->nullable();
            $table->date('tgl_spv')->nullable();
            $table->string('auditor_oleh')->nullable();
            $table->date('tgl_auditor')->nullable();
            $table->string('manager_oleh')->nullable();
            $table->date('tgl_manager')->nullable();
            $table->string('direktur_oleh')->nullable();
            $table->date('tgl_direktur')->nullable();
            $table->string('proses');
            $table->longText('alasan_tolak')->nullable();
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
        Schema::dropIfExists('pengajuan_top_data');
    }
}
