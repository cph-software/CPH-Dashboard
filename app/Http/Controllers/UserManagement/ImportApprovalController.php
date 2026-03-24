<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImportApprovalController extends Controller
{
    public function index()
    {
        $batches = \App\Models\ImportBatch::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user-management.import-approval.index', compact('batches'));
    }

    public function show($id)
    {
        $batch = \App\Models\ImportBatch::with(['user', 'items'])->findOrFail($id);
        return view('user-management.import-approval.show', compact('batch'));
    }

    public function approve($id)
    {
        $batch = \App\Models\ImportBatch::findOrFail($id);
        
        if ($batch->status !== 'Pending') {
            return redirect()->back()->with('error', 'Batch ini sudah diproses.');
        }

        $batch->update([
            'status' => 'Approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        // Process Data immediately (for now, later can be queued)
        try {
            $this->processData($batch);
            
            setLogActivity(auth()->id(), "Menyetujui dan memproses import batch #$id ({$batch->module})", [
                'module' => 'Import Approval',
                'batch_id' => $id,
                'status' => 'Success'
            ]);

            return redirect()->route('import-approval.index')->with('success', 'Batch berhasil disetujui dan data telah diproses.');
        } catch (\Exception $e) {
            $batch->update(['status' => 'Failed', 'notes' => $e->getMessage()]);
            
            return redirect()->route('import-approval.index')->with('error', 'Batch disetujui tapi gagal memproses data: ' . $e->getMessage());
        }
    }

    private function processData($batch)
    {
        $items = $batch->items()->where('status', 'Pending')->get();
        $successCount = 0;
        
        // Ambil ID Perusahaan dari user yang mengupload file
        $uploaderCompanyId = $batch->user->tyre_company_id;

        foreach ($items as $item) {
            try {
                $data = $item->data;
                
                switch ($batch->module) {
                    case 'Master Tyre':
                    case 'Tyre Master':
                        $this->processTyreMaster($data, $uploaderCompanyId);
                        break;
                    case 'Vehicle Master':
                    case 'Master Vehicle':
                        $this->processVehicleMaster($data, $uploaderCompanyId);
                        break;
                    case 'Movement History':
                        $this->processMovementHistory($data, $uploaderCompanyId);
                        break;
                    case 'Tyre Examination':
                        $this->processTyreExamination($data, $uploaderCompanyId);
                        break;
                    case 'Tyre Brand':
                    case 'Brands':
                        $this->processTyreBrand($data, $uploaderCompanyId);
                        break;
                    case 'Tyre Size':
                    case 'Sizes':
                        $this->processTyreSize($data, $uploaderCompanyId);
                        break;
                    case 'Tyre Pattern':
                    case 'Patterns':
                        $this->processTyrePattern($data, $uploaderCompanyId);
                        break;
                    case 'Failure Codes':
                        $this->processFailureCodes($data, $uploaderCompanyId);
                        break;
                    case 'Locations':
                        $this->processLocations($data, $uploaderCompanyId);
                        break;
                    case 'Segments':
                        $this->processSegments($data, $uploaderCompanyId);
                        break;
                    default:
                        throw new \Exception("Modul import tidak dikenali: " . $batch->module);
                }

                $item->update(['status' => 'Success']);
                $successCount++;
            } catch (\Exception $e) {
                $item->update(['status' => 'Failed', 'error_message' => $e->getMessage()]);
            }
        }

        $batch->update(['processed_rows' => $successCount]);
    }

    private function processFailureCodes($data, $uploaderCompanyId = null)
    {
        // Headers: failure_code, failure_name, default_category
        $code = $data['failure_code'] ?? null;
        if (!$code) throw new \Exception("Failure Code kosong");

        \App\Models\TyreFailureCode::updateOrCreate(
            ['failure_code' => $code],
            [
                'failure_name' => $data['failure_name'] ?? 'Unknown',
                'default_category' => $data['default_category'] ?? 'Other'
            ]
        );
    }

    private function processTyreBrand($data, $uploaderCompanyId = null)
    {
        // Headers: brand_name
        $name = $data['brand_name'] ?? $data['brand'] ?? null;
        if (!$name) throw new \Exception("Nama Brand kosong");

        $brand = \App\Models\TyreBrand::firstOrCreate(['brand_name' => $name]);

        if ($uploaderCompanyId) {
            $brand->companies()->syncWithoutDetaching([$uploaderCompanyId]);
        }
    }

    private function processTyreSize($data, $uploaderCompanyId = null)
    {
        // Headers: size, brand_name, type, std_otd, ply_rating
        $sizeStr = $data['size'] ?? null;
        if (!$sizeStr) throw new \Exception("Size kosong");

        $brandId = null;
        if (!empty($data['brand_name'])) {
            $brand = \App\Models\TyreBrand::firstOrCreate(['brand_name' => trim($data['brand_name'])]);
            $brandId = $brand->id;
            if ($uploaderCompanyId) {
                $brand->companies()->syncWithoutDetaching([$uploaderCompanyId]);
            }
        }

        $size = \App\Models\TyreSize::updateOrCreate(
            ['size' => $sizeStr],
            [
                'tyre_brand_id' => $brandId,
                'type' => $data['type'] ?? 'Radial', // Default to Radial if not specified
                'std_otd' => $data['std_otd'] ?? 0,
                'ply_rating' => $data['ply_rating'] ?? 0
            ]
        );

        if ($uploaderCompanyId) {
            $size->companies()->syncWithoutDetaching([$uploaderCompanyId]);
        }
    }

    private function processTyrePattern($data, $uploaderCompanyId = null)
    {
        // Headers: pattern_name, brand
        $name = $data['pattern_name'] ?? $data['pattern'] ?? null;
        if (!$name) throw new \Exception("Nama Pattern kosong");

        $brandId = null;
        if (!empty($data['brand'])) {
            $brand = \App\Models\TyreBrand::firstOrCreate(['brand_name' => $data['brand']]);
            $brandId = $brand->id;
            if ($uploaderCompanyId) {
                $brand->companies()->syncWithoutDetaching([$uploaderCompanyId]);
            }
        }

        $pattern = \App\Models\TyrePattern::updateOrCreate(
            ['name' => $name],
            ['tyre_brand_id' => $brandId]
        );

        if ($uploaderCompanyId) {
            $pattern->companies()->syncWithoutDetaching([$uploaderCompanyId]);
        }
    }

    private function processTyreMaster($data, $uploaderCompanyId)
    {
        // New Headers support: serial_number, brand, size, pattern, segment, initial_rtd, status, price, in_warehouse
        $sn = $data['serial_number'] ?? $data['sn_ban'] ?? null;
        if (!$sn) throw new \Exception("Serial Number kosong.");

        // 1. Resolve Brand
        $brandId = null;
        $brandName = $data['brand'] ?? $data['brand_name'] ?? null;
        if (!empty($brandName)) {
            $brand = \App\Models\TyreBrand::firstOrCreate(['brand_name' => strtoupper(trim($brandName))], ['status' => 'Active']);
            $brandId = $brand->id;
        }

        // 2. Resolve Size
        $sizeId = null;
        $sizeName = $data['size'] ?? $data['size_name'] ?? null;
        if (!empty($sizeName)) {
            $size = \App\Models\TyreSize::firstOrCreate(
                ['size' => strtoupper(trim($sizeName)), 'tyre_brand_id' => $brandId]
            );
            $sizeId = $size->id;
        }

        // 3. Resolve Pattern
        $patternId = null;
        $patternName = $data['pattern'] ?? $data['pattern_name'] ?? null;
        if (!empty($patternName)) {
            $pattern = \App\Models\TyrePattern::firstOrCreate(
                ['name' => strtoupper(trim($patternName)), 'tyre_brand_id' => $brandId]
            );
            $patternId = $pattern->id;
        }

        $initialRtd = (float)($data['initial_rtd'] ?? $data['otd'] ?? 0);
        $inWarehouse = ($data['in_warehouse'] ?? $data['warehouse'] ?? 'Yes') == 'Yes' ? 1 : 0;

        \App\Models\Tyre::updateOrCreate(
            ['serial_number' => $sn],
            [
                'tyre_brand_id' => $brandId,
                'tyre_size_id' => $sizeId,
                'tyre_pattern_id' => $patternId,
                'segment_name' => $data['segment'] ?? $data['segment_name'] ?? null,
                'is_in_warehouse' => $inWarehouse,
                'status' => $data['status'] ?? 'New',
                'initial_tread_depth' => $initialRtd,
                'current_tread_depth' => (float)($data['current_rtd'] ?? $initialRtd),
                'price' => (float)($data['price'] ?? 0),
                'ply_rating' => (int)($data['ply_rating'] ?? 0),
                'original_tread_depth' => $initialRtd,
                'tyre_company_id' => $uploaderCompanyId
            ]
        );
    }

    private function processVehicleMaster($data, $uploaderCompanyId)
    {
        // Headers matching UI Guide: kode_kendaraan, no_polisi, model_kendaraan, brand_kendaraan, site_location, curb_weight, payload_capacity, segment
        $code = $data['kode_kendaraan'] ?? $data['unit_code'] ?? null;
        if (!$code) throw new \Exception("Kode Unit (kode_kendaraan) kosong.");

        $layoutId = null;
        if (!empty($data['layout'])) {
            $layout = \App\Models\TyrePosition::where('name', $data['layout'])->first();
            $layoutId = $layout ? $layout->id : null;
        }

        // 1. Resolve Segment (Auto-create if missing)
        $segmentId = null;
        $segmentName = $data['segment'] ?? $data['segment_name'] ?? null;
        if (!empty($segmentName)) {
            $segment = \App\Models\TyreSegment::where('segment_name', trim($segmentName))
                ->orWhere('segment_id', trim($segmentName))
                ->first();
                
            if (!$segment) {
                $segment = \App\Models\TyreSegment::create([
                    'segment_id' => strtoupper(str_replace(' ', '_', trim($segmentName))),
                    'segment_name' => trim($segmentName),
                    'status' => 'Active'
                ]);
            }
            $segmentId = $segment->id;
        }

        // 2. Resolve Site Location (Populate 'area' column)
        $area = $data['site_location'] ?? $data['area'] ?? $data['site'] ?? 'Unknown';
        if ($area !== 'Unknown') {
            // Ensure location exists in tyre_locations table too for consistency
            \App\Models\TyreLocation::firstOrCreate(
                ['location_name' => trim($area)],
                ['location_type' => 'Service', 'capacity' => 0]
            );
        }

        \App\Models\MasterImportKendaraan::updateOrCreate(
            ['kode_kendaraan' => $code],
            [
                'no_polisi' => $data['no_polisi'] ?? null,
                'jenis_kendaraan' => $data['model_kendaraan'] ?? $data['type'] ?? 'Unknown',
                'vehicle_brand' => $data['brand_kendaraan'] ?? $data['vehicle_brand'] ?? null,
                'curb_weight' => $data['curb_weight'] ?? null,
                'payload_capacity' => $data['payload_capacity'] ?? null,
                'area' => trim($area),
                'operational_segment_id' => $segmentId,
                'tyre_position_configuration_id' => $layoutId,
                'total_tyre_position' => $data['total_positions'] ?? $data['total_ban'] ?? 0,
                'tyre_unit_status' => $data['status'] ?? 'Active',
                'tyre_company_id' => $uploaderCompanyId
            ]
        );
    }

    private function processMovementHistory($data, $uploaderCompanyId)
    {
        // Headers matching UI Guide: serial_number, kode_kendaraan, movement_type, movement_date, position_code, odometer, hm, rtd, psi, failure_code, target_status, remark
        $sn = $data['serial_number'] ?? $data['sn_ban'] ?? null;
        if (!$sn) throw new \Exception("Serial Number (serial_number) kosong");
        
        $tyre = \App\Models\Tyre::where('serial_number', $sn)->first();
        if (!$tyre) throw new \Exception("Ban $sn tidak ditemukan di Master Ban.");

        $unitCode = $data['kode_kendaraan'] ?? $data['unit'] ?? null;
        $vehicle = \App\Models\MasterImportKendaraan::where('kode_kendaraan', $unitCode)->first();
        if (!$vehicle && $unitCode) throw new \Exception("Unit $unitCode tidak ditemukan.");

        // Find Position
        $positionCode = $data['position_code'] ?? $data['posisi'] ?? null;
        $positionId = null;
        $posDetail = null;
        if ($positionCode && $vehicle) {
            $configId = $vehicle->tyre_position_configuration_id;
            if ($configId) {
                $posDetail = \App\Models\TyrePositionDetail::where('configuration_id', $configId)
                    ->where(function($q) use ($positionCode) {
                        $q->where('position_code', $positionCode)
                          ->orWhere('position_name', $positionCode);
                    })
                    ->first();
                $positionId = $posDetail ? $posDetail->id : null;
            }
        }

        $type = $data['movement_type'] ?? $data['tipe_pergerakan'] ?? 'Installation';
        $moveDate = ($data['movement_date'] ?? $data['tanggal']) ? \Carbon\Carbon::parse($data['movement_date'] ?? $data['tanggal']) : now();
        $odo = $data['odometer'] ?? $data['km'] ?? 0;
        $hm = $data['hm'] ?? 0;

        $kmDiff = 0;
        $hmDiff = 0;

        // Perform Installation/Removal Logic
        if ($type === 'Installation') {
            if (!$vehicle) throw new \Exception("Pemasangan memerlukan Unit Code.");
            if (!$posDetail) throw new \Exception("Posisi $positionCode tidak valid untuk unit $unitCode.");

            $tyre->update([
                'current_vehicle_id' => $vehicle->id,
                'current_position_id' => $posDetail->id,
                'status' => 'Installed',
                'current_tread_depth' => $data['rtd'] ?? $tyre->current_tread_depth
            ]);
            $posDetail->update(['tyre_id' => $tyre->id]);
        } else if ($type === 'Removal') {
            // Calculate Lifetime if there was a previous installation
            $lastInstallation = \App\Models\TyreMovement::where('tyre_id', $tyre->id)
                ->where('movement_type', 'Installation')
                ->orderBy('movement_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($lastInstallation) {
                $kmDiff = (float)$odo - (float)$lastInstallation->odometer_reading;
                $hmDiff = (float)$hm - (float)$lastInstallation->hour_meter_reading;
                if ($kmDiff < 0) $kmDiff = 0;
                if ($hmDiff < 0) $hmDiff = 0;
            }

            $tyre->update([
                'current_vehicle_id' => null,
                'current_position_id' => null,
                'status' => $data['target_status'] ?? 'Repaired',
                'total_lifetime_km' => ($tyre->total_lifetime_km ?? 0) + $kmDiff,
                'total_lifetime_hm' => ($tyre->total_lifetime_hm ?? 0) + $hmDiff,
                'current_tread_depth' => $data['rtd'] ?? $tyre->current_tread_depth
            ]);

            if ($posDetail && $posDetail->tyre_id == $tyre->id) {
                $posDetail->update(['tyre_id' => null]);
            }
        }

        // Find Failure Code
        $failCodeStr = $data['failure_code'] ?? null;
        $failCodeId = null;
        if ($failCodeStr) {
            $failCode = \App\Models\TyreFailureCode::where('failure_code', $failCodeStr)->first();
            $failCodeId = $failCode ? $failCode->id : null;
        }

        \App\Models\TyreMovement::create([
            'tyre_id' => $tyre->id,
            'vehicle_id' => $vehicle ? $vehicle->id : null,
            'position_id' => $positionId,
            'movement_date' => $moveDate,
            'movement_type' => $type,
            'odometer_reading' => $odo,
            'hour_meter_reading' => $hm,
            'rtd_reading' => $data['rtd'] ?? null,
            'psi_reading' => $data['psi'] ?? null,
            'running_km' => $kmDiff,
            'running_hm' => $hmDiff,
            'failure_code_id' => $failCodeId,
            'target_status' => $data['target_status'] ?? null,
            'remarks' => $data['remark'] ?? $data['notes'] ?? null,
            'created_by' => auth()->id()
        ]);
    }

    private function processTyreExamination($data, $uploaderCompanyId = null)
    {
        // Import Examination Headers only as per current export structure
        $date = $data['tanggal'] ?? null;
        $unit = $data['unit'] ?? null;
        if (!$date || !$unit) throw new \Exception("Tanggal atau Unit kosong.");

        $vehicle = \App\Models\MasterImportKendaraan::where('kode_kendaraan', $unit)->first();
        if (!$vehicle) throw new \Exception("Unit $unit tidak ditemukan.");

        \App\Models\TyreExamination::create([
            'examination_date' => \Carbon\Carbon::parse($date),
            'vehicle_id' => $vehicle->id,
            'odometer' => $data['odometer'] ?? 0,
            'tyre_man' => $data['tyre_man'] ?? auth()->user()->name,
            'status' => $data['status'] ?? 'Draft'
        ]);
        // Details are not imported in this simple version
    }

    private function processLocations($data, $uploaderCompanyId = null)
    {
        // Headers: location_name, location_type, capacity
        $name = $data['location_name'] ?? null;
        if (!$name) {
            throw new \Exception("Location name kosong");
        }

        $rawType = trim($data['location_type'] ?? 'Unknown');
        $type = ucfirst(strtolower($rawType));
        // if (!in_array($type, ['Warehouse', 'Service', 'Disposal'], true)) {
        //     $type = 'Warehouse'; // Fallback to avoid error
        // }

        $capacity = isset($data['capacity']) && $data['capacity'] !== '' ? (int) $data['capacity'] : 0;

        \App\Models\TyreLocation::updateOrCreate(
            ['location_name' => trim($name)],
            [
                'location_type' => $type,
                'capacity' => $capacity,
                'tyre_company_id' => $uploaderCompanyId,
            ]
        );
    }

    private function processSegments($data, $uploaderCompanyId = null)
    {
        // Headers: segment_id, segment_name, location_name, terrain_type, status
        $segmentId = trim($data['segment_id'] ?? null);
        $segmentName = trim($data['segment_name'] ?? null);
        if (!$segmentId || !$segmentName) {
            throw new \Exception("segment_id atau segment_name kosong");
        }

        $locationName = trim($data['location_name'] ?? null);
        $locationId = null;
        if ($locationName) {
            $location = \App\Models\TyreLocation::firstOrCreate(
                ['location_name' => $locationName],
                ['location_type' => 'Service', 'capacity' => 0]
            );
            $locationId = $location->id;
        }

        $rawTerrain = trim($data['terrain_type'] ?? 'Unknown');
        $terrain = ucfirst(strtolower($rawTerrain));
        // if (!in_array($terrain, ['Muddy', 'Rocky', 'Asphalt'], true)) {
        //     $terrain = 'Muddy';
        // }

        $rawStatus = trim($data['status'] ?? 'Active');
        $status = ucfirst(strtolower($rawStatus));
        if (!in_array($status, ['Active', 'Inactive'], true)) {
            $status = 'Active';
        }

        \App\Models\TyreSegment::updateOrCreate(
            ['segment_id' => $segmentId],
            [
                'segment_name' => $segmentName,
                'tyre_location_id' => $locationId,
                'terrain_type' => $terrain,
                'status' => $status,
                'tyre_company_id' => $uploaderCompanyId,
            ]
        );
    }

    public function reject(Request $request, $id)
    {
        $batch = \App\Models\ImportBatch::findOrFail($id);
        
        $batch->update([
            'status' => 'Rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => $request->notes
        ]);

        setLogActivity(auth()->id(), "Menolak import batch #$id ({$batch->module})", [
            'module' => 'Import Approval',
            'batch_id' => $id,
            'reason' => $request->notes
        ]);

        return redirect()->route('import-approval.index')->with('warning', 'Batch telah ditolak.');
    }
}
