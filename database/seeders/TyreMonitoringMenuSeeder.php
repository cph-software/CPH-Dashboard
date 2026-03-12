<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Role;
use App\Models\RoleMenu;

class TyreMonitoringMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Get Parent Menu (Tyre Operations)
        $parentMenu = Menu::where('name', 'Tyre Operations')->first();
        
        if (!$parentMenu) {
            $this->command->error('Parent menu "Tyre Operations" not found. Please make sure the base menus are seeded first.');
            return;
        }

        // 2. Create/Update the Monitoring Menu
        $monitoringMenu = Menu::updateOrCreate(
            ['url' => 'monitoring'],
            [
                'aplikasi_id' => $parentMenu->aplikasi_id,
                'parent_id'   => $parentMenu->id,
                'name'        => 'Tyre Monitoring',
                'icon'        => 'ri-line-chart-line',
                'order_no'    => 30, // Positioned after existing operations
            ]
        );

        $this->command->info('Menu "Tyre Monitoring" has been created/updated.');

        // 3. Assign to Super Admin (Role ID 1) by default
        $superAdmin = Role::find(1);
        if ($superAdmin) {
            RoleMenu::updateOrCreate(
                [
                    'role_id' => $superAdmin->id,
                    'menu_id' => $monitoringMenu->id
                ],
                [
                    'permissions' => ['view', 'create', 'update', 'delete', 'export']
                ]
            );
            $this->command->info('Permission assigned to Super Admin.');
        }

        $this->command->warn('Note: You still need to manually assign this menu to other roles via the User Management UI.');
    }
}
