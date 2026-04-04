<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Role;

class FixMenuPlacementSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('=== Fixing Menu Placement & Missing Permissions ===');

        $manajerial = Role::where('name', 'Manajerial')->first();
        $supervisor = Role::where('name', 'Supervisor')->first();
        $adminTyre  = Role::where('name', 'Admin Tyre')->first();

        // ──────────────────────────────────────────
        // BUG #1: Import Approval stuck in App 1 under User Management
        // Fix: Move to App 2 under System Config
        // ──────────────────────────────────────────
        $importApproval = Menu::where('name', 'Import Approval')->first();
        $systemConfig   = Menu::where('name', 'System Config')->where('aplikasi_id', 2)->first();

        if ($importApproval && $systemConfig) {
            $importApproval->update([
                'aplikasi_id' => 2,
                'parent_id'   => $systemConfig->id,
                'order_no'    => 0, // First item in System Config
            ]);
            $this->command->info("✅ Bug #1: Import Approval moved to App 2 → System Config (parent ID: {$systemConfig->id})");
        } else {
            $this->command->warn("⚠️ Bug #1: Import Approval or System Config not found");
        }

        // ──────────────────────────────────────────
        // BUG #2: Companies menu has no roles except Super Admin
        // Fix: Add view for Manajerial & Supervisor
        // ──────────────────────────────────────────
        $companies = Menu::where('name', 'Companies')->first();

        if ($companies) {
            if ($manajerial) {
                $manajerial->menus()->syncWithoutDetaching([
                    $companies->id => ['permissions' => json_encode(['view'])]
                ]);
            }
            if ($supervisor) {
                $supervisor->menus()->syncWithoutDetaching([
                    $companies->id => ['permissions' => json_encode(['view'])]
                ]);
            }
            $this->command->info("✅ Bug #2: Companies menu → view added for Manajerial & Supervisor");
        }

        // ──────────────────────────────────────────
        // BUG #3: Tyre Monitoring only has Super Admin
        // Fix: Add permissions for other roles
        // ──────────────────────────────────────────
        $tyreMonitoring = Menu::where('name', 'Tyre Monitoring')->first();

        if ($tyreMonitoring) {
            if ($manajerial) {
                $manajerial->menus()->syncWithoutDetaching([
                    $tyreMonitoring->id => ['permissions' => json_encode(['view'])]
                ]);
            }
            if ($supervisor) {
                $supervisor->menus()->syncWithoutDetaching([
                    $tyreMonitoring->id => ['permissions' => json_encode(['view', 'create', 'update', 'delete', 'export', 'import'])]
                ]);
            }
            if ($adminTyre) {
                $adminTyre->menus()->syncWithoutDetaching([
                    $tyreMonitoring->id => ['permissions' => json_encode(['view', 'create', 'import'])]
                ]);
            }
            $this->command->info("✅ Bug #3: Tyre Monitoring → permissions added for Manajerial, Supervisor, Admin Tyre");
        }

        $this->command->info('');
        $this->command->info('=== All 3 bugs fixed! ===');
    }
}
