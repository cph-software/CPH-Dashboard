<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function storeCSV(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt',
            'module' => 'required'
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));
        
        if (count($data) < 2) {
            return redirect()->back()->with('error', 'File kosong atau format tidak valid.');
        }

        $header = array_shift($data);
        $header = array_map(function($h) {
            return strtolower(str_replace([' ', '-'], '_', trim($h)));
        }, $header);

        \DB::beginTransaction();
        try {
            $batch = \App\Models\ImportBatch::create([
                'user_id' => auth()->id(),
                'module' => $request->module,
                'filename' => $file->getClientOriginalName(),
                'status' => 'Pending',
                'total_rows' => count($data)
            ]);

            foreach ($data as $row) {
                if (count($header) !== count($row)) continue;
                
                // Trim all values to prevent issues with trailing spaces/newlines
                $row = array_map('trim', $row);
                $rowData = array_combine($header, $row);
                
                \App\Models\ImportItem::create([
                    'batch_id' => $batch->id,
                    'data' => $rowData,
                    'status' => 'Pending'
                ]);
            }

            \DB::commit();
            
            setLogActivity(auth()->id(), "Mengupload file import untuk modul {$request->module}", [
                'module' => 'Import',
                'batch_id' => $batch->id,
                'filename' => $batch->filename
            ]);

            return redirect()->back()->with('success', 'Data berhasil diupload dan menunggu persetujuan (Request ID: #' . $batch->id . ').');
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }
}
