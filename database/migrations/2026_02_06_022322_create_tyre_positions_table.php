<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyrePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_positions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('position_code');
            $table->enum('axle', ['Front', 'Middle', 'Rear']);
            $table->enum('side', ['Left', 'Right']);
            $table->integer('position_order');
            $table->string('description')->nullable();
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
        Schema::dropIfExists('tyre_positions');
    }
}
