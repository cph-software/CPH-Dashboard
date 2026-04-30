<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportApprovalController extends Controller
{
    private $brandCache = [];
    private $sizeCache = [];
    private $patternCache = [];
    private $tyreCache = [];
    private $vehicleCache = [];

    /**
     * Get a company-scoped query for ImportBatch.
     * Super Admin (role_id 1) sees ALL batches.
     * Other roles only see batches uploaded by users from the SAME company.
     */
    private function scopedQuery()
    {
        $user = auth()->user();
        $query = \App\Models\ImportBatch::query();

        // Super Admin bypass
        if ($user->role_id == 1 || $user->tyre_company_id == 1) {
            // Respect company filter dropdown for Super Admin
            if (session()->has('active_company_id')) {
                $companyId = session('active_company_id');
                $query->whereHas('user', function ($q) use ($companyId) {
                    $q->where('tyre_company_id', $companyId);
                });
            }
            return $query;
        }

        // Non-admin: only see batches from same company
        $companyId = $user->tyre_company_id;
        $query->whereHas('user', function ($q) use ($companyId) {
            $q->where('tyre_company_id', $companyId);
        });

        return $query;
    }

    public function index()
    {
        $batches = $this->scopedQuery()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user-management.import-approval.index', compact('batches'));
    }

    public function show($id)
    {
        $batch = $this->scopedQuery()
            ->with(['user', 'items'])
            ->findOrFail($id);

        return view('user-management.import-approval.show', compact('batch'));
    }

    public function approve($id)
    {
        $batch = $this->scopedQuery()->findOrFail($id);
        
        $pendingCount = $batch->items()->where('status', 'Pending')->count();
        if ($batch->status !== 'Pending' && $pendingCount === 0) {
            return redirect()->back()->with('error', 'Batch ini sudah diproses dan tidak ada data Pending.');
        }

        // Process Data immediately (for now, later can be queued)
        try {
            $this->processData($batch);
            
            // Check if all items are processed (no more pending)
            $remainingPending = $batch->items()->where('status', 'Pending')->count();
            if ($remainingPending === 0) {
                $batch->update([
                    'status' => 'Approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now()
                ]);
                
                // Clear all dashboard caches to reflect new data
                \Illuminate\Support\Facades\Cache::flush();
                
                $msg = 'Batch berhasil disetujui sepenuhnya dan semua data telah diproses.';
            } else {
                $msg = 'Sebagian data berhasil disetujui. Terdapat ' . $remainingPending . ' baris yang masih perlu perbaikan dan berstatus Pending.';
            }

            // --- Send Notification to Submitter ---
            try {
                if ($batch->user) {
                    $approverName = auth()->user()->display_name;
                    $actionUrl = route('import-approval.show', $batch->id); // Can point to history or same show page
                    $statusName = ($remainingPending === 0) ? 'Approved' : 'Partially Approved';
                    \Illuminate\Support\Facades\Notification::send($batch->user, new \App\Notifications\ApprovalStatusNotification('Import ' . $batch->module, $statusName, $approverName, $actionUrl));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send Import Approved Notification: " . $e->getMessage());
            }

            setLogActivity(auth()->id(), "Menyetujui dan memproses import batch #$id ({$batch->module})", [
                'module' => 'Import Approval',
                'batch_id' => $id,
                'status' => 'Success',
                'remainingPending' => $remainingPending
            ]);

            return redirect()->route('import-approval.index')->with('success', $msg);
        } catch (\Exception $e) {
            $batch->update(['status' => 'Failed', 'notes' => $e->getMessage()]);
            
            return redirect()->route('import-approval.index')->with('error', 'Batch disetujui tapi gagal memproses data: ' . $e->getMessage());
        }
    }

    private function processData($batch)
    {
        // [P3] Batas wajar: 5 menit dan 512MB. Cukup untuk ribuan baris.
        // Jika file >10.000 baris, sisa yang belum diproses tetap Pending.
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $successCount = $batch->processed_rows ?? 0;
        
        // [P7] Ambil ID Perusahaan dari batch (saat diupload) atau fallback ke user
        $uploaderCompanyId = $batch->tyre_company_id ?? $batch->user->tyre_company_id;

        // Gunakan chunkById(200) agar RAM tidak membengkak + aman dari bug "skipped rows" 
        $batch->items()->where('status', 'Pending')->chunkById(200, function ($items) use ($batch, $uploaderCompanyId, &$successCount) {
            foreach ($items as $item) {
                try {
                    $data = $item->data;
                    
                    // Skip if invalid (Partial Approval Logic) for Movement History
                    if ($batch->module === 'Movement History') {
                        $isValid = $data['_validation']['is_valid'] ?? true;
                        if (!$isValid) {
                            continue; // Biarkan tetap Pending di keranjang
                        }
                    }

                    // [P1] Setiap baris dibungkus dalam transaction.
                    // Jika 1 baris gagal di tengah, seluruh operasi baris itu dibatalkan.
                    DB::beginTransaction();

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

                    DB::commit();
                    $item->update(['status' => 'Success']);
                    $successCount++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $item->update(['status' => 'Failed', 'error_message' => $e->getMessage()]);
                }
            }
        });

        $batch->update(['processed_rows' => $successCount]);

        // [P10] Log hasil proses ke Activity Log
        $failedCount = $batch->items()->where('status', 'Failed')->count();
        $pendingCount = $batch->items()->where('status', 'Pending')->count();
        setLogActivity(auth()->id(), "Memproses import batch #{$batch->id} ({$batch->module}): {$successCount} sukses, {$failedCount} gagal, {$pendingCount} pending", [
            'module' => 'Import Approval',
            'batch_id' => $batch->id,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'pending_count' => $pendingCount,
        ]);
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
            if ($uploaderCompanyId) {
                $brand->companies()->syncWithoutDetaching([$uploaderCompanyId]);
            }
        }

        // 2. Resolve Size
        $sizeId = null;
        $sizeName = $data['size'] ?? $data['size_name'] ?? null;
        if (!empty($sizeName)) {
            $sizeStdOtd = (float)($data['std_otd'] ?? $data['initial_rtd'] ?? $data['otd'] ?? 0);
            $sizePlyRating = (int)($data['ply_rating'] ?? 0);

            $size = \App\Models\TyreSize::firstOrCreate(
                ['size' => strtoupper(trim($sizeName)), 'tyre_brand_id' => $brandId],
                ['std_otd' => $sizeStdOtd, 'ply_rating' => $sizePlyRating]
            );
            
            if (!$size->wasRecentlyCreated && ($size->std_otd == 0 || $size->ply_rating == 0)) {
                if ($size->std_otd == 0 && $sizeStdOtd > 0) $size->std_otd = $sizeStdOtd;
                if ($size->ply_rating == 0 && $sizePlyRating > 0) $size->ply_rating = $sizePlyRating;
                if ($size->isDirty()) $size->save();
            }
            
            $sizeId = $size->id;
            if ($uploaderCompanyId) {
                $size->companies()->syncWithoutDetaching([$uploaderCompanyId]);
            }
        }

        // 3. Resolve Pattern
        $patternId = null;
        $patternName = $data['pattern'] ?? $data['pattern_name'] ?? null;
        if (!empty($patternName)) {
            $pattern = \App\Models\TyrePattern::firstOrCreate(
                ['name' => strtoupper(trim($patternName)), 'tyre_brand_id' => $brandId]
            );
            $patternId = $pattern->id;
            if ($uploaderCompanyId) {
                $pattern->companies()->syncWithoutDetaching([$uploaderCompanyId]);
            }
        }

        $initialRtd = (float)($data['initial_rtd'] ?? $data['otd'] ?? 0);
        $inWarehouse = ($data['in_warehouse'] ?? $data['warehouse'] ?? 'Yes') == 'Yes' ? 1 : 0;
        
        // 4. Resolve Location/Warehouse ID
        $locationId = null;
        $locationName = $data['location'] ?? $data['location_name'] ?? $data['warehouse_name'] ?? null;
        if (!empty($locationName)) {
            $location = \App\Models\TyreLocation::firstOrCreate(
                ['location_name' => strtoupper(trim($locationName))],
                ['location_type' => 'Warehouse', 'capacity' => 0]
            );
            $locationId = $location->id;
        }

        $tyre = \App\Models\Tyre::updateOrCreate(
            ['serial_number' => $sn, 'tyre_company_id' => $uploaderCompanyId],
            [
                'tyre_brand_id' => $brandId,
                'tyre_size_id' => $sizeId,
                'tyre_pattern_id' => $patternId,
                'segment_name' => $data['segment'] ?? $data['segment_name'] ?? null,
                'is_in_warehouse' => $inWarehouse,
                'current_location_id' => $locationId,
                'status' => $data['status'] ?? 'New',
                'initial_tread_depth' => $initialRtd,
                'current_tread_depth' => (float)($data['current_rtd'] ?? $initialRtd),
                'price' => (float)($data['price'] ?? 0),
                'ply_rating' => (int)($data['ply_rating'] ?? 0),
                'original_tread_depth' => $initialRtd,
                'tyre_company_id' => $uploaderCompanyId
            ]
        );
        
        // [P8] Hanya increment stok jika ban BARU dibuat (bukan update data existing)
        // Mencegah double-counting saat re-import file yang sama
        if ($inWarehouse && $locationId && $tyre->wasRecentlyCreated) {
            \App\Models\TyreLocation::where('id', $locationId)->increment('current_stock');
        }
    }

    private function processVehicleMaster($data, $uploaderCompanyId)
    {
        // Headers matching UI Guide: kode_kendaraan, no_polisi, model_kendaraan, brand_kendaraan, site_location, curb_weight, payload_capacity, segment
        $code = $data['kode_kendaraan'] ?? $data['unit_code'] ?? null;
        if (!$code) throw new \Exception("Kode Unit (kode_kendaraan) kosong.");

        $layoutId = null;
        $totalPositions = $data['total_positions'] ?? $data['total_ban'] ?? 0;
        if (!empty($data['layout'])) {
            $layoutName = trim($data['layout']);
            
            // Strategy 1: Exact match
            $layout = \App\Models\TyrePositionConfiguration::where('name', $layoutName)->first();
            
            // Strategy 2: Case-insensitive match
            if (!$layout) {
                $layout = \App\Models\TyrePositionConfiguration::whereRaw('UPPER(name) = ?', [strtoupper($layoutName)])->first();
            }
            
            // Strategy 3: Match by axle pattern e.g. (2+2+2)
            if (!$layout && preg_match('/\((\d+(?:\+\d+)+)\)/', $layoutName, $m)) {
                $pattern = $m[1];
                $layout = \App\Models\TyrePositionConfiguration::where('name', 'LIKE', "%({$pattern})%")->first();
            }
            
            if ($layout) {
                $layoutId = $layout->id;
                $totalPositions = $layout->total_positions;
            }
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
            ['kode_kendaraan' => $code, 'tyre_company_id' => $uploaderCompanyId],
            [
                'no_polisi' => $data['no_polisi'] ?? null,
                'jenis_kendaraan' => $data['model_kendaraan'] ?? $data['type'] ?? 'Unknown',
                'vehicle_brand' => $data['brand_kendaraan'] ?? $data['vehicle_brand'] ?? null,
                'curb_weight' => $data['curb_weight'] ?? null,
                'payload_capacity' => $data['payload_capacity'] ?? null,
                'area' => trim($area),
                'operational_segment_id' => $segmentId,
                'tyre_position_configuration_id' => $layoutId,
                'total_tyre_position' => $totalPositions,
                'tyre_unit_status' => $data['status'] ?? 'Active',
                'tyre_company_id' => $uploaderCompanyId
            ]
        );
    }

    private function processMovementHistory($data, $uploaderCompanyId)
    {
        // ============================================================
        // STEP 1: Resolve Serial Number — Auto-Create if not found
        // ============================================================
        $sn = $data['serial_number'] ?? $data['sn_ban'] ?? $data['no_seri'] ?? null;
        if (!$sn) throw new \Exception("Serial Number kosong");
        $sn = strtoupper(trim($sn));

        // ============================================================
        // PRE-VALIDATION: Prevent polluting Master Data with invalid rows
        // ============================================================
        $isDualFormat = isset($data['pemasangan_tanggal']) || isset($data['pelepasan_tanggal']);
        $unitCode = $data['kode_kendaraan'] ?? $data['unit'] ?? null;
        if ($unitCode) $unitCode = strtoupper(trim($unitCode));

        if ($isDualFormat) {
            $installDate = $this->parseFlexDate($data['pemasangan_tanggal'] ?? null);
            $keterangan  = strtoupper(trim($data['keterangan'] ?? ''));
            $isScrapOnly = in_array($keterangan, ['BUANG', 'SCRAP', 'DISPOSAL']);

            if (!$installDate && !$isScrapOnly) {
                throw new \Exception("Movement tidak diproses karena Tanggal Pemasangan kosong.");
            }

            if ($installDate && empty($unitCode)) {
                throw new \Exception("Movement gagal: Pemasangan memerlukan Unit Kendaraan yang valid.");
            }
        }

        if (array_key_exists($sn . '_' . $uploaderCompanyId, $this->tyreCache)) {
            $tyre = $this->tyreCache[$sn . '_' . $uploaderCompanyId];
        } else {
            $tyre = \App\Models\Tyre::where('serial_number', $sn)
                ->where('tyre_company_id', $uploaderCompanyId)
                ->first();
            if ($tyre) {
                $this->tyreCache[$sn . '_' . $uploaderCompanyId] = $tyre;
            }
        }

        if (!$tyre) {
            $brand = $this->brandCache['UNKNOWN'] ?? \App\Models\TyreBrand::firstOrCreate(
                ['brand_name' => 'UNKNOWN'],
                ['status' => 'Active']
            );
            if ($uploaderCompanyId) $brand->companies()->syncWithoutDetaching([$uploaderCompanyId]);
            $this->brandCache['UNKNOWN'] = $brand;

            $size = $this->sizeCache["UNKNOWN_{$brand->id}"] ?? \App\Models\TyreSize::firstOrCreate(
                ['size' => 'UNKNOWN', 'tyre_brand_id' => $brand->id],
                ['std_otd' => 0, 'ply_rating' => 0]
            );
            if ($uploaderCompanyId) $size->companies()->syncWithoutDetaching([$uploaderCompanyId]);
            $this->sizeCache["UNKNOWN_{$brand->id}"] = $size;

            $pattern = $this->patternCache["UNKNOWN_{$brand->id}"] ?? \App\Models\TyrePattern::firstOrCreate(
                ['name' => 'UNKNOWN', 'tyre_brand_id' => $brand->id]
            );
            if ($uploaderCompanyId) $pattern->companies()->syncWithoutDetaching([$uploaderCompanyId]);
            $this->patternCache["UNKNOWN_{$brand->id}"] = $pattern;

            // Auto-create tyre with minimal data
            $tyre = \App\Models\Tyre::create([
                'serial_number'       => $sn,
                'tyre_company_id'     => $uploaderCompanyId,
                'tyre_brand_id'       => $brand->id,
                'tyre_size_id'        => $size->id,
                'tyre_pattern_id'     => $pattern->id,
                'status'              => 'New',
                'is_in_warehouse'     => 1,
                'initial_tread_depth' => 0,
                'current_tread_depth' => 0,
                'original_tread_depth'=> 0,
                'price'               => 0,
                'ply_rating'          => 0,
            ]);
            $this->tyreCache[$sn] = $tyre;
        }

        // ============================================================
        // STEP 2: Resolve Vehicle — Auto-Create if not found
        // ============================================================
        $unitCode = $data['kode_kendaraan'] ?? $data['unit'] ?? null;
        if ($unitCode) $unitCode = strtoupper(trim($unitCode));

        $vehicle = null;
        if ($unitCode) {
            if (array_key_exists($unitCode . '_' . $uploaderCompanyId, $this->vehicleCache)) {
                $vehicle = $this->vehicleCache[$unitCode . '_' . $uploaderCompanyId];
            } else {
                $vehicle = \App\Models\MasterImportKendaraan::where('kode_kendaraan', $unitCode)
                    ->where('tyre_company_id', $uploaderCompanyId)
                    ->first();
                if (!$vehicle) {
                    $vehicle = \App\Models\MasterImportKendaraan::create([
                        'kode_kendaraan'      => $unitCode,
                        'no_polisi'           => '-',
                        'tyre_company_id'     => $uploaderCompanyId,
                        'jenis_kendaraan'     => 'Unknown',
                        'area'                => 'Unknown',
                        'total_tyre_position' => 0,
                        'tyre_unit_status'    => 'Active',
                    ]);
                }
                $this->vehicleCache[$unitCode . '_' . $uploaderCompanyId] = $vehicle;
            }
        }

        // ============================================================
        // STEP 3: Detect format — Dual-Row or Single-Event
        // ============================================================
        $isDualFormat = isset($data['pemasangan_tanggal']) || isset($data['pelepasan_tanggal']);

        if ($isDualFormat) {
            $this->processDualMovement($data, $tyre, $vehicle, $uploaderCompanyId);
        } else {
            $this->processSingleMovement($data, $tyre, $vehicle, $uploaderCompanyId);
        }
    }

    private function processDualMovement($data, $tyre, $vehicle, $companyId)
    {
        $positionCode = $data['position_code'] ?? $data['posisi_ban'] ?? $data['posisi'] ?? null;
        $positionId = $this->resolvePositionId($positionCode, $vehicle);

        // [P4] Resolve position detail object for 2-way sync
        $posDetail = null;
        if ($positionId) {
            $posDetail = \App\Models\TyrePositionDetail::find($positionId);
        }

        // Parse dates & numbers with flexible format
        $installDate = $this->parseFlexDate($data['pemasangan_tanggal'] ?? null);
        $installKm   = $this->parseEuroNum($data['pemasangan_km'] ?? 0);
        $installHm   = $this->parseEuroNum($data['pemasangan_hm'] ?? $data['pemasangan_hour_meter'] ?? 0);
        $removeDate  = $this->parseFlexDate($data['pelepasan_tanggal'] ?? null);
        $removeKm    = $this->parseEuroNum($data['pelepasan_km'] ?? 0);
        $removeHm    = $this->parseEuroNum($data['pelepasan_hm'] ?? $data['pelepasan_hour_meter'] ?? 0);
        $rtd         = !empty($data['tebal_telapak']) ? (float)$data['tebal_telapak'] : null;
        $remark      = $data['penyebab'] ?? $data['remark'] ?? null;
        $keterangan  = strtoupper(trim($data['keterangan'] ?? ''));

        // Map KETERANGAN -> target_status
        $targetStatus = 'Repaired';
        if (in_array($keterangan, ['BUANG', 'SCRAP', 'DISPOSAL'])) {
            $targetStatus = 'Scrap';
        }

        $sn = $tyre->serial_number;

        // 1. Create INSTALLATION record
        if ($installDate) {
            if (!$vehicle) {
                throw new \Exception("Movement gagal: Pemasangan memerlukan Unit Kendaraan yang valid.");
            }

            // [P9] Cek duplikat: apakah movement Installation dengan SN+tanggal+odometer yang sama sudah ada?
            $existsInstall = \App\Models\TyreMovement::where('tyre_id', $tyre->id)
                ->where('movement_type', 'Installation')
                ->where('movement_date', $installDate)
                ->where('odometer_reading', $installKm)
                ->exists();

            if ($existsInstall) {
                throw new \Exception("Movement duplikat: SN {$sn} Installation pada {$installDate->format('Y-m-d')} KM={$installKm} sudah tercatat.");
            }

            // [P5] Kurangi stok gudang saat ban diambil untuk dipasang
            if ($tyre->is_in_warehouse && $tyre->current_location_id) {
                \App\Models\TyreLocation::where('id', $tyre->current_location_id)->decrement('current_stock');
            }

            \App\Models\TyreMovement::create([
                'tyre_id'          => $tyre->id,
                'tyre_company_id'  => $companyId, // [P7] Company uploader, bukan approver
                'vehicle_id'       => $vehicle->id,
                'position_id'      => $positionId,
                'movement_type'    => 'Installation',
                'movement_date'    => $installDate,
                'odometer_reading' => $installKm,
                'hour_meter_reading' => $installHm,
                'created_by'       => auth()->id(),
            ]);
        }

        // 2. Create REMOVAL record (only if removal date exists)
        if ($removeDate) {
            $runningKm = max(0, $removeKm - $installKm);
            $runningHm = max(0, $removeHm - $installHm);

            // [P2] Log warning jika odometer terbalik (kemungkinan typo di Excel)
            if ($removeKm > 0 && $installKm > 0 && $removeKm < $installKm) {
                Log::warning("Import odometer anomaly: SN={$sn}, install_km={$installKm}, remove_km={$removeKm}. Running KM set to 0.");
            }

            // [P9] Cek duplikat Removal
            $existsRemoval = \App\Models\TyreMovement::where('tyre_id', $tyre->id)
                ->where('movement_type', 'Removal')
                ->where('movement_date', $removeDate)
                ->where('odometer_reading', $removeKm)
                ->exists();

            if ($existsRemoval) {
                throw new \Exception("Movement duplikat: SN {$sn} Removal pada {$removeDate->format('Y-m-d')} KM={$removeKm} sudah tercatat.");
            }

            \App\Models\TyreMovement::create([
                'tyre_id'          => $tyre->id,
                'tyre_company_id'  => $companyId, // [P7]
                'vehicle_id'       => $vehicle ? $vehicle->id : null,
                'position_id'      => $positionId,
                'movement_type'    => 'Removal',
                'movement_date'    => $removeDate,
                'odometer_reading' => $removeKm,
                'hour_meter_reading' => $removeHm,
                'running_km'       => $runningKm,
                'running_hm'       => $runningHm,
                'rtd_reading'      => $rtd,
                'target_status'    => $targetStatus,
                'remarks'          => $remark,
                'created_by'       => auth()->id(),
            ]);

            // Update tyre state -> back to warehouse
            $tyre->update([
                'current_vehicle_id'  => null,
                'current_position_id' => null,
                'is_in_warehouse'     => 1,
                'status'              => $targetStatus,
                'current_tread_depth' => $rtd ?? $tyre->current_tread_depth,
                'total_lifetime_km'   => ($tyre->total_lifetime_km ?? 0) + $runningKm,
                'total_lifetime_hm'   => ($tyre->total_lifetime_hm ?? 0) + $runningHm,
            ]);

            // [P4] Clear posisi kendaraan saat removal (sinkronisasi 2-arah)
            if ($posDetail && $posDetail->tyre_id == $tyre->id) {
                $posDetail->update(['tyre_id' => null]);
            }

            // [P6] Refresh cache agar baris berikutnya pakai data terbaru
            $tyre->refresh();
            $this->tyreCache[$sn . '_' . $companyId] = $tyre;

        } else if ($installDate && $vehicle) {
            // Only installation, no removal yet — tyre currently installed on vehicle
            $tyre->update([
                'current_vehicle_id'  => $vehicle->id,
                'current_position_id' => $positionId,
                'is_in_warehouse'     => 0,
                'status'              => 'Installed',
            ]);

            // [P4] Sinkronisasi 2-arah: update posisi kendaraan
            if ($posDetail) {
                $posDetail->update(['tyre_id' => $tyre->id]);
            }

            // [P6] Refresh cache
            $tyre->refresh();
            $this->tyreCache[$sn . '_' . $companyId] = $tyre;

        } else if (!$installDate && !$removeDate && $targetStatus === 'Scrap') {
            // SCRAP-ONLY: No movement dates, just status update
            $tyre->update([
                'status'              => 'Scrap',
                'current_tread_depth' => $rtd ?? $tyre->current_tread_depth,
            ]);

            // [P6] Refresh cache
            $tyre->refresh();
            $this->tyreCache[$sn . '_' . $companyId] = $tyre;
        }
    }

    /**
     * SINGLE-EVENT FORMAT (original/legacy): 1 baris = 1 event (Installation OR Removal)
     * Backward compatible with old import template.
     */
    private function processSingleMovement($data, $tyre, $vehicle, $uploaderCompanyId)
    {
        // Find Position
        $positionCode = $data['position_code'] ?? $data['posisi'] ?? null;
        $positionId = null;
        $posDetail = null;
        if ($positionCode && $vehicle) {
            $configId = $vehicle->tyre_position_configuration_id;
            if ($configId) {
                $posDetail = $this->resolvePosition($configId, $positionCode);
                $positionId = $posDetail ? $posDetail->id : null;
            }
        }


        $type = !empty($data['movement_type']) ? ucfirst(strtolower($data['movement_type'])) : (!empty($data['tipe_pergerakan']) ? ucfirst(strtolower($data['tipe_pergerakan'])) : 'Installation');
        $moveDate = !empty($data['movement_date']) ? \Carbon\Carbon::parse($data['movement_date']) : (!empty($data['tanggal']) ? \Carbon\Carbon::parse($data['tanggal']) : now());

        // Cast numerical columns
        $odo = !empty($data['odometer']) ? (float)$data['odometer'] : (!empty($data['km']) ? (float)$data['km'] : 0);
        $hm = !empty($data['hm']) ? (float)$data['hm'] : 0;
        $rtd = !empty($data['rtd']) ? (float)$data['rtd'] : null;
        $psi = !empty($data['psi']) ? (float)$data['psi'] : null;
        $targetStatus = !empty($data['target_status']) ? ucfirst(strtolower($data['target_status'])) : 'Repaired';

        $kmDiff = 0;
        $hmDiff = 0;

        // Perform Installation/Removal Logic (unchanged from original)
        if ($type === 'Installation') {
            if (!$vehicle) throw new \Exception("Pemasangan memerlukan Unit Code.");
            if (!$posDetail) throw new \Exception("Posisi $positionCode tidak valid untuk unit " . ($vehicle ? $vehicle->kode_kendaraan : 'N/A') . ".");

            if ($tyre->is_in_warehouse && $tyre->current_location_id) {
                \App\Models\TyreLocation::where('id', $tyre->current_location_id)->decrement('current_stock');
            }

            $tyre->update([
                'current_vehicle_id' => $vehicle->id,
                'current_position_id' => $posDetail->id,
                'is_in_warehouse' => 0,
                'current_location_id' => null,
                'status' => 'Installed',
                'current_tread_depth' => $rtd ?? $tyre->current_tread_depth
            ]);
            $posDetail->update(['tyre_id' => $tyre->id]);
        } else if ($type === 'Removal') {
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

                // [P2] Log warning jika odometer terbalik
                if ((float)$odo > 0 && (float)$lastInstallation->odometer_reading > 0 && (float)$odo < (float)$lastInstallation->odometer_reading) {
                    Log::warning("Import odometer anomaly (Single): SN={$tyre->serial_number}, install_km={$lastInstallation->odometer_reading}, remove_km={$odo}. Running KM set to 0.");
                }
            }

            $locationId = null;
            $locationName = $data['location'] ?? $data['location_name'] ?? $data['warehouse'] ?? null;
            if (!empty($locationName)) {
                $loc = \App\Models\TyreLocation::firstOrCreate(['location_name' => strtoupper(trim($locationName))]);
                $locationId = $loc->id;
                $loc->increment('current_stock');
            }

            $tyre->update([
                'current_vehicle_id' => null,
                'current_position_id' => null,
                'is_in_warehouse' => 1,
                'current_location_id' => $locationId,
                'status' => $targetStatus,
                'total_lifetime_km' => ($tyre->total_lifetime_km ?? 0) + $kmDiff,
                'total_lifetime_hm' => ($tyre->total_lifetime_hm ?? 0) + $hmDiff,
                'current_tread_depth' => $rtd ?? $tyre->current_tread_depth
            ]);

            if ($posDetail && $posDetail->tyre_id == $tyre->id) {
                $posDetail->update(['tyre_id' => null]);
            }
        }

        // [P6] Refresh cache setelah update agar baris selanjutnya pakai data terbaru
        $tyre->refresh();
        $this->tyreCache[strtoupper($tyre->serial_number)] = $tyre;

        // Find Failure Code
        $failCodeStr = $data['failure_code'] ?? null;
        $failCodeId = null;
        if (!empty($failCodeStr)) {
            $failCode = \App\Models\TyreFailureCode::where('failure_code', $failCodeStr)->first();
            $failCodeId = $failCode ? $failCode->id : null;
        }

        // [P9] Cek duplikat: hindari import ganda jika SN+Type+Date+Odo sudah ada
        $existsMovement = \App\Models\TyreMovement::where('tyre_id', $tyre->id)
            ->where('movement_type', $type)
            ->where('movement_date', $moveDate)
            ->where('odometer_reading', $odo)
            ->exists();

        if ($existsMovement) {
            throw new \Exception("Movement duplikat: SN {$tyre->serial_number} {$type} pada {$moveDate->format('Y-m-d')} KM={$odo} sudah tercatat.");
        }

        \App\Models\TyreMovement::create([
            'tyre_id' => $tyre->id,
            'tyre_company_id' => $uploaderCompanyId, // [P7] Company uploader, bukan approver
            'vehicle_id' => $vehicle ? $vehicle->id : null,
            'position_id' => $positionId,
            'movement_date' => $moveDate,
            'movement_type' => $type,
            'odometer_reading' => $odo,
            'hour_meter_reading' => $hm,
            'rtd_reading' => $rtd,
            'psi_reading' => $psi,
            'running_km' => $kmDiff,
            'running_hm' => $hmDiff,
            'failure_code_id' => $failCodeId,
            'target_status' => ($type === 'Removal') ? $targetStatus : null,
            'remarks' => $data['remark'] ?? $data['notes'] ?? null,
            'created_by' => auth()->id()
        ]);
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    /**
     * Resolve position ID from position code (number or text).
     * Returns position_detail ID or null.
     */
    private function resolvePositionId($positionCode, $vehicle)
    {
        if (!$positionCode || !$vehicle) return null;

        $configId = $vehicle->tyre_position_configuration_id;
        if (!$configId) return null;

        $posDetail = $this->resolvePosition($configId, $positionCode);
        return $posDetail ? $posDetail->id : null;
    }

    /**
     * Parse European-format number: "32.816" → 32816, "48.124" → 48124
     * Also handles: "103.574" → 103574, "1724" → 1724
     */
    private function parseEuroNum($value)
    {
        if ($value === null || $value === '') return 0;
        $str = trim((string)$value);

        // Remove spaces
        $str = str_replace(' ', '', $str);

        // Pattern: digits.3digits (e.g. 32.816, 103.574) = thousands separator
        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $str)) {
            return (float)str_replace('.', '', $str);
        }

        // Pattern: digits,3digits (e.g. 32,816) = thousands separator with comma
        if (preg_match('/^\d{1,3}(,\d{3})+$/', $str)) {
            return (float)str_replace(',', '', $str);
        }

        // Otherwise parse as regular float
        return (float)str_replace(',', '.', $str);
    }

    /**
     * Parse flexible date formats:
     * - DD.MM.YYYY (European: 17.10.2023)
     * - DD/MM/YYYY
     * - YYYY-MM-DD (ISO)
     * - Excel numeric serial (e.g. 45218)
     */
    private function parseFlexDate($value)
    {
        if ($value === null || trim($value) === '' || $value === '-') return null;
        $value = trim($value);

        // Excel numeric serial date (e.g. 45218)
        if (is_numeric($value) && (int)$value > 30000 && (int)$value < 60000) {
            return \Carbon\Carbon::createFromFormat('Y-m-d', 
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int)$value)->format('Y-m-d')
            );
        }

        // DD.MM.YYYY
        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $value, $m)) {
            return \Carbon\Carbon::createFromFormat('d.m.Y', sprintf('%02d.%02d.%s', $m[1], $m[2], $m[3]));
        }

        // DD/MM/YYYY
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $m)) {
            return \Carbon\Carbon::createFromFormat('d/m/Y', sprintf('%02d/%02d/%s', $m[1], $m[2], $m[3]));
        }

        // Fallback: let Carbon try to parse it
        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
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
        $batch = $this->scopedQuery()->findOrFail($id);
        
        $batch->update([
            'status' => 'Rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => $request->notes
        ]);

        // --- Send Notification to Submitter ---
        try {
            if ($batch->user) {
                $approverName = auth()->user()->display_name;
                $actionUrl = route('import-approval.show', $batch->id);
                \Illuminate\Support\Facades\Notification::send($batch->user, new \App\Notifications\ApprovalStatusNotification('Import ' . $batch->module, 'Rejected', $approverName, $actionUrl, $request->notes));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send Import Rejected Notification: " . $e->getMessage());
        }

        setLogActivity(auth()->id(), "Menolak import batch #$id ({$batch->module})", [
            'module' => 'Import Approval',
            'batch_id' => $id,
            'reason' => $request->notes
        ]);

        return redirect()->route('import-approval.index')->with('warning', 'Batch telah ditolak.');
    }

    public function updateItem(Request $request, $itemId)
    {
        $item = \App\Models\ImportItem::findOrFail($itemId);
        $batch = $item->batch;
        
        $data = $item->data;
        $updates = $request->except('_token', '_method');
        
        // Merge updates
        foreach ($updates as $key => $val) {
            $data[$key] = $val;
        }

        // Re-validate menggunakan validasi yang diperkuat
        $validation = $this->validateMovementRow($data, $batch->user->tyre_company_id ?? null);
        $data['_validation'] = $validation;

        $item->data = $data;
        if ($validation['is_valid']) {
            $item->status = 'Pending';
        }
        $item->save();

        return response()->json([
            'success' => true,
            'is_valid' => $validation['is_valid'],
            'errors' => $validation['errors'],
            'warnings' => $validation['warnings'] ?? [],
            'item_data' => $data
        ]);
    }

    /**
     * Validasi mendalam untuk setiap baris import Movement History.
     * Ini adalah QUALITY GATE utama yang membagi data menjadi "Ready" vs "Perlu Perbaikan".
     * 
     * Mengatasi: P2 (odometer), P4 (posisi), P7 (company), P9 (duplikat)
     */
    private function validateMovementRow($data, $uploaderCompanyId = null)
    {
        $sn = $data['serial_number'] ?? $data['sn_ban'] ?? $data['no_seri'] ?? null;
        $sn = strtoupper(trim((string)$sn));
        $errors = [];
        $warnings = [];

        // ── 1. VALIDASI WAJIB: Serial Number ──
        if (empty($sn)) {
            $errors[] = "Nomor Seri kosong.";
        } else {
            // Cek apakah SN terdaftar di database
            $tyreExists = \App\Models\Tyre::withoutGlobalScopes()->where('serial_number', $sn)->exists();
            if (!$tyreExists) {
                $errors[] = "Ban SN '{$sn}' tidak ditemukan di Master Tyre. Daftarkan terlebih dahulu.";
            }
        }

        // ── 2. VALIDASI TANGGAL ──
        $installDateRaw = $data['pemasangan_tanggal'] ?? null;
        $installDate = !empty($installDateRaw) && trim($installDateRaw) !== '0';

        $removeDateRaw = $data['pelepasan_tanggal'] ?? null;
        $removeDate = !empty($removeDateRaw) && trim($removeDateRaw) !== '0';
        
        $movementDateRaw = $data['movement_date'] ?? $data['tanggal'] ?? null;
        $hasSingleMovementDate = !empty($movementDateRaw) && trim($movementDateRaw) !== '0';

        $keterangan = strtoupper(trim($data['keterangan'] ?? $data['remark'] ?? ''));
        $isScrapOnly = in_array($keterangan, ['BUANG', 'SCRAP', 'DISPOSAL']) || strtoupper(trim($data['target_status'] ?? '')) === 'SCRAP';
        
        $unitCode = trim($data['kode_kendaraan'] ?? $data['unit'] ?? '');

        if (!$installDate && !$hasSingleMovementDate && !$isScrapOnly) {
            $errors[] = "Tanggal Pemasangan kosong dan bukan pembuangan (Scrap).";
        }

        $isInstallation = $installDate || (strtoupper(trim($data['movement_type'] ?? '')) === 'INSTALLATION');

        // Cek tanggal masa depan
        if ($installDate) {
            try {
                $parsedInstall = \Carbon\Carbon::parse($installDateRaw);
                if ($parsedInstall->isFuture()) {
                    $errors[] = "Tanggal Pemasangan ({$installDateRaw}) tidak boleh di masa depan.";
                }
            } catch (\Exception $e) {
                $errors[] = "Format Tanggal Pemasangan tidak valid: '{$installDateRaw}'.";
            }
        }

        if ($removeDate) {
            try {
                $parsedRemove = \Carbon\Carbon::parse($removeDateRaw);
                if ($parsedRemove->isFuture()) {
                    $errors[] = "Tanggal Pelepasan ({$removeDateRaw}) tidak boleh di masa depan.";
                }
                // Cek kronologi: remove harus setelah install
                if ($installDate) {
                    $parsedInstallCheck = \Carbon\Carbon::parse($installDateRaw);
                    if ($parsedRemove->lt($parsedInstallCheck)) {
                        $errors[] = "Tanggal Pelepasan ({$removeDateRaw}) lebih awal dari Tanggal Pemasangan ({$installDateRaw}).";
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Format Tanggal Pelepasan tidak valid: '{$removeDateRaw}'.";
            }
        }

        if ($hasSingleMovementDate) {
            try {
                $parsedSingleDate = \Carbon\Carbon::parse($movementDateRaw);
                if ($parsedSingleDate->isFuture()) {
                    $errors[] = "Tanggal Movement ({$movementDateRaw}) tidak boleh di masa depan.";
                }
            } catch (\Exception $e) {
                $errors[] = "Format Tanggal Movement tidak valid: '{$movementDateRaw}'.";
            }
        }

        // ── 3. VALIDASI KENDARAAN ──
        if ($isInstallation && empty($unitCode)) {
            $errors[] = "Pemasangan memerlukan Unit Kendaraan yang diisi.";
        }

        if (!empty($unitCode)) {
            $vehicleExists = \App\Models\MasterImportKendaraan::withoutGlobalScopes()
                ->where('kode_kendaraan', strtoupper($unitCode))
                ->exists();
            if (!$vehicleExists) {
                $errors[] = "Unit '{$unitCode}' tidak ditemukan di Master Vehicle.";
            }
        }

        // ── 4. VALIDASI ODOMETER (P2) ──
        $installKm = (float)($data['pemasangan_km'] ?? 0);
        $removeKm = (float)($data['pelepasan_km'] ?? 0);

        if ($removeDate && $installDate && $removeKm > 0 && $installKm > 0) {
            if ($removeKm < $installKm) {
                $warnings[] = "Odometer anomali: KM Pelepasan ({$removeKm}) lebih kecil dari KM Pemasangan ({$installKm}). Running KM akan diset 0.";
            }
        }

        // ── 5. VALIDASI POSISI BAN (P4) ──
        $positionCode = $data['position_code'] ?? $data['posisi_ban'] ?? $data['posisi'] ?? null;
        // Validasi posisi harus jalan baik untuk format Dual (pemasangan_tanggal) 
        // MAUPUN format Single Event (movement_date + movement_type=Installation)
        $needsPositionCheck = $installDate || $isInstallation || $hasSingleMovementDate;
        
        if ($needsPositionCheck && !empty($unitCode)) {
            if (empty(trim((string)($positionCode ?? '')))) {
                $warnings[] = "Posisi Ban tidak diisi. Ban akan dipasang tanpa info posisi.";
            } else {
                $vehicle = \App\Models\MasterImportKendaraan::withoutGlobalScopes()
                    ->where('kode_kendaraan', strtoupper($unitCode))
                    ->first();
                if ($vehicle) {
                    $resolvedPosId = $this->resolvePositionId($positionCode, $vehicle);
                    if (!$resolvedPosId) {
                        $configName = \Illuminate\Support\Facades\DB::table('tyre_position_configurations')
                            ->where('id', $vehicle->tyre_position_configuration_id)->value('name') ?? 'Unknown';
                        $errors[] = "Posisi '{$positionCode}' tidak valid untuk unit '{$unitCode}' (Config: {$configName}). Periksa konfigurasi kendaraan.";
                    }
                }
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
    private function resolvePosition($configId, $positionCode)
    {
        $searchCode = strtoupper(trim($positionCode));
        $numericPos = preg_replace('/[^0-9]/', '', $positionCode);
        
        // Dictionary mapping common user terms to our internal base codes
        $aliasMap = [
            'LM-IN' => 'LMI', 'LM-OUT' => 'LMO', 'LMI' => 'LMI', 'LMO' => 'LMO',
            'RM-IN' => 'RMI', 'RM-OUT' => 'RMO', 'RMI' => 'RMI', 'RMO' => 'RMO',
            'LR-IN' => 'LRI', 'LR-OUT' => 'LRO', 'LRI' => 'LRI', 'LRO' => 'LRO',
            'RR-IN' => 'RRI', 'RR-OUT' => 'RRO', 'RRI' => 'RRI', 'RRO' => 'RRO',
            'LF-IN' => 'LFI', 'LF-OUT' => 'LFO', 'LFI' => 'LFI', 'LFO' => 'LFO',
            'RF-IN' => 'RFI', 'RF-OUT' => 'RFO', 'RFI' => 'RFI', 'RFO' => 'RFO',
            'LF' => 'LF', 'RF' => 'RF', 'SP' => 'SP',
            // Sometimes they use spaces
            'LM IN' => 'LMI', 'LM OUT' => 'LMO', 'RM IN' => 'RMI', 'RM OUT' => 'RMO',
            'LR IN' => 'LRI', 'LR OUT' => 'LRO', 'RR IN' => 'RRI', 'RR OUT' => 'RRO',
        ];

        // If the code exactly matches a key, use the mapped base code
        $baseSearch = $aliasMap[$searchCode] ?? null;

        // If not mapped, maybe it has numbers like "RR-IN 2" -> "RR-IN"
        if (!$baseSearch) {
            $textOnly = trim(preg_replace('/[0-9]/', '', $searchCode));
            $baseSearch = $aliasMap[$textOnly] ?? $textOnly;
        }

        return \App\Models\TyrePositionDetail::where('configuration_id', $configId)
            ->where(function($q) use ($positionCode, $searchCode, $baseSearch, $numericPos) {
                // Exact match of raw input
                $q->where('position_code', $searchCode)
                  ->orWhere('position_name', $positionCode);

                // Match base alias + any sequence (e.g., LRI/3)
                if ($baseSearch) {
                    $q->orWhere('position_code', 'LIKE', $baseSearch . '/%');
                    $q->orWhere('position_code', $baseSearch); // if no seq
                    
                    // Handle standard config reversals (FL vs LF, FR vs RF)
                    if ($baseSearch === 'LF') {
                        $q->orWhere('position_code', 'FL');
                    } elseif ($baseSearch === 'RF') {
                        $q->orWhere('position_code', 'FR');
                    }
                }
                
                // Handle cases where they input FL/FR directly
                if ($searchCode === 'FL') {
                    $q->orWhere('position_code', 'LIKE', 'LF/%');
                    $q->orWhere('position_code', 'LF');
                } elseif ($searchCode === 'FR') {
                    $q->orWhere('position_code', 'LIKE', 'RF/%');
                    $q->orWhere('position_code', 'RF');
                }

                // Match by numeric display order if provided
                if ($numericPos !== '') {
                    $q->orWhere('display_order', $numericPos);
                }
            })
            ->first();
    }
}
