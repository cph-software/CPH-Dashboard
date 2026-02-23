<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * curb_weight       = Berat kosong kendaraan (kg)
     * payload_capacity  = Kapasitas muat maks (ton) - dihitung otomatis atau diisi manual
     * vehicle_brand     = Merk kendaraan (Volvo, Hino, dll)
     */
    public function up(): void
    {
        Schema::table('master_import_kendaraan', function (Blueprint $table) {
            $table->string('vehicle_brand')->nullable()->after('jenis_kendaraan');
            $table->unsignedInteger('curb_weight')->nullable()->after('vehicle_brand')->comment('Berat kosong (kg)');
            $table->decimal('payload_capacity', 8, 2)->nullable()->after('curb_weight')->comment('Kapasitas muat maks (ton)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_import_kendaraan', function (Blueprint $table) {
            $table->dropColumn(['vehicle_brand', 'curb_weight', 'payload_capacity']);
        });
    }
};
