<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Aplikasi;
use App\Models\Menu;
use App\Models\Role;

class ImportApprovalMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Get Application
        $app = Aplikasi::where('name', 'CPH Dashboard')->first();
        if (!$app) return;

        // 2. Get Parent Menu (User Management)
        $userMgmt = Menu::where('name', 'User Management')->first();
        if (!$userMgmt) return;

        // 3. Create Import Approval Menu
        $menu = Menu::updateOrCreate(
            ['name' => 'Import Approval', 'aplikasi_id' => $app->id],
            [
                'url' => 'import-approval', 
                'icon' => 'ri-checkbox-multiple-line', 
                'order_no' => 5,
                'parent_id' => $userMgmt->id
            ]
        );

        // 4. Assign to Roles
        $roles = Role::whereIn('name', ['Administrator','Super Admin', 'Manajerial', 'Supervisor'])->get();
        foreach ($roles as $role) {
            $role->menus()->syncWithoutDetaching([$menu->id]);
        }

        $this->command->info('Import Approval Menu added and assigned to roles.');
    }
}
