<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyreSize;
use App\Models\TyreBrand;
use Illuminate\Http\Request;

class TyreSizeController extends Controller
{
    public function index()
    {
        $sizes = TyreSize::with('brand')->get();
        $brands = TyreBrand::where('status', 'Active')->get();
        return view('tyre-performance.master.sizes.index', compact('sizes', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'size' => 'required|string|max:255',
            'tyre_brand_id' => 'required|exists:tyre_brands,id',
            'type' => 'required|in:Bias,Radial',
            'std_otd' => 'nullable|numeric',
            'ply_rating' => 'nullable|integer',
        ]);

        TyreSize::create($request->all());

        return redirect()->back()->with('success', 'Size created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'size' => 'required|string|max:255',
            'tyre_brand_id' => 'required|exists:tyre_brands,id',
            'type' => 'required|in:Bias,Radial',
            'std_otd' => 'nullable|numeric',
            'ply_rating' => 'nullable|integer',
        ]);

        $size = TyreSize::findOrFail($id);
        $size->update($request->all());

        return redirect()->back()->with('success', 'Size updated successfully');
    }

    public function destroy($id)
    {
        $size = TyreSize::findOrFail($id);
        $size->delete();

        return redirect()->back()->with('success', 'Size deleted successfully');
    }
}
