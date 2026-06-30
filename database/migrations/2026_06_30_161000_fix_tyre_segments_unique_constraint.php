<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tyre_segments', function (Blueprint $table) {
            // Drop global unique constraint on segment_id
            $table->dropUnique('tyre_segments_segment_id_unique');
            
            // Add composite unique constraint on segment_id and tyre_company_id
            $table->unique(['segment_id', 'tyre_company_id'], 'tyre_segments_segment_company_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tyre_segments', function (Blueprint $table) {
            $table->dropUnique('tyre_segments_segment_company_unique');
            $table->unique('segment_id');
        });
    }
};
