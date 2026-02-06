<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterImportWilayahLanggananTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_import_wilayah_langganan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cabang_id');
            $table->string('kode_lang');
            $table->string('wilayah');
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
        Schema::dropIfExists('master_import_wilayah_langganan');
    }
}
