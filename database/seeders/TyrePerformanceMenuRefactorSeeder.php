<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Aplikasi;
use App\Models\Menu;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class TyrePerformanceMenuRefactorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Dapatkan Aplikasi "Tyre Performance" (ID 20)
        // Kita asumsikan ID 20 karena sebelumnya di hardcode, tapi best practice pakai query
        $app = Aplikasi::where('id', 20)->first();
        if (!$app) {
            $app = Aplikasi::updateOrCreate(
                ['name' => 'Tyre Performance'],
                ['name' => 'Tyre Performance']
            );
        }

        // 2. Dapatkan Role Super Admin
        $superAdmin = Role::where('name', 'Super Admin')->first();

        // 3. Hapus Mapping Menu Lama untuk App ini (agar bersih, lalu create ulang)
        $oldMenuIds = Menu::where('aplikasi_id', $app->id)->pluck('id');

        // Detach dari role_menu pivot
        DB::table('role_menu')->whereIn('menu_id', $oldMenuIds)->delete();

        // Hapus menu fisik
        Menu::where('aplikasi_id', $app->id)->delete();

        // 4. Struktur Menu Baru

        // A. Dashboard (Top Level)
        $dashboard = Menu::create([
            'aplikasi_id' => $app->id,
            'name' => 'Dashboard',
            'url' => 'dashboard',
            'icon' => 'ri-dashboard-3-line',
            'order_no' => 1,
            'parent_id' => null
        ]);

        // B. Tyre Operations (Group)
        $opsGroup = Menu::create([
            'aplikasi_id' => $app->id,
            'name' => 'Tyre Operations',
            'url' => '#', // Group Parent
            'icon' => 'ri-tools-line',
            'order_no' => 2,
            'parent_id' => null
        ]);

        // Sub-menus Ops
        Menu::create([
            'aplikasi_id' => $app->id,
            'parent_id' => $opsGroup->id,
            'name' => 'Pemasangan (Install)',
            'url' => 'pemasangan',
            'icon' => 'ri-add-circle-line', // Icon lebih spesifik
            'order_no' => 1
        ]);
        Menu::create([
            'aplikasi_id' => $app->id,
            'parent_id' => $opsGroup->id,
            'name' => 'Pelepasan (Remove)',
            'url' => 'pelepasan',
            'icon' => 'ri-indeterminate-circle-line',
            'order_no' => 2
        ]);
        Menu::create([
            'aplikasi_id' => $app->id,
            'parent_id' => $opsGroup->id,
            'name' => 'Rotasi (Rotate)',
            'url' => 'rotasi',
            'icon' => 'ri-refresh-line',
            'order_no' => 3
        ]);
        Menu::create([
            'aplikasi_id' => $app->id,
            'parent_id' => $opsGroup->id,
            'name' => 'Movement History',
            'url' => 'movement',
            'icon' => 'ri-history-line',
            'order_no' => 4
        ]);

        // C. Assets Management (Group)
        $assetGroup = Menu::create([
            'aplikasi_id' => $app->id,
            'name' => 'Assets Management',
            'url' => '#',
            'icon' => 'ri-database-2-line',
            'order_no' => 3,
            'parent_id' => null
        ]);

        // Sub-menus Assets
        Menu::create([
            'aplikasi_id' => $app->id,
            'parent_id' => $assetGroup->id,
            'name' => 'Master Tyre',
            'url' => 'master_tyre',
            'icon' => 'ri-disc-line',
            'order_no' => 1
        ]);
        Menu::create([
            'aplikasi_id' => $app->id,
            'parent_id' => $assetGroup->id,
            'name' => 'Vehicle Master',
            'url' => 'master_kendaraan',
            'icon' => 'ri-truck-line',
            'order_no' => 2
        ]);

        // D. System Config (Group)
        $configGroup = Menu::create([
            'aplikasi_id' => $app->id,
            'name' => 'System Config',
            'url' => '#',
            'icon' => 'ri-settings-3-line',
            'order_no' => 4,
            'parent_id' => null
        ]);

        // Sub-menus Config
        $configs = [
            ['name' => 'Companies', 'url' => 'master_company', 'icon' => 'ri-building-4-line'],
            ['name' => 'Brands', 'url' => 'master_brand', 'icon' => 'ri-bookmark-3-line'],
            ['name' => 'Sizes', 'url' => 'master_size', 'icon' => 'ri-ruler-2-line'],
            ['name' => 'Patterns', 'url' => 'master_pattern', 'icon' => 'ri-layout-masonry-line'],
            ['name' => 'Failure Codes', 'url' => 'master_failure_code', 'icon' => 'ri-error-warning-line'],
            ['name' => 'Locations', 'url' => 'master_location', 'icon' => 'ri-map-pin-line'],
            ['name' => 'Segments', 'url' => 'master_segment', 'icon' => 'ri-route-line'],
            ['name' => 'Position Layouts', 'url' => 'master_position', 'icon' => 'ri-layout-grid-line'],
        ];

        foreach ($configs as $idx => $conf) {
            Menu::create([
                'aplikasi_id' => $app->id,
                'parent_id' => $configGroup->id,
                'name' => $conf['name'],
                'url' => $conf['url'],
                'icon' => $conf['icon'],
                'order_no' => $idx + 1
            ]);
        }

        // 5. Re-assign permissions ke Super Admin
        if ($superAdmin) {
            $newMenuIds = Menu::where('aplikasi_id', $app->id)->pluck('id');
            // Attach menus baru
            $superAdmin->menus()->syncWithoutDetaching($newMenuIds);
        }

        $this->command->info('Tyre Performance Menus Reorganized Successfully!');
    }
}
