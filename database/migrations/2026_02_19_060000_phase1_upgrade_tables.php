<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Phase1UpgradeTables extends Migration
{
    /**
     * Run the migrations.
     * 
     * PENTING: Semua kolom baru NULLABLE agar backward-compatible
     * dengan project CPH yang pakai database yang sama.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tambah kolom 'name' ke users table
        //    Untuk dummy users yang tidak punya link ke karyawan
        if (!Schema::hasColumn('users', 'name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('name')->nullable()->after('id');
            });
        }

        // 2. Upgrade activity_logs dengan kolom detail
        //    Kolom 'activity' tetap ada (backward compat)
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('activity_logs', 'action_type')) {
                    $table->string('action_type', 50)->nullable()->after('activity')
                        ->comment('login, create, update, delete, import, export, view');
                }
                if (!Schema::hasColumn('activity_logs', 'module')) {
                    $table->string('module', 100)->nullable()->after('action_type')
                        ->comment('BA, Invoice, Tyre, User, etc.');
                }
                if (!Schema::hasColumn('activity_logs', 'data_before')) {
                    $table->text('data_before')->nullable()->after('module')
                        ->comment('JSON snapshot sebelum perubahan');
                }
                if (!Schema::hasColumn('activity_logs', 'data_after')) {
                    $table->text('data_after')->nullable()->after('data_before')
                        ->comment('JSON snapshot setelah perubahan');
                }
                if (!Schema::hasColumn('activity_logs', 'ip_address')) {
                    $table->string('ip_address', 45)->nullable()->after('data_after');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('users', 'name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }

        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $columns = ['action_type', 'module', 'data_before', 'data_after', 'ip_address'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('activity_logs', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
