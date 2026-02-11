<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBoltQuantityAndLocationIdToTyreMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_movements', function (Blueprint $table) {
            $table->integer('new_bolts_quantity')->default(0)->after('new_bolts_used');
            $table->unsignedBigInteger('work_location_id')->nullable()->after('operational_segment_id');
            
            $table->foreign('work_location_id')->references('id')->on('tyre_locations')->onDelete('set null');
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
            $table->dropForeign(['work_location_id']);
            $table->dropColumn(['new_bolts_quantity', 'work_location_id']);
        });
    }
}
