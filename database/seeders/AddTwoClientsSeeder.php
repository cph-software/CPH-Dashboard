<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TyreCompany;

class AddTwoClientsSeeder extends Seeder
{
    public function run()
    {
        $parent = TyreCompany::find(4);

        $clients = [
            'PT. Arabic Sukses Logistik',
            'PT. Mitra Hijau Asia',
        ];

        foreach ($clients as $name) {
            $exists = TyreCompany::whereRaw('LOWER(company_name) = ?', [strtolower($name)])->exists();
            if ($exists) {
                $this->command->warn("Skip (sudah ada): {$name}");
            } else {
                TyreCompany::create([
                    'company_name'      => $name,
                    'description'       => 'Klien Bengkel ' . $parent->company_name,
                    'status'            => 'Active',
                    'parent_company_id' => $parent->id,
                ]);
                $this->command->info("✅ Dibuat: {$name}");
            }
        }
    }
}
