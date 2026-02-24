<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyreLocation;
use Illuminate\Http\Request;

class TyreLocationController extends Controller
{
    public function index()
    {
        $locations = TyreLocation::latest()->get();
        return view('tyre-performance.master.locations.index', compact('locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_name' => 'required|string|max:255',
            'location_type' => 'required|string|max:255',
            'capacity' => 'nullable|integer',
        ]);

        TyreLocation::create($request->all());

        setLogActivity(auth()->id(), 'Menambah lokasi: ' . $request->location_name, [
            'action_type' => 'create',
            'module' => 'Locations',
            'data_after' => $request->all()
        ]);

        return redirect()->back()->with('success', 'Location created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'location_name' => 'required|string|max:255',
            'location_type' => 'required|string|max:255',
            'capacity' => 'nullable|integer',
        ]);

        $location = TyreLocation::findOrFail($id);
        $dataBefore = $location->toArray();
        $location->update($request->all());

        setLogActivity(auth()->id(), 'Memperbarui lokasi: ' . $request->location_name, [
            'action_type' => 'update',
            'module' => 'Locations',
            'data_before' => $dataBefore,
            'data_after' => $request->all()
        ]);

        return redirect()->back()->with('success', 'Location updated successfully');
    }

    public function destroy($id)
    {
        $location = TyreLocation::findOrFail($id);

        if ($location->tyres()->exists() || $location->segments()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete location. It is currently being used by some segment or tyre records.');
        }

        setLogActivity(auth()->id(), 'Menghapus lokasi: ' . $location->location_name, [
            'action_type' => 'delete',
            'module' => 'Locations',
            'data_before' => $location->toArray()
        ]);

        $location->delete();

        return redirect()->back()->with('success', 'Location deleted successfully');
    }
}
