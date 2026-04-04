<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\TyreCompany;
use App\Models\TyreBrand;
use App\Models\TyreSize;
use App\Models\TyrePattern;
use App\Models\TyreLocation;
use App\Models\TyreSegment;
use App\Models\TyreFailureCode;
use App\Models\TyrePositionConfiguration;
use App\Models\TyrePositionDetail;
use App\Models\MasterImportKendaraan;
use App\Models\Tyre;
use App\Models\TyreMovement;
use App\Models\User;

/**
 * Demo Data Seeder (Poin 7.4)
 *
 * Membuat data demo berisi:
 * - 1 Company Demo (PT MITRA TAMBANG DEMO)
 * - 4 Akun Demo (super_admin, admin_demo, user_demo, viewer_demo)
 * - Master data lengkap (brand, size, pattern, lokasi, segmen, failure code, konfigurasi axle)
 * - 5 kendaraan demo (3 Dump Truck + 2 Light Truck)
 * - 50 ban demo (20 stok gudang, 30 terpasang) + 8 scrap + 5 repaired
 * - Riwayat pergerakan 6 bulan
 *
 * ⚠️  AMAN DIJALANKAN — tidak menghapus data existing.
 *      Semua data demo terikat pada company_id baru.
 *
 * Cara run:
 *   php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    private int $companyId;
    private array $brandIds = [];
    private array $sizeIds = [];
    private array $patternIds = [];
    private array $locationIds = [];
    private array $failureIds = [];

    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->command->info('🚀 Memulai Demo Data Seeder...');

        // =============================================
        // 1. Company Demo
        // =============================================
        $this->command->info('📦 Membuat Company Demo...');
        $company = TyreCompany::updateOrCreate(
            ['company_name' => 'PT MITRA TAMBANG DEMO'],
            [
                'total_tyres' => 100,
                'description' => 'Perusahaan demo untuk testing & presentasi. JANGAN digunakan untuk produksi.',
                'status' => 'Active',
            ]
        );
        $this->companyId = $company->id;
        $this->command->info("   ✅ Company ID: {$this->companyId}");

        // =============================================
        // 2. Akun Demo
        // =============================================
        $this->command->info('👤 Membuat Akun Demo...');
        $demoUsers = [
            [
                'name'            => 'Super Admin Demo',
                'role_id'         => 1,
                'tyre_company_id' => $this->companyId,
                'password'        => Hash::make('demo1234'),
                'foto'            => '',
            ],
            [
                'name'            => 'Admin Demo',
                'role_id'         => 2,
                'tyre_company_id' => $this->companyId,
                'password'        => Hash::make('demo1234'),
                'foto'            => '',
            ],
            [
                'name'            => 'User Operasional Demo',
                'role_id'         => 3,
                'tyre_company_id' => $this->companyId,
                'password'        => Hash::make('demo1234'),
                'foto'            => '',
            ],
        ];

        foreach ($demoUsers as $userData) {
            User::firstOrCreate(
                ['name' => $userData['name'], 'tyre_company_id' => $this->companyId],
                $userData
            );
        }
        $adminUserId = User::where('name', 'Admin Demo')->where('tyre_company_id', $this->companyId)->first()->id;
        $this->command->info('   ✅ 3 akun demo dibuat (password: demo1234)');

        // =============================================
        // 3. Master: Brand
        // =============================================
        $this->command->info('🏷️  Membuat Master Brand...');
        $brands = ['BRIDGESTONE', 'GITI', 'MICHELIN', 'GOODYEAR', 'DUNLOP'];
        foreach ($brands as $b) {
            $brand = TyreBrand::firstOrCreate(['brand_name' => $b], ['status' => 'Active']);
            $this->brandIds[$b] = $brand->id;
        }

        // =============================================
        // 4. Master: Size
        // =============================================
        $this->command->info('📏 Membuat Master Size...');
        $sizes = [
            ['size' => '11.00-20',    'tyre_brand_id' => $this->brandIds['BRIDGESTONE'], 'std_otd' => 16.5, 'ply_rating' => 16],
            ['size' => '10.00-20',    'tyre_brand_id' => $this->brandIds['GITI'],        'std_otd' => 15.0, 'ply_rating' => 14],
            ['size' => '12.00R24',    'tyre_brand_id' => $this->brandIds['MICHELIN'],    'std_otd' => 18.0, 'ply_rating' => 20],
            ['size' => '7.50R16',     'tyre_brand_id' => $this->brandIds['GOODYEAR'],    'std_otd' => 10.5, 'ply_rating' => 14],
            ['size' => 'R25 29.5',    'tyre_brand_id' => $this->brandIds['MICHELIN'],    'std_otd' => 42.0, 'ply_rating' => 32],
        ];
        foreach ($sizes as $s) {
            $size = TyreSize::firstOrCreate(['size' => $s['size']], $s);
            $this->sizeIds[] = $size->id;
        }

        // =============================================
        // 5. Master: Pattern
        // =============================================
        $this->command->info('🔷 Membuat Master Pattern...');
        $patternsData = [
            ['name' => 'G580',    'tyre_brand_id' => $this->brandIds['BRIDGESTONE']],
            ['name' => 'GTL971',  'tyre_brand_id' => $this->brandIds['GITI']],
            ['name' => 'XDM2',    'tyre_brand_id' => $this->brandIds['MICHELIN']],
            ['name' => 'RT9B',    'tyre_brand_id' => $this->brandIds['GOODYEAR']],
            ['name' => 'SP281',   'tyre_brand_id' => $this->brandIds['DUNLOP']],
        ];
        foreach ($patternsData as $p) {
            $pattern = TyrePattern::firstOrCreate(['name' => $p['name']], $p);
            $this->patternIds[] = $pattern->id;
        }

        // =============================================
        // 6. Master: Locations (Gudang)
        // =============================================
        $this->command->info('📍 Membuat Master Lokasi...');
        $locations = [
            ['location_name' => 'GUDANG PUSAT DEMO',      'location_type' => 'Warehouse', 'capacity' => 200, 'tyre_company_id' => $this->companyId],
            ['location_name' => 'GUDANG SITE A DEMO',     'location_type' => 'Warehouse', 'capacity' => 100, 'tyre_company_id' => $this->companyId],
            ['location_name' => 'WORKSHOP SITE A DEMO',   'location_type' => 'Service',   'capacity' => 30,  'tyre_company_id' => $this->companyId],
            ['location_name' => 'DISPOSAL AREA DEMO',     'location_type' => 'Disposal',  'capacity' => 500, 'tyre_company_id' => $this->companyId],
        ];
        foreach ($locations as $loc) {
            $location = TyreLocation::firstOrCreate(
                ['location_name' => $loc['location_name']],
                $loc
            );
            $this->locationIds[$loc['location_name']] = $location->id;
        }
        $gudangPusatId  = $this->locationIds['GUDANG PUSAT DEMO'];
        $gudangSiteAId  = $this->locationIds['GUDANG SITE A DEMO'];
        $workshopId     = $this->locationIds['WORKSHOP SITE A DEMO'];
        $disposalId     = $this->locationIds['DISPOSAL AREA DEMO'];

        // =============================================
        // 7. Master: Segments
        // =============================================
        $this->command->info('🗺️  Membuat Master Segmen...');
        $segments = [
            ['segment_id' => 'DEMO/HAUL/01', 'segment_name' => 'Coal Hauling',   'tyre_location_id' => $gudangSiteAId, 'terrain_type' => 'Muddy',   'tyre_company_id' => $this->companyId],
            ['segment_id' => 'DEMO/OB/01',   'segment_name' => 'Overburden',      'tyre_location_id' => $gudangSiteAId, 'terrain_type' => 'Rocky',   'tyre_company_id' => $this->companyId],
            ['segment_id' => 'DEMO/INFRA/01','segment_name' => 'Infrastructure',  'tyre_location_id' => $gudangPusatId, 'terrain_type' => 'Asphalt', 'tyre_company_id' => $this->companyId],
        ];
        foreach ($segments as $seg) {
            TyreSegment::firstOrCreate(
                ['segment_id' => $seg['segment_id']],
                $seg
            );
        }

        // =============================================
        // 8. Master: Failure Codes
        // =============================================
        $this->command->info('⚠️  Membuat Failure Codes...');
        $failures = [
            ['failure_code' => 'CUT',  'failure_name' => 'Cut Separation',     'default_category' => 'Repair'],
            ['failure_code' => 'EXTN', 'failure_name' => 'External Damage',    'default_category' => 'Repair'],
            ['failure_code' => 'WEAR', 'failure_name' => 'Irregular Wear',     'default_category' => 'Scrap'],
            ['failure_code' => 'BURS', 'failure_name' => 'Burst / Blowout',    'default_category' => 'Scrap'],
            ['failure_code' => 'SB',   'failure_name' => 'Separation Belt',    'default_category' => 'Scrap'],
            ['failure_code' => 'WW',   'failure_name' => 'Worn Out (Batas)',   'default_category' => 'Scrap'],
            ['failure_code' => 'BEAD', 'failure_name' => 'Bead Damage',        'default_category' => 'Repair'],
            ['failure_code' => 'CLIM', 'failure_name' => 'Manufacturing Claim','default_category' => 'Claim'],
        ];
        foreach ($failures as $f) {
            $fc = TyreFailureCode::firstOrCreate(['failure_code' => $f['failure_code']], $f);
            $this->failureIds[] = $fc->id;
        }

        // =============================================
        // 9. Axle Layout / Konfigurasi Posisi
        // =============================================
        $this->command->info('🚛 Membuat Konfigurasi Axle...');
        $dt10w = $this->createOrGetConfig('Dump Truck 10 Roda (2+4+4)', 'DT10W-DEMO', 10, [
            ['position_code' => 'FL',  'position_name' => 'Front Left',          'axle_type' => 'Front', 'axle_number' => 1, 'side' => 'Left',  'is_spare' => false, 'display_order' => 1],
            ['position_code' => 'FR',  'position_name' => 'Front Right',         'axle_type' => 'Front', 'axle_number' => 1, 'side' => 'Right', 'is_spare' => false, 'display_order' => 2],
            ['position_code' => 'LRI1','position_name' => 'Rear Left Inner 1',   'axle_type' => 'Rear',  'axle_number' => 2, 'side' => 'Left',  'is_spare' => false, 'display_order' => 3],
            ['position_code' => 'LRO1','position_name' => 'Rear Left Outer 1',   'axle_type' => 'Rear',  'axle_number' => 2, 'side' => 'Left',  'is_spare' => false, 'display_order' => 4],
            ['position_code' => 'RRI1','position_name' => 'Rear Right Inner 1',  'axle_type' => 'Rear',  'axle_number' => 2, 'side' => 'Right', 'is_spare' => false, 'display_order' => 5],
            ['position_code' => 'RRO1','position_name' => 'Rear Right Outer 1',  'axle_type' => 'Rear',  'axle_number' => 2, 'side' => 'Right', 'is_spare' => false, 'display_order' => 6],
            ['position_code' => 'LRI2','position_name' => 'Rear Left Inner 2',   'axle_type' => 'Rear',  'axle_number' => 3, 'side' => 'Left',  'is_spare' => false, 'display_order' => 7],
            ['position_code' => 'LRO2','position_name' => 'Rear Left Outer 2',   'axle_type' => 'Rear',  'axle_number' => 3, 'side' => 'Left',  'is_spare' => false, 'display_order' => 8],
            ['position_code' => 'RRI2','position_name' => 'Rear Right Inner 2',  'axle_type' => 'Rear',  'axle_number' => 3, 'side' => 'Right', 'is_spare' => false, 'display_order' => 9],
            ['position_code' => 'RRO2','position_name' => 'Rear Right Outer 2',  'axle_type' => 'Rear',  'axle_number' => 3, 'side' => 'Right', 'is_spare' => false, 'display_order' => 10],
        ]);

        $lt6w = $this->createOrGetConfig('Light Truck 6 Roda (2+2+2)', 'LT6W-DEMO', 6, [
            ['position_code' => 'FL',  'position_name' => 'Front Left',       'axle_type' => 'Front', 'axle_number' => 1, 'side' => 'Left',  'is_spare' => false, 'display_order' => 1],
            ['position_code' => 'FR',  'position_name' => 'Front Right',      'axle_type' => 'Front', 'axle_number' => 1, 'side' => 'Right', 'is_spare' => false, 'display_order' => 2],
            ['position_code' => 'LRI', 'position_name' => 'Rear Left Inner',  'axle_type' => 'Rear',  'axle_number' => 2, 'side' => 'Left',  'is_spare' => false, 'display_order' => 3],
            ['position_code' => 'LRO', 'position_name' => 'Rear Left Outer',  'axle_type' => 'Rear',  'axle_number' => 2, 'side' => 'Left',  'is_spare' => false, 'display_order' => 4],
            ['position_code' => 'RRI', 'position_name' => 'Rear Right Inner', 'axle_type' => 'Rear',  'axle_number' => 2, 'side' => 'Right', 'is_spare' => false, 'display_order' => 5],
            ['position_code' => 'RRO', 'position_name' => 'Rear Right Outer', 'axle_type' => 'Rear',  'axle_number' => 2, 'side' => 'Right', 'is_spare' => false, 'display_order' => 6],
        ]);

        // =============================================
        // 10. Kendaraan Demo
        // =============================================
        $this->command->info('🚗 Membuat Kendaraan Demo...');
        $kendaraan = [
            ['kode_kendaraan' => 'DEMO-DT-001', 'no_polisi' => 'KT 1001 DM', 'jenis_kendaraan' => 'DUMP TRUCK', 'vehicle_brand' => 'HINO',   'area' => 'SITE A',    'payload_capacity' => 20, 'tyre_position_configuration_id' => $dt10w, 'total_tyre_position' => 10, 'tyre_company_id' => $this->companyId],
            ['kode_kendaraan' => 'DEMO-DT-002', 'no_polisi' => 'KT 1002 DM', 'jenis_kendaraan' => 'DUMP TRUCK', 'vehicle_brand' => 'SCANIA',  'area' => 'SITE A',    'payload_capacity' => 25, 'tyre_position_configuration_id' => $dt10w, 'total_tyre_position' => 10, 'tyre_company_id' => $this->companyId],
            ['kode_kendaraan' => 'DEMO-DT-003', 'no_polisi' => 'KT 1003 DM', 'jenis_kendaraan' => 'DUMP TRUCK', 'vehicle_brand' => 'VOLVO',   'area' => 'SITE B',    'payload_capacity' => 30, 'tyre_position_configuration_id' => $dt10w, 'total_tyre_position' => 10, 'tyre_company_id' => $this->companyId],
            ['kode_kendaraan' => 'DEMO-LT-001', 'no_polisi' => 'KT 2001 DM', 'jenis_kendaraan' => 'LIGHT TRUCK', 'vehicle_brand' => 'ISUZU',  'area' => 'WORKSHOP', 'payload_capacity' => 5,  'tyre_position_configuration_id' => $lt6w,  'total_tyre_position' => 6,  'tyre_company_id' => $this->companyId],
            ['kode_kendaraan' => 'DEMO-LT-002', 'no_polisi' => 'KT 2002 DM', 'jenis_kendaraan' => 'LIGHT TRUCK', 'vehicle_brand' => 'TOYOTA', 'area' => 'WORKSHOP', 'payload_capacity' => 3,  'tyre_position_configuration_id' => $lt6w,  'total_tyre_position' => 6,  'tyre_company_id' => $this->companyId],
        ];
        $vehicleIds = [];
        foreach ($kendaraan as $v) {
            $vehicle = MasterImportKendaraan::firstOrCreate(['kode_kendaraan' => $v['kode_kendaraan']], $v);
            $vehicleIds[$v['kode_kendaraan']] = $vehicle->id;
        }

        // =============================================
        // 11. Ban Demo
        // =============================================
        $this->command->info('🔵 Membuat Data Ban Demo...');

        // 11a. 20 ban stok di GUDANG PUSAT (status: New)
        $stockCount = 0;
        for ($i = 1; $i <= 20; $i++) {
            $brand = array_rand($this->brandIds);
            $otd = round(rand(155, 215) / 10, 1);
            $loc = ($i <= 14) ? $gudangPusatId : $gudangSiteAId;
            Tyre::firstOrCreate(
                ['serial_number' => 'DEMO-SN-' . str_pad($i, 4, '0', STR_PAD_LEFT)],
                [
                    'tyre_company_id'     => $this->companyId,
                    'tyre_brand_id'       => $this->brandIds[$brand],
                    'tyre_size_id'        => $this->sizeIds[array_rand($this->sizeIds)],
                    'tyre_pattern_id'     => $this->patternIds[array_rand($this->patternIds)],
                    'segment_name'        => 'Coal Hauling',
                    'ply_rating'          => '16',
                    'original_tread_depth'=> $otd,
                    'initial_tread_depth' => $otd,
                    'current_tread_depth' => $otd,
                    'price'               => rand(35, 60) * 100000,
                    'status'              => 'New',
                    'is_in_warehouse'     => true,
                    'current_location_id' => $loc,
                    'total_lifetime_km'   => 0,
                    'total_lifetime_hm'   => 0,
                ]
            );
            $stockCount++;
        }
        // Update stock count for locations
        TyreLocation::where('id', $gudangPusatId)->update(['current_stock' => 14]);
        TyreLocation::where('id', $gudangSiteAId)->update(['current_stock' => 6]);

        // 11b. Ban terpasang di kendaraan
        $this->command->info('   - Memasang ban ke kendaraan...');
        $installedCount = 0;
        $snCounter = 21;

        $vehicles = MasterImportKendaraan::where('tyre_company_id', $this->companyId)->get();
        foreach ($vehicles as $vehicle) {
            $positions = TyrePositionDetail::where('configuration_id', $vehicle->tyre_position_configuration_id)->orderBy('display_order')->get();

            foreach ($positions as $pos) {
                $brand = array_rand($this->brandIds);
                $otd   = round(rand(155, 200) / 10, 1);
                $rtd   = round($otd - rand(10, 70) / 10, 1);
                if ($rtd < 3) $rtd = round(rand(30, 50) / 10, 1);
                $lifetimeKm = rand(4000, 18000);
                $lifetimeHm = rand(200, 1200);
                $installDate = Carbon::now()->subDays(rand(20, 150));

                $sn = 'DEMO-SN-' . str_pad($snCounter, 4, '0', STR_PAD_LEFT);
                $tyre = Tyre::firstOrCreate(
                    ['serial_number' => $sn],
                    [
                        'tyre_company_id'      => $this->companyId,
                        'tyre_brand_id'        => $this->brandIds[$brand],
                        'tyre_size_id'         => $this->sizeIds[array_rand($this->sizeIds)],
                        'tyre_pattern_id'       => $this->patternIds[array_rand($this->patternIds)],
                        'segment_name'         => 'Coal Hauling',
                        'ply_rating'           => '16',
                        'original_tread_depth' => $otd,
                        'initial_tread_depth'  => $otd,
                        'current_tread_depth'  => $rtd,
                        'price'                => rand(35, 60) * 100000,
                        'status'               => 'Installed',
                        'is_in_warehouse'      => false,
                        'current_location_id'  => null,
                        'current_vehicle_id'   => $vehicle->id,
                        'current_position_id'  => $pos->id,
                        'total_lifetime_km'    => $lifetimeKm,
                        'total_lifetime_hm'    => $lifetimeHm,
                    ]
                );

                // Update position detail
                $pos->update(['tyre_id' => $tyre->id]);

                // Movement log: Installation
                TyreMovement::create([
                    'tyre_id'              => $tyre->id,
                    'vehicle_id'           => $vehicle->id,
                    'position_id'          => $pos->id,
                    'movement_type'        => 'Installation',
                    'movement_date'        => $installDate,
                    'odometer_reading'     => rand(10000, 50000),
                    'hour_meter_reading'   => rand(500, 3000),
                    'psi_reading'          => rand(90, 120),
                    'rtd_reading'          => $otd,
                    'tyre_company_id'      => $this->companyId,
                    'created_by'           => $adminUserId,
                    'notes'                => 'Demo: Pemasangan Awal',
                ]);

                $snCounter++;
                $installedCount++;
            }
        }

        // 11c. 5 ban Repaired / Workshop
        $this->command->info('   - Ban di Workshop (Repaired)...');
        for ($i = 1; $i <= 5; $i++) {
            $brand = array_rand($this->brandIds);
            $otd   = round(rand(155, 200) / 10, 1);
            $sn    = 'DEMO-REP-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            Tyre::firstOrCreate(
                ['serial_number' => $sn],
                [
                    'tyre_company_id'      => $this->companyId,
                    'tyre_brand_id'        => $this->brandIds[$brand],
                    'tyre_size_id'         => $this->sizeIds[array_rand($this->sizeIds)],
                    'tyre_pattern_id'      => $this->patternIds[array_rand($this->patternIds)],
                    'segment_name'         => 'Coal Hauling',
                    'ply_rating'           => '14',
                    'original_tread_depth' => $otd,
                    'initial_tread_depth'  => $otd,
                    'current_tread_depth'  => round(rand(50, 90) / 10, 1),
                    'price'                => rand(30, 50) * 100000,
                    'status'               => 'Repaired',
                    'is_in_warehouse'      => true,
                    'current_location_id'  => $workshopId,
                    'retread_count'        => 1,
                    'total_lifetime_km'    => rand(8000, 22000),
                    'total_lifetime_hm'    => rand(600, 1800),
                ]
            );
        }
        TyreLocation::where('id', $workshopId)->update(['current_stock' => 5]);

        // 11d. 8 ban Scrap / Disposal
        $this->command->info('   - Ban di Disposal (Scrap)...');
        for ($i = 1; $i <= 8; $i++) {
            $brand = array_rand($this->brandIds);
            $sn    = 'DEMO-SCRAP-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $otd   = round(rand(155, 200) / 10, 1);
            Tyre::firstOrCreate(
                ['serial_number' => $sn],
                [
                    'tyre_company_id'      => $this->companyId,
                    'tyre_brand_id'        => $this->brandIds[$brand],
                    'tyre_size_id'         => $this->sizeIds[array_rand($this->sizeIds)],
                    'tyre_pattern_id'      => $this->patternIds[array_rand($this->patternIds)],
                    'segment_name'         => 'Overburden',
                    'ply_rating'           => '16',
                    'original_tread_depth' => $otd,
                    'initial_tread_depth'  => $otd,
                    'current_tread_depth'  => round(rand(0, 15) / 10, 1),
                    'price'                => rand(30, 50) * 100000,
                    'status'               => 'Scrap',
                    'is_in_warehouse'      => true,
                    'current_location_id'  => $disposalId,
                    'retread_count'        => rand(0, 2),
                    'total_lifetime_km'    => rand(15000, 40000),
                    'total_lifetime_hm'    => rand(1000, 3500),
                ]
            );
        }
        TyreLocation::where('id', $disposalId)->update(['current_stock' => 8]);

        // =============================================
        // 12. Riwayat Pergerakan 6 Bulan
        // =============================================
        $this->command->info('📊 Membuat Riwayat Pergerakan 6 Bulan...');
        $allDemoTyres    = Tyre::where('tyre_company_id', $this->companyId)->pluck('id')->toArray();
        $allDemoVehicles = MasterImportKendaraan::where('tyre_company_id', $this->companyId)->get();
        $failureValues   = TyreFailureCode::pluck('id')->toArray();

        for ($month = 5; $month >= 1; $month--) {
            $monthStart = Carbon::now()->subMonths($month)->startOfMonth();

            // 4-8 Installation events per bulan
            for ($j = 0; $j < rand(4, 8); $j++) {
                $vehicle  = $allDemoVehicles->random();
                $position = TyrePositionDetail::where('configuration_id', $vehicle->tyre_position_configuration_id)->inRandomOrder()->first();
                $tyreId   = $allDemoTyres[array_rand($allDemoTyres)];
                if (!$position) continue;

                TyreMovement::create([
                    'tyre_id'            => $tyreId,
                    'vehicle_id'         => $vehicle->id,
                    'position_id'        => $position->id,
                    'movement_type'      => 'Installation',
                    'movement_date'      => $monthStart->copy()->addDays(rand(0, 25)),
                    'odometer_reading'   => rand(5000, 60000),
                    'hour_meter_reading' => rand(200, 4000),
                    'psi_reading'        => rand(90, 125),
                    'rtd_reading'        => round(rand(130, 200) / 10, 1),
                    'tyre_company_id'    => $this->companyId,
                    'created_by'         => $adminUserId,
                    'notes'              => "Demo Movement - {$monthStart->format('M Y')}",
                ]);
            }

            // 2-5 Removal events per bulan
            for ($j = 0; $j < rand(2, 5); $j++) {
                $vehicle  = $allDemoVehicles->random();
                $position = TyrePositionDetail::where('configuration_id', $vehicle->tyre_position_configuration_id)->inRandomOrder()->first();
                $tyreId   = $allDemoTyres[array_rand($allDemoTyres)];
                if (!$position) continue;

                $hasFailure   = rand(0, 1);
                $failureCodeId = $hasFailure ? $failureValues[array_rand($failureValues)] : null;

                TyreMovement::create([
                    'tyre_id'            => $tyreId,
                    'vehicle_id'         => $vehicle->id,
                    'position_id'        => $position->id,
                    'movement_type'      => 'Removal',
                    'target_status'      => $hasFailure ? 'Scrap' : 'Repaired',
                    'failure_code_id'    => $failureCodeId,
                    'movement_date'      => $monthStart->copy()->addDays(rand(5, 28)),
                    'odometer_reading'   => rand(15000, 80000),
                    'hour_meter_reading' => rand(800, 5000),
                    'psi_reading'        => rand(70, 115),
                    'rtd_reading'        => round(rand(15, 80) / 10, 1),
                    'running_km'         => rand(3000, 20000),
                    'running_hm'         => rand(150, 1500),
                    'tyre_company_id'    => $this->companyId,
                    'created_by'         => $adminUserId,
                    'notes'              => "Demo Removal - {$monthStart->format('M Y')}",
                ]);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // =============================================
        // Ringkasan
        // =============================================
        $this->command->newLine();
        $this->command->info('✅ Demo Data Seeder selesai!');
        $this->command->table(
            ['Item', 'Jumlah'],
            [
                ['Company Demo', '1 (PT MITRA TAMBANG DEMO)'],
                ['Akun Demo', '3 (password: demo1234)'],
                ['Brand', count($this->brandIds)],
                ['Ukuran Ban', count($this->sizeIds)],
                ['Pattern Ban', count($this->patternIds)],
                ['Lokasi/Gudang', count($this->locationIds)],
                ['Kendaraan', count($vehicleIds)],
                ['Ban Stok (Gudang)', '20'],
                ['Ban Terpasang', $installedCount],
                ['Ban Repaired', '5'],
                ['Ban Scrap', '8'],
                ['Riwayat Pergerakan', 'Sekitar 30-65 record (6 bulan)'],
            ]
        );
    }

    private function createOrGetConfig(string $name, string $code, int $totalPositions, array $positions): int
    {
        $config = TyrePositionConfiguration::updateOrCreate(
            ['code' => $code],
            [
                'name'             => $name,
                'description'      => "{$totalPositions} posisi ban ({$name})",
                'total_positions'  => $totalPositions,
                'total_spare'      => 0,
            ]
        );

        foreach ($positions as $pos) {
            TyrePositionDetail::updateOrCreate(
                ['configuration_id' => $config->id, 'position_code' => $pos['position_code']],
                $pos
            );
        }

        return $config->id;
    }
}
