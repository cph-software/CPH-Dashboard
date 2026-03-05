<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyreSize;
use App\Models\TyreBrand;
use App\Models\TyrePattern;
use Illuminate\Http\Request;

class TyreSizeController extends Controller
{
    public function index()
    {
        $sizes = TyreSize::with(['brand', 'pattern'])->latest()->get();
        $brands = TyreBrand::where('status', 'Active')->get();
        $patterns = TyrePattern::where('status', 'Active')->get();
        return view('tyre-performance.master.sizes.index', compact('sizes', 'brands', 'patterns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'size' => 'required|string|max:255',
            'tyre_brand_id' => 'required|exists:tyre_brands,id',
            'tyre_pattern_id' => 'nullable',
            'type' => 'required|in:Bias,Radial',
            'std_otd' => 'nullable|numeric',
            'ply_rating' => 'nullable|integer',
        ]);

        $data = $request->all();

        // Handle Custom Pattern (Tagging)
        if ($request->tyre_pattern_id && !is_numeric($request->tyre_pattern_id)) {
            $pattern = TyrePattern::firstOrCreate(
                ['name' => $request->tyre_pattern_id],
                ['tyre_brand_id' => $request->tyre_brand_id, 'status' => 'Active']
            );
            $data['tyre_pattern_id'] = $pattern->id;
        }

        $size = TyreSize::create($data);
        $size->load(['brand', 'pattern']);

        setLogActivity(auth()->id(), 'Menambah ukuran ban: ' . $request->size, [
            'action_type' => 'create',
            'module' => 'Sizes',
            'data_after' => [
                'Ukuran' => $size->size,
                'Brand' => $size->brand->brand_name ?? '-',
                'Pattern' => $size->pattern->name ?? '-',
                'Type' => $size->type,
                'OTD (mm)' => $size->std_otd,
                'Ply Rating' => $size->ply_rating,
            ]
        ]);

        return redirect()->back()->with('success', 'Size created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'size' => 'required|string|max:255',
            'tyre_brand_id' => 'required|exists:tyre_brands,id',
            'tyre_pattern_id' => 'nullable',
            'type' => 'required|in:Bias,Radial',
            'std_otd' => 'nullable|numeric',
            'ply_rating' => 'nullable|integer',
        ]);

        $size = TyreSize::findOrFail($id);
        $dataBefore = $size->toArray();
        $data = $request->all();

        // Handle Custom Pattern (Tagging)
        if ($request->tyre_pattern_id && !is_numeric($request->tyre_pattern_id)) {
            $pattern = TyrePattern::firstOrCreate(
                ['name' => $request->tyre_pattern_id],
                ['tyre_brand_id' => $request->tyre_brand_id, 'status' => 'Active']
            );
            $data['tyre_pattern_id'] = $pattern->id;
        }

        $size->update($data);
        $size->load(['brand', 'pattern']);

        setLogActivity(auth()->id(), 'Memperbarui ukuran ban: ' . $request->size, [
            'action_type' => 'update',
            'module' => 'Sizes',
            'data_before' => $dataBefore,
            'data_after' => [
                'Ukuran' => $size->size,
                'Brand' => $size->brand->brand_name ?? '-',
                'Pattern' => $size->pattern->name ?? '-',
                'Type' => $size->type,
                'OTD (mm)' => $size->std_otd,
                'Ply Rating' => $size->ply_rating,
            ]
        ]);

        return redirect()->back()->with('success', 'Size updated successfully');
    }

    public function destroy($id)
    {
        $size = TyreSize::findOrFail($id);

        if ($size->tyres()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete size. It is currently being used by some tyre records.');
        }

        setLogActivity(auth()->id(), 'Menghapus ukuran ban: ' . $size->size, [
            'action_type' => 'delete',
            'module' => 'Sizes',
            'data_before' => $size->toArray()
        ]);

        $size->delete();

        return redirect()->back()->with('success', 'Size deleted successfully');
    }
}
