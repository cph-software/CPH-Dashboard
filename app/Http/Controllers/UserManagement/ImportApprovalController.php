<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImportApprovalController extends Controller
{
    public function index()
    {
        $batches = \App\Models\ImportBatch::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user-management.import-approval.index', compact('batches'));
    }

    public function show($id)
    {
        $batch = \App\Models\ImportBatch::with(['user', 'items'])->findOrFail($id);
        return view('user-management.import-approval.show', compact('batch'));
    }

    public function approve($id)
    {
        $batch = \App\Models\ImportBatch::findOrFail($id);
        
        if ($batch->status !== 'Pending') {
            return redirect()->back()->with('error', 'Batch ini sudah diproses.');
        }

        $batch->update([
            'status' => 'Approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        // Logic to move data from import_items to actual tables 
        // will be implemented specifically per module later.
        
        setLogActivity(auth()->id(), "Menyetujui import batch #$id ({$batch->module})", [
            'module' => 'Import Approval',
            'batch_id' => $id
        ]);

        return redirect()->route('import-approval.index')->with('success', 'Batch berhasil disetujui dan sedang diproses.');
    }

    public function reject(Request $request, $id)
    {
        $batch = \App\Models\ImportBatch::findOrFail($id);
        
        $batch->update([
            'status' => 'Rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => $request->notes
        ]);

        setLogActivity(auth()->id(), "Menolak import batch #$id ({$batch->module})", [
            'module' => 'Import Approval',
            'batch_id' => $id,
            'reason' => $request->notes
        ]);

        return redirect()->route('import-approval.index')->with('warning', 'Batch telah ditolak.');
    }
}
