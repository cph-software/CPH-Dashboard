<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function storeCSV(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls',
            'module' => 'required'
        ]);

        $file = $request->file('file');
        $module = $request->module;
        
        // Handle Excel vs CSV
        if ($file->getClientOriginalExtension() === 'csv' || $file->getClientOriginalExtension() === 'txt') {
            $data = array_map('str_getcsv', file($file->getRealPath()));
        } else {
            $data = \Maatwebsite\Excel\Facades\Excel::toArray([], $file)[0];
        }

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
                'module' => $module,
                'filename' => $file->getClientOriginalName(),
                'status' => 'Pending',
                'total_rows' => count($data)
            ]);

            foreach ($data as $row) {
                if (count($header) !== count($row)) continue;
                
                $row = array_map('trim', $row);
                $rowData = array_combine($header, $row);
                
                \App\Models\ImportItem::create([
                    'batch_id' => $batch->id,
                    'data' => $rowData,
                    'status' => 'Pending'
                ]);
            }

            \DB::commit();
            return redirect()->back()->with('success', 'Data berhasil diupload dan menunggu persetujuan (ID: #' . $batch->id . ').');
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }
}
