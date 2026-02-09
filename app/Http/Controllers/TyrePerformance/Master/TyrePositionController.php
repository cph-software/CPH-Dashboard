<?php

namespace App\Http\Controllers\TyrePerformance\Master;

use App\Http\Controllers\Controller;
use App\Models\TyrePositionConfiguration;
use App\Models\TyrePositionDetail;
use App\Models\Tyre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TyrePositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $configurations = TyrePositionConfiguration::latest()->paginate(9);
        return view('tyre-performance.master.positions.index', compact('configurations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tyre-performance.master.positions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:tyre_position_configurations,code',
            'description' => 'nullable|string',
            'front_axles' => 'required|integer|min:0|max:5',
            'rear_axles' => 'required|integer|min:0|max:10',
            'spare_tyres' => 'required|integer|min:0|max:5',
        ]);

        DB::beginTransaction();
        try {
            // Calculate total positions
            $totalPositions = ($validated['front_axles'] * 2) + 
                            ($validated['rear_axles'] * 4) + 
                            $validated['spare_tyres'];

            // Create configuration
            $configuration = TyrePositionConfiguration::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'total_positions' => $totalPositions,
                'total_spare' => $validated['spare_tyres'],
                'description' => $validated['description'] ?? null,
                'status' => 'Active',
            ]);

            // Generate positions
            $axleConfig = [
                'front' => $validated['front_axles'],
                'rear' => $validated['rear_axles'],
                'spare' => $validated['spare_tyres'],
            ];

            $positions = $configuration->generatePositions($axleConfig);
            
            // Insert positions
            foreach ($positions as $position) {
                TyrePositionDetail::create($position);
            }

            DB::commit();

            return redirect()->route('tyre-positions.index')
                ->with('success', 'Konfigurasi posisi ban berhasil dibuat dengan ' . $totalPositions . ' posisi');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat konfigurasi: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $configuration = TyrePositionConfiguration::with(['details' => function($query) {
            $query->orderBy('display_order');
        }])->findOrFail($id);

        $tyres = Tyre::all();

        return view('tyre-performance.master.positions.show', compact('configuration', 'tyres'));
    }

    public function getLayout($id)
    {
        $configuration = TyrePositionConfiguration::with('details')->findOrFail($id);
        return view('tyre-performance.master.positions._layout_visual', compact('configuration'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $configuration = TyrePositionConfiguration::with('details')->findOrFail($id);
        
        $frontAxles = $configuration->details->where('axle_type', 'Front')->max('axle_number') ?? 0;
        $rearAxles = $configuration->details->where('axle_type', 'Rear')->max('axle_number') ?? 0;
        $spareTyres = $configuration->total_spare;
        
        return view('tyre-performance.master.positions.edit', compact('configuration', 'frontAxles', 'rearAxles', 'spareTyres'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:tyre_position_configurations,code,' . $id,
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
            'front_axles' => 'required|integer|min:0|max:5',
            'rear_axles' => 'required|integer|min:0|max:10',
            'spare_tyres' => 'required|integer|min:0|max:5',
        ]);

        $configuration = TyrePositionConfiguration::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Calculate total positions
            $totalPositions = ($validated['front_axles'] * 2) + 
                            ($validated['rear_axles'] * 4) + 
                            $validated['spare_tyres'];
            
            // Update configuration
            $configuration->update([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'total_positions' => $totalPositions,
                'total_spare' => $validated['spare_tyres'],
                'description' => $validated['description'],
                'status' => $validated['status'],
            ]);
            
            // Re-generate positions
            // WARNING: This will delete existing details and recreate them. 
            // In a template-only model, this is acceptable.
            $configuration->details()->delete();
            
            $axleConfig = [
                'front' => $validated['front_axles'],
                'rear' => $validated['rear_axles'],
                'spare' => $validated['spare_tyres'],
            ];

            $positions = $configuration->generatePositions($axleConfig);
            
            foreach ($positions as $position) {
                TyrePositionDetail::create($position);
            }

            DB::commit();

            return redirect()->route('tyre-positions.index')
                ->with('success', 'Konfigurasi posisi ban berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate konfigurasi: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $configuration = TyrePositionConfiguration::findOrFail($id);
            
            // Delete all position details
            $configuration->details()->delete();
            
            // Delete configuration
            $configuration->delete();

            DB::commit();

            return redirect()->route('tyre-positions.index')
                ->with('success', 'Konfigurasi posisi ban berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus konfigurasi: ' . $e->getMessage());
        }
    }
}
