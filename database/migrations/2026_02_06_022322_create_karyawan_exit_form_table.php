<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKaryawanExitFormTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('karyawan_exit_form', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_form');
            $table->string('nama_karyawan');
            $table->string('jabatan');
            $table->string('lokasi');
            $table->date('tanggal_bergabung');
            $table->date('tanggal_keluar');
            $table->string('masa_kerja');
            $table->longText('alasan_keluar');
            $table->longText('nama_perusahaan_baru')->nullable();
            $table->longText('jabatan_baru')->nullable();
            $table->longText('gaji_baru')->nullable();
            $table->longText('penilaian_atasan');
            $table->longText('alasan_penilaian_atasan');
            $table->longText('saran');
            $table->string('kenyamanan_kerja');
            $table->string('beban_kerja');
            $table->string('gaji_tunjangan');
            $table->string('kesempatan_berkembang');
            $table->string('efektivitas_organisasi');
            $table->string('fasilitas_kesehatan');
            $table->string('perhatian_management');
            $table->string('lingkungan_kerja');
            $table->string('kualitas_pelatihan');
            $table->longText('komentar');
            $table->longText('seragam')->nullable();
            $table->longText('nametag')->nullable();
            $table->longText('kunci_loker')->nullable();
            $table->longText('pinjaman')->nullable();
            $table->longText('handphone')->nullable();
            $table->longText('laptop')->nullable();
            $table->longText('faktur_sales')->nullable();
            $table->longText('sisa_faktur')->nullable();
            $table->longText('ijazah')->nullable();
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
        Schema::dropIfExists('karyawan_exit_form');
    }
}
