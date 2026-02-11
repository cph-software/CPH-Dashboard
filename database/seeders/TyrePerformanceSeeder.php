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
use Illuminate\Support\Facades\DB;

class TyrePerformanceSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks for clean seeding if necessary
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate relevant tables
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

        // 1. Tyre Brands
        $brands = [
            ['brand_name' => 'Bridgestone', 'brand_type' => 'Radial', 'status' => 'Active'],
            ['brand_name' => 'Michelin', 'brand_type' => 'Radial', 'status' => 'Active'],
            ['brand_name' => 'Goodyear', 'brand_type' => 'Bias', 'status' => 'Active'],
            ['brand_name' => 'Gajah Tunggal', 'brand_type' => 'Bias', 'status' => 'Active'],
            ['brand_name' => 'Aeolus', 'brand_type' => 'Radial', 'status' => 'Active'],
        ];
        foreach ($brands as $brand) TyreBrand::create($brand);
        $brandIds = TyreBrand::pluck('id', 'brand_name')->toArray();

        // 2. Tyre Sizes
        $sizes = [
            ['size' => '11.00R20', 'tyre_brand_id' => $brandIds['Bridgestone'], 'type' => 'Radial', 'std_otd' => 15.5, 'ply_rating' => 16],
            ['size' => '10.00R20', 'tyre_brand_id' => $brandIds['Bridgestone'], 'type' => 'Radial', 'std_otd' => 14.0, 'ply_rating' => 16],
            ['size' => '12.00R24', 'tyre_brand_id' => $brandIds['Michelin'], 'type' => 'Radial', 'std_otd' => 18.0, 'ply_rating' => 20],
            ['size' => '7.50R16', 'tyre_brand_id' => $brandIds['Gajah Tunggal'], 'type' => 'Bias', 'std_otd' => 10.0, 'ply_rating' => 14],
        ];
        foreach ($sizes as $size) TyreSize::create($size);

        // 3. Tyre Patterns
        $patterns = [
            ['name' => 'G580'],
            ['name' => 'M729'],
            ['name' => 'HN-08'],
            ['name' => 'S811'],
        ];
        foreach ($patterns as $pattern) TyrePattern::create($pattern);

        // 4. Tyre Locations
        $locations = [
            ['location_name' => 'Gudang Pusat', 'location_type' => 'Warehouse', 'capacity' => 1000],
            ['location_name' => 'Workshop Area A', 'location_type' => 'Service', 'capacity' => 50],
            ['location_name' => 'Disposal Yard', 'location_type' => 'Disposal', 'capacity' => 500],
        ];
        foreach ($locations as $location) TyreLocation::create($location);
        $locationIds = TyreLocation::pluck('id')->toArray();

        // 5. Tyre Segments
        $segments = [
            ['segment_id' => 'S-DT', 'segment_name' => 'Dump Truck', 'tyre_location_id' => $locationIds[0], 'terrain_type' => 'Muddy'],
            ['segment_id' => 'S-PM', 'segment_name' => 'Prime Mover', 'tyre_location_id' => $locationIds[0], 'terrain_type' => 'Asphalt'],
            ['segment_id' => 'S-BUS', 'segment_name' => 'Bus Passenger', 'tyre_location_id' => $locationIds[1], 'terrain_type' => 'Asphalt'],
        ];
        foreach ($segments as $segment) TyreSegment::create($segment);

        // 6. Tyre Failure Codes
        $failures = [
            ['failure_code' => 'CO', 'failure_name' => 'Cut On Tread', 'default_category' => 'Repair'],
            ['failure_code' => 'SB', 'failure_name' => 'Separation Belt', 'default_category' => 'Scrap'],
            ['failure_code' => 'BU', 'failure_name' => 'Burst', 'default_category' => 'Scrap'],
            ['failure_code' => 'WW', 'failure_name' => 'Worn Out', 'default_category' => 'Scrap'],
        ];
        foreach ($failures as $failure) TyreFailureCode::create($failure);

        // 7. Tyre Position Configurations
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
            
            $cfgData['total_positions'] = 0; // Temporary
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

        // 8. Vehicles
        $dt10wId = TyrePositionConfiguration::where('code', 'DT10W')->first()->id;
        $lt6wId = TyrePositionConfiguration::where('code', 'LT6W')->first()->id;

        $vehicles = [
            ['kode_kendaraan' => 'DT-001', 'no_polisi' => 'B 1234 ABC', 'jenis_kendaraan' => 'Dump Truck', 'area' => 'Site Sangatta', 'tyre_position_configuration_id' => $dt10wId],
            ['kode_kendaraan' => 'DT-002', 'no_polisi' => 'B 5678 DEF', 'jenis_kendaraan' => 'Dump Truck', 'area' => 'Site Sangatta', 'tyre_position_configuration_id' => $dt10wId],
            ['kode_kendaraan' => 'LT-010', 'no_polisi' => 'B 9012 GHI', 'jenis_kendaraan' => 'Light Truck', 'area' => 'Workshop A', 'tyre_position_configuration_id' => $lt6wId],
        ];
        foreach ($vehicles as $v) MasterImportKendaraan::create($v);

        // 9. Tyres
        $patternIds = TyrePattern::pluck('id')->toArray();
        $segmentIds = TyreSegment::pluck('id')->toArray();
        $sizeIds = TyreSize::pluck('id')->toArray();
        $locId = DB::table('tyre_locations')->first()->id; 

        // New Tyres in stock
        for ($i = 1; $i <= 20; $i++) {
            $otd = rand(15, 20);
            Tyre::create([
                'serial_number' => 'SN-STOCK-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tyre_type' => 'Radial',
                'tyre_pattern_id' => $patternIds[array_rand($patternIds)],
                'tyre_segment_id' => $segmentIds[array_rand($segmentIds)],
                'status' => 'New',
                'tyre_brand_id' => $brandIds[array_rand($brandIds)],
                'tyre_size_id' => $sizeIds[array_rand($sizeIds)],
                'work_location_id' => $locId,
                'price' => rand(3000000, 5000000),
                'initial_tread_depth' => $otd,
                'current_tread_depth' => $otd,
                'retread_count' => 0
            ]);
        }

        // Install tyres on DT-001
        $dt001 = MasterImportKendaraan::where('kode_kendaraan', 'DT-001')->first();
        // Since we might not have the relationship set up correctly in model yet, fetch config manually
        $config = TyrePositionConfiguration::find($dt001->tyre_position_configuration_id);
        
        if ($config) {
            $positions = $config->details;
            foreach ($positions as $p) {
                $otd = rand(16, 22);
                $rtd = $otd - rand(1, 5); // Simulating some wear
                
                $tyre = Tyre::create([
                    'serial_number' => 'SN-INST-' . $dt001->kode_kendaraan . '-' . $p->position_code,
                    'tyre_type' => 'Radial',
                    'tyre_pattern_id' => $patternIds[array_rand($patternIds)],
                    'tyre_segment_id' => $segmentIds[array_rand($segmentIds)],
                    'status' => 'Installed',
                    'tyre_brand_id' => $brandIds[array_rand($brandIds)],
                    'tyre_size_id' => $sizeIds[array_rand($sizeIds)],
                    'work_location_id' => $locId,
                    'current_vehicle_id' => $dt001->id,
                    'current_position_id' => $p->id,
                    'price' => rand(3500000, 5500000),
                    'initial_tread_depth' => $otd,
                    'current_tread_depth' => $rtd,
                    'retread_count' => 0,
                    'total_lifetime_km' => rand(1000, 5000),
                    'total_lifetime_hm' => rand(100, 500)
                ]);
                // Update the position detail as well
                $p->update(['tyre_id' => $tyre->id]);
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
