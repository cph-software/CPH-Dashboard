<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotWaCounterHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_wa_counter_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_lang');
            $table->string('nama_lang');
            $table->string('nama');
            $table->string('jabatan');
            $table->string('no_telp');
            $table->string('daerah');
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
        Schema::dropIfExists('bot_wa_counter_history');
    }
}
