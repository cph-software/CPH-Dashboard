<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyreLocation;
use Illuminate\Http\Request;

class TyreLocationController extends Controller
{
    public function index()
    {
        $locations = TyreLocation::all();
        return view('tyre-performance.master.locations.index', compact('locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_name' => 'required|string|max:255',
            'location_type' => 'required|in:Warehouse,Service,Disposal',
            'capacity' => 'nullable|integer',
        ]);

        TyreLocation::create($request->all());

        return redirect()->back()->with('success', 'Location created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'location_name' => 'required|string|max:255',
            'location_type' => 'required|in:Warehouse,Service,Disposal',
            'capacity' => 'nullable|integer',
        ]);

        $location = TyreLocation::findOrFail($id);
        $location->update($request->all());

        return redirect()->back()->with('success', 'Location updated successfully');
    }

    public function destroy($id)
    {
        $location = TyreLocation::findOrFail($id);
        $location->delete();

        return redirect()->back()->with('success', 'Location deleted successfully');
    }
}
