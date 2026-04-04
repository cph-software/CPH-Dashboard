<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Aplikasi;
use App\Models\Menu;
use App\Models\Role;

class TyreExaminationMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $app = Aplikasi::where('name', 'Master Data Tyre')->first();
        if (!$app) {
            $app = Aplikasi::where('name', 'Tyre Performance')->first();
        }
        if (!$app)
            return;

        $superAdmin = Role::where('name', 'Super Admin')->first();

        // Menu Utama: Examination / Pengecekan
        $examMenu = Menu::updateOrCreate(
            ['name' => 'Examination', 'aplikasi_id' => $app->id],
            [
                'url' => 'examination',
                'icon' => 'ri-search-eye-line',
                'order_no' => 3 // After Dashboard (1) and Movement (2)
            ]
        );

        // Update Master Data order_no to 4
        Menu::where('name', 'Master Data')->where('aplikasi_id', $app->id)->update(['order_no' => 4]);

        // Assign to Super Admin
        if ($superAdmin) {
            $superAdmin->menus()->syncWithoutDetaching([$examMenu->id]);
        }
    }
}
