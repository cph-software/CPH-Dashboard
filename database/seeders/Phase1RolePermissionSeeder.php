<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Aplikasi;
use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Phase1RolePermissionSeeder extends Seeder
{
    /**
     * Phase 1: Setup Roles, Placeholder Menus, Permissions, dan Dummy Users.
     *
     * Jalankan: php artisan db:seed --class=Phase1RolePermissionSeeder
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('=== Phase 1: Role & Permission Setup ===');

        // ──────────────────────────────────────────────────
        // 1. GET TYRE APP (Master Data Tyre / Tyre Performance)
        // ──────────────────────────────────────────────────
        $app = Aplikasi::where('name', 'Master Data Tyre')->first();
        if (!$app) {
            $app = Aplikasi::where('name', 'Tyre Performance')->first();
        }
        if (!$app) {
            $app = Aplikasi::updateOrCreate(
                ['name' => 'Master Data Tyre'],
                ['name' => 'Master Data Tyre']
            );
        }
        $this->command->info("App: {$app->name} (ID: {$app->id})");

        // ──────────────────────────────────────────────────
        // 2. CREATE 3 NEW ROLES
        // ──────────────────────────────────────────────────
        $manajerial = Role::updateOrCreate(
            ['name' => 'Manajerial'],
            ['status' => 'active']
        );
        $supervisor = Role::updateOrCreate(
            ['name' => 'Supervisor'],
            ['status' => 'active']
        );
        $admin = Role::updateOrCreate(
            ['name' => 'Admin Tyre'],
            ['status' => 'active']
        );

        $this->command->info("Roles created: Manajerial (ID: {$manajerial->id}), Supervisor (ID: {$supervisor->id}), Admin Tyre (ID: {$admin->id})");

        // ──────────────────────────────────────────────────
        // 3. CREATE PLACEHOLDER MENUS FOR NEW MODULES
        // ──────────────────────────────────────────────────

        // ── A. BA Management (Berita Acara) ──
        $baGroup = Menu::updateOrCreate(
            ['name' => 'BA Management', 'aplikasi_id' => $app->id, 'parent_id' => null],
            ['url' => '#', 'icon' => 'ri-file-list-3-line', 'order_no' => 5]
        );
        $baMenus = [
            ['name' => 'Berita Acara', 'url' => 'berita-acara', 'icon' => 'ri-file-text-line', 'order_no' => 1],
            ['name' => 'BA Reports', 'url' => 'berita-acara/reports', 'icon' => 'ri-bar-chart-2-line', 'order_no' => 2],
        ];
        foreach ($baMenus as $m) {
            Menu::updateOrCreate(
                ['name' => $m['name'], 'aplikasi_id' => $app->id, 'parent_id' => $baGroup->id],
                ['url' => $m['url'], 'icon' => $m['icon'], 'order_no' => $m['order_no']]
            );
        }

        // ── B. Invoicing ──
        $invGroup = Menu::updateOrCreate(
            ['name' => 'Invoicing', 'aplikasi_id' => $app->id, 'parent_id' => null],
            ['url' => '#', 'icon' => 'ri-money-dollar-circle-line', 'order_no' => 6]
        );
        $invMenus = [
            ['name' => 'Invoice List', 'url' => 'invoices', 'icon' => 'ri-bill-line', 'order_no' => 1],
            ['name' => 'Overdue Tracking', 'url' => 'invoices/overdue', 'icon' => 'ri-alarm-warning-line', 'order_no' => 2],
            ['name' => 'AR Aging', 'url' => 'invoices/ar-aging', 'icon' => 'ri-funds-line', 'order_no' => 3],
        ];
        foreach ($invMenus as $m) {
            Menu::updateOrCreate(
                ['name' => $m['name'], 'aplikasi_id' => $app->id, 'parent_id' => $invGroup->id],
                ['url' => $m['url'], 'icon' => $m['icon'], 'order_no' => $m['order_no']]
            );
        }

        // ── C. Lead Time ──
        $ltGroup = Menu::updateOrCreate(
            ['name' => 'Lead Time', 'aplikasi_id' => $app->id, 'parent_id' => null],
            ['url' => '#', 'icon' => 'ri-timer-line', 'order_no' => 7]
        );
        $ltMenus = [
            ['name' => 'Lead Time Monitor', 'url' => 'lead-time', 'icon' => 'ri-time-line', 'order_no' => 1],
            ['name' => 'Lead Time Reports', 'url' => 'lead-time/reports', 'icon' => 'ri-line-chart-line', 'order_no' => 2],
        ];
        foreach ($ltMenus as $m) {
            Menu::updateOrCreate(
                ['name' => $m['name'], 'aplikasi_id' => $app->id, 'parent_id' => $ltGroup->id],
                ['url' => $m['url'], 'icon' => $m['icon'], 'order_no' => $m['order_no']]
            );
        }

        // ── D. Activity Logs ──
        $logGroup = Menu::updateOrCreate(
            ['name' => 'Activity Logs', 'aplikasi_id' => $app->id, 'parent_id' => null],
            ['url' => '#', 'icon' => 'ri-history-line', 'order_no' => 8]
        );
        $logMenus = [
            ['name' => 'Import/Export Log', 'url' => 'activity-logs/import-export', 'icon' => 'ri-upload-cloud-2-line', 'order_no' => 1],
            ['name' => 'Edit History', 'url' => 'activity-logs/edit-history', 'icon' => 'ri-edit-line', 'order_no' => 2],
            ['name' => 'All Activity', 'url' => 'activity-logs', 'icon' => 'ri-list-check-2', 'order_no' => 3],
        ];
        foreach ($logMenus as $m) {
            Menu::updateOrCreate(
                ['name' => $m['name'], 'aplikasi_id' => $app->id, 'parent_id' => $logGroup->id],
                ['url' => $m['url'], 'icon' => $m['icon'], 'order_no' => $m['order_no']]
            );
        }

        $this->command->info("Placeholder menus created: BA Management, Invoicing, Lead Time, Activity Logs");

        // ──────────────────────────────────────────────────
        // 4. ASSIGN PERMISSIONS PER ROLE
        // ──────────────────────────────────────────────────

        // Ambil semua menu milik Tyre Performance
        $allTyreMenus = Menu::where('aplikasi_id', $app->id)->get();

        // -- Permission Matrix --
        // Manajerial: SEMUA menu, tapi VIEW ONLY
        $this->assignPermissions($manajerial, $allTyreMenus, ['view']);

        // Supervisor: SEMUA menu, bisa edit + export/import
        $this->assignPermissions($supervisor, $allTyreMenus, ['view', 'create', 'update', 'delete', 'export', 'import']);

        // Admin Tyre: Menu tertentu saja, bisa input + import request
        $adminMenuNames = [
            'Dashboard',
            'Pemasangan (Install)', 'Pelepasan (Remove)', 'Movement History',
            'Master Tyre', 'Vehicle Master',
            'Berita Acara',
            'Invoice List',
            'Lead Time Monitor',
        ];
        $adminMenus = $allTyreMenus->filter(function ($menu) use ($adminMenuNames) {
            return in_array($menu->name, $adminMenuNames);
        });
        // Admin juga butuh parent group menus agar sidebar muncul
        $adminParentIds = $adminMenus->pluck('parent_id')->unique()->filter();
        $adminParentMenus = $allTyreMenus->whereIn('id', $adminParentIds);
        $adminAllMenus = $adminMenus->merge($adminParentMenus)->unique('id');

        $this->assignPermissions($admin, $adminAllMenus, ['view', 'create', 'import']);

        $this->command->info("Permissions assigned per role");

        // ──────────────────────────────────────────────────
        // 5. LINK ROLES TO TYRE PERFORMANCE APP
        // ──────────────────────────────────────────────────
        $manajerial->aplikasi()->syncWithoutDetaching([$app->id]);
        $supervisor->aplikasi()->syncWithoutDetaching([$app->id]);
        $admin->aplikasi()->syncWithoutDetaching([$app->id]);

        // Also ensure Super Admin has access to new menus
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->aplikasi()->syncWithoutDetaching([$app->id]);
            $this->assignPermissions($superAdmin, $allTyreMenus, ['view', 'create', 'update', 'delete', 'export', 'import']);
            $this->command->info("Super Admin updated with new menu access");
        }

        // ──────────────────────────────────────────────────
        // 6. CREATE 5 DUMMY USERS
        // ──────────────────────────────────────────────────
        $dummyUsers = [
            [
                'name' => 'Demo Manajerial',
                'master_karyawan_id' => 'DEMO-MGR',
                'role_id' => $manajerial->id,
                'password' => Hash::make('demo123'),
            ],
            [
                'name' => 'Demo Supervisor 1',
                'master_karyawan_id' => 'DEMO-SPV1',
                'role_id' => $supervisor->id,
                'password' => Hash::make('demo123'),
            ],
            [
                'name' => 'Demo Supervisor 2',
                'master_karyawan_id' => 'DEMO-SPV2',
                'role_id' => $supervisor->id,
                'password' => Hash::make('demo123'),
            ],
            [
                'name' => 'Demo Admin 1',
                'master_karyawan_id' => 'DEMO-ADM1',
                'role_id' => $admin->id,
                'password' => Hash::make('demo123'),
            ],
            [
                'name' => 'Demo Admin 2',
                'master_karyawan_id' => 'DEMO-ADM2',
                'role_id' => $admin->id,
                'password' => Hash::make('demo123'),
            ],
        ];

        foreach ($dummyUsers as $userData) {
            $user = User::updateOrCreate(
                ['master_karyawan_id' => $userData['master_karyawan_id']],
                [
                    'name' => $userData['name'],
                    'role_id' => $userData['role_id'],
                    'password' => $userData['password'],
                    'foto' => '',
                ]
            );
            $this->command->info("  ✓ Created: {$userData['name']} (Login: {$userData['master_karyawan_id']} / demo123)");
        }

        // ──────────────────────────────────────────────────
        // 7. SUMMARY
        // ──────────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════╗');
        $this->command->info('║          PHASE 1 SETUP COMPLETE!                ║');
        $this->command->info('╠══════════════════════════════════════════════════╣');
        $this->command->info('║  DUMMY ACCOUNTS:                                ║');
        $this->command->info('║  ┌────────────┬──────────────┬─────────┐        ║');
        $this->command->info('║  │ Employee ID│ Role         │ Password│        ║');
        $this->command->info('║  ├────────────┼──────────────┼─────────┤        ║');
        $this->command->info('║  │ DEMO-MGR   │ Manajerial   │ demo123 │        ║');
        $this->command->info('║  │ DEMO-SPV1  │ Supervisor   │ demo123 │        ║');
        $this->command->info('║  │ DEMO-SPV2  │ Supervisor   │ demo123 │        ║');
        $this->command->info('║  │ DEMO-ADM1  │ Admin Tyre   │ demo123 │        ║');
        $this->command->info('║  │ DEMO-ADM2  │ Admin Tyre   │ demo123 │        ║');
        $this->command->info('║  └────────────┴──────────────┴─────────┘        ║');
        $this->command->info('║                                                  ║');
        $this->command->info('║  Login: /login → Tipe CPH → Employee ID + Pass  ║');
        $this->command->info('╚══════════════════════════════════════════════════╝');
    }

    /**
     * Assign permissions untuk sebuah role ke sekumpulan menus
     *
     * @param Role $role
     * @param \Illuminate\Support\Collection $menus
     * @param array $permissions  e.g. ['view'], ['view', 'create', 'update', 'delete']
     */
    private function assignPermissions(Role $role, $menus, array $permissions)
    {
        $pivotData = [];
        $permissionsJson = json_encode($permissions);

        foreach ($menus as $menu) {
            $pivotData[$menu->id] = ['permissions' => $permissionsJson];
        }

        $role->menus()->syncWithoutDetaching($pivotData);
    }
}
