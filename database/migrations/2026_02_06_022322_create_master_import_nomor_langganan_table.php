<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterImportNomorLanggananTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_import_nomor_langganan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_langganan');
            $table->string('pic')->nullable();
            $table->string('nomor_telepon')->nullable();
            $table->string('sebagai')->nullable();
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
        Schema::dropIfExists('master_import_nomor_langganan');
    }
}
