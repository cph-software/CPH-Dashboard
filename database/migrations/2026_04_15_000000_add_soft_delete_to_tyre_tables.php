<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tabel Ban (tyres)
        Schema::table('tyres', function (Blueprint $table) {
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
            $table->timestamp('permanent_deleted_at')->nullable()->after('deleted_by');
        });

        // Tabel Kendaraan (master_import_kendaraan)
        Schema::table('master_import_kendaraan', function (Blueprint $table) {
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
            $table->timestamp('permanent_deleted_at')->nullable()->after('deleted_by');
        });
    }

    public function down()
    {
        Schema::table('tyres', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['deleted_by', 'permanent_deleted_at']);
        });

        Schema::table('master_import_kendaraan', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['deleted_by', 'permanent_deleted_at']);
        });
    }
};
