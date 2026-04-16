<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fix unique constraints to be compatible with SoftDeletes.
 * 
 * Must drop foreign keys first, then recreate them after changing the index.
 */
return new class extends Migration
{
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // ============ TYRES TABLE ============
        Schema::table('tyres', function (Blueprint $table) {
            $table->dropUnique('tyres_serial_number_unique');
            $table->dropUnique('tyres_custom_serial_number_unique');

            $table->unique(['serial_number', 'deleted_at'], 'tyres_serial_number_soft_unique');
            $table->unique(['custom_serial_number', 'deleted_at'], 'tyres_custom_serial_number_soft_unique');
        });

        // ============ KENDARAAN TABLE ============
        $this->dropIndexSafe('master_import_kendaraan', 'master_import_kendaraan_kode_kendaraan_unique');

        Schema::table('master_import_kendaraan', function (Blueprint $table) {
            $table->unique(['kode_kendaraan', 'deleted_at'], 'kendaraan_kode_soft_unique');
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::table('tyres', function (Blueprint $table) {
            $table->dropUnique('tyres_serial_number_soft_unique');
            $table->dropUnique('tyres_custom_serial_number_soft_unique');
            $table->unique('serial_number');
            $table->unique('custom_serial_number');
        });

        $this->dropIndexSafe('master_import_kendaraan', 'kendaraan_kode_soft_unique');

        Schema::table('master_import_kendaraan', function (Blueprint $table) {
            $table->unique('kode_kendaraan');
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function dropIndexSafe(string $table, string $indexName): void
    {
        $indexes = DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$indexName]);
        if (count($indexes) > 0) {
            Schema::table($table, fn(Blueprint $t) => $t->dropUnique($indexName));
        }
    }
};
