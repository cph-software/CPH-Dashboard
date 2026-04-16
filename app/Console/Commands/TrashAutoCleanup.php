<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tyre;
use App\Models\MasterImportKendaraan;
use Carbon\Carbon;

class TrashAutoCleanup extends Command
{
    protected $signature = 'trash:cleanup';
    protected $description = 'Auto-cleanup: Tier 1 → Tier 2 (3 hari), Tier 2 → Hard Delete (3 hari)';

    public function handle()
    {
        $this->info('🗑️  Starting Trash Auto-Cleanup...');

        $models = [
            'Tyres' => Tyre::class,
            'Kendaraan' => MasterImportKendaraan::class,
        ];

        $tier1Count = 0;
        $tier2Count = 0;

        foreach ($models as $label => $modelClass) {
            // ============================================
            // TIER 1 → TIER 2: Data soft-deleted > 3 hari
            // ============================================
            $tier1Items = $modelClass::onlyTrashed()
                ->whereNull('permanent_deleted_at')
                ->where('deleted_at', '<', Carbon::now()->subDays(3))
                ->get();

            foreach ($tier1Items as $item) {
                $item->update(['permanent_deleted_at' => now()]);
                $tier1Count++;
            }

            if ($tier1Items->count() > 0) {
                $this->line("  [$label] {$tier1Items->count()} item dipindahkan ke Tier 2");
            }

            // ============================================
            // TIER 2 → HARD DELETE: Data permanent_deleted > 3 hari
            // ============================================
            $tier2Items = $modelClass::onlyTrashed()
                ->whereNotNull('permanent_deleted_at')
                ->where('permanent_deleted_at', '<', Carbon::now()->subDays(3))
                ->get();

            foreach ($tier2Items as $item) {
                $itemName = $item->serial_number ?? $item->kode_kendaraan ?? 'Unknown';

                setLogActivity(null, "[Auto-Cleanup] Hard delete: $itemName ($label)", [
                    'action_type' => 'auto_purge',
                    'module' => 'Backup & Restore',
                    'data_before' => $item->toArray()
                ]);

                $item->forceDelete();
                $tier2Count++;
            }

            if ($tier2Items->count() > 0) {
                $this->line("  [$label] {$tier2Items->count()} item di-hard-delete (purged)");
            }
        }

        $this->info("✅ Cleanup selesai! Tier1→Tier2: $tier1Count | HardDeleted: $tier2Count");

        return Command::SUCCESS;
    }
}
