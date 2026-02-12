<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyreFailureCode;
use Illuminate\Http\Request;

class TyreFailureCodeController extends Controller
{
    public function index()
    {
        $failureCodes = TyreFailureCode::latest()->get();
        return view('tyre-performance.master.failure-codes.index', compact('failureCodes'));
    }

    public function create()
    {
        return view('tyre-performance.master.failure-codes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'failure_code' => 'required|string|max:255',
            'failure_name' => 'required|string|max:255',
            'image_1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'default_category' => 'required|in:Scrap,Repair,Claim',
            'status' => 'required|in:Active,Inactive',
        ]);

        $data = $request->except(['image_1', 'image_2']);

        if ($request->hasFile('image_1')) {
            $data['image_1'] = $request->file('image_1')->store('tyre-failures', 'public');
        }

        if ($request->hasFile('image_2')) {
            $data['image_2'] = $request->file('image_2')->store('tyre-failures', 'public');
        }

        TyreFailureCode::create($data);

        return redirect()->route('tyre-failure-codes.index')->with('success', 'Failure code created successfully');
    }

    public function show($id)
    {
        $failureCode = TyreFailureCode::findOrFail($id);
        return view('tyre-performance.master.failure-codes.show', compact('failureCode'));
    }

    public function edit($id)
    {
        $failureCode = TyreFailureCode::findOrFail($id);
        return view('tyre-performance.master.failure-codes.edit', compact('failureCode'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'failure_code' => 'required|string|max:255',
            'failure_name' => 'required|string|max:255',
            'image_1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'default_category' => 'required|in:Scrap,Repair,Claim',
            'status' => 'required|in:Active,Inactive',
        ]);

        $failureCode = TyreFailureCode::findOrFail($id);
        $data = $request->except(['image_1', 'image_2']);

        if ($request->hasFile('image_1')) {
            // Ideally delete old image here if exists
            $data['image_1'] = $request->file('image_1')->store('tyre-failures', 'public');
        }

        if ($request->hasFile('image_2')) {
            $data['image_2'] = $request->file('image_2')->store('tyre-failures', 'public');
        }

        $failureCode->update($data);

        return redirect()->route('tyre-failure-codes.index')->with('success', 'Failure code updated successfully');
    }

    public function destroy($id)
    {
        $failureCode = TyreFailureCode::findOrFail($id);

        if ($failureCode->movements()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete failure code. It is currently associated with some movement history records.');
        }

        $failureCode->delete();

        return redirect()->back()->with('success', 'Failure code deleted successfully');
    }
}
