<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Role;

class FixRolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Fixes missing Import Approval and Rotasi menus for specific roles.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('=== Standardizing Role Permissions (Manajerial, SPV, Admin Tyre) ===');

        $adminTyre = Role::where('name', 'Admin Tyre')->orWhere('id', 4)->first();
        $supervisor = Role::where('name', 'Supervisor')->orWhere('id', 3)->first();
        $manajerial = Role::where('name', 'Manajerial')->orWhere('name', 'Manager')->orWhere('id', 2)->first();

        if (!$adminTyre || !$supervisor || !$manajerial) {
            $this->command->error('One or more standard roles not found. Please ensure they exist.');
            return;
        }

        // Definisi Permissions
        $FULL_ACCESS = json_encode(['view', 'create', 'update', 'delete', 'export', 'import']);
        $READ_WRITE = json_encode(['view', 'create', 'update', 'export']);
        $READ_APPROVE = json_encode(['view', 'update', 'export']); // Update acts as Approve
        $READ_ONLY = json_encode(['view', 'export']);
        $VIEW_ONLY = json_encode(['view']);

        // Matriks Konfigurasi Hak Akses (Menu Name => [Role => Permission])
        $roleMatrix = [
            'Dashboard' => [
                'Admin Tyre' => $VIEW_ONLY, 'Supervisor' => $READ_ONLY, 'Manajerial' => $READ_ONLY
            ],
            
            // --- TYRE OPERATIONS ---
            'Tyre Operations' => [ // Parent
                'Admin Tyre' => $VIEW_ONLY, 'Supervisor' => $VIEW_ONLY, 'Manajerial' => $VIEW_ONLY
            ],
            'Pemasangan (Install)' => [
                'Admin Tyre' => $FULL_ACCESS, 'Supervisor' => $READ_ONLY, 'Manajerial' => $READ_ONLY
            ],
            'Pelepasan (Remove)' => [
                'Admin Tyre' => $FULL_ACCESS, 'Supervisor' => $READ_ONLY, 'Manajerial' => $READ_ONLY
            ],
            'Rotasi (Rotate)' => [
                'Admin Tyre' => $FULL_ACCESS, 'Supervisor' => $READ_ONLY, 'Manajerial' => $READ_ONLY
            ],
            'Movement History' => [
                'Admin Tyre' => $FULL_ACCESS, 'Supervisor' => $READ_ONLY, 'Manajerial' => $READ_ONLY
            ],
            
            // --- ASSETS MANAGEMENT ---
            'Assets Management' => [ // Parent
                'Admin Tyre' => $VIEW_ONLY, 'Supervisor' => $VIEW_ONLY, 'Manajerial' => $VIEW_ONLY
            ],
            'Master Tyre' => [
                'Admin Tyre' => $FULL_ACCESS, 'Supervisor' => $READ_ONLY, 'Manajerial' => $READ_ONLY
            ],
            'Vehicle Master' => [
                'Admin Tyre' => $FULL_ACCESS, 'Supervisor' => $READ_ONLY, 'Manajerial' => $READ_ONLY
            ],

            // --- MONITORING & EXAMINATION ---
            'Examination' => [
                'Admin Tyre' => $FULL_ACCESS, 'Supervisor' => $READ_APPROVE, 'Manajerial' => $READ_APPROVE
            ],
            'Tyre Monitoring' => [
                'Admin Tyre' => $FULL_ACCESS, 'Supervisor' => $READ_APPROVE, 'Manajerial' => $READ_ONLY
            ],
            
            // --- SYSTEM CONFIG (MASTER DATA PENDUKUNG) ---
            'System Config' => [ // Parent
                'Admin Tyre' => $VIEW_ONLY, 'Supervisor' => $VIEW_ONLY, 'Manajerial' => $VIEW_ONLY
            ],
            'Brands' => [
                'Admin Tyre' => $READ_ONLY, 'Supervisor' => $FULL_ACCESS, 'Manajerial' => $READ_ONLY
            ],
            'Sizes' => [
                'Admin Tyre' => $READ_ONLY, 'Supervisor' => $FULL_ACCESS, 'Manajerial' => $READ_ONLY
            ],
            'Patterns' => [
                'Admin Tyre' => $READ_ONLY, 'Supervisor' => $FULL_ACCESS, 'Manajerial' => $READ_ONLY
            ],
            'Failure Codes' => [
                'Admin Tyre' => $READ_ONLY, 'Supervisor' => $FULL_ACCESS, 'Manajerial' => $READ_ONLY
            ],
            'Locations' => [
                'Admin Tyre' => $READ_ONLY, 'Supervisor' => $FULL_ACCESS, 'Manajerial' => $READ_ONLY
            ],
            'Segments' => [
                'Admin Tyre' => $READ_ONLY, 'Supervisor' => $FULL_ACCESS, 'Manajerial' => $READ_ONLY
            ],
            'Position Layouts' => [
                'Admin Tyre' => $READ_ONLY, 'Supervisor' => $FULL_ACCESS, 'Manajerial' => $READ_ONLY
            ],
            
            // --- APPROVAL & LOGS ---
            'Import Approval' => [
                // Admin tyre bisa lihat daftar requestnya sendiri, SPV/Manager bisa Approve (Update)
                'Admin Tyre' => json_encode(['view', 'create']), 
                'Supervisor' => $READ_APPROVE, 
                'Manajerial' => $READ_APPROVE
            ],
            'Activity Logs' => [ // Parent
                'Admin Tyre' => null, 'Supervisor' => $VIEW_ONLY, 'Manajerial' => $VIEW_ONLY
            ],
            'All Activity' => [
                'Admin Tyre' => null, 'Supervisor' => $READ_ONLY, 'Manajerial' => $READ_ONLY
            ],
            'Import/Export Log' => [
                'Admin Tyre' => null, 'Supervisor' => $READ_ONLY, 'Manajerial' => $READ_ONLY
            ],
            'Error Notification' => [ // Menu ID 73 - Diperlukan agar icon lonceng tampil di navbar
                'Admin Tyre' => $VIEW_ONLY, 'Supervisor' => $VIEW_ONLY, 'Manajerial' => $VIEW_ONLY
            ],
        ];

        // Pertama, hapus SEMUA permission lama untuk 3 role ini agar bersih (Reset)
        $adminTyre->menus()->detach();
        $supervisor->menus()->detach();
        $manajerial->menus()->detach();

        $menus = Menu::all();

        foreach ($menus as $menu) {
            $menuName = $menu->name;

            // Jika menu ada di matrix, pasang permissionnya
            if (isset($roleMatrix[$menuName])) {
                
                if (isset($roleMatrix[$menuName]['Admin Tyre']) && $roleMatrix[$menuName]['Admin Tyre'] !== null) {
                    $adminTyre->menus()->attach($menu->id, ['permissions' => $roleMatrix[$menuName]['Admin Tyre']]);
                }
                
                if (isset($roleMatrix[$menuName]['Supervisor']) && $roleMatrix[$menuName]['Supervisor'] !== null) {
                    $supervisor->menus()->attach($menu->id, ['permissions' => $roleMatrix[$menuName]['Supervisor']]);
                }
                
                if (isset($roleMatrix[$menuName]['Manajerial']) && $roleMatrix[$menuName]['Manajerial'] !== null) {
                    $manajerial->menus()->attach($menu->id, ['permissions' => $roleMatrix[$menuName]['Manajerial']]);
                }
            }
        }

        $this->command->info('✅ Role Permissions successfully standardized!');
    }
}
