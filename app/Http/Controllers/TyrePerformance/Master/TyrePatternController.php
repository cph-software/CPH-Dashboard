<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyrePattern;
use Illuminate\Http\Request;

class TyrePatternController extends Controller
{
    public function index()
    {
        $patterns = TyrePattern::with('brand')->latest()->get();
        $brands = \App\Models\TyreBrand::where('status', 'Active')->get();
        return view('tyre-performance.master.patterns.index', compact('patterns', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tyre_brand_id' => 'required|exists:tyre_brands,id',
            'status' => 'required|in:Active,Inactive',
        ]);

        TyrePattern::create($request->all());

        return redirect()->back()->with('success', 'Pattern created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tyre_brand_id' => 'required|exists:tyre_brands,id',
            'status' => 'required|in:Active,Inactive',
        ]);

        $pattern = TyrePattern::findOrFail($id);
        $pattern->update($request->all());

        return redirect()->back()->with('success', 'Pattern updated successfully');
    }

    public function destroy($id)
    {
        $pattern = TyrePattern::findOrFail($id);

        if ($pattern->tyres()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete pattern. It is currently being used by some tyre records.');
        }

        $pattern->delete();

        return redirect()->back()->with('success', 'Pattern deleted successfully');
    }
}
