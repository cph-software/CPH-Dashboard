<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTyreTablesForOperationsRevision extends Migration
{
    public function up()
    {
        // 1. Tyre Company
        Schema::table('tyre_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_companies', 'total_tyre_capacity')) {
                $table->integer('total_tyre_capacity')->default(0)->after('description');
            }
        });

        // 2. Tyres
        Schema::table('tyres', function (Blueprint $table) {
            if (!Schema::hasColumn('tyres', 'custom_serial_number')) {
                $table->string('custom_serial_number')->nullable()->unique()->after('serial_number');
            }
            if (!Schema::hasColumn('tyres', 'current_km')) {
                $table->decimal('current_km', 15, 2)->default(0)->after('total_lifetime_km');
            }
            if (!Schema::hasColumn('tyres', 'current_hm')) {
                $table->decimal('current_hm', 15, 2)->default(0)->after('total_lifetime_hm');
            }
        });

        // 3. Tyre Position Configurations
        Schema::table('tyre_position_configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_position_configurations', 'config_type')) {
                $table->enum('config_type', ['Rigid', 'Head Unit', 'Trailer'])->default('Rigid')->after('layout_template');
            }
        });

        // 4. Monitoring Check
        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_monitoring_check', 'tread_photo_path')) {
                $table->string('tread_photo_path')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('tyre_monitoring_check', 'approval_status')) {
                $table->enum('approval_status', ['Pending', 'Approved', 'Rejected'])->default('Pending')->after('tread_photo_path');
            }
            if (!Schema::hasColumn('tyre_monitoring_check', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('tyre_monitoring_check', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('tyre_monitoring_check', 'is_sales_input')) {
                $table->boolean('is_sales_input')->default(false)->after('rejection_reason');
            }
        });

        // 5. Monitoring Installation
        Schema::table('tyre_monitoring_installation', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_monitoring_installation', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('notes');
            }
        });

        // 6. Monitoring Removal
        Schema::table('tyre_monitoring_removal', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_monitoring_removal', 'rtd_1')) {
                $table->decimal('rtd_1', 8, 2)->nullable()->after('notes');
                $table->decimal('rtd_2', 8, 2)->nullable()->after('rtd_1');
                $table->decimal('rtd_3', 8, 2)->nullable()->after('rtd_2');
                $table->decimal('rtd_4', 8, 2)->nullable()->after('rtd_3');
            }
            if (!Schema::hasColumn('tyre_monitoring_removal', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('rtd_4');
            }
        });
    }

    public function down()
    {
        Schema::table('tyre_companies', function (Blueprint $table) {
            $table->dropColumn('total_tyre_capacity');
        });

        Schema::table('tyres', function (Blueprint $table) {
            $table->dropColumn(['custom_serial_number', 'current_km', 'current_hm']);
        });

        Schema::table('tyre_position_configurations', function (Blueprint $table) {
            $table->dropColumn('config_type');
        });

        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            $table->dropColumn(['tread_photo_path', 'approval_status', 'approved_by', 'rejection_reason', 'is_sales_input']);
        });

        Schema::table('tyre_monitoring_installation', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });

        Schema::table('tyre_monitoring_removal', function (Blueprint $table) {
            $table->dropColumn(['rtd_1', 'rtd_2', 'rtd_3', 'rtd_4', 'photo_path']);
        });
    }
}
