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
        $rawData = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $file)[0];

        // ============================================================
        // MOVEMENT HISTORY: Special handling for wide format
        // ============================================================
        if (in_array($module, ['Movement History'])) {
            return $this->handleMovementImport($rawData, $module, $file);
        }

        // ============================================================
        // OTHER MODULES: Standard import flow
        // ============================================================
        return $this->handleStandardImport($rawData, $module, $file);
    }

    /**
     * Movement History import — supports both WIDE format (8 groups) and NARROW format.
     * WIDE: Each row = 1 tyre, columns = 8 installation/removal cycles.
     * NARROW: Each row = 1 movement event.
     */
    private function handleMovementImport($rawData, $module, $file)
    {
        // Step 1: Find the actual data start row (skip merged headers)
        $dataStartIdx = null;
        $snColIdx = 1; // NO SERI typically column B (index 1)

        for ($i = 0; $i < min(15, count($rawData)); $i++) {
            $row = $rawData[$i];
            // Check columns 0-3 for serial number pattern (8+ chars, alphanumeric like "23282I06173")
            for ($c = 0; $c <= min(3, count($row) - 1); $c++) {
                $val = trim((string)($row[$c] ?? ''));
                if (strlen($val) >= 8 && preg_match('/^\d{3,}[A-Za-z]\d+$/', $val)) {
                    $dataStartIdx = $i;
                    $snColIdx = $c;
                    break 2;
                }
            }
        }

        if ($dataStartIdx === null) {
            // Fallback: look for header row with "NO SERI" or "serial_number" keyword
            for ($i = 0; $i < min(10, count($rawData)); $i++) {
                $rowStr = strtoupper(implode('|', array_map(function($v) {
                    return trim((string)($v ?? ''));
                }, $rawData[$i])));
                if (strpos($rowStr, 'NO SERI') !== false || strpos($rowStr, 'SERIAL') !== false) {
                    $dataStartIdx = $i + 1; // Data starts after this header row
                    break;
                }
            }
        }

        if ($dataStartIdx === null) {
            return redirect()->back()->with('error',
                'Format file tidak dikenali. Pastikan file memiliki kolom NO SERI atau SERIAL NUMBER.');
        }

        $dataRows = array_slice($rawData, $dataStartIdx);

        // Step 2: Count actual columns to detect format
        $firstRow = $dataRows[0] ?? [];
        $colCount = count($firstRow);

        // WIDE format: > 20 columns (8 groups × 6 cols + 2 id + 5 summary = 55)
        if ($colCount > 20) {
            $result = $this->expandWideMovement($dataRows, $snColIdx);
        } else {
            // NARROW format: standard 13 columns
            // FIX: Jika data ditemukan via regex (bukan via header keyword),
            // header row TIDAK termasuk di $dataRows. Kita perlu cari header
            // di baris-baris SEBELUM dataStartIdx.
            $headerRow = null;
            for ($h = $dataStartIdx - 1; $h >= 0; $h--) {
                $rowStr = strtoupper(implode('|', array_map(function($v) {
                    return trim((string)($v ?? ''));
                }, $rawData[$h])));
                
                // Cari baris yang mengandung keyword header
                if (strpos($rowStr, 'NO SERI') !== false 
                    || strpos($rowStr, 'NO_SERI') !== false
                    || strpos($rowStr, 'SERIAL') !== false
                    || strpos($rowStr, 'UNIT') !== false) {
                    $headerRow = $rawData[$h];
                    
                    // Untuk merged headers: gabungkan dengan sub-header (baris berikutnya)
                    // Contoh: Baris 2 = ["No", "NO SERI", "UNIT", "POSISI BAN", "PEMASANGAN", "", "PELEPASAN", ""]
                    //         Baris 3 = ["", "", "", "", "TANGGAL", "KM", "TANGGAL", "KM"]
                    // Hasil:  ["No", "NO SERI", "UNIT", "POSISI BAN", "PEMASANGAN TANGGAL", "PEMASANGAN KM", "PELEPASAN TANGGAL", "PELEPASAN KM"]
                    if ($h + 1 < $dataStartIdx) {
                        $subRow = $rawData[$h + 1] ?? [];
                        $parentGroup = '';
                        for ($ci = 0; $ci < count($headerRow); $ci++) {
                            $parent = trim((string)($headerRow[$ci] ?? ''));
                            $sub = trim((string)($subRow[$ci] ?? ''));
                            
                            // Track parent group name (PEMASANGAN, PELEPASAN)
                            if (!empty($parent)) {
                                $parentGroup = $parent;
                            }
                            
                            // Jika cell header kosong tapi sub-header ada → gabungkan parent + sub
                            if (empty($parent) && !empty($sub)) {
                                $headerRow[$ci] = $parentGroup . ' ' . $sub;
                            } else if (!empty($parent) && !empty($sub)) {
                                $headerRow[$ci] = $parent . ' ' . $sub;
                            }
                        }
                    }
                    break;
                }
            }

            // Jika header ditemukan, sisipkan di awal dataRows agar parseNarrowMovement bisa array_shift()
            if ($headerRow) {
                array_unshift($dataRows, $headerRow);
            }
            
            $result = $this->parseNarrowMovement($dataRows);
        }

        $header = $result['header'];
        $expandedData = $result['data'];

        if (count($expandedData) === 0) {
            return redirect()->back()->with('error', 'Tidak ada data movement yang valid ditemukan di file.');
        }

        \DB::beginTransaction();
        try {
            $batch = \App\Models\ImportBatch::create([
                'user_id' => auth()->id(),
                'module' => $module,
                'filename' => $file->getClientOriginalName(),
                'status' => 'Pending',
                'total_rows' => count($expandedData)
            ]);

            $imported = 0;
            $invalidCount = 0;
            $insertData = [];
            $now = now()->toDateTimeString();

            foreach ($expandedData as $row) {
                $rowData = array_combine($header, array_pad(array_slice($row, 0, count($header)), count($header), ''));

                // Perform pre-validation to tag valid vs invalid rows
                $rowData['_validation'] = $this->validateMovementRow($rowData);
                if (!$rowData['_validation']['is_valid']) {
                    $invalidCount++;
                }

                $insertData[] = [
                    'batch_id'   => $batch->id,
                    'data'       => json_encode($rowData),
                    'status'     => 'Pending',
                    'created_at' => $now,
                    'updated_at' => $now
                ];
                $imported++;
            }

            foreach (array_chunk($insertData, 500) as $chunk) {
                \App\Models\ImportItem::insert($chunk);
            }

            \DB::commit();

            $logOptions = [
                'module' => 'Import Approval',
                'batch_id' => $batch->id,
                'filename' => $batch->filename
            ];

            if ($invalidCount > 0) {
                $logOptions['action_type'] = 'error';
                $logOptions['Pesan Error'] = [
                    "Dari total {$imported} baris data, terdapat {$invalidCount} data yang berstatus 'Perlu Diperbaiki'.",
                    "Silahkan tinjau dan perbaiki di halaman Log Approval."
                ];
            }

            setLogActivity(auth()->id(), "Import Excel {$module} Selesai Diunggah", $logOptions);

            return redirect()->back()->with('success',
                "Data berhasil diupload ({$imported} data movement) dan menunggu persetujuan (ID: #{$batch->id}).");
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    private function validateMovementRow($data)
    {
        $sn = $data['serial_number'] ?? $data['sn_ban'] ?? $data['no_seri'] ?? null;
        $sn = strtoupper(trim((string)$sn));
        $errors = [];
        $warnings = [];

        // 1. Serial Number wajib
        if (empty($sn)) {
            $errors[] = "Nomor Seri kosong.";
        } else {
            // Cek apakah SN terdaftar di database
            $tyreExists = \App\Models\Tyre::withoutGlobalScopes()->where('serial_number', $sn)->exists();
            if (!$tyreExists) {
                $errors[] = "Ban SN '{$sn}' tidak ditemukan di Master Tyre.";
            }
        }

        // 2. Tanggal
        $installDateRaw = $data['pemasangan_tanggal'] ?? null;
        $installDate = !empty($installDateRaw) && trim($installDateRaw) !== '0';

        $removeDateRaw = $data['pelepasan_tanggal'] ?? null;
        $removeDate = !empty($removeDateRaw) && trim($removeDateRaw) !== '0';

        $keterangan = strtoupper(trim($data['keterangan'] ?? ''));
        $isScrapOnly = in_array($keterangan, ['BUANG', 'SCRAP', 'DISPOSAL']);
        
        $unitCode = trim($data['kode_kendaraan'] ?? $data['unit'] ?? '');

        if (!$installDate && !$isScrapOnly) {
            $errors[] = "Tanggal Pemasangan kosong dan bukan pembuangan (Scrap).";
        }

        // 3. Kendaraan
        if ($installDate && empty($unitCode)) {
            $errors[] = "Pemasangan memerlukan Unit Kendaraan yang diisi.";
        }

        if (!empty($unitCode)) {
            $vehicleExists = \App\Models\MasterImportKendaraan::withoutGlobalScopes()
                ->where('kode_kendaraan', strtoupper($unitCode))
                ->exists();
            if (!$vehicleExists) {
                $errors[] = "Unit '{$unitCode}' tidak ditemukan di Master Vehicle.";
            }
        }

        // 4. Odometer anomali (warning, bukan error)
        $installKm = (float)($data['pemasangan_km'] ?? 0);
        $removeKm = (float)($data['pelepasan_km'] ?? 0);
        if ($removeDate && $installDate && $removeKm > 0 && $installKm > 0 && $removeKm < $installKm) {
            $warnings[] = "Odometer anomali: KM Lepas ({$removeKm}) < KM Pasang ({$installKm}).";
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Expand WIDE format: 1 row with 8 groups → multiple movement rows.
     *
     * Excel structure:
     * Col 0: No (skip)
     * Col 1: NO SERI (serial_number)
     * Group 1 (cols 2-7):  UNIT, POSISI BAN, PEMASANGAN_TGL, PEMASANGAN_KM, PELEPASAN_TGL, PELEPASAN_KM
     * Group 2 (cols 8-13):  same structure...
     * ... up to Group 8 (cols 44-49)
     * Col 50: JARAK TEMPUH (skip, calculated)
     * Col 51: TOTAL HARI (skip, calculated)
     * Col 52: KETERANGAN
     * Col 53: TEBAL TELAPAK
     * Col 54: PENYEBAB
     */
    private function expandWideMovement($dataRows, $snColIdx = 1)
    {
        $header = [
            'serial_number', 'kode_kendaraan', 'position_code',
            'pemasangan_tanggal', 'pemasangan_km',
            'pelepasan_tanggal', 'pelepasan_km',
            'keterangan', 'tebal_telapak', 'penyebab'
        ];

        $groupCols = 6;    // Each group has 6 columns
        $groupStart = 2;   // First group starts at column index 2
        $maxGroups = 8;     // 8 installation cycles max
        $summaryStart = $groupStart + ($maxGroups * $groupCols); // = col 50

        $expanded = [];

        foreach ($dataRows as $row) {
            // Get serial number
            $sn = isset($row[$snColIdx]) ? trim((string)($row[$snColIdx] ?? '')) : '';
            if ($sn === '') continue;

            // Skip rows where SN looks like a header/label
            $snUpper = strtoupper($sn);
            if (in_array($snUpper, ['NO SERI', 'NO_SERI', 'SERIAL', 'SERIAL_NUMBER', 'COLUMN1', 'COLUMN2'])) continue;

            // Get summary columns
            $keterangan = isset($row[$summaryStart + 2]) ? trim((string)($row[$summaryStart + 2] ?? '')) : '';
            $tebalTelapak = isset($row[$summaryStart + 3]) ? trim((string)($row[$summaryStart + 3] ?? '')) : '';
            $penyebab = isset($row[$summaryStart + 4]) ? trim((string)($row[$summaryStart + 4] ?? '')) : '';

            // Find which groups have data
            $filledGroups = [];
            for ($g = 0; $g < $maxGroups; $g++) {
                $offset = $groupStart + ($g * $groupCols);
                $unit = isset($row[$offset]) ? trim((string)($row[$offset] ?? '')) : '';
                $tgl = isset($row[$offset + 2]) ? trim((string)($row[$offset + 2] ?? '')) : '';

                if (!empty($unit) || (!empty($tgl) && $tgl !== '0')) {
                    $filledGroups[] = $g;
                }
            }

            // CASE: No groups filled (tyre with no installations at all)
            // MUST unconditionally include it so row counts and alignment match Excel exactly
            if (empty($filledGroups)) {
                $expanded[] = [
                    $sn, '', '', '', '', '', '',
                    $keterangan, $tebalTelapak, $penyebab
                ];
                continue;
            }

            $lastFilledGroup = end($filledGroups);

            // Expand each filled group
            foreach ($filledGroups as $g) {
                $offset = $groupStart + ($g * $groupCols);
                $unit = isset($row[$offset]) ? trim((string)($row[$offset] ?? '')) : '';
                $posisi = isset($row[$offset + 1]) ? trim((string)($row[$offset + 1] ?? '')) : '';
                $tglPemasangan = $this->excelDate($row[$offset + 2] ?? null);
                $kmPemasangan = $this->excelNumber($row[$offset + 3] ?? null);
                $tglPelepasan = $this->excelDate($row[$offset + 4] ?? null);
                $kmPelepasan = $this->excelNumber($row[$offset + 5] ?? null);

                if (empty($tglPemasangan) && empty($unit)) continue;

                $isLast = ($g === $lastFilledGroup);

                $expanded[] = [
                    $sn,
                    $unit,
                    $posisi,
                    $tglPemasangan,
                    $kmPemasangan,
                    $tglPelepasan,
                    $kmPelepasan,
                    $isLast ? $keterangan : '',
                    $isLast ? $tebalTelapak : '',
                    $isLast ? $penyebab : '',
                ];
            }
        }

        return ['header' => $header, 'data' => $expanded];
    }

    /**
     * Convert Excel serial date (45216) → "2023-10-17" readable string.
     * Also handles already-formatted dates and empty values.
     */
    private function excelDate($value)
    {
        if ($value === null || trim((string)$value) === '' || $value === '-') return '';
        $val = trim((string)$value);

        // Excel serial number (e.g. 45216 = 2023-10-17)
        if (is_numeric($val) && (float)$val > 30000 && (float)$val < 60000) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int)$val);
                return $dt->format('Y-m-d');
            } catch (\Exception $e) {
                return $val;
            }
        }

        return $val; // Already formatted date string
    }

    /**
     * Convert Excel number: strip thousands separators, handle European format.
     * "32.816" → "32816", "48124" → "48124"
     */
    private function excelNumber($value)
    {
        if ($value === null || trim((string)$value) === '') return '';
        $str = trim((string)$value);
        $str = str_replace(' ', '', $str);

        // Pattern: digits.3digits (e.g. 32.816) = thousands separator
        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $str)) {
            return str_replace('.', '', $str);
        }
        // Pattern: digits,3digits = thousands separator with comma
        if (preg_match('/^\d{1,3}(,\d{3})+$/', $str)) {
            return str_replace(',', '', $str);
        }

        return $str;
    }

    /**
     * Parse NARROW format (standard 13-column: serial, unit, type, date, pos, odo, hm, rtd, psi, fail, status, loc, remark)
     * Also handles the dual-row format (with pemasangan/pelepasan columns)
     */
    private function parseNarrowMovement($dataRows)
    {
        // Filter empty rows
        $dataRows = array_filter($dataRows, function($row) {
            return count(array_filter($row, function($cell) {
                return $cell !== null && trim((string)$cell) !== '';
            })) > 0;
        });
        $dataRows = array_values($dataRows);

        if (count($dataRows) < 2) {
            return ['header' => [], 'data' => []];
        }

        // Use first row as header
        $header = array_shift($dataRows);
        $header = array_map(function($h) {
            return strtolower(str_replace([' ', '-'], '_', trim((string)$h)));
        }, $header);

        // Apply aliases
        $aliases = [
            'no' => '_row_number', 'no_seri' => 'serial_number',
            'unit' => 'kode_kendaraan', 'posisi_ban' => 'position_code',
            'pemasangan_tanggal' => 'pemasangan_tanggal', 'pemasangan_km' => 'pemasangan_km',
            'pelepasan_tanggal' => 'pelepasan_tanggal', 'pelepasan_km' => 'pelepasan_km',
            'jarak_tempuh_ban_(km)' => '_jarak_tempuh', 'jarak_tempuh_ban' => '_jarak_tempuh',
            'total_hari' => '_total_hari', 'keterangan' => 'keterangan',
            'tebal_telapak' => 'tebal_telapak', 'penyebab' => 'penyebab',
        ];
        $header = array_map(function($h) use ($aliases) {
            return isset($aliases[$h]) ? $aliases[$h] : $h;
        }, $header);

        // Return the data as-is (will be combined with header later)
        $cleanData = [];
        foreach ($dataRows as $row) {
            $padded = array_pad($row, count($header), null);
            $sliced = array_slice($padded, 0, count($header));
            $mapped = array_map(function($v) { return $v !== null ? trim((string)$v) : ''; }, $sliced);
            $cleanData[] = $mapped;
        }

        return ['header' => $header, 'data' => $cleanData];
    }

    /**
     * Standard import handler for non-Movement modules.
     */
    private function handleStandardImport($rawData, $module, $file)
    {
        // Filter empty rows
        $data = array_filter($rawData, function($row) {
            return count(array_filter($row, function($cell) {
                return $cell !== null && trim($cell) !== '';
            })) > 0;
        });
        $data = array_values($data);

        if (count($data) < 2) {
            return redirect()->back()->with('error', 'File kosong atau format tidak valid.');
        }

        $header = array_shift($data);
        $header = array_map(function($h) {
            return strtolower(str_replace([' ', '-'], '_', trim($h)));
        }, $header);

        \DB::beginTransaction();
        try {
            $unitArray = [];
            $layoutArray = [];

            foreach ($data as $row) {
                $rowSafe = array_pad($row, count($header), null);
                $rowSafe = array_slice($rowSafe, 0, count($header));
                $rowData = array_combine($header, $rowSafe);

                if (in_array($module, ['Tyre Examination'])) {
                    $unitCode = $rowData['kode_kendaraan'] ?? $rowData['unit'] ?? null;
                    if (!empty(trim($unitCode))) $unitArray[] = strtoupper(trim($unitCode));
                }

                if (in_array($module, ['Vehicle Master', 'Master Vehicle'])) {
                    $layout = $rowData['layout'] ?? $rowData['konfigurasi'] ?? null;
                    if (!empty(trim($layout))) $layoutArray[] = strtoupper(trim($layout));
                }
            }

            // Examination: check vehicle codes exist
            if (!empty($unitArray) && !in_array($module, ['Vehicle Master', 'Master Vehicle'])) {
                $unitArray = array_unique($unitArray);
                $existingUnits = [];
                foreach(array_chunk($unitArray, 1000) as $chunk) {
                    $foundUnits = \App\Models\MasterImportKendaraan::withoutGlobalScopes()->whereIn('kode_kendaraan', $chunk)->pluck('kode_kendaraan')->toArray();
                    $existingUnits = array_merge($existingUnits, array_map('strtoupper', $foundUnits));
                }
                $missingUnits = array_diff($unitArray, $existingUnits);
                if (count($missingUnits) > 0) {
                    $list = implode(', ', array_slice(array_values($missingUnits), 0, 10));
                    throw new \Exception('Auto Reject: ' . count($missingUnits) . ' Unit belum terdaftar: ' . $list);
                }
            }

            // Vehicle Master: check layouts exist
            if (!empty($layoutArray)) {
                $layoutArray = array_unique($layoutArray);
                $existingLayouts = [];
                foreach(array_chunk($layoutArray, 1000) as $chunk) {
                    $found = \App\Models\TyrePositionConfiguration::withoutGlobalScopes()->whereIn('name', $chunk)->pluck('name')->toArray();
                    $existingLayouts = array_merge($existingLayouts, array_map('strtoupper', $found));
                }
                $missing = array_diff($layoutArray, $existingLayouts);
                if (count($missing) > 0) {
                    $list = implode(', ', array_slice(array_values($missing), 0, 10));
                    throw new \Exception('Auto Reject: ' . count($missing) . ' Layout tidak dikenali: ' . $list);
                }
            }

            $batch = \App\Models\ImportBatch::create([
                'user_id' => auth()->id(),
                'module' => $module,
                'filename' => $file->getClientOriginalName(),
                'status' => 'Pending',
                'total_rows' => count($data)
            ]);

            $imported = 0;
            foreach ($data as $row) {
                if (count($row) < count($header)) $row = array_pad($row, count($header), null);
                elseif (count($row) > count($header)) $row = array_slice($row, 0, count($header));

                $row = array_map(function($v) { return $v !== null ? trim($v) : ''; }, $row);
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
                'module' => 'Import', 'batch_id' => $batch->id, 'filename' => $batch->filename
            ]);

            return redirect()->back()->with('success', "Data berhasil diupload ({$imported} baris) menunggu persetujuan (ID: #{$batch->id}).");
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }
}
