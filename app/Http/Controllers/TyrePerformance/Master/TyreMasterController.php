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
        $sizes = TyreSize::all();
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
                0 => 'serial_number',
                1 => 'tyre_brand_id',
                2 => 'tyre_size_id',
                3 => 'tyre_pattern_id',
                4 => 'tyre_segment_id',
                5 => 'tyre_type',
                6 => 'work_location_id',
                7 => 'status'
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

    public function store(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string|max:255|unique:tyres',
            'tyre_brand_id' => 'required|exists:tyre_brands,id',
            'tyre_size_id' => 'required|exists:tyre_sizes,id',
            'tyre_segment_id' => 'nullable|exists:tyre_segments,id',
            'tyre_pattern_id' => 'nullable|exists:tyre_patterns,id',
            'work_location_id' => 'required|exists:tyre_locations,id',
            'tyre_type' => 'required|string|max:255',
            'status' => 'required|in:New,Installed,Scrap,Repaired',
            'price' => 'nullable|numeric|min:0',
            'initial_tread_depth' => 'nullable|numeric|min:0',
            'current_tread_depth' => 'nullable|numeric|min:0',
            'retread_count' => 'nullable|integer|min:0',
        ]);

        Tyre::create($request->all());

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
            'tyre_type' => 'required|string|max:255',
            'status' => 'required|in:New,Installed,Scrap,Repaired',
            'price' => 'nullable|numeric|min:0',
            'initial_tread_depth' => 'nullable|numeric|min:0',
            'current_tread_depth' => 'nullable|numeric|min:0',
            'retread_count' => 'nullable|integer|min:0',
        ]);

        $tyre = Tyre::findOrFail($id);
        $tyre->update($request->all());

        return redirect()->back()->with('success', 'Tyre updated successfully');
    }

    public function destroy($id)
    {
        $tyre = Tyre::findOrFail($id);

        if ($tyre->movements()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete tyre. it has movement history records.');
        }

        $tyre->delete();

        return redirect()->back()->with('success', 'Tyre deleted successfully');
    }
}
