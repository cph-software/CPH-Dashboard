<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLimitTokoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('limit_toko', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_toko')->unique();
            $table->string('nama_toko');
            $table->string('average_revenue_tunai')->nullable();
            $table->string('average_revenue_kredit')->nullable();
            $table->string('payment_term_tunai')->nullable();
            $table->string('payment_term_kredir')->nullable();
            $table->string('limit_toko')->nullable();
            $table->date('tanggal_bergabung')->nullable();
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
        Schema::dropIfExists('limit_toko');
    }
}
