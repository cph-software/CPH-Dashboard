<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyrePattern;
use Illuminate\Http\Request;

class TyrePatternController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $user->tyre_company_id;
        if ($user->role_id == 1 && session('active_company_id')) {
            $companyId = session('active_company_id');
        }

        $query = TyrePattern::with('brand');

        if ($companyId) {
            $company = \App\Models\TyreCompany::find($companyId);
            if ($company) {
                $query->whereIn('id', $company->patterns()->pluck('tyre_patterns.id'));
            }
        }

        $patterns = $query->latest()->get();
        return view('tyre-performance.master.patterns.index', compact('patterns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $pattern = TyrePattern::create($request->all());
        $pattern->load('brand');

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
