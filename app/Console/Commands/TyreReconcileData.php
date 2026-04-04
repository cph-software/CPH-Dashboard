<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tyre;
use App\Models\TyreLocation;
use Illuminate\Support\Facades\DB;

class TyreReconcileData extends Command
{
    protected $signature = 'tyre:reconcile {--fix : Otomatis perbaiki data yang tidak konsisten}';
    protected $description = 'Deteksi dan perbaiki data ban yang tidak sinkron (status vs posisi kendaraan)';

    public function handle()
    {
        $this->info('=== CPH Tyre Data Reconciliation ===');
        $this->newLine();

        $autoFix = $this->option('fix');
        $issues = 0;
        $fixed = 0;

        // =============================================
        // 1. Ban status "Installed" tapi tidak punya kendaraan
        // =============================================
        $this->info('[CHECK 1] Ban berstatus "Installed" tanpa kendaraan...');
        $orphanInstalled = Tyre::where('status', 'Installed')
            ->whereNull('current_vehicle_id')
            ->get();

        if ($orphanInstalled->count() > 0) {
            $issues += $orphanInstalled->count();
            foreach ($orphanInstalled as $tyre) {
                $this->warn("  ⚠ SN: {$tyre->serial_number} — Status: Installed, tapi vehicle_id = NULL");

                if ($autoFix) {
                    $tyre->update([
                        'status' => 'Repaired',
                        'is_in_warehouse' => true,
                        'current_position_id' => null,
                    ]);
                    $fixed++;
                    $this->line("    ✅ Diperbaiki → Status: Repaired, Kembali ke gudang");
                }
            }
        } else {
            $this->line('  ✅ Tidak ada masalah.');
        }

        // =============================================
        // 2. Ban punya kendaraan tapi status bukan "Installed"
        // =============================================
        $this->newLine();
        $this->info('[CHECK 2] Ban punya kendaraan tapi status bukan "Installed"...');
        $wrongStatus = Tyre::whereNotNull('current_vehicle_id')
            ->where('status', '!=', 'Installed')
            ->get();

        if ($wrongStatus->count() > 0) {
            $issues += $wrongStatus->count();
            foreach ($wrongStatus as $tyre) {
                $this->warn("  ⚠ SN: {$tyre->serial_number} — Status: {$tyre->status}, tapi vehicle_id = {$tyre->current_vehicle_id}");

                if ($autoFix) {
                    $tyre->update(['status' => 'Installed', 'is_in_warehouse' => false]);
                    $fixed++;
                    $this->line("    ✅ Diperbaiki → Status: Installed");
                }
            }
        } else {
            $this->line('  ✅ Tidak ada masalah.');
        }

        // =============================================
        // 3. Ban di gudang (is_in_warehouse = true) tapi masih punya kendaraan
        // =============================================
        $this->newLine();
        $this->info('[CHECK 3] Ban di gudang tapi masih terikat kendaraan...');
        $warehouseConflict = Tyre::where('is_in_warehouse', true)
            ->whereNotNull('current_vehicle_id')
            ->get();

        if ($warehouseConflict->count() > 0) {
            $issues += $warehouseConflict->count();
            foreach ($warehouseConflict as $tyre) {
                $this->warn("  ⚠ SN: {$tyre->serial_number} — Di gudang, tapi vehicle_id = {$tyre->current_vehicle_id}");

                if ($autoFix) {
                    $tyre->update([
                        'current_vehicle_id' => null,
                        'current_position_id' => null,
                    ]);
                    $fixed++;
                    $this->line("    ✅ Diperbaiki → Kendaraan & posisi dikosongkan");
                }
            }
        } else {
            $this->line('  ✅ Tidak ada masalah.');
        }

        // =============================================
        // 4. Sinkronisasi stok gudang
        // =============================================
        $this->newLine();
        $this->info('[CHECK 4] Sinkronisasi stok gudang...');
        $locations = TyreLocation::all();
        $stockMismatch = 0;

        foreach ($locations as $location) {
            $realCount = Tyre::where('current_location_id', $location->id)
                ->where('is_in_warehouse', true)
                ->count();

            if ($location->current_stock != $realCount) {
                $stockMismatch++;
                $issues++;
                $this->warn("  ⚠ {$location->location_name}: Tercatat {$location->current_stock}, seharusnya {$realCount}");

                if ($autoFix) {
                    $location->update(['current_stock' => $realCount]);
                    $fixed++;
                    $this->line("    ✅ Diperbaiki → current_stock = {$realCount}");
                }
            }
        }

        if ($stockMismatch === 0) {
            $this->line('  ✅ Semua stok gudang sinkron.');
        }

        // =============================================
        // SUMMARY
        // =============================================
        $this->newLine();
        $this->info('=== RINGKASAN ===');
        $this->line("Total masalah ditemukan: {$issues}");

        if ($autoFix) {
            $this->line("Total masalah diperbaiki: {$fixed}");
        } else {
            if ($issues > 0) {
                $this->warn("Jalankan dengan opsi --fix untuk memperbaiki otomatis:");
                $this->line("  php artisan tyre:reconcile --fix");
            }
        }

        return 0;
    }
}
