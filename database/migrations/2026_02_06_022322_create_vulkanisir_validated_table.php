<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVulkanisirValidatedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vulkanisir_validated', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transid');
            $table->string('kode_lang');
            $table->string('nama_lang');
            $table->string('nama');
            $table->string('telp');
            $table->string('jumlah');
            $table->string('validated_by');
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
        Schema::dropIfExists('vulkanisir_validated');
    }
}
