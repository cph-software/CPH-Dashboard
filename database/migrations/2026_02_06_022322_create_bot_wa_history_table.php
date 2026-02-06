<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotWaHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_wa_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('bot_name');
            $table->string('source');
            $table->string('type');
            $table->string('to');
            $table->string('group_or_number');
            $table->longText('message');
            $table->string('pic')->nullable();
            $table->longText('response')->nullable();
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
        Schema::dropIfExists('bot_wa_history');
    }
}
