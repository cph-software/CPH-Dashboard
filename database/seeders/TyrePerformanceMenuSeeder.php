<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Aplikasi;
use App\Models\Menu;
use App\Models\Role;

class TyrePerformanceMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Dapatkan atau Buat Aplikasi "Tyre Performance"
        $app = Aplikasi::updateOrCreate(
            ['name' => 'Tyre Performance'],
            ['name' => 'Tyre Performance']
        );

        // 2. Dapatkan Role Super Admin untuk mapping menu
        $superAdmin = Role::where('name', 'Super Admin')->first();

        // 3. Menu: Dashboard (Sudah ada biasanya, tapi kita pastikan)
        Menu::updateOrCreate(
            ['name' => 'Dashboard', 'aplikasi_id' => $app->id],
            ['url' => 'tyre_performance/dashboard', 'icon' => 'ri-dashboard-line', 'order_no' => 1]
        );

        // 4. Menu Utama: Transaksi / Movement
        $movementMenu = Menu::updateOrCreate(
            ['name' => 'Tyre Movement', 'aplikasi_id' => $app->id],
            ['url' => 'tyre_performance/movement', 'icon' => 'ri-repeat-line', 'order_no' => 2]
        );

        // Sub-menu: Pemasangan
        Menu::updateOrCreate(
            ['name' => 'Pemasangan Ban', 'parent_id' => $movementMenu->id, 'aplikasi_id' => $app->id],
            ['url' => 'tyre_performance/pemasangan', 'icon' => 'ri-add-circle-line', 'order_no' => 1]
        );

        // Sub-menu: Pelepasan
        Menu::updateOrCreate(
            ['name' => 'Pelepasan Ban', 'parent_id' => $movementMenu->id, 'aplikasi_id' => $app->id],
            ['url' => 'tyre_performance/pelepasan', 'icon' => 'ri-indeterminate-circle-line', 'order_no' => 2]
        );

        // 5. Menu Utama: Master Data
        $masterMenu = Menu::updateOrCreate(
            ['name' => 'Master Data', 'aplikasi_id' => $app->id],
            ['url' => '#', 'icon' => 'ri-database-2-line', 'order_no' => 3]
        );

        // Sub-menus Master Data
        $masters = [
            ['name' => 'Tyre Master', 'url' => 'tyre_performance/master_tyre', 'icon' => 'ri-poker-cells-line', 'order_no' => 1],
            ['name' => 'Vehicle Master', 'url' => 'tyre_performance/master_kendaraan', 'icon' => 'ri-truck-line', 'order_no' => 2],
            ['name' => 'Brands', 'url' => 'tyre_performance/master_brand', 'icon' => 'ri-copyright-line', 'order_no' => 3],
            ['name' => 'Sizes', 'url' => 'tyre_performance/master_size', 'icon' => 'ri-expand-diagonal-2-line', 'order_no' => 4],
            ['name' => 'Patterns', 'url' => 'tyre_performance/master_pattern', 'icon' => 'ri-layout-top-line', 'order_no' => 5],
            ['name' => 'Failure Codes', 'url' => 'tyre_performance/master_failure_code', 'icon' => 'ri-error-warning-line', 'order_no' => 6],
            ['name' => 'Locations', 'url' => 'tyre_performance/master_location', 'icon' => 'ri-map-pin-line', 'order_no' => 7],
            ['name' => 'Segments', 'url' => 'tyre_performance/master_segment', 'icon' => 'ri-segment-line', 'order_no' => 8],
            ['name' => 'Position Config', 'url' => 'tyre_performance/master_position', 'icon' => 'ri-layout-grid-line', 'order_no' => 9],
        ];

        foreach ($masters as $m) {
            Menu::updateOrCreate(
                ['name' => $m['name'], 'parent_id' => $masterMenu->id, 'aplikasi_id' => $app->id],
                ['url' => $m['url'], 'icon' => $m['icon'], 'order_no' => $m['order_no']]
            );
        }

        // 6. Assign semua menu baru ke Super Admin
        if ($superAdmin) {
            $allTyreMenus = Menu::where('aplikasi_id', $app->id)->pluck('id');
            // Gunakan syncWithoutDetaching agar tidak menghapus menu User Management yang sudah ada
            $superAdmin->menus()->syncWithoutDetaching($allTyreMenus);
        }
    }
}
