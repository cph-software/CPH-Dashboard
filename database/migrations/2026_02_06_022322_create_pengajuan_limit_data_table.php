<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengajuanLimitDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengajuan_limit_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_pengajuanlimit');
            $table->string('kode_langganan');
            $table->string('nama_langganan');
            $table->string('avg_revenue_tunai')->nullable();
            $table->string('avg_revenue_kredit')->nullable();
            $table->string('top');
            $table->string('payment_term_tunai')->nullable();
            $table->string('payment_term_kredit')->nullable();
            $table->string('limit');
            $table->string('limit_dipakai');
            $table->string('limit_diajukan');
            $table->string('status_limit');
            $table->string('lama_limit')->nullable();
            $table->date('tgl_tj');
            $table->string('nomor_tj');
            $table->string('nilai_tj');
            $table->longText('alasan_penambahan')->nullable();
            $table->string('foto_ar')->nullable();
            $table->string('foto_arr')->nullable();
            $table->string('foto_arrr')->nullable();
            $table->string('foto_audit')->nullable();
            $table->string('foto_auditt')->nullable();
            $table->string('surat_pernyataan_transfer');
            $table->string('surat_transfer')->nullable();
            $table->date('tgl_bergabung_langganan')->nullable();
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
            $table->longText('alasan_pending')->nullable();
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
        Schema::dropIfExists('pengajuan_limit_data');
    }
}
