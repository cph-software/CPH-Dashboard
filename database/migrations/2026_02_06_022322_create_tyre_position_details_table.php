<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyrePositionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_position_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('configuration_id');
            $table->string('position_code');
            $table->string('position_name');
            $table->enum('axle_type', ['Front', 'Rear', 'Spare', 'Trailer'])->default('Rear');
            $table->integer('axle_number')->default(1);
            $table->enum('side', ['Left', 'Right', 'Center', 'None'])->default('Left');
            $table->boolean('is_spare')->default(false);
            $table->integer('display_order')->default(0);
            $table->decimal('x_coordinate')->nullable();
            $table->decimal('y_coordinate')->nullable();
            $table->timestamps();

            $table->unique(['configuration_id', 'position_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tyre_position_details');
    }
}
