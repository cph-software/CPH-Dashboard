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

        foreach ($items as $item) {
            try {
                $data = $item->data; // This is already an array due to Attribute casting in Model
                
                switch ($batch->module) {
                    case 'Master Tyre':
                    case 'Tyre Master':
                        $this->processTyreMaster($data);
                        break;
                    case 'Vehicle Master':
                    case 'Master Vehicle':
                        $this->processVehicleMaster($data);
                        break;
                    case 'Movement History':
                        $this->processMovementHistory($data);
                        break;
                    case 'Tyre Examination':
                        $this->processTyreExamination($data);
                        break;
                    case 'Tyre Brand':
                    case 'Brands':
                        $this->processTyreBrand($data);
                        break;
                    case 'Tyre Size':
                    case 'Sizes':
                        $this->processTyreSize($data);
                        break;
                    case 'Tyre Pattern':
                    case 'Patterns':
                        $this->processTyrePattern($data);
                        break;
                    case 'Failure Codes':
                        $this->processFailureCodes($data);
                        break;
                    case 'Locations':
                        $this->processLocations($data);
                        break;
                    case 'Segments':
                        $this->processSegments($data);
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

    private function processFailureCodes($data)
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

    private function processTyreBrand($data)
    {
        // Headers: brand_name
        $name = $data['brand_name'] ?? $data['brand'] ?? null;
        if (!$name) throw new \Exception("Nama Brand kosong");

        \App\Models\TyreBrand::firstOrCreate(['brand_name' => $name]);
    }

    private function processTyreSize($data)
    {
        // Headers: size, brand_name, type, std_otd, ply_rating
        $size = $data['size'] ?? null;
        if (!$size) throw new \Exception("Size kosong");

        $brandId = null;
        if (!empty($data['brand_name'])) {
            $brand = \App\Models\TyreBrand::firstOrCreate(['brand_name' => trim($data['brand_name'])]);
            $brandId = $brand->id;
        }

        \App\Models\TyreSize::updateOrCreate(
            ['size' => $size],
            [
                'tyre_brand_id' => $brandId,
                'type' => $data['type'] ?? 'Radial', // Default to Radial if not specified
                'std_otd' => $data['std_otd'] ?? 0,
                'ply_rating' => $data['ply_rating'] ?? 0
            ]
        );
    }

    private function processTyrePattern($data)
    {
        // Headers: pattern_name, brand
        $name = $data['pattern_name'] ?? $data['pattern'] ?? null;
        if (!$name) throw new \Exception("Nama Pattern kosong");

        $brandId = null;
        if (!empty($data['brand'])) {
            $brand = \App\Models\TyreBrand::firstOrCreate(['brand_name' => $data['brand']]);
            $brandId = $brand->id;
        }

        \App\Models\TyrePattern::updateOrCreate(
            ['name' => $name],
            ['tyre_brand_id' => $brandId]
        );
    }

    private function processTyreMaster($data)
    {
        // Headers: serial_number, brand_name, size_name, pattern_name, initial_rtd, location_name, segment_name, price, status
        $sn = $data['sn_ban'] ?? $data['serial_number'] ?? null;
        if (!$sn) throw new \Exception("Serial Number kosong.");

        // 1. Resolve Brand
        $brandId = null;
        $brandName = $data['brand_name'] ?? $data['brand'] ?? null;
        if (!empty($brandName)) {
            $brand = \App\Models\TyreBrand::firstOrCreate(['brand_name' => trim($brandName)]);
            $brandId = $brand->id;
        }

        // 2. Resolve Size (Mandatory in DB, so we auto-create if missing)
        $sizeId = null;
        $sizeName = $data['size_name'] ?? $data['size'] ?? null;
        if (!empty($sizeName)) {
            // Find size that matches both the name and the brand
            $size = \App\Models\TyreSize::where('size', $sizeName)
                ->where('tyre_brand_id', $brandId)
                ->first();
                
            if (!$size) {
                // Auto-create size with basic defaults to satisfy DB constraints
                $size = \App\Models\TyreSize::create([
                    'size' => $sizeName,
                    'tyre_brand_id' => $brandId,
                    'type' => 'Radial',
                    'std_otd' => (float)($data['initial_rtd'] ?? 0),
                    'ply_rating' => 0
                ]);
            }
            $sizeId = $size->id;
        }

        // 3. Resolve Pattern
        $patternId = null;
        $patternName = $data['pattern_name'] ?? $data['pattern'] ?? null;
        if (!empty($patternName)) {
            $pattern = \App\Models\TyrePattern::firstOrCreate(
                ['name' => trim($patternName)],
                ['tyre_brand_id' => $brandId]
            );
            $patternId = $pattern->id;
        }

        // 4. Resolve Location (Mandatory in DB)
        $locationId = null;
        $locationName = $data['location_name'] ?? $data['location'] ?? null;
        if (!empty($locationName)) {
            $location = \App\Models\TyreLocation::firstOrCreate(
                ['location_name' => trim($locationName)],
                ['location_type' => 'Warehouse']
            );
            $locationId = $location->id;
        }

        // 5. Resolve Segment (Nullable in DB)
        $segmentId = null;
        $segmentName = $data['segment_name'] ?? $data['segment'] ?? null;
        if (!empty($segmentName)) {
            $segment = \App\Models\TyreSegment::where('segment_name', trim($segmentName))->first();
            $segmentId = $segment ? $segment->id : null;
        }

        $initialRtd = $data['initial_rtd'] ?? $data['otd'] ?? $data['initial_tread_depth'] ?? 0;

        \App\Models\Tyre::updateOrCreate(
            ['serial_number' => $sn],
            [
                'tyre_brand_id' => $brandId,
                'tyre_size_id' => $sizeId,
                'tyre_pattern_id' => $patternId,
                'work_location_id' => $locationId,
                'tyre_segment_id' => $segmentId,
                'status' => $data['status'] ?? 'New',
                'initial_tread_depth' => $initialRtd,
                'current_tread_depth' => $data['current_tread_depth'] ?? $initialRtd,
                'price' => $data['price'] ?? 0
            ]
        );
    }

    private function processVehicleMaster($data)
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
                'tyre_unit_status' => $data['status'] ?? 'Active'
            ]
        );
    }

    private function processMovementHistory($data)
    {
        // Headers matching UI Guide: serial_number, kode_kendaraan, movement_type, movement_date, position_code, odometer, hm, rtd, psi, failure_code, remark
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
        if ($positionCode && $vehicle) {
            $configId = $vehicle->tyre_position_config_id;
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
            'movement_date' => ($data['movement_date'] ?? $data['tanggal']) ? \Carbon\Carbon::parse($data['movement_date'] ?? $data['tanggal']) : now(),
            'movement_type' => $data['movement_type'] ?? $data['tipe_pergerakan'] ?? 'Installation',
            'odometer_reading' => $data['odometer'] ?? 0,
            'hour_meter_reading' => $data['hm'] ?? 0,
            'rtd_reading' => $data['rtd'] ?? null,
            'psi_reading' => $data['psi'] ?? null,
            'failure_code_id' => $failCodeId,
            'remarks' => $data['remark'] ?? null,
            'created_by' => auth()->id()
        ]);
    }

    private function processTyreExamination($data)
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

    private function processLocations($data)
    {
        // Headers: location_name, location_type, capacity
        $name = $data['location_name'] ?? null;
        if (!$name) {
            throw new \Exception("Location name kosong");
        }

        $type = $data['location_type'] ?? 'Warehouse';
        if (!in_array($type, ['Warehouse', 'Service', 'Disposal'], true)) {
            throw new \Exception("location_type invalid untuk lokasi {$name}. Gunakan: Warehouse/Service/Disposal");
        }

        \App\Models\TyreLocation::updateOrCreate(
            ['location_name' => $name],
            [
                'location_type' => $type,
                'capacity' => isset($data['capacity']) && $data['capacity'] !== '' ? (int) $data['capacity'] : null,
            ]
        );
    }

    private function processSegments($data)
    {
        // Headers: segment_id, segment_name, location_name, terrain_type, status
        $segmentId = $data['segment_id'] ?? null;
        $segmentName = $data['segment_name'] ?? null;
        if (!$segmentId || !$segmentName) {
            throw new \Exception("segment_id atau segment_name kosong");
        }

        $locationName = $data['location_name'] ?? null;
        $locationId = null;
        if ($locationName) {
            $location = \App\Models\TyreLocation::where('location_name', $locationName)->first();
            if (!$location) {
                throw new \Exception("Lokasi '{$locationName}' tidak ditemukan untuk segment {$segmentId}");
            }
            $locationId = $location->id;
        }

        $terrain = $data['terrain_type'] ?? 'Muddy';
        if (!in_array($terrain, ['Muddy', 'Rocky', 'Asphalt'], true)) {
            throw new \Exception("terrain_type invalid untuk segment {$segmentId}. Gunakan: Muddy/Rocky/Asphalt");
        }

        $status = $data['status'] ?? 'Active';
        if (!in_array($status, ['Active', 'Inactive'], true)) {
            throw new \Exception("status invalid untuk segment {$segmentId}. Gunakan: Active/Inactive");
        }

        \App\Models\TyreSegment::updateOrCreate(
            ['segment_id' => $segmentId],
            [
                'segment_name' => $segmentName,
                'tyre_location_id' => $locationId,
                'terrain_type' => $terrain,
                'status' => $status,
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
