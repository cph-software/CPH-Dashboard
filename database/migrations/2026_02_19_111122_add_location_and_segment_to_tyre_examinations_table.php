<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationAndSegmentToTyreExaminationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_examinations', function (Blueprint $table) {
            // Drop the old string location
            $table->dropColumn('location');

            // Add relation to locations and segments
            $table->unsignedBigInteger('location_id')->nullable()->after('examination_date');
            $table->unsignedBigInteger('operational_segment_id')->nullable()->after('location_id');

            $table->foreign('location_id')->references('id')->on('tyre_locations')->onDelete('set null');
            $table->foreign('operational_segment_id')->references('id')->on('tyre_segments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyre_examinations', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropForeign(['operational_segment_id']);
            $table->dropColumn(['location_id', 'operational_segment_id']);
            $table->string('location')->nullable()->after('examination_date');
        });
    }
}
