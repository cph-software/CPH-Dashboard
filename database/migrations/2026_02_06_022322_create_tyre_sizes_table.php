<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreSizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_sizes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('size');
            $table->unsignedBigInteger('tyre_brand_id')->index('tyre_sizes_tyre_brand_id_foreign');
            $table->decimal('std_otd');
            $table->integer('ply_rating');
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
        Schema::dropIfExists('tyre_sizes');
    }
}
