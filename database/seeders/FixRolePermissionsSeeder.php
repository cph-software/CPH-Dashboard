<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Role;

class FixRolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Fixes missing Import Approval and Rotasi menus for specific roles.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('=== Fixing Missing Role Permissions ===');

        // 1. Fix Rotasi for Admin Tyre
        $admin = Role::where('name', 'Admin Tyre')->first();
        $rotasi = Menu::where('name', 'Rotasi (Rotate)')->first();

        if ($admin && $rotasi) {
            $permissionsJson = json_encode(['view', 'create', 'import']);
            $admin->menus()->syncWithoutDetaching([
                $rotasi->id => ['permissions' => $permissionsJson]
            ]);
            $this->command->info('✅ Fixed: Rotasi menu added to Admin Tyre.');
        }

        // 2. Fix Import Approval for Supervisor & Manajerial
        $importMenu = Menu::where('name', 'Import Approval')->first();
        $supervisor = Role::where('name', 'Supervisor')->first();
        $manajerial = Role::where('name', 'Manajerial')->first();

        if ($importMenu) {
            // Supervisor gets full approve rights (update)
            if ($supervisor) {
                $supervisor->menus()->syncWithoutDetaching([
                    $importMenu->id => ['permissions' => json_encode(['view', 'create', 'update', 'delete', 'export', 'import'])]
                ]);
                $this->command->info('✅ Fixed: Import Approval (Full Access) added to Supervisor.');
            }

            // Manajerial gets view + update (approve) per DEVELOPMENT_ROADMAP.md:
            // "Level Manajerial/Supervisor memberikan 'Approval' baru data dipindahkan ke Master"
            if ($manajerial) {
                $manajerial->menus()->syncWithoutDetaching([
                    $importMenu->id => ['permissions' => json_encode(['view', 'update'])]
                ]);
                $this->command->info('✅ Fixed: Import Approval (View + Approve) added to Manajerial.');
            }
        }
        
        // 3. Ensure System Settings Parent is visible so they can see Import Approval
        $systemSettings = Menu::where('name', 'System Settings')->first();
        if ($systemSettings && $importMenu) {
             if ($supervisor) {
                 $supervisor->menus()->syncWithoutDetaching([
                    $systemSettings->id => ['permissions' => json_encode(['view'])]
                 ]);
             }
             if ($manajerial) {
                 $manajerial->menus()->syncWithoutDetaching([
                    $systemSettings->id => ['permissions' => json_encode(['view'])]
                 ]);
             }
        }

        // 4. Ensure Dashboard is visible so they don't get 403 denied on /tyre-dashboard
        $dashboardMenu = Menu::where('name', 'Dashboard')->where(function($q) {
             $q->where('url', 'tyre-dashboard')
               ->orWhere('aplikasi_id', 2)
               ->orWhere('aplikasi_id', 3);
        })->first();

        // If not found by precise query, try broader
        if (!$dashboardMenu) {
            $dashboardMenu = Menu::where('name', 'Dashboard')->first();
        }

        if ($dashboardMenu) {
            $adminTyre = Role::where('name', 'Admin Tyre')->first();
            
            if ($supervisor) {
                $supervisor->menus()->syncWithoutDetaching([
                    $dashboardMenu->id => ['permissions' => json_encode(['view'])]
                ]);
            }
            if ($manajerial) {
                $manajerial->menus()->syncWithoutDetaching([
                    $dashboardMenu->id => ['permissions' => json_encode(['view'])]
                ]);
            }
            if ($adminTyre) {
                $adminTyre->menus()->syncWithoutDetaching([
                    $dashboardMenu->id => ['permissions' => json_encode(['view'])]
                ]);
            }
            $this->command->info('✅ Fixed: Dashboard permission added to Supervisor, Manajerial, Admin Tyre.');
        }
    }
}
