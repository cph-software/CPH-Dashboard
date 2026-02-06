<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKaryawanRequestKaryawanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('karyawan_request_karyawan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('karyawan_request_karyawan_user_id_foreign');
            $table->unsignedBigInteger('cabang_id')->index('karyawan_request_karyawan_cabang_id_foreign');
            $table->unsignedBigInteger('role_id')->index('karyawan_request_karyawan_role_id_foreign');
            $table->integer('jumlah_yang_diajukan');
            $table->string('alasan_pengajuan');
            $table->string('nama_karyawan_resign')->nullable();
            $table->string('alasan_penambahan')->nullable();
            $table->string('status')->nullable();
            $table->date('acc_hrd_at')->nullable();
            $table->date('dcc_hrd_at')->nullable();
            $table->date('acc_pimpinan_at')->nullable();
            $table->date('dcc_pimpinan_at')->nullable();
            $table->unsignedBigInteger('acc_hrd_id')->nullable()->index('karyawan_request_karyawan_acc_hrd_id_foreign');
            $table->unsignedBigInteger('dcc_hrd_id')->nullable()->index('karyawan_request_karyawan_dcc_hrd_id_foreign');
            $table->unsignedBigInteger('acc_pimpinan_id')->nullable()->index('karyawan_request_karyawan_acc_pimpinan_id_foreign');
            $table->unsignedBigInteger('dcc_pimpinan_id')->nullable()->index('karyawan_request_karyawan_dcc_pimpinan_id_foreign');
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
        Schema::dropIfExists('karyawan_request_karyawan');
    }
}
