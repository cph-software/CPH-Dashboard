<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyreBrand;
use Illuminate\Http\Request;

class TyreBrandController extends Controller
{
    public function index()
    {
        $query = TyreBrand::latest();
        
        if (auth()->user()->role_id != 1) {
            $activeCompanyId = \App\Helpers\SessionCompanyHelper::getActiveCompanyId() ?? auth()->user()->tyre_company_id;
            $companyIds = is_array($activeCompanyId) ? $activeCompanyId : [$activeCompanyId];
            if (auth()->user()->tyre_company_id) {
                $companyIds[] = auth()->user()->tyre_company_id;
            }
            $companyIds = array_values(array_unique(array_filter($companyIds)));

            if (!empty($companyIds)) {
                $hasMapping = \DB::table('tyre_company_brands')->whereIn('tyre_company_id', $companyIds)->exists();
                if ($hasMapping) {
                    $query->whereHas('companies', function($q) use ($companyIds) {
                        $q->whereIn('tyre_company_id', $companyIds);
                    });
                }
            }
        }
        
        $brands = $query->get();

        return view('tyre-performance.master.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand_name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $brand = TyreBrand::create($request->all());

        // Automatically map to active company (e.g. customer) AND user's company
        $activeCompanyId = \App\Helpers\SessionCompanyHelper::getActiveCompanyId();
        $targetCompanyIds = [];
        if ($activeCompanyId) {
            if (is_array($activeCompanyId)) {
                $targetCompanyIds = array_merge($targetCompanyIds, $activeCompanyId);
            } else {
                $targetCompanyIds[] = (int) $activeCompanyId;
            }
        }
        if (auth()->user()->tyre_company_id) {
            $targetCompanyIds[] = (int) auth()->user()->tyre_company_id;
        }
        $targetCompanyIds = array_values(array_unique(array_filter($targetCompanyIds)));

        if (!empty($targetCompanyIds)) {
            $brand->companies()->syncWithoutDetaching($targetCompanyIds);
        }

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
