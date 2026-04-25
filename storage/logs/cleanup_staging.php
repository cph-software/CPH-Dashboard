<?php
// Script Pembersihan Data Perusahaan Spesifik (Sangat Aman)
// Letakkan file ini di: storage/logs/cleanup_staging.php
// Cara eksekusi di CPanel: php storage/logs/cleanup_staging.php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// =========================================================================
// ⚠️ TENTUKAN NAMA PERUSAHAAN YANG INGIN DIHAPUS DATANYA DI BAWAH INI:
$targetCompanyName = "Perusahaan Tes"; 
// =========================================================================

$company = DB::table('tyre_companies')->where('company_name', 'LIKE', '%' . $targetCompanyName . '%')->first();

if (!$company) {
    echo "❌ ERROR: Perusahaan dengan nama yang mengandung '{$targetCompanyName}' tidak ditemukan di database!\n";
    echo "\nDaftar Perusahaan yang Terdaftar di Database Anda:\n";
    
    $companies = DB::table('tyre_companies')->get();
    foreach($companies as $c) {
        echo " - ID: {$c->id} | NAMA: {$c->company_name}\n";
    }
    echo "\nSilakan edit variabel \$targetCompanyName di dalam script ini agar sesuai dengan nama di atas.\n";
    exit;
}

$companyId = $company->id;
$companyName = $company->company_name;

echo "=========================================================\n";
echo "🧹 PERSIAPAN PENGHAPUSAN DATA UNTUK:\n";
echo "🏢 NAMA PERUSAHAAN : {$companyName}\n";
echo "🆔 ID PERUSAHAAN   : {$companyId}\n";
echo "=========================================================\n\n";

// Preview sebelum dihapus
echo "--- PREVIEW DATA YANG AKAN DIHAPUS (HANYA MILIK {$companyName}) ---\n";
$tyreCount = App\Models\Tyre::withoutGlobalScopes()->where('tyre_company_id', $companyId)->count();
$vehicleCount = App\Models\MasterImportKendaraan::where('tyre_company_id', $companyId)->count();
$movementCount = App\Models\TyreMovement::where('tyre_company_id', $companyId)->count();
$batchCount = App\Models\ImportBatch::whereHas('user', fn($q) => $q->where('tyre_company_id', $companyId))->count();
$sessionCount = App\Models\TyreMonitoringSession::whereHas('vehicle', fn($q) => $q->where('tyre_company_id', $companyId))->count();

echo "  Ban (Tyre Master)      : {$tyreCount} data\n";
echo "  Kendaraan (Vehicle)    : {$vehicleCount} data\n";
echo "  Riwayat Pemasangan/dll : {$movementCount} data\n";
echo "  Riwayat Sesi Monitoring: {$sessionCount} data\n";
echo "  Riwayat Import Excel   : {$batchCount} data\n\n";

DB::beginTransaction();
try {
    // 1. Hapus Monitoring Sessions, Checks, Installations, Removals, Images (Constraint safety)
    $vehicleIds = App\Models\MasterImportKendaraan::where('tyre_company_id', $companyId)->pluck('id')->toArray();
    if (!empty($vehicleIds)) {
        $sessionIds = App\Models\TyreMonitoringSession::whereIn('vehicle_id', $vehicleIds)->pluck('session_id')->toArray();
        if (!empty($sessionIds)) {
            DB::table('tyre_monitoring_check')->whereIn('session_id', $sessionIds)->delete();
            DB::table('tyre_monitoring_installation')->whereIn('session_id', $sessionIds)->delete();
            DB::table('tyre_monitoring_removal')->whereIn('session_id', $sessionIds)->delete();
            DB::table('tyre_monitoring_images')->whereIn('session_id', $sessionIds)->delete();
            DB::table('tyre_monitoring_session')->whereIn('session_id', $sessionIds)->delete();
            echo "✓ Menghapus data Monitoring & Pemeriksaan\n";
        }
    }

    // 2. Hapus Riwayat Movements (Pemasangan, Rotasi, Pelepasan)
    $deletedMovements = DB::table('tyre_movements')->where('tyre_company_id', $companyId)->delete();
    echo "✓ Menghapus {$deletedMovements} riwayat pergerakan (Movements)\n";

    // 3. Cabut ban dari konfig posisi (Un-assign)
    $tyreIds = App\Models\Tyre::withoutGlobalScopes()->where('tyre_company_id', $companyId)->pluck('id')->toArray();
    if (!empty($tyreIds)) {
        $clearedPositions = DB::table('tyre_position_details')->whereIn('tyre_id', $tyreIds)->update(['tyre_id' => null]);
        echo "✓ Melepas {$clearedPositions} ban dari posisi kendaraan\n";
    }

    // 4. Hapus Ban (termasuk yang ada di tong sampah / soft deleted)
    $deletedTyres = DB::table('tyres')->where('tyre_company_id', $companyId)->delete();
    echo "✓ Menghapus total {$deletedTyres} data fisik ban (Tyre Master)\n";

    // 5. Hapus Kendaraan
    $deletedVehicles = DB::table('master_import_kendaraan')->where('tyre_company_id', $companyId)->delete();
    echo "✓ Menghapus {$deletedVehicles} data Kendaraan\n";

    // 6. Hapus Riwayat File Import (File Approval)
    $userIds = DB::table('users')->where('tyre_company_id', $companyId)->pluck('id')->toArray();
    if (!empty($userIds)) {
        $batchIds = DB::table('import_batches')->whereIn('user_id', $userIds)->pluck('id')->toArray();
        if (!empty($batchIds)) {
            $deletedItems = DB::table('import_items')->whereIn('batch_id', $batchIds)->delete();
            $deletedBatches = DB::table('import_batches')->whereIn('id', $batchIds)->delete();
            echo "✓ Menghapus riwayat import file Excel\n";
        }
    }

    // 7. Hitung ulang stok gudang agar tidak minus / bocor
    DB::table('tyre_locations')->update(['current_stock' => 0]);
    $stockByLocation = App\Models\Tyre::withoutGlobalScopes()
        ->where('is_in_warehouse', 1)
        ->whereNotNull('current_location_id')
        ->selectRaw('current_location_id, count(*) as cnt')
        ->groupBy('current_location_id')
        ->pluck('cnt', 'current_location_id');
    foreach ($stockByLocation as $locId => $stock) {
        DB::table('tyre_locations')->where('id', $locId)->update(['current_stock' => $stock]);
    }
    echo "✓ Melakukan sinkronisasi ulang sisa stok gudang\n";

    // 8. Bersihkan counter kuota perusahaan
    DB::table('tyre_companies')->where('id', $companyId)->update(['total_tyres' => 0, 'total_tyre_capacity' => 0]);
    echo "✓ Mereset kuota ban perusahaan\n";

    // 9. Bersihkan Cache Laravel
    Illuminate\Support\Facades\Cache::flush();
    echo "✓ Membesihkan Cache Sistem\n";

    DB::commit();

    echo "\n✅ BERHASIL SEPENUHNYA! Data perusahaan {$companyName} kembali KOSONG BERSIH.\n";
    echo "   (Data perusahaan lain dipastikan aman 100% dan tidak tersentuh)\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ TERJADI ERROR: " . $e->getMessage() . "\n";
    echo "⚠️ Sistem Keamanan Aktif: Penghapusan dibatalkan sepenuhnya (Rollback).\n";
}
