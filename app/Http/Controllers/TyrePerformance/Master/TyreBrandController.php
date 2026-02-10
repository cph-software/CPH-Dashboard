<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyreBrand;
use Illuminate\Http\Request;

class TyreBrandController extends Controller
{
    public function index()
    {
        $brands = TyreBrand::latest()->get();
        return view('tyre-performance.master.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand_name' => 'required|string|max:255',
            'brand_type' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        TyreBrand::create($request->all());

        return redirect()->back()->with('success', 'Brand created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'brand_name' => 'required|string|max:255',
            'brand_type' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $brand = TyreBrand::findOrFail($id);
        $brand->update($request->all());

        return redirect()->back()->with('success', 'Brand updated successfully');
    }

    public function destroy($id)
    {
        $brand = TyreBrand::findOrFail($id);

        if ($brand->tyres()->exists() || $brand->sizes()->exists() || $brand->patterns()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete brand. It is currently being used by some size, pattern, or tyre records.');
        }

        $brand->delete();

        return redirect()->back()->with('success', 'Brand deleted successfully');
    }
}
