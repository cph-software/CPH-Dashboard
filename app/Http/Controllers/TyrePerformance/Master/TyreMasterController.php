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
        $tyres = Tyre::with(['brand', 'size', 'segment', 'pattern', 'location'])->get();
        $brands = TyreBrand::where('status', 'Active')->get();
        $sizes = TyreSize::all();
        $segments = TyreSegment::where('status', 'Active')->get();
        $patterns = TyrePattern::all();
        $locations = TyreLocation::all(); // Fetch all locations
        
        return view('tyre-performance.master.tyres.index', compact('tyres', 'brands', 'sizes', 'segments', 'patterns', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string|max:255|unique:tyres',
            'tyre_brand_id' => 'required|exists:tyre_brands,id',
            'tyre_size_id' => 'required|exists:tyre_sizes,id',
            'tyre_segment_id' => 'nullable|exists:tyre_segments,id',
            'tyre_pattern_id' => 'nullable|exists:master_import_pattern,id',
            'work_location_id' => 'required|exists:tyre_locations,id',
            'tyre_type' => 'required|string|max:255',
            'status' => 'required|in:New,Installed,Scrap,Repaired',
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
            'tyre_pattern_id' => 'nullable|exists:master_import_pattern,id',
            'work_location_id' => 'required|exists:tyre_locations,id',
            'tyre_type' => 'required|string|max:255',
            'status' => 'required|in:New,Installed,Scrap,Repaired',
        ]);

        $tyre = Tyre::findOrFail($id);
        $tyre->update($request->all());

        return redirect()->back()->with('success', 'Tyre updated successfully');
    }

    public function destroy($id)
    {
        $tyre = Tyre::findOrFail($id);
        $tyre->delete();

        return redirect()->back()->with('success', 'Tyre deleted successfully');
    }
}
