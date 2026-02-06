<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKaryawanKandidatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('karyawan_kandidat', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cabang');
            $table->string('pos_mks');
            $table->string('pos_kdi');
            $table->string('pos_pal');
            $table->string('pos_bmp');
            $table->string('posisi');
            $table->string('nama');
            $table->string('mobile');
            $table->string('email');
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
        Schema::dropIfExists('karyawan_kandidat');
    }
}
