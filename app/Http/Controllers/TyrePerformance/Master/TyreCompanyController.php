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
        $companies = TyreCompany::withCount('users')->latest()->get();
        return view('tyre-performance.master.companies.index', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_tyre_capacity' => 'required|integer|min:0',
            'status' => 'required|in:Active,Inactive',
        ]);

        TyreCompany::create($request->all());

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
            'total_tyre_capacity' => 'required|integer|min:0',
            'status' => 'required|in:Active,Inactive',
        ]);

        $company = TyreCompany::findOrFail($id);
        $dataBefore = $company->toArray();
        $company->update($request->all());

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
        $company = TyreCompany::with(['brands', 'patterns', 'sizes'])->findOrFail($id);
        $allBrands = TyreBrand::orderBy('brand_name')->get();
        $allPatterns = TyrePattern::with('brand')->orderBy('name')->get();
        $allSizes = TyreSize::with('brand')->orderBy('size')->get();

        return view('tyre-performance.master.companies.mapping', compact('company', 'allBrands', 'allPatterns', 'allSizes'));
    }

    public function updateMapping(Request $request, $id)
    {
        $company = TyreCompany::findOrFail($id);

        $company->brands()->sync($request->input('brands', []));
        $company->patterns()->sync($request->input('patterns', []));
        $company->sizes()->sync($request->input('sizes', []));

        setLogActivity(auth()->id(), 'Memperbarui mapping data untuk: ' . $company->company_name, [
            'action_type' => 'update_mapping',
            'module' => 'Tyre Companies',
            'company_id' => $id
        ]);

        return redirect()->route('tyre-companies.index')->with('success', 'Mapping updated successfully');
    }
}
