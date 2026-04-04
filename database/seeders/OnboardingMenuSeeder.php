<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OnboardingMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $app = \App\Models\Aplikasi::where('name', 'Tyre Performance')->first();
        $parent = \App\Models\Menu::where('name', 'System Config')->first();

        $menu = \App\Models\Menu::create([
            'aplikasi_id' => $app ? $app->id : 1,
            'parent_id' => $parent ? $parent->id : null,
            'name' => 'Onboarding Manager',
            'url' => 'onboarding-projects',
            'icon' => 'ri-rocket-line',
            'order_no' => 10
        ]);

        // Grant access with JSON permissions in the pivot table
        $permissions = ['view', 'create', 'update', 'delete', 'export', 'import', 'approve'];
        
        \App\Models\RoleMenu::create([
            'role_id' => 1,
            'menu_id' => $menu->id,
            'permissions' => json_encode($permissions)
        ]);
    }
}
