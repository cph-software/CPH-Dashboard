<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSerahTerimaKreditDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serah_terima_kredit_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('no_surat')->nullable()->unique();
            $table->unsignedBigInteger('cabang_id')->nullable();
            $table->string('cabang_manual')->nullable();
            $table->unsignedBigInteger('origin_branch_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_nama')->nullable();
            $table->string('customer_kendaraan')->nullable();
            $table->string('customer_plat')->nullable();
            $table->date('tgl_serah_terima');
            $table->string('status')->default('Menunggu');
            $table->string('approve_oleh')->nullable();
            $table->dateTime('tgl_approve')->nullable();
            $table->text('alasan_tolak')->nullable();
            $table->string('diserahkan_oleh')->nullable();
            $table->string('diterima_oleh')->nullable();
            $table->text('catatan')->nullable();
            $table->string('created_by');
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
        Schema::dropIfExists('serah_terima_kredit_data');
    }
}
