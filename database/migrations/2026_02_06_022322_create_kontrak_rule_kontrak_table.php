<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKontrakRuleKontrakTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kontrak_rule_kontrak', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('kontrak_id')->index('kontrak_rule_kontrak_kontrak_id_foreign');
            $table->unsignedBigInteger('rule_id')->nullable()->index('kontrak_rule_kontrak_rule_id_foreign');
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
        Schema::dropIfExists('kontrak_rule_kontrak');
    }
}
