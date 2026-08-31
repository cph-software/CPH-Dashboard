<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\Tyre;
use App\Models\TyreBrand;
use App\Models\TyreSize;
use App\Models\TyreSegment;
use App\Models\TyrePattern;
use App\Models\TyreLocation; // Import TyreLocation
use Illuminate\Http\Request;

class TyreMasterController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $activeCompanyId = \App\Helpers\SessionCompanyHelper::getActiveCompanyId();

        $brandQuery = TyreBrand::where('status', 'Active')->orderBy('brand_name');
        $sizeQuery = TyreSize::with('brand')->orderBy('size');
        $patternQuery = TyrePattern::with('brand')->orderBy('name');

        if ($user->role_id != 1 && $activeCompanyId && !is_array($activeCompanyId)) {
            $hasBrandMapping = \DB::table('tyre_company_brands')->where('tyre_company_id', $activeCompanyId)->exists();
            if ($hasBrandMapping) {
                $brandQuery->whereHas('companies', fn($q) => $q->where('tyre_company_id', $activeCompanyId));
            }

            $hasSizeMapping = \DB::table('tyre_company_sizes')->where('tyre_company_id', $activeCompanyId)->exists();
            if ($hasSizeMapping) {
                $sizeQuery->whereHas('companies', fn($q) => $q->where('tyre_company_id', $activeCompanyId));
            }

            $hasPatternMapping = \DB::table('tyre_company_patterns')->where('tyre_company_id', $activeCompanyId)->exists();
            if ($hasPatternMapping) {
                $patternQuery->whereHas('companies', fn($q) => $q->where('tyre_company_id', $activeCompanyId));
            }
        }

        $brands = $brandQuery->get();
        $sizes = $sizeQuery->get();
        $patterns = $patternQuery->get();
        
        $segments = TyreSegment::with('location')->where('status', 'Active')->get();
        $locations = TyreLocation::all();
        $companies = \App\Models\TyreCompany::where('status', 'Active')->orderBy('company_name')->get();

        return view('tyre-performance.master.tyres.index', compact('brands', 'sizes', 'segments', 'patterns', 'locations', 'companies'));
    }

    /**
     * Data for Server-Side DataTables
     */
    public function data(Request $request)
    {
        $query = Tyre::with(['brand', 'size', 'pattern', 'location', 'company']);

        // Search logic
        if ($request->has('search') && $request->input('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('serial_number', 'like', "%$searchValue%")
                    ->orWhere('custom_serial_number', 'like', "%$searchValue%")
                    ->orWhereHas('brand', function ($sub) use ($searchValue) {
                        $sub->where('brand_name', 'like', "%$searchValue%");
                    })
                    ->orWhereHas('size', function ($sub) use ($searchValue) {
                        $sub->where('size', 'like', "%$searchValue%");
                    })
                    ->orWhereHas('company', function ($sub) use ($searchValue) {
                        $sub->where('company_name', 'like', "%$searchValue%");
                    })
                    ->orWhere('status', 'like', "%$searchValue%");
            });
        }

        // totalRecords: total ban untuk perusahaan aktif (tanpa search filter)
        // BelongsToCompany global scope otomatis mengfilter berdasarkan perusahaan aktif
        $baseQuery = Tyre::query(); // Global scope aktif di sini
        $totalRecords = $baseQuery->count();
        $filteredRecords = $query->count();


        // Ordering
        if ($request->has('order')) {
            $columnIndex = $request->input('order.0.column');
            $columnDir = $request->input('order.0.dir');

            $isAdmin = (auth()->check() && auth()->user()->role_id == 1);

            if ($isAdmin) {
                // Map column index to DB field for Admin (with company column)
                $cols = [
                    1 => 'serial_number',
                    2 => 'tyre_company_id',
                    3 => 'tyre_brand_id',
                    4 => 'tyre_size_id',
                    5 => 'segment_name',
                    6 => 'is_in_warehouse',
                    7 => 'status'
                ];
            } else {
                // Map column index to DB field for Normal User (without company column)
                $cols = [
                    1 => 'serial_number',
                    2 => 'tyre_brand_id',
                    3 => 'tyre_size_id',
                    4 => 'segment_name',
                    5 => 'is_in_warehouse',
                    6 => 'status'
                ];
            }

            if (isset($cols[$columnIndex])) {
                $query->orderBy($cols[$columnIndex], $columnDir);
            }
        } else {
            $query->latest();
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $tyres = $query->skip($start)->take($length)->get();

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalRecords),
            "recordsFiltered" => intval($filteredRecords),
            "data" => $tyres
        ]);
    }

    public function show($id)
    {
        $tyre = Tyre::with(['brand', 'size', 'pattern', 'location', 'currentVehicle', 'currentPosition', 'movements.vehicle', 'movements.position'])
            ->findOrFail($id);

        $companyMode = 'BOTH';
        if ($tyre->tyre_company_id) {
            $company = \App\Models\TyreCompany::find($tyre->tyre_company_id);
            $companyMode = $company ? $company->measurement_mode : 'BOTH';
        }

        return view('tyre-performance.master.tyres.show', compact('tyre', 'companyMode'));
    }

    public function edit($id)
    {
        $tyre = Tyre::findOrFail($id);
        $user = auth()->user();
        $targetCompanyId = $tyre->tyre_company_id ?? \App\Helpers\SessionCompanyHelper::getActiveCompanyId();

        $brandQuery = TyreBrand::where('status', 'Active')->orderBy('brand_name');
        $sizeQuery = TyreSize::with('brand')->orderBy('size');
        $patternQuery = TyrePattern::with('brand')->orderBy('name');

        if ($user->role_id != 1 && $targetCompanyId && !is_array($targetCompanyId)) {
            $hasBrandMapping = \DB::table('tyre_company_brands')->where('tyre_company_id', $targetCompanyId)->exists();
            if ($hasBrandMapping) {
                $brandQuery->whereHas('companies', fn($q) => $q->where('tyre_company_id', $targetCompanyId));
            }

            $hasSizeMapping = \DB::table('tyre_company_sizes')->where('tyre_company_id', $targetCompanyId)->exists();
            if ($hasSizeMapping) {
                $sizeQuery->whereHas('companies', fn($q) => $q->where('tyre_company_id', $targetCompanyId));
            }

            $hasPatternMapping = \DB::table('tyre_company_patterns')->where('tyre_company_id', $targetCompanyId)->exists();
            if ($hasPatternMapping) {
                $patternQuery->whereHas('companies', fn($q) => $q->where('tyre_company_id', $targetCompanyId));
            }
        }

        $brands = $brandQuery->get();
        $sizes = $sizeQuery->get();
        $patterns = $patternQuery->get();
        
        $segments = TyreSegment::where('status', 'Active')->get();
        $locations = TyreLocation::all();

        return view('tyre-performance.master.tyres.edit', compact('tyre', 'brands', 'sizes', 'segments', 'patterns', 'locations'));
    }

    private function generateStockSerialNumber($brandId = null)
    {
        $brandPrefix = 'STK';
        if ($brandId) {
            $brandName = TyreBrand::where('id', $brandId)->value('brand_name');
            if ($brandName) {
                $clean = preg_replace('/[^A-Za-z0-9]/', '', $brandName);
                $brandPrefix = 'STK-' . strtoupper(substr($clean, 0, 3));
            }
        }
        
        $datePart = now()->format('Ymd');
        
        do {
            $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
            $sn = "{$brandPrefix}-{$datePart}-{$random}";
            $exists = Tyre::withoutGlobalScopes()->where('serial_number', $sn)->exists();
        } while ($exists);
        
        return $sn;
    }

    public function store(Request $request)
    {
        $request->validate([
            'serial_number' => 'nullable|string|max:255|unique:tyres,serial_number,NULL,id,deleted_at,NULL',
            'custom_serial_number' => 'nullable|string|max:255|unique:tyres,custom_serial_number,NULL,id,deleted_at,NULL',
            'quantity' => 'nullable|integer|min:1|max:100',
            'tyre_brand_id' => 'required', // Can be ID or New Name String
            'tyre_size_id' => 'required',  // Can be ID or New Name String
            'tyre_pattern_id' => 'nullable', // Can be ID or New Name String
            'segment_name' => 'nullable|string|max:255',
            'is_in_warehouse' => 'nullable|boolean',
            'status' => 'required|in:New,Installed,Scrap,Repaired,Retread',
            'price' => 'nullable',
            'initial_tread_depth' => 'nullable|numeric|min:0',
            'ply_rating' => 'nullable|string|max:50',
            'retread_count' => 'nullable|integer|min:0',
            'tyre_company_id' => auth()->user()->role_id == 1 ? 'required|exists:tyre_companies,id' : 'nullable',
        ]);

        $data = $request->all();
        $user = auth()->user();
        $quantity = (int)($request->quantity ?? 1);

        // Price parser: handle Rupiah / currency strings e.g. "3.500.000"
        if (!empty($data['price'])) {
            $cleanPrice = str_replace(['Rp', 'rp', ' ', '.'], '', (string)$data['price']);
            $cleanPrice = str_replace(',', '.', $cleanPrice);
            $data['price'] = (float)$cleanPrice;
        }

        // Determine target company ID
        $targetCompanyId = null;
        if ($user->role_id == 1) {
            $targetCompanyId = $data['tyre_company_id'] ?? \App\Helpers\SessionCompanyHelper::getActiveCompanyId();
        } else {
            $targetCompanyId = $user->tyre_company_id ?? \App\Helpers\SessionCompanyHelper::getActiveCompanyId();
            $data['tyre_company_id'] = $targetCompanyId;
        }

        // 1. Resolve Brand (ID or New Name String)
        if (!empty($request->tyre_brand_id)) {
            $brand = is_numeric($request->tyre_brand_id) ? TyreBrand::find($request->tyre_brand_id) : null;
            if (!$brand) {
                $brandName = strtoupper(trim($request->tyre_brand_id));
                $brand = TyreBrand::firstOrCreate(['brand_name' => $brandName], ['status' => 'Active']);
            }
            $data['tyre_brand_id'] = $brand->id;
            if ($brand && $targetCompanyId && !is_array($targetCompanyId)) {
                $brand->companies()->syncWithoutDetaching([$targetCompanyId]);
            }
        }

        // 2. Resolve Size (ID or New Name String e.g. "0222", "11.00-20")
        if (!empty($request->tyre_size_id)) {
            $size = is_numeric($request->tyre_size_id) ? TyreSize::find($request->tyre_size_id) : null;
            if (!$size) {
                $sizeName = strtoupper(trim($request->tyre_size_id));
                $size = TyreSize::firstOrCreate(
                    [
                        'size' => $sizeName,
                        'tyre_brand_id' => $data['tyre_brand_id'] ?? null
                    ],
                    [
                        'std_otd' => !empty($data['initial_tread_depth']) ? (float)$data['initial_tread_depth'] : 0,
                        'ply_rating' => !empty($data['ply_rating']) ? (int)preg_replace('/[^0-9]/', '', (string)$data['ply_rating']) : 16,
                    ]
                );
            }
            $data['tyre_size_id'] = $size->id;
            if ($size && $targetCompanyId && !is_array($targetCompanyId)) {
                $size->companies()->syncWithoutDetaching([$targetCompanyId]);
            }
        }

        // 3. Resolve Pattern (ID or New Name String)
        if ($request->filled('tyre_pattern_id')) {
            $pattern = is_numeric($request->tyre_pattern_id) ? TyrePattern::find($request->tyre_pattern_id) : null;
            if (!$pattern) {
                $patternName = strtoupper(trim($request->tyre_pattern_id));
                $pattern = TyrePattern::firstOrCreate(
                    [
                        'name' => $patternName,
                        'tyre_brand_id' => $data['tyre_brand_id'] ?? null
                    ],
                    [
                        'status' => 'Active'
                    ]
                );
            }
            $data['tyre_pattern_id'] = $pattern->id;
            if ($pattern && $targetCompanyId && !is_array($targetCompanyId)) {
                $pattern->companies()->syncWithoutDetaching([$targetCompanyId]);
            }
        }

        // 4. Resolve Segment
        if (!empty($data['segment_name']) && $targetCompanyId && !is_array($targetCompanyId)) {
            $segName = trim($data['segment_name']);
            TyreSegment::firstOrCreate(
                [
                    'segment_name' => $segName,
                    'tyre_company_id' => $targetCompanyId
                ],
                [
                    'segment_id' => 'SEG-' . strtoupper(\Illuminate\Support\Str::slug(substr($segName, 0, 5))) . '-' . rand(100, 999),
                    'terrain_type' => 'Standard',
                    'status' => 'Active'
                ]
            );
        }

        // 5. Resolve Location / Warehouse (ID or New Name String)
        if (!empty($request->current_location_id) && $targetCompanyId && !is_array($targetCompanyId)) {
            $location = is_numeric($request->current_location_id) ? TyreLocation::find($request->current_location_id) : null;
            if (!$location) {
                $locName = trim($request->current_location_id);
                $location = TyreLocation::firstOrCreate(
                    [
                        'location_name' => $locName,
                        'tyre_company_id' => $targetCompanyId
                    ],
                    [
                        'location_type' => 'Warehouse',
                        'capacity' => 100,
                        'current_stock' => 0,
                    ]
                );
            }
            $data['current_location_id'] = $location->id;
        }

        // Set Default Tread Depths
        if (empty($data['original_tread_depth'])) {
            if (!empty($data['initial_tread_depth'])) {
                $data['original_tread_depth'] = $data['initial_tread_depth'];
            } else {
                $size = TyreSize::find($data['tyre_size_id']);
                $data['original_tread_depth'] = $size ? $size->std_otd : 0;
                $data['initial_tread_depth'] = $data['original_tread_depth'];
            }
        }
        if (!isset($data['current_tread_depth'])) {
            $data['current_tread_depth'] = $data['initial_tread_depth'];
        }

        $createdSns = [];
        \DB::beginTransaction();
        try {
            for ($i = 0; $i < $quantity; $i++) {
                $itemData = $data;
                if (empty($request->serial_number) || $quantity > 1) {
                    $itemData['serial_number'] = $this->generateStockSerialNumber($data['tyre_brand_id'] ?? null);
                    if ($i === 0 && !empty($request->serial_number) && $quantity > 1) {
                        $itemData['serial_number'] = strtoupper(trim($request->serial_number));
                    }
                } else {
                    $itemData['serial_number'] = strtoupper(trim($request->serial_number));
                }

                $tyre = Tyre::create($itemData);
                $createdSns[] = $itemData['serial_number'];

                if (!empty($itemData['is_in_warehouse']) && !empty($itemData['current_location_id'])) {
                    \App\Models\TyreLocation::where('id', $itemData['current_location_id'])->increment('current_stock');
                }
            }
            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan ban: ' . $e->getMessage())->withInput();
        }

        // Log Activity
        $logMessage = $quantity > 1 
            ? "Menambah {$quantity} ban baru (SN: " . implode(', ', array_slice($createdSns, 0, 5)) . ($quantity > 5 ? '...' : '') . ")"
            : "Menambah ban baru: " . ($createdSns[0] ?? '-');

        setLogActivity(auth()->id(), $logMessage, [
            'action_type' => 'create',
            'module' => 'Master Tyre',
            'data_after' => [
                'Total Qty' => $quantity,
                'Serial Numbers' => $createdSns,
                'Brand' => $tyre->brand->brand_name ?? $data['tyre_brand_id'],
                'Size' => $tyre->size->size ?? $data['tyre_size_id'],
                'Status' => $tyre->status ?? 'New',
            ]
        ]);

        $successMsg = $quantity > 1 
            ? "{$quantity} ban berhasil ditambahkan ke master!"
            : "Ban " . ($createdSns[0] ?? '') . " berhasil ditambahkan ke master!";

        return redirect()->back()->with('success', $successMsg);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'serial_number' => 'required|string|max:255|unique:tyres,serial_number,' . $id . ',id,deleted_at,NULL',
            'custom_serial_number' => 'nullable|string|max:255|unique:tyres,custom_serial_number,' . $id . ',id,deleted_at,NULL',
            'tyre_brand_id' => 'required',
            'tyre_size_id' => 'required',
            'tyre_pattern_id' => 'nullable',
            'segment_name' => 'nullable|string|max:255',
            'is_in_warehouse' => 'nullable|boolean',
            'status' => 'required|in:New,Installed,Scrap,Repaired,Retread',
            'price' => 'nullable|numeric|min:0',
            'initial_tread_depth' => 'nullable|numeric|min:0',
            'ply_rating' => 'nullable|string|max:50',
            'retread_count' => 'nullable|integer|min:0',
            'tyre_company_id' => auth()->user()->role_id == 1 ? 'required|exists:tyre_companies,id' : 'nullable',
        ]);

        $tyre = Tyre::findOrFail($id);
        $data = $request->all();
        $user = auth()->user();

        // Determine target company ID
        $targetCompanyId = null;
        if ($user->role_id == 1) {
            $targetCompanyId = $data['tyre_company_id'] ?? $tyre->tyre_company_id;
        } else {
            $targetCompanyId = $tyre->tyre_company_id ?? $user->tyre_company_id;
            $data['tyre_company_id'] = $targetCompanyId;
        }

        // 1. Resolve Brand
        if (!empty($request->tyre_brand_id)) {
            $brand = is_numeric($request->tyre_brand_id) ? TyreBrand::find($request->tyre_brand_id) : null;
            if (!$brand) {
                $brandName = strtoupper(trim($request->tyre_brand_id));
                $brand = TyreBrand::firstOrCreate(['brand_name' => $brandName], ['status' => 'Active']);
            }
            $data['tyre_brand_id'] = $brand->id;
            if ($brand && $targetCompanyId && !is_array($targetCompanyId)) {
                $brand->companies()->syncWithoutDetaching([$targetCompanyId]);
            }
        }

        // 2. Resolve Size
        if (!empty($request->tyre_size_id)) {
            $size = is_numeric($request->tyre_size_id) ? TyreSize::find($request->tyre_size_id) : null;
            if (!$size) {
                $sizeName = strtoupper(trim($request->tyre_size_id));
                $size = TyreSize::firstOrCreate(
                    [
                        'size' => $sizeName,
                        'tyre_brand_id' => $data['tyre_brand_id'] ?? null
                    ],
                    [
                        'std_otd' => !empty($data['initial_tread_depth']) ? (float)$data['initial_tread_depth'] : 0,
                        'ply_rating' => !empty($data['ply_rating']) ? (int)preg_replace('/[^0-9]/', '', (string)$data['ply_rating']) : 16,
                    ]
                );
            }
            $data['tyre_size_id'] = $size->id;
            if ($size && $targetCompanyId && !is_array($targetCompanyId)) {
                $size->companies()->syncWithoutDetaching([$targetCompanyId]);
            }
        }

        // 3. Resolve Pattern
        if ($request->filled('tyre_pattern_id')) {
            $pattern = is_numeric($request->tyre_pattern_id) ? TyrePattern::find($request->tyre_pattern_id) : null;
            if (!$pattern) {
                $patternName = strtoupper(trim($request->tyre_pattern_id));
                $pattern = TyrePattern::firstOrCreate(
                    [
                        'name' => $patternName,
                        'tyre_brand_id' => $data['tyre_brand_id'] ?? null
                    ],
                    [
                        'status' => 'Active'
                    ]
                );
            }
            $data['tyre_pattern_id'] = $pattern->id;
            if ($pattern && $targetCompanyId && !is_array($targetCompanyId)) {
                $pattern->companies()->syncWithoutDetaching([$targetCompanyId]);
            }
        }

        // 4. Resolve Segment
        if (!empty($data['segment_name']) && $targetCompanyId && !is_array($targetCompanyId)) {
            $segName = trim($data['segment_name']);
            TyreSegment::firstOrCreate(
                [
                    'segment_name' => $segName,
                    'tyre_company_id' => $targetCompanyId
                ],
                [
                    'segment_id' => 'SEG-' . strtoupper(\Illuminate\Support\Str::slug(substr($segName, 0, 5))) . '-' . rand(100, 999),
                    'terrain_type' => 'Standard',
                    'status' => 'Active'
                ]
            );
        }

        // 5. Resolve Location / Warehouse (ID or New Name String)
        if (!empty($request->current_location_id) && $targetCompanyId && !is_array($targetCompanyId)) {
            $location = is_numeric($request->current_location_id) ? TyreLocation::find($request->current_location_id) : null;
            if (!$location) {
                $locName = trim($request->current_location_id);
                $location = TyreLocation::firstOrCreate(
                    [
                        'location_name' => $locName,
                        'tyre_company_id' => $targetCompanyId
                    ],
                    [
                        'location_type' => 'Warehouse',
                        'capacity' => 100,
                        'current_stock' => 0,
                    ]
                );
            }
            $data['current_location_id'] = $location->id;
        }

        // Sync Tread Depths jika sebelumnya kosong
        if (empty($tyre->original_tread_depth) && !empty($data['initial_tread_depth'])) {
            $data['original_tread_depth'] = $data['initial_tread_depth'];
        } elseif (empty($tyre->original_tread_depth) && empty($data['initial_tread_depth'])) {
            $size = TyreSize::find($data['tyre_size_id']);
            $data['original_tread_depth'] = $size ? $size->std_otd : 0;
            $data['initial_tread_depth'] = $data['original_tread_depth'];
        }

        if (empty($tyre->current_tread_depth) && !empty($data['initial_tread_depth'])) {
            $data['current_tread_depth'] = $data['initial_tread_depth'];
        }

        $dataBefore = $tyre->toArray();
        $tyre->update($data);
        $tyre->load(['brand', 'size', 'pattern', 'location']);

        setLogActivity(auth()->id(), 'Memperbarui ban: ' . $request->serial_number, [
            'action_type' => 'update',
            'module' => 'Master Tyre',
            'data_before' => $dataBefore,
            'data_after' => [
                'Serial Number' => $tyre->serial_number,
                'Brand' => $tyre->brand->brand_name ?? '-',
                'Size' => $tyre->size->size ?? '-',
                'Segment' => $tyre->segment_name ?? '-',
                'Work Location' => $tyre->location->location_name ?? '-',
                'Status' => $tyre->status,
            ]
        ]);

        return redirect()->back()->with('success', 'Tyre updated successfully');
    }

    public function destroy($id)
    {
        $tyre = Tyre::findOrFail($id);

        if ($tyre->movements()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete tyre. it has movement history records.');
        }

        setLogActivity(auth()->id(), 'Menghapus ban: ' . $tyre->serial_number, [
            'action_type' => 'delete',
            'module' => 'Master Tyre',
            'data_before' => $tyre->toArray()
        ]);

        $tyre->update(['deleted_by' => auth()->id()]);
        $tyre->delete();

        return redirect()->back()->with('success', 'Tyre deleted successfully');
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids');
        $action = $request->input('action');

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        if ($action === 'delete') {
            $deletedCount = 0;
            $skippedCount = 0;

            foreach ($ids as $id) {
                $tyre = Tyre::find($id);
                if ($tyre) {
                    if ($tyre->movements()->exists()) {
                        $skippedCount++;
                    } else {
                        $tyre->delete();
                        $deletedCount++;
                    }
                }
            }

            setLogActivity(auth()->id(), "Bulk delete ban: $deletedCount berhasil, $skippedCount dilewati (ada riwayat)", [
                'action_type' => 'delete',
                'module' => 'Master Tyre',
                'ids' => $ids
            ]);

            $msg = "$deletedCount data ban berhasil dihapus.";
            if ($skippedCount > 0) {
                $msg .= " $skippedCount data dilewati karena memiliki riwayat pergerakan.";
            }

            return redirect()->back()->with($skippedCount > 0 ? 'warning' : 'success', $msg);
        }

        if ($action === 'update') {
            $data = [];
            if ($request->filled('status')) $data['status'] = $request->status;
            if ($request->filled('current_location_id')) $data['current_location_id'] = $request->current_location_id;
            if ($request->filled('segment_name')) $data['segment_name'] = $request->segment_name;
            if ($request->filled('retread_count')) $data['retread_count'] = $request->retread_count;

            if (empty($data)) {
                return redirect()->back()->with('error', 'Tidak ada field yang dipilih untuk diperbarui.');
            }

            Tyre::whereIn('id', $ids)->update($data);

            setLogActivity(auth()->id(), "Bulk update ban untuk " . count($ids) . " data", [
                'action_type' => 'update',
                'module' => 'Master Tyre',
                'ids' => $ids,
                'updated_fields' => $data
            ]);

            return redirect()->back()->with('success', count($ids) . ' data ban berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Aksi tidak dikenal.');
    }
}
