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
            'tyre_failure_codes',
            'tyre_position_configurations',
            'tyre_position_details',
            'tyre_positions',
            'tyre_monitoring_vehicle',
            'tyre_monitoring_session',
            'tyre_monitoring_check',
            'tyre_monitoring_installation',
            'tyre_monitoring_removal',
            'tyre_monitoring_images',
            'tyre_examination_details'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // Multi-tenancy
                    if (!Schema::hasColumn($tableName, 'tyre_company_id')) {
                        $table->unsignedBigInteger('tyre_company_id')->nullable()->after(Schema::hasColumn($tableName, 'id') ? 'id' : (Schema::hasColumn($tableName, 'session_id') ? 'session_id' : (Schema::hasColumn($tableName, 'vehicle_id') ? 'vehicle_id' : (Schema::hasColumn($tableName, 'image_id') ? 'image_id' : 'created_at'))));
                        $table->foreign('tyre_company_id')->references('id')->on('tyre_companies')->onDelete('cascade');
                    }

                    // Tracking
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
            'tyre_failure_codes',
            'tyre_position_configurations',
            'tyre_position_details',
            'tyre_positions',
            'tyre_monitoring_vehicle',
            'tyre_monitoring_session',
            'tyre_monitoring_check',
            'tyre_monitoring_installation',
            'tyre_monitoring_removal',
            'tyre_monitoring_images',
            'tyre_examination_details'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'tyre_company_id')) {
                        $table->dropForeign(['tyre_company_id']);
                        $table->dropColumn(['tyre_company_id', 'created_by', 'updated_by']);
                    }
                });
            }
        }
    }
};
