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
        // Removed eager loading of ALL tyres to improve performance
        // Data will be loaded via AJAX for the DataTable
        $brands = TyreBrand::where('status', 'Active')->get();
        $sizes = TyreSize::with('pattern')->get();
        $segments = TyreSegment::where('status', 'Active')->get();
        $patterns = TyrePattern::all();
        $locations = TyreLocation::all();

        return view('tyre-performance.master.tyres.index', compact('brands', 'sizes', 'segments', 'patterns', 'locations'));
    }

    /**
     * Data for Server-Side DataTables
     */
    public function data(Request $request)
    {
        $query = Tyre::with(['brand', 'size', 'segment', 'pattern', 'location']);

        // Search logic
        if ($request->has('search') && $request->input('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('serial_number', 'like', "%$searchValue%")
                    ->orWhereHas('brand', function ($sub) use ($searchValue) {
                        $sub->where('brand_name', 'like', "%$searchValue%");
                    })
                    ->orWhereHas('size', function ($sub) use ($searchValue) {
                        $sub->where('size', 'like', "%$searchValue%");
                    })
                    ->orWhere('status', 'like', "%$searchValue%");
            });
        }

        $totalRecords = Tyre::count();
        $filteredRecords = $query->count();

        // Ordering
        if ($request->has('order')) {
            $columnIndex = $request->input('order.0.column');
            $columnDir = $request->input('order.0.dir');

            // Map column index to DB field
            $cols = [
                1 => 'serial_number',
                2 => 'tyre_brand_id',
                3 => 'tyre_size_id',
                4 => 'tyre_pattern_id',
                5 => 'tyre_segment_id',
                6 => 'tyre_size_id', // Size type/id relationship
                7 => 'work_location_id',
                8 => 'status'
            ];

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
        $tyre = Tyre::with(['brand', 'size', 'pattern', 'segment', 'location', 'currentVehicle', 'currentPosition', 'movements.vehicle', 'movements.position'])
            ->findOrFail($id);

        return view('tyre-performance.master.tyres.show', compact('tyre'));
    }

    public function edit($id)
    {
        $tyre = Tyre::findOrFail($id);
        $brands = TyreBrand::where('status', 'Active')->get();
        $sizes = TyreSize::with('pattern')->get();
        $segments = TyreSegment::where('status', 'Active')->get();
        $patterns = TyrePattern::all();
        $locations = TyreLocation::all();

        return view('tyre-performance.master.tyres.edit', compact('tyre', 'brands', 'sizes', 'segments', 'patterns', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string|max:255|unique:tyres',
            'tyre_brand_id' => 'required|exists:tyre_brands,id',
            'tyre_size_id' => 'required|exists:tyre_sizes,id',
            'tyre_segment_id' => 'nullable|exists:tyre_segments,id',
            'tyre_pattern_id' => 'nullable|exists:tyre_patterns,id',
            'work_location_id' => 'required|exists:tyre_locations,id',
            'status' => 'required|in:New,Installed,Scrap,Repaired,Retread',
            'price' => 'nullable|numeric|min:0',
            'initial_tread_depth' => 'nullable|numeric|min:0',
            'current_tread_depth' => 'nullable|numeric|min:0',
            'retread_count' => 'nullable|integer|min:0',
        ]);

        $tyre = Tyre::create($request->all());
        $tyre->load(['brand', 'size', 'pattern', 'segment', 'location']);

        setLogActivity(auth()->id(), 'Menambah ban baru: ' . $request->serial_number, [
            'action_type' => 'create',
            'module' => 'Master Tyre',
            'data_after' => [
                'Serial Number' => $tyre->serial_number,
                'Brand' => $tyre->brand->brand_name ?? '-',
                'Size' => $tyre->size->size ?? '-',
                'Pattern' => $tyre->pattern->name ?? '-',
                'Segment' => $tyre->segment->segment_name ?? '-',
                'Work Location' => $tyre->location->location_name ?? '-',
                'Status' => $tyre->status,
                'Price' => $tyre->price,
                'Initial Tread Depth' => $tyre->initial_tread_depth,
            ]
        ]);

        return redirect()->back()->with('success', 'Tyre created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'serial_number' => 'required|string|max:255|unique:tyres,serial_number,' . $id,
            'tyre_brand_id' => 'required|exists:tyre_brands,id',
            'tyre_size_id' => 'required|exists:tyre_sizes,id',
            'tyre_segment_id' => 'nullable|exists:tyre_segments,id',
            'tyre_pattern_id' => 'nullable|exists:tyre_patterns,id',
            'work_location_id' => 'required|exists:tyre_locations,id',
            'status' => 'required|in:New,Installed,Scrap,Repaired,Retread',
            'price' => 'nullable|numeric|min:0',
            'initial_tread_depth' => 'nullable|numeric|min:0',
            'current_tread_depth' => 'nullable|numeric|min:0',
            'retread_count' => 'nullable|integer|min:0',
        ]);

        $tyre = Tyre::findOrFail($id);
        $tyre->load(['brand', 'size', 'pattern', 'segment', 'location']);
        
        $dataBefore = [
            'Serial Number' => $tyre->serial_number,
            'Brand' => $tyre->brand->brand_name ?? '-',
            'Size' => $tyre->size->size ?? '-',
            'Pattern' => $tyre->pattern->name ?? '-',
            'Segment' => $tyre->segment->segment_name ?? '-',
            'Work Location' => $tyre->location->location_name ?? '-',
            'Status' => $tyre->status,
        ];

        $tyre->update($request->all());
        $tyre->load(['brand', 'size', 'pattern', 'segment', 'location']); // Reload to get updated names

        setLogActivity(auth()->id(), 'Memperbarui ban: ' . $request->serial_number, [
            'action_type' => 'update',
            'module' => 'Master Tyre',
            'data_before' => $dataBefore,
            'data_after' => [
                'Serial Number' => $tyre->serial_number,
                'Brand' => $tyre->brand->brand_name ?? '-',
                'Size' => $tyre->size->size ?? '-',
                'Pattern' => $tyre->pattern->name ?? '-',
                'Segment' => $tyre->segment->segment_name ?? '-',
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
            if ($request->filled('work_location_id')) $data['work_location_id'] = $request->work_location_id;
            if ($request->filled('tyre_segment_id')) $data['tyre_segment_id'] = $request->tyre_segment_id;
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
