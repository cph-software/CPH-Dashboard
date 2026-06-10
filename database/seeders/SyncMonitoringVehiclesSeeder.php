<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterImportKendaraan;
use App\Models\TyreMonitoringVehicle;

class SyncMonitoringVehiclesSeeder extends Seeder
{
    public function run()
    {
        $vehicles = MasterImportKendaraan::withoutGlobalScopes()->get();
        $count = 0;
        foreach ($vehicles as $v) {
            TyreMonitoringVehicle::updateOrCreate(
                ['master_vehicle_id' => $v->id],
                [
                    'fleet_name' => $v->kode_kendaraan,
                    'vehicle_number' => $v->no_polisi ?: '-',
                    'driver_name' => 'Driver',
                    'tire_positions' => $v->total_tyre_position ?: 6,
                    'is_trail' => stripos($v->model_kendaraan ?? '', 'trailer') !== false || stripos($v->model_kendaraan ?? '', 'gandengan') !== false,
                    'status' => $v->tyre_unit_status === 'Active' ? 'active' : 'inactive',
                    'tyre_company_id' => $v->tyre_company_id ?? 1
                ]
            );
            $count++;
        }
        $this->command->info("Successfully synced {$count} vehicles to monitoring.");
    }
}
