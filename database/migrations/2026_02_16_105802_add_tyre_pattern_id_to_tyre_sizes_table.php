<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTyrePatternIdToTyreSizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_sizes', function (Blueprint $table) {
            $table->unsignedBigInteger('tyre_pattern_id')->nullable()->after('tyre_brand_id');
            $table->foreign('tyre_pattern_id')->references('id')->on('tyre_patterns')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyre_sizes', function (Blueprint $table) {
            $table->dropForeign(['tyre_pattern_id']);
            $table->dropColumn('tyre_pattern_id');
        });
    }
}
