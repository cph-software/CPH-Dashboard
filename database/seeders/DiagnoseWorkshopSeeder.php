<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DiagnoseWorkshopSeeder extends Seeder
{
    public function run()
    {
        $this->command->info("========== DIAGNOSA MULTI-COMPANY ==========");

        // 1. Cek semua perusahaan
        $companies = \App\Models\TyreCompany::select('id', 'company_name', 'parent_company_id', 'status')->get();
        $this->command->info("\n📋 Semua Perusahaan ({$companies->count()}):");
        foreach ($companies as $c) {
            $parentLabel = $c->parent_company_id ? " → Anak dari ID:{$c->parent_company_id}" : " (INDUK)";
            $this->command->info("   ID:{$c->id} | {$c->company_name} | {$c->status}{$parentLabel}");
        }

        // 2. Cek kolom parent_company_id ada atau tidak
        $hasColumn = \Schema::hasColumn('tyre_companies', 'parent_company_id');
        $this->command->info("\n🔧 Kolom parent_company_id: " . ($hasColumn ? '✅ ADA' : '❌ TIDAK ADA'));

        // 3. Cek user admin BDR
        $users = \App\Models\User::select('id', 'name', 'role_id', 'tyre_company_id')->get();
        $this->command->info("\n👤 Semua User:");
        foreach ($users as $u) {
            $companyName = $u->tyreCompany ? $u->tyreCompany->company_name : 'NULL';
            $childCount = $u->tyreCompany ? $u->tyreCompany->children()->count() : 0;
            $isWA = ($childCount > 0) ? '✅ YA' : '❌ BUKAN';
            $this->command->info("   ID:{$u->id} | {$u->name} | Role:{$u->role_id} | Company:{$companyName} (ID:{$u->tyre_company_id}) | Children:{$childCount} | WorkshopAdmin:{$isWA}");
        }

        // 4. Cek parent-child relationship
        $parents = $companies->whereNull('parent_company_id')->where('status', 'Active');
        $this->command->info("\n🏢 Perusahaan Induk:");
        foreach ($parents as $p) {
            $children = \App\Models\TyreCompany::where('parent_company_id', $p->id)->get();
            $this->command->info("   {$p->company_name} (ID:{$p->id}) → {$children->count()} anak");
            foreach ($children as $child) {
                $this->command->info("      └─ {$child->company_name} (ID:{$child->id})");
            }
        }

        $this->command->info("\n========== SELESAI ==========");
    }
}
