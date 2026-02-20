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
        // Headers: size
        $size = $data['size'] ?? null;
        if (!$size) throw new \Exception("Size kosong");

        \App\Models\TyreSize::firstOrCreate(['size' => $size]);
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
        // Headers: sn_ban, brand, size, pattern, status, otd, price
        // Mapping from CSV headers (lowercase, snake_case)
        $sn = $data['sn_ban'] ?? $data['serial_number'] ?? null;
        if (!$sn) throw new \Exception("Serial Number kosong.");

        // Find or Create Relations
        $brandId = null;
        if (!empty($data['brand'])) {
            $brand = \App\Models\TyreBrand::firstOrCreate(['brand_name' => $data['brand']]);
            $brandId = $brand->id;
        }

        $sizeId = null;
        if (!empty($data['size'])) {
            $size = \App\Models\TyreSize::firstOrCreate(['size' => $data['size']]);
            $sizeId = $size->id;
        }

        $patternId = null;
        if (!empty($data['pattern'])) {
            $pattern = \App\Models\TyrePattern::firstOrCreate(['name' => $data['pattern']]);
            $patternId = $pattern->id;
        }

        \App\Models\Tyre::updateOrCreate(
            ['serial_number' => $sn],
            [
                'brand_id' => $brandId,
                'size_id' => $sizeId,
                'pattern_id' => $patternId,
                'status' => $data['status'] ?? 'Sparre',
                'initial_tread_depth' => $data['otd'] ?? $data['initial_tread_depth'] ?? 0,
                // If existing, don't overwrite current_tread_depth unless needed
                'current_tread_depth' => $data['current_tread_depth'] ?? ($data['otd'] ?? 0),
                'price' => $data['price'] ?? 0
            ]
        );
    }

    private function processVehicleMaster($data)
    {
        // Headers: unit_code, type, layout, total_positions, status
        $code = $data['unit_code'] ?? $data['kode_kendaraan'] ?? null;
        if (!$code) throw new \Exception("Kode Unit kosong.");

        $layoutId = null;
        if (!empty($data['layout'])) {
            $layout = \App\Models\TyrePosition::where('name', $data['layout'])->first();
            $layoutId = $layout ? $layout->id : null;
        }

        \App\Models\MasterImportKendaraan::updateOrCreate(
            ['kode_kendaraan' => $code],
            [
                'jenis_kendaraan' => $data['type'] ?? $data['jenis_kendaraan'] ?? 'Unknown',
                'tyre_position_config_id' => $layoutId,
                'total_tyre_position' => $data['total_positions'] ?? $data['total_ban'] ?? 0,
                'tyre_unit_status' => $data['status'] ?? 'Active'
            ]
        );
    }

    private function processMovementHistory($data)
    {
        // Headers: tanggal, sn_ban, unit, posisi, tipe_pergerakan, odometer, hm, rtd, psi, failure_code, remark
        $sn = $data['sn_ban'] ?? null;
        if (!$sn) throw new \Exception("SN Ban kosong");
        
        $tyre = \App\Models\Tyre::where('serial_number', $sn)->first();
        if (!$tyre) throw new \Exception("Ban $sn tidak ditemukan di Master Ban.");

        $unitCode = $data['unit'] ?? null;
        $vehicle = \App\Models\MasterImportKendaraan::where('kode_kendaraan', $unitCode)->first();
        if (!$vehicle && $unitCode) throw new \Exception("Unit $unitCode tidak ditemukan.");

        // Find Position
        $positionCode = $data['posisi'] ?? null;
        $positionId = null;
        if ($positionCode && $vehicle) {
            // Complex lookup: Find position ID based on vehicle config
            $configId = $vehicle->tyre_position_config_id;
            if ($configId) {
                // Correctly filter by configuration_id and (code OR name)
                $posDetail = \App\Models\TyrePositionDetail::where('configuration_id', $configId)
                    ->where(function($q) use ($positionCode) {
                        $q->where('position_code', $positionCode) // code like 'FL'
                          ->orWhere('position_name', $positionCode); // name like 'Front Left'
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
            'movement_date' => $data['tanggal'] ? \Carbon\Carbon::parse($data['tanggal']) : now(),
            'movement_type' => $data['tipe_pergerakan'] ?? 'Installation', // Default
            'odometer_reading' => $data['odometer'] ?? 0,
            'hour_meter_reading' => $data['hm'] ?? 0,
            'rtd_reading' => $data['rtd'] ?? null,
            'psi_reading' => $data['psi'] ?? null,
            'failure_code_id' => $failCodeId,
            'remarks' => $data['remark'] ?? null,
            'created_by' => auth()->id()
        ]);
        
        // Note: We do NOT update Tyre status/current position here to prevent messing up 
        // current state with out-of-order historical data import.
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
