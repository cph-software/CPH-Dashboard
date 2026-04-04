<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // 1. Core System & Access
            UserManagementSeeder::class,
            TyrePerformanceMenuRefactorSeeder::class,
            TyreExaminationMenuSeeder::class,
            TyreMonitoringMenuSeeder::class,
            ImportApprovalMenuSeeder::class,
            OnboardingMenuSeeder::class,
            GlobalExportImportPermissionSeeder::class,
            
            // 2. Roles, Permissions, and User Mapping
            Phase1RolePermissionSeeder::class,
            
            // 3. Operational Master Data & Demos
            DemoDataSeeder::class,
            TimUserSeeder::class,
        ]);
    }
}
