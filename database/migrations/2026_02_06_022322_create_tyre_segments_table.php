<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreSegmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_segments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('segment_id')->unique();
            $table->string('segment_name');
            $table->unsignedBigInteger('tyre_location_id')->nullable()->index('tyre_segments_tyre_location_id_foreign');
            $table->string('terrain_type')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
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
        Schema::dropIfExists('tyre_segments');
    }
}
