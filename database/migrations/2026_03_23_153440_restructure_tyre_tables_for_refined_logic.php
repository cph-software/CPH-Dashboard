<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RestructureTyreTablesForRefinedLogic extends Migration
{
    public function up()
    {
        // 1. Update Master Tyres
        Schema::table('tyres', function (Blueprint $table) {
            // New static specifications
            if (!Schema::hasColumn('tyres', 'ply_rating')) {
                $table->string('ply_rating')->nullable()->after('tyre_pattern_id');
            }
            if (!Schema::hasColumn('tyres', 'original_tread_depth')) {
                $table->decimal('original_tread_depth', 8, 2)->nullable()->after('ply_rating');
            }
            
            // Context Tracking
            if (!Schema::hasColumn('tyres', 'is_in_warehouse')) {
                $table->boolean('is_in_warehouse')->default(true)->after('original_tread_depth');
            }
            if (!Schema::hasColumn('tyres', 'segment_name')) {
                $table->string('segment_name')->nullable()->after('is_in_warehouse');
            }

            // Clean up calculated/operational fields from master record
        }); // Close previous Schema::table block

        // Safely attempt to drop foreign keys in separate blocks so failures don't crash the entire migration
        if (Schema::hasColumn('tyres', 'tyre_segment_id')) {
            try {
                Schema::table('tyres', function (Blueprint $table) {
                    $table->dropForeign('tyres_tyre_segment_id_foreign');
                });
            } catch (\Exception $e) {}
        }
        if (Schema::hasColumn('tyres', 'work_location_id')) {
            try {
                Schema::table('tyres', function (Blueprint $table) {
                    $table->dropForeign('tyres_work_location_id_foreign');
                });
            } catch (\Exception $e) {}
        }

        // Re-open Schema::table for remaining column drops
        Schema::table('tyres', function (Blueprint $table) {

            $columnsToDrop = [];
            foreach (['total_lifetime_km', 'total_lifetime_hm', 'current_km', 'current_hm', 'tyre_segment_id', 'work_location_id'] as $col) {
                if (Schema::hasColumn('tyres', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // 2. Update Examinations
        Schema::table('tyre_examinations', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_examinations', 'exam_type')) {
                $table->enum('exam_type', ['Sales', 'Customer'])->default('Customer')->after('status');
            }
            if (!Schema::hasColumn('tyre_examinations', 'approval_status')) {
                $table->enum('approval_status', ['Pending', 'Approved', 'Rejected'])->default('Approved')->after('exam_type');
            }
            if (!Schema::hasColumn('tyre_examinations', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            }
        });

        // 3. Position Configurations (Add Head Unit / Trailer and Trail / Non-Trail Context)
        Schema::table('tyre_position_configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_position_configurations', 'is_trail')) {
                $table->boolean('is_trail')->default(false)->after('config_type');
            }
        });
    }

    public function down()
    {
        Schema::table('tyres', function (Blueprint $table) {
            $table->dropColumn(['ply_rating', 'original_tread_depth', 'is_in_warehouse', 'segment_name']);
        });

        Schema::table('tyre_examinations', function (Blueprint $table) {
            $table->dropColumn(['exam_type', 'approval_status', 'approved_by']);
        });

        Schema::table('tyre_position_configurations', function (Blueprint $table) {
            $table->dropColumn('is_trail');
        });
    }
}
