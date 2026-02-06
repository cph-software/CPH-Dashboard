<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKaryawanKandidatTerpilihTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('karyawan_kandidat_terpilih', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('request_karyawan_id')->index('karyawan_kandidat_terpilih_request_karyawan_id_foreign');
            $table->unsignedBigInteger('kandidat_id')->index('karyawan_kandidat_terpilih_kandidat_id_foreign');
            $table->string('status');
            $table->date('tgl_test1');
            $table->date('tgl_wawancara1');
            $table->date('tgl_test2');
            $table->date('tgl_wawancara2');
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
        Schema::dropIfExists('karyawan_kandidat_terpilih');
    }
}
