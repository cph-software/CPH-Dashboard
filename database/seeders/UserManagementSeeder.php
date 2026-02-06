<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Aplikasi
        $app = \App\Models\Aplikasi::updateOrCreate(
            ['name' => 'CPH Dashboard'],
            ['name' => 'CPH Dashboard']
        );

        // 2. Roles
        $superAdmin = \App\Models\Role::updateOrCreate(
            ['name' => 'Super Admin'],
            ['status' => 'active']
        );

        // 3. Menus
        // User Management (Parent)
        $userMgmt = \App\Models\Menu::updateOrCreate(
            ['name' => 'User Management', 'aplikasi_id' => $app->id],
            ['url' => '#', 'icon' => 'ri-user-settings-line', 'order_no' => 1]
        );

        // Sub Menus for User Management
        \App\Models\Menu::updateOrCreate(
            ['name' => 'Roles', 'parent_id' => $userMgmt->id, 'aplikasi_id' => $app->id],
            ['url' => 'roles', 'icon' => 'ri-shield-user-line', 'order_no' => 1]
        );

        \App\Models\Menu::updateOrCreate(
            ['name' => 'Menus', 'parent_id' => $userMgmt->id, 'aplikasi_id' => $app->id],
            ['url' => 'menus', 'icon' => 'ri-menu-search-line', 'order_no' => 2]
        );

        \App\Models\Menu::updateOrCreate(
            ['name' => 'Users', 'parent_id' => $userMgmt->id, 'aplikasi_id' => $app->id],
            ['url' => 'users', 'icon' => 'ri-user-follow-line', 'order_no' => 3]
        );

        // 4. Assign Menus to Super Admin
        $allMenus = \App\Models\Menu::all();
        foreach ($allMenus as $m) {
            $superAdmin->menus()->syncWithoutDetaching([$m->id => ['permissions' => json_encode(['view', 'create', 'update', 'delete'])]]);
        }

        // 5. Create Super Admin User
        \App\Models\User::updateOrCreate(
            ['id' => 1],
            [
                'role_id' => $superAdmin->id,
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'foto' => ''
            ]
        );
    }
}
