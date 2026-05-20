<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TyreCompany;

class MahaputraClientSeeder extends Seeder
{
    public function run()
    {
        // Cari ID Bengkel Mahaputra Bandara sebagai parent
        $parent = TyreCompany::where('company_name', 'LIKE', '%Mahaputra%')->first();

        if (!$parent) {
            $this->command->error('❌ Perusahaan Bengkel Mahaputra tidak ditemukan! Pastikan sudah ada di database.');
            return;
        }

        $this->command->info("✅ Parent ditemukan: {$parent->company_name} (ID: {$parent->id})");

        // 19 klien dari daftar (PT. Prima Tunggal Jaya sudah ada, di-skip otomatis)
        $clients = [
            'PT. Albatros Logistik Express',
            'PT. Rezky Energi Abadi',
            'PT. Sinarekputra',
            'PT. Hastura Najwah Utama',
            'CV. Globalpack Logistics',
            'PT. Global Yimi Cargo',
            'PT. Sahabat Karya Sakti',
            'PT. Cahaya Mulia Indoperkasa',
            'PT. Madinah Jasa Trasindo',
            'PT. Bahtera Mulya Intikarsa',
            'PT. Singosari Timur Jaya',
            'PT. Dirgantara Surya Persada',
            'Era Sejahtera Trasindo',
            'PT. Sinar Alam Indo',
            'PT. Lintas Perdana Gasindo',
            'PT. Tunjung Inti Mandiri',
            'Arabi Sukses Logistik',
            'PT. Albatros Logistik',
        ];

        $created = 0;
        $skipped = 0;

        foreach ($clients as $name) {
            // Cek apakah sudah ada (case-insensitive, tanpa titik/spasi ekstra)
            $exists = TyreCompany::whereRaw('LOWER(REPLACE(company_name, ".", "")) = ?', [
                strtolower(str_replace('.', '', $name))
            ])->exists();

            if ($exists) {
                $this->command->warn("⏭️  Skip (sudah ada): {$name}");
                $skipped++;
                continue;
            }

            TyreCompany::create([
                'company_name'      => $name,
                'description'       => 'Klien Bengkel ' . $parent->company_name,
                'status'            => 'Active',
                'parent_company_id' => $parent->id,
            ]);

            $this->command->info("✅ Dibuat: {$name}");
            $created++;
        }

        $this->command->newLine();
        $this->command->info("========================================");
        $this->command->info("📊 Selesai! {$created} perusahaan baru dibuat, {$skipped} di-skip.");
        $this->command->info("   Parent: {$parent->company_name} (ID: {$parent->id})");
        $this->command->info("========================================");
    }
}
