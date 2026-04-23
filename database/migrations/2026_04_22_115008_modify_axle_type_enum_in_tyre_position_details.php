<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyAxleTypeEnumInTyrePositionDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE tyre_position_details MODIFY COLUMN axle_type ENUM('Front', 'Middle', 'Rear', 'Spare', 'Trailer') DEFAULT 'Rear'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverting this might cause data truncation if there are Middle axles.
        // It's safer not to strictly revert the ENUM to remove 'Middle'.
    }
}
