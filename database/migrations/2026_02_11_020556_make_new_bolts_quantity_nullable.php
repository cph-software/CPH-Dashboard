<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeNewBoltsQuantityNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_movements', function (Blueprint $table) {
            $table->integer('new_bolts_quantity')->nullable()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyre_movements', function (Blueprint $table) {
            $table->integer('new_bolts_quantity')->nullable(false)->default(0)->change();
        });
    }
}
