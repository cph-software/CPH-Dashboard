<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GlobalExportImportPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Ambil semua data role_menu
        $roleMenus = DB::table('role_menu')->get();

        foreach ($roleMenus as $rm) {
            $perms = json_decode($rm->permissions, true) ?: [];

            // Tambahkan export dan import jika belum ada
            if (!in_array('export', $perms)) {
                $perms[] = 'export';
            }
            if (!in_array('import', $perms)) {
                $perms[] = 'import';
            }

            DB::table('role_menu')
                ->where('role_id', $rm->role_id)
                ->where('menu_id', $rm->menu_id)
                ->update(['permissions' => json_encode($perms)]);
        }

        $this->command->info('Global Export/Import permissions assigned to all users.');
    }
}
