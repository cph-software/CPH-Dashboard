<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyrePattern;
use Illuminate\Http\Request;

class TyrePatternController extends Controller
{
    public function index()
    {
        $patterns = TyrePattern::latest()->get();
        return view('tyre-performance.master.patterns.index', compact('patterns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        TyrePattern::create($request->all());

        setLogActivity(auth()->id(), 'Menambah pattern ban: ' . $request->name, [
            'action_type' => 'create',
            'module' => 'Patterns',
            'data_after' => $request->all()
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
        $pattern->update($request->all());

        setLogActivity(auth()->id(), 'Memperbarui pattern ban: ' . $request->name, [
            'action_type' => 'update',
            'module' => 'Patterns',
            'data_after' => $request->all()
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
