<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreFailureAliasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_failure_aliases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('toko_id')->comment('Representasi company/instansi');
            $table->unsignedBigInteger('tyre_failure_code_id');
            $table->string('alias_name');
            $table->timestamps();

            $table->foreign('tyre_failure_code_id')->references('id')->on('tyre_failure_codes')->onDelete('cascade');
            // Jika ada tabel tb_toko atau master_toko, Anda bisa tambahkan foreign key ke toko_id juga di sini.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tyre_failure_aliases');
    }
}
