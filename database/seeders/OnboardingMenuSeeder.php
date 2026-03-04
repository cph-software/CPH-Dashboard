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
        $menu = \App\Models\Menu::create([
            'aplikasi_id' => 20,
            'parent_id' => 141, // System Settings
            'name' => 'Onboarding Manager',
            'url' => 'onboarding-projects',
            'icon' => 'ri-rocket-line',
            'order_no' => 1
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
