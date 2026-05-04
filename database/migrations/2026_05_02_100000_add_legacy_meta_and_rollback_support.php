<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add legacy_meta JSON column to import_batches
        Schema::table('import_batches', function (Blueprint $table) {
            $table->json('legacy_meta')->nullable()->after('notes');
        });

        // 2. Add import_batch_id to tyres for rollback tracking
        Schema::table('tyres', function (Blueprint $table) {
            $table->unsignedBigInteger('import_batch_id')->nullable()->after('updated_by');
            $table->index('import_batch_id');
        });

        // 3. Add import_batch_id to tyre_movements for rollback tracking
        Schema::table('tyre_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('import_batch_id')->nullable()->after('updated_by');
            $table->index('import_batch_id');
        });

        // 4. Add import_batch_id to master_import_kendaraan for rollback tracking
        Schema::table('master_import_kendaraan', function (Blueprint $table) {
            $table->unsignedBigInteger('import_batch_id')->nullable()->after('updated_at');
            $table->index('import_batch_id');
        });
    }

    public function down(): void
    {
        Schema::table('import_batches', function (Blueprint $table) {
            $table->dropColumn('legacy_meta');
        });

        Schema::table('tyres', function (Blueprint $table) {
            $table->dropIndex(['import_batch_id']);
            $table->dropColumn('import_batch_id');
        });

        Schema::table('tyre_movements', function (Blueprint $table) {
            $table->dropIndex(['import_batch_id']);
            $table->dropColumn('import_batch_id');
        });

        Schema::table('master_import_kendaraan', function (Blueprint $table) {
            $table->dropIndex(['import_batch_id']);
            $table->dropColumn('import_batch_id');
        });
    }
};
