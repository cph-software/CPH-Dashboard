<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyrePattern;
use Illuminate\Http\Request;

class TyrePatternController extends Controller
{
    public function index()
    {
        $patterns = TyrePattern::all();
        return view('tyre-performance.master.patterns.index', compact('patterns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        TyrePattern::create($request->all());

        return redirect()->back()->with('success', 'Pattern created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $pattern = TyrePattern::findOrFail($id);
        $pattern->update($request->all());

        return redirect()->back()->with('success', 'Pattern updated successfully');
    }

    public function destroy($id)
    {
        $pattern = TyrePattern::findOrFail($id);
        $pattern->delete();

        return redirect()->back()->with('success', 'Pattern deleted successfully');
    }
}
