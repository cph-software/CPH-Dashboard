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

        $adminTyre = Role::where('name', 'Admin Tyre')->orWhere('id', 4)->first();
        $supervisor = Role::where('name', 'Supervisor')->orWhere('id', 3)->first();
        $manajerial = Role::where('name', 'Manajerial')->orWhere('name', 'Manager')->orWhere('id', 2)->first();

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
            'Assets Management', // Parent Menu for Master Tyre and Vehicle Master
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
            'Import Approval',
            'Examination'
        ];

        $menuUrls = [
            'dashboard', 'tyre-dashboard', 'monitoring', 'pemasangan', 'pelepasan', 
            'rotasi', 'history', 'master_company', 'master_tyre', 'master_kendaraan',
            'import-approval', 'examination'
        ];

        // Temukan semua menu yang namanya ada di daftar di atas ATAU URL-nya cocok
        $menus = Menu::whereIn('name', $menuNames)
                     ->orWhereIn('url', $menuUrls)
                     ->get();

        foreach ($menus as $menu) {
            $basePerms = ['view'];
            
            // Berikan hak penuh untuk Import Approval bagi Supervisor
            if ($menu->name === 'Import Approval' || str_contains($menu->name, 'Tyre Monitoring')) {
                $supervisorPerms = ['view', 'create', 'update', 'delete', 'export', 'import'];
            } else {
                $supervisorPerms = ['view'];
            }

            // Sync untuk Manajerial (View Only rata-rata + Update di import, monitoring, dan examination)
            if ($manajerial) {
                $manajerialPerms = ['view'];
                if (in_array($menu->name, ['Import Approval', 'Tyre Monitoring', 'Examination'])) {
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

        // --- CLEANUP ACCIDENTAL MENUS ---
        // Karena '#' sebelumnya mencakup semua parent, kita hapus parent yang bukan bagian dari modul Tyre
        $unwantedMenus = [
            'User Management',
            'BA Management',
            'Berita Acara',
            'BA Reports',
            'Invoicing',
            'Invoice List',
            'Overdue Tracking',
            'AR Aging',
            'Lead Time',
            'Lead Time Monitor',
            'Lead Time Reports'
        ];
        $menusToHide = Menu::whereIn('name', $unwantedMenus)->get();

        foreach ($menusToHide as $badMenu) {
            if ($manajerial) $manajerial->menus()->detach($badMenu->id);
            if ($supervisor) $supervisor->menus()->detach($badMenu->id);
            // $adminTyre might need them, or might not. Better safe.
        }

        $this->command->info('✅ ALL Operational and Parent Menus linked perfectly, extraneous menus detached!');
    }
}
