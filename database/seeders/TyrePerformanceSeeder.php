<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TyreBrand;
use App\Models\TyreSize;
use App\Models\TyrePattern;
use App\Models\TyreSegment;
use App\Models\TyreLocation;
use App\Models\TyreFailureCode;
use App\Models\TyrePositionConfiguration;
use App\Models\TyrePositionDetail;
use App\Models\MasterImportKendaraan;
use App\Models\Tyre;
use App\Models\TyreMovement;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TyrePerformanceSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        TyreMovement::truncate();
        Tyre::truncate();
        MasterImportKendaraan::truncate();
        TyrePositionDetail::truncate();
        TyrePositionConfiguration::truncate();
        TyreFailureCode::truncate();
        TyreSegment::truncate();
        TyreLocation::truncate();
        TyrePattern::truncate();
        TyreSize::truncate();
        TyreBrand::truncate();

        // ========================================
        // 1. Tyre Brands
        // ========================================
        $brands = [
            ['brand_name' => 'Bridgestone', 'status' => 'Active'],
            ['brand_name' => 'Michelin', 'status' => 'Active'],
            ['brand_name' => 'Goodyear', 'status' => 'Active'],
            ['brand_name' => 'Gajah Tunggal', 'status' => 'Active'],
            ['brand_name' => 'Aeolus', 'status' => 'Active'],
        ];
        foreach ($brands as $brand)
            TyreBrand::create($brand);
        $brandIds = TyreBrand::pluck('id', 'brand_name')->toArray();

        // ========================================
        // 2. Tyre Sizes
        // ========================================
        $sizes = [
            ['size' => '11.00R20', 'tyre_brand_id' => $brandIds['Bridgestone'], 'type' => 'Radial', 'std_otd' => 15.5, 'ply_rating' => 16],
            ['size' => '10.00R20', 'tyre_brand_id' => $brandIds['Bridgestone'], 'type' => 'Radial', 'std_otd' => 14.0, 'ply_rating' => 16],
            ['size' => '12.00R24', 'tyre_brand_id' => $brandIds['Michelin'], 'type' => 'Radial', 'std_otd' => 18.0, 'ply_rating' => 20],
            ['size' => '7.50R16', 'tyre_brand_id' => $brandIds['Gajah Tunggal'], 'type' => 'Bias', 'std_otd' => 10.0, 'ply_rating' => 14],
        ];
        foreach ($sizes as $size)
            TyreSize::create($size);

        // ========================================
        // 3. Tyre Patterns
        // ========================================
        $patterns = [
            ['name' => 'G580'],
            ['name' => 'M729'],
            ['name' => 'HN-08'],
            ['name' => 'S811'],
        ];
        foreach ($patterns as $pattern)
            TyrePattern::create($pattern);

        // ========================================
        // 4. Tyre Locations
        // ========================================
        $locations = [
            ['location_name' => 'Gudang Pusat', 'location_type' => 'Warehouse', 'capacity' => 1000],
            ['location_name' => 'Workshop Area A', 'location_type' => 'Service', 'capacity' => 50],
            ['location_name' => 'Disposal Yard', 'location_type' => 'Disposal', 'capacity' => 500],
        ];
        foreach ($locations as $location)
            TyreLocation::create($location);
        $locationIds = TyreLocation::pluck('id')->toArray();

        // ========================================
        // 5. Tyre Segments
        // ========================================
        $segments = [
            ['segment_id' => 'S-DT', 'segment_name' => 'Dump Truck', 'tyre_location_id' => $locationIds[0], 'terrain_type' => 'Muddy'],
            ['segment_id' => 'S-PM', 'segment_name' => 'Prime Mover', 'tyre_location_id' => $locationIds[0], 'terrain_type' => 'Asphalt'],
            ['segment_id' => 'S-BUS', 'segment_name' => 'Bus Passenger', 'tyre_location_id' => $locationIds[1], 'terrain_type' => 'Asphalt'],
        ];
        foreach ($segments as $segment)
            TyreSegment::create($segment);

        // ========================================
        // 6. Tyre Failure Codes
        // ========================================
        $failures = [
            ['failure_code' => 'CO', 'failure_name' => 'Cut On Tread', 'default_category' => 'Repair'],
            ['failure_code' => 'SB', 'failure_name' => 'Separation Belt', 'default_category' => 'Scrap'],
            ['failure_code' => 'BU', 'failure_name' => 'Burst', 'default_category' => 'Scrap'],
            ['failure_code' => 'WW', 'failure_name' => 'Worn Out', 'default_category' => 'Scrap'],
            ['failure_code' => 'FW', 'failure_name' => 'Flat Wear', 'default_category' => 'Repair'],
            ['failure_code' => 'BO', 'failure_name' => 'Blow Out', 'default_category' => 'Scrap'],
        ];
        foreach ($failures as $failure)
            TyreFailureCode::create($failure);
        $failureIds = TyreFailureCode::pluck('id', 'failure_code')->toArray();

        // ========================================
        // 7. Tyre Position Configurations
        // ========================================
        $configs = [
            [
                'name' => 'Dump Truck 10-Wheeler',
                'code' => 'DT10W',
                'description' => '2 Front, 8 Rear',
                'axle_config' => ['front' => 1, 'rear' => 2, 'spare' => 1]
            ],
            [
                'name' => 'Light Truck 6-Wheeler',
                'code' => 'LT6W',
                'description' => '2 Front, 4 Rear',
                'axle_config' => ['front' => 1, 'rear' => 1, 'spare' => 1]
            ],
        ];

        foreach ($configs as $cfgData) {
            $axleConfig = $cfgData['axle_config'];
            unset($cfgData['axle_config']);

            $cfgData['total_positions'] = 0;
            $config = TyrePositionConfiguration::create($cfgData);
            $positions = $config->generatePositions($axleConfig);
            foreach ($positions as $pos) {
                TyrePositionDetail::create($pos);
            }

            $config->update([
                'total_positions' => count($positions),
                'total_spare' => $axleConfig['spare'] ?? 0
            ]);
        }

        // ========================================
        // 8. Vehicles (5 kendaraan)
        // ========================================
        $dt10wId = TyrePositionConfiguration::where('code', 'DT10W')->first()->id;
        $lt6wId = TyrePositionConfiguration::where('code', 'LT6W')->first()->id;

        $vehicles = [
            ['kode_kendaraan' => 'DT-001', 'no_polisi' => 'B 1234 ABC', 'jenis_kendaraan' => 'Dump Truck', 'area' => 'Site Sangatta', 'tyre_position_configuration_id' => $dt10wId],
            ['kode_kendaraan' => 'DT-002', 'no_polisi' => 'B 5678 DEF', 'jenis_kendaraan' => 'Dump Truck', 'area' => 'Site Sangatta', 'tyre_position_configuration_id' => $dt10wId],
            ['kode_kendaraan' => 'DT-003', 'no_polisi' => 'B 2233 GHI', 'jenis_kendaraan' => 'Dump Truck', 'area' => 'Site Bontang', 'tyre_position_configuration_id' => $dt10wId],
            ['kode_kendaraan' => 'LT-010', 'no_polisi' => 'B 9012 JKL', 'jenis_kendaraan' => 'Light Truck', 'area' => 'Workshop A', 'tyre_position_configuration_id' => $lt6wId],
            ['kode_kendaraan' => 'LT-011', 'no_polisi' => 'B 3344 MNO', 'jenis_kendaraan' => 'Light Truck', 'area' => 'Workshop B', 'tyre_position_configuration_id' => $lt6wId],
        ];
        foreach ($vehicles as $v)
            MasterImportKendaraan::create($v);

        // ========================================
        // 9. Tyres & Movements
        // ========================================
        $patternIds = TyrePattern::pluck('id')->toArray();
        $segmentIds = TyreSegment::pluck('id')->toArray();
        $sizeIds = TyreSize::pluck('id')->toArray();
        $allBrandIds = array_values($brandIds);
        $gudangId = $locationIds[0]; // Gudang Pusat
        $workshopId = $locationIds[1]; // Workshop
        $disposalId = $locationIds[2]; // Disposal

        // --------------------
        // 9a. New Tyres (20 in stock)
        // --------------------
        for ($i = 1; $i <= 20; $i++) {
            $otd = rand(150, 200) / 10;
            Tyre::create([
                'serial_number' => 'SN-STOCK-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tyre_pattern_id' => $patternIds[array_rand($patternIds)],
                'tyre_segment_id' => $segmentIds[array_rand($segmentIds)],
                'status' => 'New',
                'tyre_brand_id' => $allBrandIds[array_rand($allBrandIds)],
                'tyre_size_id' => $sizeIds[array_rand($sizeIds)],
                'work_location_id' => $gudangId,
                'price' => rand(30, 55) * 100000,
                'initial_tread_depth' => $otd,
                'current_tread_depth' => $otd,
                'retread_count' => 0
            ]);
        }

        // --------------------
        // 9b. Installed Tyres on DT-001, DT-002, DT-003
        // --------------------
        $dumpTrucks = MasterImportKendaraan::where('jenis_kendaraan', 'Dump Truck')->get();

        foreach ($dumpTrucks as $truck) {
            $config = TyrePositionConfiguration::find($truck->tyre_position_configuration_id);
            if (!$config)
                continue;

            $positions = $config->details;
            foreach ($positions as $p) {
                $brandKey = array_rand($allBrandIds);
                $selectedBrand = $allBrandIds[$brandKey];
                $otd = rand(160, 220) / 10;
                $rtd = $otd - rand(10, 80) / 10; // Wear between 1-8mm
                if ($rtd < 2)
                    $rtd = rand(20, 40) / 10;

                $lifetimeKm = rand(3000, 15000);
                $lifetimeHm = rand(150, 1200);

                $tyre = Tyre::create([
                    'serial_number' => 'SN-' . $truck->kode_kendaraan . '-' . $p->position_code,
                    'tyre_pattern_id' => $patternIds[array_rand($patternIds)],
                    'tyre_segment_id' => $segmentIds[array_rand($segmentIds)],
                    'status' => 'Installed',
                    'tyre_brand_id' => $selectedBrand,
                    'tyre_size_id' => $sizeIds[array_rand($sizeIds)],
                    'work_location_id' => $gudangId,
                    'current_vehicle_id' => $truck->id,
                    'current_position_id' => $p->id,
                    'price' => rand(35, 55) * 100000,
                    'initial_tread_depth' => $otd,
                    'current_tread_depth' => $rtd,
                    'retread_count' => rand(0, 1),
                    'total_lifetime_km' => $lifetimeKm,
                    'total_lifetime_hm' => $lifetimeHm
                ]);
                $p->update(['tyre_id' => $tyre->id]);

                // Create installation movement history
                $installDate = Carbon::now()->subDays(rand(30, 180));
                TyreMovement::create([
                    'tyre_id' => $tyre->id,
                    'vehicle_id' => $truck->id,
                    'position_id' => $p->id,
                    'operational_segment_id' => $segmentIds[array_rand($segmentIds)],
                    'work_location_id' => $gudangId,
                    'movement_type' => 'Installation',
                    'movement_date' => $installDate,
                    'odometer_reading' => rand(5000, 20000),
                    'hour_meter_reading' => rand(200, 1000),
                    'psi_reading' => rand(90, 120),
                    'rtd_reading' => $otd,
                    'created_by' => 1
                ]);
            }
        }

        // --------------------
        // 9c. Installed Tyres on Light Trucks
        // --------------------
        $lightTrucks = MasterImportKendaraan::where('jenis_kendaraan', 'Light Truck')->get();

        foreach ($lightTrucks as $truck) {
            $config = TyrePositionConfiguration::find($truck->tyre_position_configuration_id);
            if (!$config)
                continue;

            $positions = $config->details;
            foreach ($positions as $p) {
                $otd = rand(100, 140) / 10;
                $rtd = $otd - rand(5, 40) / 10;
                if ($rtd < 2)
                    $rtd = rand(20, 40) / 10;

                $tyre = Tyre::create([
                    'serial_number' => 'SN-' . $truck->kode_kendaraan . '-' . $p->position_code,
                    'tyre_pattern_id' => $patternIds[array_rand($patternIds)],
                    'tyre_segment_id' => $segmentIds[array_rand($segmentIds)],
                    'status' => 'Installed',
                    'tyre_brand_id' => $allBrandIds[array_rand($allBrandIds)],
                    'tyre_size_id' => $sizeIds[3], // 7.50R16
                    'work_location_id' => $gudangId,
                    'current_vehicle_id' => $truck->id,
                    'current_position_id' => $p->id,
                    'price' => rand(18, 30) * 100000,
                    'initial_tread_depth' => $otd,
                    'current_tread_depth' => $rtd,
                    'retread_count' => 0,
                    'total_lifetime_km' => rand(2000, 8000),
                    'total_lifetime_hm' => rand(100, 500)
                ]);
                $p->update(['tyre_id' => $tyre->id]);

                $installDate = Carbon::now()->subDays(rand(20, 120));
                TyreMovement::create([
                    'tyre_id' => $tyre->id,
                    'vehicle_id' => $truck->id,
                    'position_id' => $p->id,
                    'operational_segment_id' => $segmentIds[array_rand($segmentIds)],
                    'work_location_id' => $gudangId,
                    'movement_type' => 'Installation',
                    'movement_date' => $installDate,
                    'odometer_reading' => rand(3000, 12000),
                    'hour_meter_reading' => rand(100, 600),
                    'psi_reading' => rand(80, 100),
                    'rtd_reading' => $otd,
                    'created_by' => 1
                ]);
            }
        }

        // --------------------
        // 9d. Repaired Tyres (in workshop)
        // --------------------
        for ($i = 1; $i <= 5; $i++) {
            $otd = rand(150, 200) / 10;
            Tyre::create([
                'serial_number' => 'SN-REP-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'tyre_pattern_id' => $patternIds[array_rand($patternIds)],
                'tyre_segment_id' => $segmentIds[array_rand($segmentIds)],
                'status' => 'Repaired',
                'tyre_brand_id' => $allBrandIds[array_rand($allBrandIds)],
                'tyre_size_id' => $sizeIds[array_rand($sizeIds)],
                'work_location_id' => $workshopId,
                'price' => rand(30, 50) * 100000,
                'initial_tread_depth' => $otd,
                'current_tread_depth' => rand(60, 100) / 10,
                'retread_count' => 1,
                'total_lifetime_km' => rand(8000, 20000),
                'total_lifetime_hm' => rand(600, 1500)
            ]);
        }

        // --------------------
        // 9e. Scrapped Tyres (in disposal yard)
        // --------------------
        for ($i = 1; $i <= 8; $i++) {
            $otd = rand(150, 200) / 10;
            Tyre::create([
                'serial_number' => 'SN-SCRAP-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'tyre_pattern_id' => $patternIds[array_rand($patternIds)],
                'tyre_segment_id' => $segmentIds[array_rand($segmentIds)],
                'status' => 'Scrap',
                'tyre_brand_id' => $allBrandIds[array_rand($allBrandIds)],
                'tyre_size_id' => $sizeIds[array_rand($sizeIds)],
                'work_location_id' => $disposalId,
                'price' => rand(30, 50) * 100000,
                'initial_tread_depth' => $otd,
                'current_tread_depth' => rand(0, 20) / 10,
                'retread_count' => rand(0, 2),
                'total_lifetime_km' => rand(15000, 35000),
                'total_lifetime_hm' => rand(1000, 3000)
            ]);
        }

        // --------------------
        // 9f. Historical Movement Records (6 months of data)
        // --------------------
        $allDumpTrucks = MasterImportKendaraan::where('jenis_kendaraan', 'Dump Truck')->get();

        for ($month = 5; $month >= 0; $month--) {
            // Each month: some installations and some removals
            $installCount = rand(3, 8);
            $removalCount = rand(2, 6);

            $monthStart = Carbon::now()->subMonths($month)->startOfMonth();

            // Installation records
            for ($j = 0; $j < $installCount; $j++) {
                $truck = $allDumpTrucks->random();
                $fakePositionId = TyrePositionDetail::where('configuration_id', $truck->tyre_position_configuration_id)
                    ->inRandomOrder()->first();

                if (!$fakePositionId)
                    continue;

                $fakeTyre = Tyre::inRandomOrder()->first();
                if (!$fakeTyre)
                    continue;

                TyreMovement::create([
                    'tyre_id' => $fakeTyre->id,
                    'vehicle_id' => $truck->id,
                    'position_id' => $fakePositionId->id,
                    'operational_segment_id' => $segmentIds[array_rand($segmentIds)],
                    'work_location_id' => $gudangId,
                    'movement_type' => 'Installation',
                    'movement_date' => $monthStart->copy()->addDays(rand(0, 27)),
                    'odometer_reading' => rand(5000, 30000),
                    'hour_meter_reading' => rand(200, 2000),
                    'psi_reading' => rand(90, 120),
                    'rtd_reading' => rand(120, 200) / 10,
                    'created_by' => 1
                ]);
            }

            // Removal records
            for ($j = 0; $j < $removalCount; $j++) {
                $truck = $allDumpTrucks->random();
                $fakePositionId = TyrePositionDetail::where('configuration_id', $truck->tyre_position_configuration_id)
                    ->inRandomOrder()->first();

                if (!$fakePositionId)
                    continue;

                $fakeTyre = Tyre::inRandomOrder()->first();
                if (!$fakeTyre)
                    continue;

                // Random failure code for some removals
                $hasFailure = rand(0, 1);
                $failureIdValues = array_values($failureIds);
                $failureCodeId = $hasFailure ? $failureIdValues[array_rand($failureIdValues)] : null;

                TyreMovement::create([
                    'tyre_id' => $fakeTyre->id,
                    'vehicle_id' => $truck->id,
                    'position_id' => $fakePositionId->id,
                    'operational_segment_id' => $segmentIds[array_rand($segmentIds)],
                    'work_location_id' => $gudangId,
                    'movement_type' => 'Removal',
                    'target_status' => $hasFailure ? 'Scrap' : 'Repaired',
                    'failure_code_id' => $failureCodeId,
                    'movement_date' => $monthStart->copy()->addDays(rand(0, 27)),
                    'odometer_reading' => rand(10000, 40000),
                    'hour_meter_reading' => rand(500, 3000),
                    'psi_reading' => rand(60, 110),
                    'rtd_reading' => rand(10, 80) / 10,
                    'created_by' => 1
                ]);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Sync location stock counts
        $this->command->info('Syncing location stock...');
        \Artisan::call('tyre:sync-location-stock');
        $this->command->info('Tyre Performance seeding completed!');
    }
}
