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
        // Insert into menu table for Aplikasi ID 20 (Tyre Performance)
        $menuId = DB::table('menu')->insertGetId([
            'aplikasi_id' => 20,
            'name' => 'Master Instansi',
            'url' => 'master_company',
            'icon' => 'ri-community-line',
            'order_no' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Link to Role ID 1 (Super Admin typically)
        DB::table('role_menu')->insert([
            'role_id' => 1,
            'menu_id' => $menuId,
            'permissions' => '["view", "create", "update", "delete"]',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $id = DB::table('menu')->where('url', 'master_company')->value('id');
        if ($id) {
            DB::table('role_menu')->where('menu_id', $id)->delete();
            DB::table('menu')->where('id', $id)->delete();
        }
    }
};
