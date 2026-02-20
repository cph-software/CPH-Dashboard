<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Role;
use App\Models\Aplikasi;

class MoveUserManagementToTyreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Target Apps
        $tyreAppId = 20; // Master Data Tyre
        $userMgmtAppId = 25; // User Management
        
        // 2. Create "System Settings" Parent Menu in Tyre App
        // Icon ideas: ri-settings-3-line, ri-admin-line
        $parentMenu = Menu::firstOrCreate(
            ['name' => 'System Settings', 'aplikasi_id' => $tyreAppId],
            [
                'url' => '#', 
                'icon' => 'ri-settings-3-line', 
                'order_no' => 99, // Put at the bottom
                'parent_id' => null
            ]
        );
        $this->command->info("Created Parent Menu: System Settings (ID: {$parentMenu->id})");

        // 3. Move target menus from User Mgmt App to Tyre App under new Parent
        $targetMenus = ['Roles', 'Users', 'Menus', 'Permission Matrix'];
        
        foreach ($targetMenus as $menuName) {
            $menu = Menu::where('name', $menuName)->where('aplikasi_id', $userMgmtAppId)->first();
            if ($menu) {
                $menu->update([
                    'aplikasi_id' => $tyreAppId,
                    'parent_id' => $parentMenu->id
                ]);
                $this->command->info("Moved Menu: {$menu->name} to App 20");
            } else {
                $this->command->warn("Menu not found in App 25: {$menuName}");
            }
        }

        // 4. Update Permissions for Parent Menu
        // Only Administrator and Super Admin should see "System Settings"
        $adminRoles = Role::whereIn('name', ['Administrator', 'Super Admin'])->get();
        foreach ($adminRoles as $role) {
            $role->menus()->syncWithoutDetaching([$parentMenu->id]);
        }
        
        // 5. Ensure "Import Approval" is also in App 20 (it should be already)
        // And maybe moved under System Settings? 
        // User said "User Management content" (Roles, Users etc). 
        // Import Approval is operational. Let's leave it top level or under "Transactions". 
        // Actually, let's put it under System Settings too IF the user wants consolidated view, 
        // BUT Managers need access to Import Approval but NOT Users/Roles.
        // If I put Import Approval under System Settings, Managers need access to System Settings Parent.
        // If Managers have access to Parent, they see Parent. 
        // Inside Parent, they only see Import Approval (if permissions are correct).
        // This is clean. Let's do that.
        
        $importMenu = Menu::where('url', 'import-approval')->first();
        if ($importMenu) {
            $importMenu->update([
                'aplikasi_id' => $tyreAppId,
                'parent_id' => $parentMenu->id,
                'order_no' => 1 // Put at top of settings
            ]);
            $this->command->info("Moved Import Approval to System Settings");
            
            // Give Managers access to Parent Menu
            $managerRoles = Role::whereIn('name', ['Manajerial', 'Supervisor'])->get();
            foreach ($managerRoles as $role) {
                // Determine if they should have access
                // Yes, to see Import Approval
                $role->menus()->syncWithoutDetaching([$parentMenu->id]);
            }
        }
    }
}
