<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKontrakRuleKontrakCustomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kontrak_rule_kontrak_custom', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('master_rule_kontrak_id')->index('kontrak_rule_kontrak_custom_master_rule_kontrak_id_foreign');
            $table->string('nama_kategori')->nullable();
            $table->string('product_id')->nullable();
            $table->bigInteger('poin')->nullable();
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
        Schema::dropIfExists('kontrak_rule_kontrak_custom');
    }
}
