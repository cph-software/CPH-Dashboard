<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TimUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Buat Perusahaan PT TIM
        $company = \App\Models\TyreCompany::firstOrCreate(
            ['company_name' => 'PT TUNJUNG INTI MANDIRI'],
            [
                'total_tyres' => 0,
                'total_tyre_capacity' => 1000,
                'status' => 'Active',
                'description' => 'PT TIM (Tunjung Inti Mandiri) Project'
            ]
        );

        $adminRoleId = \App\Models\Role::where('name', 'Super Admin')->value('id') ?? 1;
        $managerRoleId = \App\Models\Role::where('name', 'Manajerial')->value('id') ?? 2;
        $spvRoleId = \App\Models\Role::where('name', 'Supervisor')->value('id') ?? 3;
        $posRoleId = \App\Models\Role::where('name', 'Admin Tyre')->value('id') ?? 4;

        $users = [
            [
                'name' => 'admin_tim',
                'master_karyawan_id' => 'TIM-ADM',
                'role_id' => $adminRoleId,
                'password' => \Hash::make('Tim123456'),
                'tyre_company_id' => $company->id,
                'foto' => ''
            ],
            [
                'name' => 'mgr_tim',
                'master_karyawan_id' => 'TIM-MGR',
                'role_id' => $managerRoleId,
                'password' => \Hash::make('Tim123456'),
                'tyre_company_id' => $company->id,
                'foto' => ''
            ],
            [
                'name' => 'spv_tim',
                'master_karyawan_id' => 'TIM-SPV',
                'role_id' => $spvRoleId,
                'password' => \Hash::make('Tim123456'),
                'tyre_company_id' => $company->id,
                'foto' => ''
            ],
            [
                'name' => 'pos_tim1',
                'master_karyawan_id' => 'TIM-POS1',
                'role_id' => $posRoleId,
                'password' => \Hash::make('Tim123456'),
                'tyre_company_id' => $company->id,
                'foto' => ''
            ],
            [
                'name' => 'pos_tim2',
                'master_karyawan_id' => 'TIM-POS2',
                'role_id' => $posRoleId,
                'password' => \Hash::make('Tim123456'),
                'tyre_company_id' => $company->id,
                'foto' => ''
            ]
        ];

        foreach ($users as $userData) {
            \App\Models\User::firstOrCreate(
                ['name' => $userData['name']],
                $userData
            );
        }
    }
}
