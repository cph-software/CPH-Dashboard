<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyrePattern;
use Illuminate\Http\Request;

class TyrePatternController extends Controller
{
    public function index()
    {
        $query = TyrePattern::with('brand')->latest();
        $brandQuery = \App\Models\TyreBrand::where('status', 'Active')->orderBy('brand_name');
        
        if (auth()->user()->role_id != 1) {
            $activeCompanyId = \App\Helpers\SessionCompanyHelper::getActiveCompanyId() ?? auth()->user()->tyre_company_id;
            $companyIds = is_array($activeCompanyId) ? $activeCompanyId : [$activeCompanyId];
            if (auth()->user()->tyre_company_id) {
                $companyIds[] = auth()->user()->tyre_company_id;
            }
            $companyIds = array_values(array_unique(array_filter($companyIds)));

            if (!empty($companyIds)) {
                $hasPatternMap = \DB::table('tyre_company_patterns')->whereIn('tyre_company_id', $companyIds)->exists();
                if ($hasPatternMap) {
                    $query->whereHas('companies', function($q) use ($companyIds) {
                        $q->whereIn('tyre_company_id', $companyIds);
                    });
                }
                
                $hasBrandMap = \DB::table('tyre_company_brands')->whereIn('tyre_company_id', $companyIds)->exists();
                if ($hasBrandMap) {
                    $brandQuery->whereHas('companies', function($q) use ($companyIds) {
                        $q->whereIn('tyre_company_id', $companyIds);
                    });
                }
            }
        }
        
        $patterns = $query->get();
        $brands = $brandQuery->get();

        return view('tyre-performance.master.patterns.index', compact('patterns', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tyre_brand_id' => 'required|exists:tyre_brands,id',
            'status' => 'required|in:Active,Inactive',
        ]);

        $pattern = TyrePattern::create($request->all());
        $pattern->load('brand');

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
            $pattern->companies()->syncWithoutDetaching($targetCompanyIds);
        }

        setLogActivity(auth()->id(), 'Menambah pattern ban: ' . $request->name, [
            'action_type' => 'create',
            'module' => 'Patterns',
            'data_after' => [
                'Pattern Name' => $pattern->name,
                'Brand' => $pattern->brand->brand_name ?? '-',
                'Status' => $pattern->status,
            ]
        ]);

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
        $dataBefore = $pattern->toArray();
        $pattern->update($request->all());
        $pattern->load('brand');

        setLogActivity(auth()->id(), 'Memperbarui pattern ban: ' . $request->name, [
            'action_type' => 'update',
            'module' => 'Patterns',
            'data_before' => $dataBefore,
            'data_after' => [
                'Pattern Name' => $pattern->name,
                'Brand' => $pattern->brand->brand_name ?? '-',
                'Status' => $pattern->status,
            ]
        ]);

        return redirect()->back()->with('success', 'Pattern updated successfully');
    }

    public function destroy($id)
    {
        $pattern = TyrePattern::findOrFail($id);

        if ($pattern->tyres()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete pattern. It is currently being used by some tyre records.');
        }

        setLogActivity(auth()->id(), 'Menghapus pattern ban: ' . $pattern->name, [
            'action_type' => 'delete',
            'module' => 'Patterns',
            'data_before' => $pattern->toArray()
        ]);

        $pattern->delete();

        return redirect()->back()->with('success', 'Pattern deleted successfully');
    }
}
