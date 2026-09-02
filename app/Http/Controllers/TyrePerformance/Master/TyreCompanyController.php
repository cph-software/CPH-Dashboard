<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyreCompany;
use App\Models\TyreBrand;
use App\Models\TyrePattern;
use App\Models\TyreSize;
use Illuminate\Http\Request;

class TyreCompanyController extends Controller
{
    public function index()
    {
        $query = TyreCompany::with(['parent', 'children'])->withCount([
            'users',
            'tyres' => function ($query) {
                $query->withoutGlobalScope('company');
            }
        ])->latest();

        // Workshop Admin: only see their own company + their clients
        if (\App\Helpers\SessionCompanyHelper::isWorkshopAdmin() && !\App\Helpers\SessionCompanyHelper::isSuperAdmin()) {
            $ownId = auth()->user()->tyre_company_id;
            $query->where(function($q) use ($ownId) {
                $q->where('id', $ownId)
                  ->orWhere('parent_company_id', $ownId);
            });
        }

        $companies = $query->get();

        return view('tyre-performance.master.companies.index', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_company_id' => 'nullable|integer|exists:tyre_companies,id',
            'total_tyre_capacity' => 'required|integer|min:0',
            'max_users' => 'required|integer|min:1',
            'measurement_mode' => 'required|in:KM,HM,BOTH',
            'status' => 'required|in:Active,Inactive',
        ]);

        $data = $request->all();
        // Convert empty string to null for parent_company_id
        if (empty($data['parent_company_id'])) $data['parent_company_id'] = null;
        // Workshop Admin: force parent to their own company
        if (\App\Helpers\SessionCompanyHelper::isWorkshopAdmin() && !\App\Helpers\SessionCompanyHelper::isSuperAdmin()) {
            $data['parent_company_id'] = auth()->user()->tyre_company_id;
        }
        $data['total_tyres'] = (int)($data['total_tyre_capacity'] ?? 0);
        TyreCompany::create($data);

        setLogActivity(auth()->id(), 'Menambah instansi tyre: ' . $request->company_name, [
            'action_type' => 'create',
            'module' => 'Tyre Companies',
            'data_after' => $request->all()
        ]);

        return redirect()->back()->with('success', 'Company created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_company_id' => 'nullable|integer|exists:tyre_companies,id',
            'total_tyre_capacity' => 'required|integer|min:0',
            'max_users' => 'required|integer|min:1',
            'measurement_mode' => 'required|in:KM,HM,BOTH',
            'status' => 'required|in:Active,Inactive',
        ]);

        $company = TyreCompany::findOrFail($id);
        $dataBefore = $company->toArray();
        $data = $request->all();
        if (empty($data['parent_company_id'])) $data['parent_company_id'] = null;
        if (isset($data['total_tyre_capacity'])) {
            $data['total_tyres'] = (int)$data['total_tyre_capacity'];
        }
        $company->update($data);

        setLogActivity(auth()->id(), 'Memperbarui instansi tyre: ' . $request->company_name, [
            'action_type' => 'update',
            'module' => 'Tyre Companies',
            'data_before' => $dataBefore,
            'data_after' => $request->all()
        ]);

        return redirect()->back()->with('success', 'Company updated successfully');
    }

    public function destroy($id)
    {
        $company = TyreCompany::findOrFail($id);

        if ($company->users()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete company. It is currently associated with users.');
        }

        setLogActivity(auth()->id(), 'Menghapus instansi tyre: ' . $company->company_name, [
            'action_type' => 'delete',
            'module' => 'Tyre Companies',
            'data_before' => $company->toArray()
        ]);

        $company->delete();

        return redirect()->back()->with('success', 'Company deleted successfully');
    }

    // API helper for JSON response
    public function show($id)
    {
        return response()->json(TyreCompany::findOrFail($id));
    }

    public function mapping($id)
    {
        $company = TyreCompany::with(['brands', 'patterns', 'sizes', 'roles'])->findOrFail($id);
        $allBrands = TyreBrand::orderBy('brand_name')->get();
        $allPatterns = TyrePattern::with('brand')->orderBy('name')->get();
        $allSizes = TyreSize::with('brand')->orderBy('size')->get();
        $allRoles = \App\Models\Role::where('id', '!=', 1)->orderBy('name')->get();

        return view('tyre-performance.master.companies.mapping', compact('company', 'allBrands', 'allPatterns', 'allSizes', 'allRoles'));
    }

    public function updateMapping(Request $request, $id)
    {
        $company = TyreCompany::findOrFail($id);

        $company->brands()->sync($request->input('brands', []));
        $company->patterns()->sync($request->input('patterns', []));
        $company->sizes()->sync($request->input('sizes', []));
        $company->roles()->sync($request->input('roles', []));

        setLogActivity(auth()->id(), 'Memperbarui mapping data untuk: ' . $company->company_name, [
            'action_type' => 'update_mapping',
            'module' => 'Tyre Companies',
            'company_id' => $id
        ]);

        return redirect()->route('tyre-companies.index')->with('success', 'Mapping updated successfully');
    }
}
