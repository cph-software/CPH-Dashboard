<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get parent ID for Tyre Operations
        $parentId = DB::table('menu')->where('name', 'Tyre Operations')->where('aplikasi_id', 20)->value('id');

        if ($parentId) {
            // Insert Rotasi (Rotate) menu
            $menuId = DB::table('menu')->insertGetId([
                'aplikasi_id' => 20,
                'parent_id' => $parentId,
                'name' => 'Rotasi (Rotate)',
                'url' => 'rotasi',
                'icon' => 'ri-arrow-left-right-line',
                'order_no' => 25, // After Removal (order handled by sequence usually, but let's be explicit if needed)
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Give permission to Super Admin (Role ID 1)
            DB::table('role_menu')->insert([
                'role_id' => 1,
                'menu_id' => $menuId,
                'permissions' => '["view", "create", "update", "delete"]',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $id = DB::table('menu')->where('name', 'Rotasi (Rotate)')->where('aplikasi_id', 20)->value('id');
        if ($id) {
            DB::table('role_menu')->where('menu_id', $id)->delete();
            DB::table('menu')->where('id', $id)->delete();
        }
    }
};
