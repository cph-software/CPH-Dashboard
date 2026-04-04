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
        $this->command->info('=== Fixing Missing Role Permissions Completely ===');

        $adminTyre = Role::where('name', 'Admin Tyre')->first();
        $supervisor = Role::where('name', 'Supervisor')->first();
        $manajerial = Role::where('name', 'Manajerial')->first();

        // Target Menus to Ensure Operational Roles Have Access To
        $menuNames = [
            'Dashboard',
            'Tyre Operations', // Parent Menu (CRITICAL for sidebar rendering)
            'Pemasangan (Install)',
            'Pelepasan (Remove)',
            'Rotasi (Rotate)',
            'Tyre Monitoring',
            'Monitoring',
            'Movement History',
            'Master Data', // Parent Menu
            'Brands',
            'Sizes',
            'Patterns',
            'Failure Codes',
            'Locations',
            'Segments',
            'Axle Layouts',
            'Position Layouts',
            'Vehicle Master',
            'Master Tyre',
            'System Config', // Parent Menu
            'System Settings', // Alternative Parent Menu
            'Companies',
            'Import Approval'
        ];

        // Temukan semua menu yang namanya ada di daftar di atas
        $menus = Menu::whereIn('name', $menuNames)->get();

        foreach ($menus as $menu) {
            $basePerms = ['view'];
            
            // Berikan hak penuh untuk Import Approval bagi Supervisor
            if ($menu->name === 'Import Approval' || str_contains($menu->name, 'Tyre Monitoring')) {
                $supervisorPerms = ['view', 'create', 'update', 'delete', 'export', 'import'];
            } else {
                $supervisorPerms = ['view'];
            }

            // Sync untuk Manajerial (View Only rata-rata + Update di import)
            if ($manajerial) {
                $manajerialPerms = ['view'];
                if ($menu->name === 'Import Approval') {
                    $manajerialPerms[] = 'update'; // Bisa approve
                }
                $manajerial->menus()->syncWithoutDetaching([
                    $menu->id => ['permissions' => json_encode($manajerialPerms)]
                ]);
            }

            // Sync untuk Supervisor
            if ($supervisor) {
                $supervisor->menus()->syncWithoutDetaching([
                    $menu->id => ['permissions' => json_encode($supervisorPerms)]
                ]);
            }

            // Sync untuk Admin Tyre (Operasional Penuh)
            if ($adminTyre && in_array($menu->name, ['Tyre Operations', 'Pemasangan (Install)', 'Pelepasan (Remove)', 'Rotasi (Rotate)', 'Tyre Monitoring', 'Movement History', 'Dashboard'])) {
                $adminTyre->menus()->syncWithoutDetaching([
                    $menu->id => ['permissions' => json_encode(['view', 'create', 'update', 'delete', 'export', 'import'])]
                ]);
            }
        }

        $this->command->info('✅ ALL Operational and Parent Menus linked to Manager, Supervisor, and Admin Tyre!');
    }
}
