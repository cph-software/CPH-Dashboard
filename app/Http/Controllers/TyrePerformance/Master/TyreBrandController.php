<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyreBrand;
use Illuminate\Http\Request;

class TyreBrandController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $user->tyre_company_id;
        if ($user->role_id == 1 && session('active_company_id')) {
            $companyId = session('active_company_id');
        }

        $query = TyreBrand::query();

        if ($companyId) {
            $company = \App\Models\TyreCompany::find($companyId);
            if ($company) {
                $query->whereIn('id', $company->brands()->pluck('tyre_brands.id'));
            }
        }

        $brands = $query->latest()->get();
        return view('tyre-performance.master.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand_name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $brand = TyreBrand::create($request->all());

        setLogActivity(auth()->id(), 'Menambah brand ban: ' . $request->brand_name, [
            'action_type' => 'create',
            'module' => 'Brands',
            'data_after' => $request->all()
        ]);

        return redirect()->back()->with('success', 'Brand created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'brand_name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $brand = TyreBrand::findOrFail($id);
        $dataBefore = $brand->toArray();
        $brand->update($request->all());

        setLogActivity(auth()->id(), 'Memperbarui brand ban: ' . $request->brand_name, [
            'action_type' => 'update',
            'module' => 'Brands',
            'data_before' => $dataBefore,
            'data_after' => $request->all()
        ]);

        return redirect()->back()->with('success', 'Brand updated successfully');
    }

    public function destroy($id)
    {
        $brand = TyreBrand::findOrFail($id);

        if ($brand->tyres()->exists() || $brand->sizes()->exists() || $brand->patterns()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete brand. It is currently being used by some size, pattern, or tyre records.');
        }

        setLogActivity(auth()->id(), 'Menghapus brand ban: ' . $brand->brand_name, [
            'action_type' => 'delete',
            'module' => 'Brands',
            'data_before' => $brand->toArray()
        ]);

        $brand->delete();

        return redirect()->back()->with('success', 'Brand deleted successfully');
    }
}
