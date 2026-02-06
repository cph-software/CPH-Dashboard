<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterImportDataEkspedisiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_import_data_ekspedisi', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cabang')->nullable();
            $table->string('nama');
            $table->string('pic');
            $table->string('alamat');
            $table->string('tujuan');
            $table->string('telepon');
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
        Schema::dropIfExists('master_import_data_ekspedisi');
    }
}
