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
        $segments = TyreSegment::with('location')->latest()->get();
        $locations = TyreLocation::all();
        return view('tyre-performance.master.segments.index', compact('segments', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'segment_id' => 'required|string|max:255',
            'segment_name' => 'required|string|max:255',
            'tyre_location_id' => 'nullable|exists:tyre_locations,id',
            'terrain_type' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $segment = TyreSegment::create($request->all());
        $segment->load('location');

        setLogActivity(auth()->id(), 'Menambah segment: ' . $request->segment_name, [
            'action_type' => 'create',
            'module' => 'Segments',
            'data_after' => [
                'Segment ID' => $segment->segment_id,
                'Nama Segment' => $segment->segment_name,
                'Lokasi' => $segment->location->location_name ?? '-',
                'Tipe Medan' => $segment->terrain_type,
                'Status' => $segment->status,
            ]
        ]);

        return redirect()->back()->with('success', 'Segment created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'segment_id' => 'required|string|max:255',
            'segment_name' => 'required|string|max:255',
            'tyre_location_id' => 'nullable|exists:tyre_locations,id',
            'terrain_type' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $segment = TyreSegment::findOrFail($id);
        $dataBefore = $segment->toArray();
        $segment->update($request->all());
        $segment->load('location');

        setLogActivity(auth()->id(), 'Memperbarui segment: ' . $request->segment_name, [
            'action_type' => 'update',
            'module' => 'Segments',
            'data_before' => $dataBefore,
            'data_after' => [
                'Segment ID' => $segment->segment_id,
                'Nama Segment' => $segment->segment_name,
                'Lokasi' => $segment->location->location_name ?? '-',
                'Tipe Medan' => $segment->terrain_type,
                'Status' => $segment->status,
            ]
        ]);

        return redirect()->back()->with('success', 'Segment updated successfully');
    }

    public function destroy($id)
    {
        $segment = TyreSegment::findOrFail($id);

        if ($segment->tyres()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete segment. It is currently being used by some tyre records.');
        }

        setLogActivity(auth()->id(), 'Menghapus segment: ' . $segment->segment_name, [
            'action_type' => 'delete',
            'module' => 'Segments',
            'data_before' => $segment->toArray()
        ]);

        $segment->delete();

        return redirect()->back()->with('success', 'Segment deleted successfully');
    }
}
