<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyreFailureAlias;
use Illuminate\Http\Request;

class TyreFailureAliasController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'tyre_failure_code_id' => 'required|exists:tyre_failure_codes,id',
            'tyre_company_id' => 'required|exists:tyre_companies,id',
            'alias_name' => 'required|string|max:255',
        ]);

        // Use updateOrCreate to prevent duplicates for same company-failure combo
        TyreFailureAlias::updateOrCreate(
            [
                'tyre_failure_code_id' => $request->tyre_failure_code_id,
                'tyre_company_id' => $request->tyre_company_id,
            ],
            ['alias_name' => $request->alias_name]
        );

        setLogActivity(auth()->id(), 'Menambah/Memperbarui alias failure code: ' . $request->alias_name, [
            'action_type' => 'create_alias',
            'module' => 'Failure Aliases',
            'data_after' => $request->all()
        ]);

        return redirect()->back()->with('success', 'Alias saved successfully');
    }

    public function destroy($id)
    {
        $alias = TyreFailureAlias::findOrFail($id);
        
        setLogActivity(auth()->id(), 'Menghapus alias failure code: ' . $alias->alias_name, [
            'action_type' => 'delete_alias',
            'module' => 'Failure Aliases',
            'data_before' => $alias->toArray()
        ]);

        $alias->delete();

        return redirect()->back()->with('success', 'Alias deleted successfully');
    }
}
