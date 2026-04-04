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

        $users = [
            [
                'name' => 'admin_tim',
                'master_karyawan_id' => 'TIM-ADM',
                'role_id' => 1, // Super Admin
                'password' => \Hash::make('Tim123456'),
                'tyre_company_id' => $company->id,
                'foto' => ''
            ],
            [
                'name' => 'mgr_tim',
                'master_karyawan_id' => 'TIM-MGR',
                'role_id' => 2, // Manajerial
                'password' => \Hash::make('Tim123456'),
                'tyre_company_id' => $company->id,
                'foto' => ''
            ],
            [
                'name' => 'spv_tim',
                'master_karyawan_id' => 'TIM-SPV',
                'role_id' => 3, // Supervisor
                'password' => \Hash::make('Tim123456'),
                'tyre_company_id' => $company->id,
                'foto' => ''
            ],
            [
                'name' => 'pos_tim1',
                'master_karyawan_id' => 'TIM-POS1',
                'role_id' => 4, // Admin Tyre / POS
                'password' => \Hash::make('Tim123456'),
                'tyre_company_id' => $company->id,
                'foto' => ''
            ],
            [
                'name' => 'pos_tim2',
                'master_karyawan_id' => 'TIM-POS2',
                'role_id' => 4, // Admin Tyre / POS
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
