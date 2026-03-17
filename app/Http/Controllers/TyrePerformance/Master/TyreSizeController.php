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
        $user = auth()->user();
        $companyId = $user->tyre_company_id;
        if ($user->role_id == 1 && session('active_company_id')) {
            $companyId = session('active_company_id');
        }

        $query = TyreSize::with(['brand', 'pattern']);

        if ($companyId) {
            $company = \App\Models\TyreCompany::find($companyId);
            if ($company) {
                $query->whereIn('id', $company->sizes()->pluck('tyre_sizes.id'));
            }
        }

        $sizes = $query->latest()->get();
        $brands = TyreBrand::where('status', 'Active')->get();

        // Also filter brands in dropdown if company context exists
        if ($companyId) {
            $company = \App\Models\TyreCompany::find($companyId);
            if ($company) {
                $brands = TyreBrand::whereIn('id', $company->brands()->pluck('tyre_brands.id'))->where('status', 'Active')->get();
            }
        }

        return view('tyre-performance.master.sizes.index', compact('sizes', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'size' => 'required|string|max:255',
            'tyre_brand_id' => 'required|exists:tyre_brands,id',
            'std_otd' => 'nullable|numeric',
            'ply_rating' => 'nullable|integer',
        ]);

        $data = $request->all();

        $size = TyreSize::create($data);
        $size->load(['brand', 'pattern']);

        setLogActivity(auth()->id(), 'Menambah ukuran ban: ' . $request->size, [
            'action_type' => 'create',
            'module' => 'Sizes',
            'data_after' => [
                'Ukuran' => $size->size,
                'Brand' => $size->brand->brand_name ?? '-',
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
            'std_otd' => 'nullable|numeric',
            'ply_rating' => 'nullable|integer',
        ]);

        $size = TyreSize::findOrFail($id);
        $dataBefore = $size->toArray();
        $data = $request->all();

        $size->update($data);
        $size->load(['brand', 'pattern']);

        setLogActivity(auth()->id(), 'Memperbarui ukuran ban: ' . $request->size, [
            'action_type' => 'update',
            'module' => 'Sizes',
            'data_before' => $dataBefore,
            'data_after' => [
                'Ukuran' => $size->size,
                'Brand' => $size->brand->brand_name ?? '-',
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
