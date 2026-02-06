<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyres', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('serial_number')->unique();
            $table->string('tyre_type');
            $table->unsignedBigInteger('tyre_pattern_id')->nullable()->index('tyres_tyre_pattern_id_foreign');
            $table->unsignedBigInteger('tyre_segment_id')->nullable()->index('tyres_tyre_segment_id_foreign');
            $table->enum('status', ['Installed', 'New', 'Scrap', 'Repaired'])->default('New');
            $table->unsignedBigInteger('tyre_brand_id')->index('tyres_tyre_brand_id_foreign');
            $table->unsignedBigInteger('tyre_size_id')->index('tyres_tyre_size_id_foreign');
            $table->unsignedBigInteger('current_vehicle_id')->nullable()->index('tyres_current_vehicle_id_foreign');
            $table->unsignedBigInteger('current_position_id')->nullable()->index('tyres_current_position_id_foreign');
            $table->unsignedBigInteger('work_location_id')->index('tyres_work_location_id_foreign');
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
        Schema::dropIfExists('tyres');
    }
}
