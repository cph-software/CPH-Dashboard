<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tyre_id');
            $table->unsignedBigInteger('vehicle_id')->nullable(); // Target or Source vehicle
            $table->unsignedBigInteger('position_id')->nullable(); // tyre_position_details id
            $table->enum('movement_type', ['Installation', 'Removal', 'Rotation', 'Inspection']);
            $table->date('movement_date');
            $table->decimal('odometer_reading', 15, 2)->nullable();
            $table->decimal('hour_meter_reading', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('tyre_id')->references('id')->on('tyres')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('master_import_kendaraan')->onDelete('set null');
            $table->foreign('position_id')->references('id')->on('tyre_position_details')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tyre_movements');
    }
}
