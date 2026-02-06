<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKontrakMasterRuleKontrakTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kontrak_master_rule_kontrak', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('principal');
            $table->string('rule_kontrak');
            $table->string('rule_kontrak_keterangan')->nullable();
            $table->enum('hitung', ['QTY', 'DPP', 'POIN', 'Liter', 'Dos']);
            $table->enum('jenis', ['Kategori', 'Custom Produk', 'Exclude']);
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
        Schema::dropIfExists('kontrak_master_rule_kontrak');
    }
}
