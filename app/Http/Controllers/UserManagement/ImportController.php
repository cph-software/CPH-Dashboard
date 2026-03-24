<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function storeCSV(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'module' => 'required'
        ], [
            'file.mimes' => 'Format file harus Excel (.xlsx atau .xls).'
        ]);

        $file = $request->file('file');
        $module = $request->module;

        // Parse Excel file
        $data = \Maatwebsite\Excel\Facades\Excel::toArray([], $file)[0];

        // Filter baris kosong
        $data = array_filter($data, function($row) {
            return count(array_filter($row, function($cell) {
                return $cell !== null && trim($cell) !== '';
            })) > 0;
        });
        $data = array_values($data);

        if (count($data) < 2) {
            return redirect()->back()->with('error', 'File kosong atau format tidak valid. Pastikan baris pertama adalah header.');
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

            $imported = 0;
            foreach ($data as $row) {
                // Pastikan jumlah kolom sesuai header
                if (count($row) < count($header)) {
                    $row = array_pad($row, count($header), null);
                } elseif (count($row) > count($header)) {
                    $row = array_slice($row, 0, count($header));
                }

                $row = array_map(function($v) {
                    return $v !== null ? trim($v) : '';
                }, $row);
                $rowData = array_combine($header, $row);

                \App\Models\ImportItem::create([
                    'batch_id' => $batch->id,
                    'data' => $rowData,
                    'status' => 'Pending'
                ]);
                $imported++;
            }

            \DB::commit();

            setLogActivity(auth()->id(), "Import Excel ({$module}): {$imported} baris", [
                'module' => 'Import',
                'batch_id' => $batch->id,
                'filename' => $batch->filename
            ]);

            return redirect()->back()->with('success', "Data berhasil diupload ({$imported} baris) dan menunggu persetujuan (ID: #{$batch->id}).");
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }
}
