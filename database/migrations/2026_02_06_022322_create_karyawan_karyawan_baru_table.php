<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKaryawanKaryawanBaruTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('karyawan_karyawan_baru', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_master_karyawan')->nullable();
            $table->string('id_karyawan')->nullable();
            $table->string('nama')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('nama_atasan')->nullable();
            $table->string('wa_atasan')->nullable();
            $table->string('email_atasan')->nullable();
            $table->string('lokasi');
            $table->date('tanggal_join');
            $table->integer('lama');
            $table->enum('status', ['Kontrak', 'Permanen', 'Selesai']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('karyawan_karyawan_baru');
    }
}
