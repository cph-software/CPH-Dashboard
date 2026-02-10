<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyreSegment;
use App\Models\TyreLocation;
use Illuminate\Http\Request;

class TyreSegmentController extends Controller
{
    public function index()
    {
        $segments = TyreSegment::with('location')->get();
        $locations = TyreLocation::all();
        return view('tyre-performance.master.segments.index', compact('segments', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'segment_id' => 'required|string|max:255',
            'segment_name' => 'required|string|max:255',
            'tyre_location_id' => 'nullable|exists:tyre_locations,id',
            'terrain_type' => 'required|in:Muddy,Rocky,Asphalt',
            'status' => 'required|in:Active,Inactive',
        ]);

        TyreSegment::create($request->all());

        return redirect()->back()->with('success', 'Segment created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'segment_id' => 'required|string|max:255',
            'segment_name' => 'required|string|max:255',
            'tyre_location_id' => 'nullable|exists:tyre_locations,id',
            'terrain_type' => 'required|in:Muddy,Rocky,Asphalt',
            'status' => 'required|in:Active,Inactive',
        ]);

        $segment = TyreSegment::findOrFail($id);
        $segment->update($request->all());

        return redirect()->back()->with('success', 'Segment updated successfully');
    }

    public function destroy($id)
    {
        $segment = TyreSegment::findOrFail($id);

        if ($segment->tyres()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete segment. It is currently being used by some tyre records.');
        }

        $segment->delete();

        return redirect()->back()->with('success', 'Segment deleted successfully');
    }
}
