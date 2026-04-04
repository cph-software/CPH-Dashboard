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
            // === VALIDASI PRE-CHECK KESELURUHAN MODUL ===
            // Mengumpulkan data untuk divalidasi
            $snArray = [];
            $unitArray = [];
            $layoutArray = [];

            foreach ($data as $row) {
                $rowSafe = array_pad($row, count($header), null);
                $rowSafe = array_slice($rowSafe, 0, count($header));
                $rowData = array_combine($header, $rowSafe);
                
                if (in_array($module, ['Movement History'])) {
                    $sn = $rowData['serial_number'] ?? $rowData['sn_ban'] ?? null;
                    if (!empty(trim($sn))) $snArray[] = strtoupper(trim($sn));
                }

                if (in_array($module, ['Movement History', 'Tyre Examination', 'Vehicle Master', 'Master Vehicle'])) {
                    $unitCode = $rowData['kode_kendaraan'] ?? $rowData['unit'] ?? null;
                    if (!empty(trim($unitCode))) $unitArray[] = strtoupper(trim($unitCode));
                }

                if (in_array($module, ['Vehicle Master', 'Master Vehicle'])) {
                    $layout = $rowData['layout'] ?? $rowData['konfigurasi'] ?? null;
                    if (!empty(trim($layout))) $layoutArray[] = strtoupper(trim($layout));
                }
            }
            
            // 1. Pengecekan Serial Number (Untuk Movement)
            if (!empty($snArray)) {
                $snArray = array_unique($snArray);
                $existingTyres = [];
                foreach(array_chunk($snArray, 1000) as $chunk) {
                    $found = \App\Models\Tyre::withoutGlobalScopes()->whereIn('serial_number', $chunk)->pluck('serial_number')->toArray();
                    $existingTyres = array_merge($existingTyres, array_map('strtoupper', $found));
                }
                
                $missingSn = array_diff($snArray, $existingTyres);
                if (count($missingSn) > 0) {
                    $missingList = implode(', ', array_slice(array_values($missingSn), 0, 10));
                    throw new \Exception('Ditolak Otomatis (Auto Reject): Terdapat ' . count($missingSn) . ' Serial Number Ban yang belum terdaftar di Master Tyre. Harap daftarkan dulu: ' . $missingList);
                }
            }

            // 2. Pengecekan Kode Kendaraan (Untuk Movement, Examination)
            if (!empty($unitArray) && !in_array($module, ['Vehicle Master', 'Master Vehicle'])) {
                $unitArray = array_unique($unitArray);
                $existingUnits = [];
                foreach(array_chunk($unitArray, 1000) as $chunk) {
                    $foundUnits = \App\Models\MasterImportKendaraan::withoutGlobalScopes()->whereIn('kode_kendaraan', $chunk)->pluck('kode_kendaraan')->toArray();
                    $existingUnits = array_merge($existingUnits, array_map('strtoupper', $foundUnits));
                }

                $missingUnits = array_diff($unitArray, $existingUnits);
                if (count($missingUnits) > 0) {
                    $missingUnitsList = implode(', ', array_slice(array_values($missingUnits), 0, 10));
                    throw new \Exception('Ditolak Otomatis (Auto Reject): Terdapat ' . count($missingUnits) . ' Kode Kendaraan/Unit yang belum terdaftar di Master Vehicle. Harap jadikan template unit berikut di Master Unit dulu: ' . $missingUnitsList);
                }
            }

            // 3. Pengecekan Layout / Konfigurasi Posisi (Untuk Vehicle Master)
            if (!empty($layoutArray)) {
                $layoutArray = array_unique($layoutArray);
                $existingLayouts = [];
                foreach(array_chunk($layoutArray, 1000) as $chunk) {
                    $foundLayouts = \App\Models\TyrePositionConfiguration::withoutGlobalScopes()->whereIn('name', $chunk)->pluck('name')->toArray();
                    $existingLayouts = array_merge($existingLayouts, array_map('strtoupper', $foundLayouts));
                }

                $missingLayouts = array_diff($layoutArray, $existingLayouts);
                if (count($missingLayouts) > 0) {
                    $missingLayoutsList = implode(', ', array_slice(array_values($missingLayouts), 0, 10));
                    throw new \Exception('Ditolak Otomatis (Auto Reject): Terdapat ' . count($missingLayouts) . ' Konfigurasi Roda (Layout) yang tidak dikenali sistem. Pastikan layout berikut sudah disetting di menu Position Configuration: ' . $missingLayoutsList);
                }
            }
            // ====================================================

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
