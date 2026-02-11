<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateTyreCurrentPositionIdForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyres', function (Blueprint $table) {
            // Check if foreign key exists before dropping
            $constraints = DB::select("SELECT CONSTRAINT_NAME 
                                     FROM information_schema.KEY_COLUMN_USAGE 
                                     WHERE TABLE_NAME = 'tyres' 
                                     AND CONSTRAINT_NAME = 'tyres_current_position_id_foreign'
                                     AND TABLE_SCHEMA = DATABASE()");
            
            if (count($constraints) > 0) {
                $table->dropForeign('tyres_current_position_id_foreign');
            }
            
            // Add the new foreign key pointing to 'tyre_position_details'
            $table->foreign('current_position_id')
                  ->references('id')
                  ->on('tyre_position_details')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyres', function (Blueprint $table) {
            $table->dropForeign(['current_position_id']);
        });
    }
}
