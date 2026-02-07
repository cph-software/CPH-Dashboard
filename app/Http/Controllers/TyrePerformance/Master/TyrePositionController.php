<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyrePosition;
use Illuminate\Http\Request;

class TyrePositionController extends Controller
{
    public function index()
    {
        $positions = TyrePosition::orderBy('position_order')->get();
        return view('tyre-performance.master.positions.index', compact('positions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'position_code' => 'required|string|max:255',
            'axle' => 'required|in:Front,Middle,Rear',
            'side' => 'required|in:Left,Right',
            'position_order' => 'required|integer',
        ]);

        TyrePosition::create($request->all());

        return redirect()->back()->with('success', 'Position created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'position_code' => 'required|string|max:255',
            'axle' => 'required|in:Front,Middle,Rear',
            'side' => 'required|in:Left,Right',
            'position_order' => 'required|integer',
        ]);

        $position = TyrePosition::findOrFail($id);
        $position->update($request->all());

        return redirect()->back()->with('success', 'Position updated successfully');
    }

    public function destroy($id)
    {
        $position = TyrePosition::findOrFail($id);
        $position->delete();

        return redirect()->back()->with('success', 'Position deleted successfully');
    }
}
