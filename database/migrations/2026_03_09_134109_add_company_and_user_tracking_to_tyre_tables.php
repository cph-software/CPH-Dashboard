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
            'tyres',
            'master_import_kendaraan',
            'tyre_movements',
            'tyre_examinations',
            'tyre_locations',
            'tyre_segments'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                // Multi-tenancy
                if (!Schema::hasColumn($table->getTable(), 'tyre_company_id')) {
                    $table->unsignedBigInteger('tyre_company_id')->nullable()->after('id');
                    $table->foreign('tyre_company_id')->references('id')->on('tyre_companies')->onDelete('cascade');
                }

                // Tracking
                if (!Schema::hasColumn($table->getTable(), 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable();
                }
                if (!Schema::hasColumn($table->getTable(), 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable();
                }
            });
        }

        // Add company_id to activity_logs
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('activity_logs', 'tyre_company_id')) {
                    $table->unsignedBigInteger('tyre_company_id')->nullable()->after('user_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'tyres',
            'master_import_kendaraan',
            'tyre_movements',
            'tyre_examinations',
            'tyre_locations',
            'tyre_segments'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['tyre_company_id']);
                $table->dropColumn(['tyre_company_id', 'created_by', 'updated_by']);
            });
        }

        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->dropColumn('tyre_company_id');
            });
        }
    }
};
