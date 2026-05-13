<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tyre;
use App\Models\TyreLocation;
use App\Models\TyrePositionDetail;
use App\Models\MasterImportKendaraan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TyreSyncData extends Command
{
    protected $signature = 'tyre:sync-data 
                            {--stock : Reconcile location stock counts}
                            {--positions : Reconcile position ↔ tyre links}
                            {--all : Run all reconciliation}
                            {--fix : Actually fix the data (without this flag, only report)}';

    protected $description = 'Reconcile and fix Tyre data inconsistencies (stock counts, position links)';

    public function handle()
    {
        $runAll = $this->option('all');
        $dryRun = !$this->option('fix');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE — No changes will be made. Use --fix to apply fixes.');
        } else {
            $this->warn('🔧 FIX MODE — Changes will be applied to database.');
        }

        $this->line('');

        if ($runAll || $this->option('stock')) {
            $this->reconcileStock($dryRun);
        }

        if ($runAll || $this->option('positions')) {
            $this->reconcilePositions($dryRun);
        }

        // Always clear cache after reconciliation
        if (!$dryRun) {
            Cache::flush();
            $this->info('🗑️  Cache cleared.');
        }

        $this->line('');
        $this->info('✅ Reconciliation complete.');
    }

    /**
     * [FIX-SYNC-2] Reconcile tyre_locations.current_stock with actual tyre count
     */
    private function reconcileStock(bool $dryRun)
    {
        $this->info('📦 === STOCK RECONCILIATION ===');
        
        $locations = TyreLocation::withoutGlobalScopes()->get();
        $fixed = 0;

        foreach ($locations as $loc) {
            // Count actual tyres at this location (in warehouse only)
            $actualCount = Tyre::withoutGlobalScopes()
                ->where('current_location_id', $loc->id)
                ->where('is_in_warehouse', true)
                ->count();

            $recorded = $loc->current_stock ?? 0;

            if ($actualCount !== $recorded) {
                $this->warn("  ⚠ Location [{$loc->id}] {$loc->location_name}: recorded={$recorded}, actual={$actualCount}");
                
                if (!$dryRun) {
                    $loc->update(['current_stock' => $actualCount]);
                    $this->info("    → Fixed to {$actualCount}");
                }
                $fixed++;
            }
        }

        if ($fixed === 0) {
            $this->info('  ✅ All location stocks are correct.');
        } else {
            $this->warn("  Found {$fixed} mismatched locations" . ($dryRun ? ' (dry run, not fixed)' : ' (fixed)'));
        }
        $this->line('');
    }

    /**
     * [FIX-SYNC-5] Reconcile tyre_position_details.tyre_id ↔ tyres.current_position_id
     */
    private function reconcilePositions(bool $dryRun)
    {
        $this->info('🔗 === POSITION RECONCILIATION ===');
        $issues = 0;

        // Case 1: Position says it has a tyre, but that tyre doesn't point back
        $positionsWithTyre = TyrePositionDetail::whereNotNull('tyre_id')->get();

        foreach ($positionsWithTyre as $pos) {
            $tyre = Tyre::withoutGlobalScopes()->find($pos->tyre_id);

            if (!$tyre) {
                $this->warn("  ⚠ Position [{$pos->id}] {$pos->position_code}: references tyre_id={$pos->tyre_id} but tyre NOT FOUND");
                if (!$dryRun) {
                    $pos->update(['tyre_id' => null]);
                    $this->info("    → Cleared position tyre_id");
                }
                $issues++;
                continue;
            }

            if ($tyre->current_position_id !== $pos->id) {
                $this->warn("  ⚠ Position [{$pos->id}] {$pos->position_code}: has tyre_id={$tyre->id} (SN: {$tyre->serial_number}), but tyre points to position_id={$tyre->current_position_id}");
                if (!$dryRun) {
                    // Trust the tyre record — if tyre says it's elsewhere, clear position
                    if ($tyre->status !== 'Installed' || $tyre->current_position_id !== $pos->id) {
                        $pos->update(['tyre_id' => null]);
                        $this->info("    → Cleared position (tyre is not installed here)");
                    }
                }
                $issues++;
            }
        }

        // Case 2: Tyre says it's installed at a position, but position doesn't point back
        $installedTyres = Tyre::withoutGlobalScopes()
            ->where('status', 'Installed')
            ->whereNotNull('current_position_id')
            ->get();

        foreach ($installedTyres as $tyre) {
            $pos = TyrePositionDetail::find($tyre->current_position_id);

            if (!$pos) {
                $this->warn("  ⚠ Tyre [{$tyre->id}] SN:{$tyre->serial_number}: points to position_id={$tyre->current_position_id} but position NOT FOUND");
                if (!$dryRun) {
                    $tyre->update([
                        'current_position_id' => null,
                        'current_vehicle_id' => null,
                        'status' => 'Repaired',
                        'is_in_warehouse' => true,
                    ]);
                    $this->info("    → Reset tyre to warehouse (Repaired)");
                }
                $issues++;
                continue;
            }

            if ($pos->tyre_id !== $tyre->id) {
                $this->warn("  ⚠ Tyre [{$tyre->id}] SN:{$tyre->serial_number}: installed at position {$pos->position_code}, but position has tyre_id={$pos->tyre_id}");
                if (!$dryRun) {
                    // If position is empty, fill it. If another tyre, conflict.
                    if (empty($pos->tyre_id)) {
                        $pos->update(['tyre_id' => $tyre->id]);
                        $this->info("    → Linked position to tyre");
                    } else {
                        $this->error("    ✖ CONFLICT: Position already has different tyre (id={$pos->tyre_id}). Manual review needed.");
                    }
                }
                $issues++;
            }
        }

        // Case 3: Tyres marked Installed but with no vehicle/position
        $orphanInstalled = Tyre::withoutGlobalScopes()
            ->where('status', 'Installed')
            ->where(function ($q) {
                $q->whereNull('current_vehicle_id')->orWhereNull('current_position_id');
            })
            ->get();

        foreach ($orphanInstalled as $tyre) {
            $this->warn("  ⚠ Tyre [{$tyre->id}] SN:{$tyre->serial_number}: status=Installed but vehicle={$tyre->current_vehicle_id}, position={$tyre->current_position_id}");
            if (!$dryRun) {
                $tyre->update([
                    'status' => 'Repaired',
                    'current_vehicle_id' => null,
                    'current_position_id' => null,
                    'is_in_warehouse' => true,
                ]);
                $this->info("    → Reset to warehouse (Repaired)");
            }
            $issues++;
        }

        if ($issues === 0) {
            $this->info('  ✅ All position links are consistent.');
        } else {
            $this->warn("  Found {$issues} issues" . ($dryRun ? ' (dry run, not fixed)' : ' (fixed)'));
        }
    }
}
