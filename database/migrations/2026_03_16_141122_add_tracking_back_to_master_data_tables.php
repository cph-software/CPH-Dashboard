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
        $tables = [
            'tyre_brands',
            'tyre_patterns',
            'tyre_sizes',
            'tyre_locations',
            'tyre_segments',
            'tyre_position_configurations',
            'tyre_position_details',
            'tyre_positions'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'created_by')) {
                        $table->unsignedBigInteger('created_by')->nullable();
                    }
                    if (!Schema::hasColumn($tableName, 'updated_by')) {
                        $table->unsignedBigInteger('updated_by')->nullable();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'tyre_brands',
            'tyre_patterns',
            'tyre_sizes',
            'tyre_locations',
            'tyre_segments',
            'tyre_position_configurations',
            'tyre_position_details',
            'tyre_positions'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'created_by')) {
                        $table->dropColumn(['created_by']);
                    }
                    if (Schema::hasColumn($tableName, 'updated_by')) {
                        $table->dropColumn(['updated_by']);
                    }
                });
            }
        }
    }
};
