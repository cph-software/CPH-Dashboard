<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTyreIdToTyrePositionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_position_details', function (Blueprint $table) {
            $table->unsignedBigInteger('tyre_id')->nullable()->after('configuration_id');
            $table->foreign('tyre_id')->references('id')->on('tyres')->onDelete('set null');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyre_position_details', function (Blueprint $table) {
            $table->dropForeign(['tyre_id']);
            $table->dropColumn('tyre_id');
        });
    }
}
