<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrentLocationIdToTyres extends Migration
{
    public function up()
    {
        Schema::table('tyres', function (Blueprint $table) {
            if (!Schema::hasColumn('tyres', 'current_location_id')) {
                $table->unsignedBigInteger('current_location_id')->nullable()->after('is_in_warehouse');
                $table->foreign('current_location_id')->references('id')->on('tyre_locations')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('tyres', function (Blueprint $table) {
            $table->dropForeign(['current_location_id']);
            $table->dropColumn('current_location_id');
        });
    }
}
