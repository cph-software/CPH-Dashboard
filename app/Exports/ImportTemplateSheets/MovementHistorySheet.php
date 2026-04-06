<?php

namespace App\Exports\ImportTemplateSheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MovementHistorySheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    public function title(): string { return 'Movement History'; }

    public function array(): array
    {
        return [
            // === FORMAT A: Format Baru (Dual-Row) — 1 baris = Pemasangan + Pelepasan ===
            ['*** FORMAT A: DATA RIWAYAT LENGKAP (REKOMENDASI) ***'],
            ['Gunakan format ini jika setiap baris memiliki data pemasangan DAN pelepasan.'],
            ['Ban & Kendaraan yang belum ada di Master akan OTOMATIS didaftarkan.'],
            [],
            ['no_seri', 'unit', 'posisi_ban', 'pemasangan_tanggal', 'pemasangan_km', 'pelepasan_tanggal', 'pelepasan_km', 'keterangan', 'tebal_telapak', 'penyebab'],
            ['23282I06173', 'DT 535', '2', '17.10.2023', '32816', '09.11.2024', '48124', 'BUANG', '10', 'TELAPAK RUSAK'],
            ['23272I06061', 'DT 550', '7', '17.10.2023', '25494', '19.10.2023', '25718', 'BUANG', '21', 'TELAPAK TERTUSUK BATU'],
            ['23272I06135', 'DT 550', '7', '17.10.2023', '25494', '27.10.2023', '26759', '', '20', 'TELAPAK TERTUSUK BATU'],
            [],
            ['Kolom', 'Keterangan', 'Wajib?', 'Contoh Nilai'],
            ['no_seri', 'Nomor seri ban. Jika belum ada, akan OTOMATIS didaftarkan', 'YA', '23282I06173'],
            ['unit', 'Kode kendaraan. Jika belum ada, akan OTOMATIS didaftarkan', 'YA', 'DT 535'],
            ['posisi_ban', 'Nomor urut posisi ban pada kendaraan', 'TIDAK', '2'],
            ['pemasangan_tanggal', 'Tanggal pemasangan (DD.MM.YYYY atau YYYY-MM-DD)', 'YA', '17.10.2023'],
            ['pemasangan_km', 'Odometer saat pemasangan (km)', 'TIDAK', '32816'],
            ['pelepasan_tanggal', 'Tanggal pelepasan (kosongkan jika masih terpasang)', 'TIDAK', '09.11.2024'],
            ['pelepasan_km', 'Odometer saat pelepasan (km)', 'TIDAK', '48124'],
            ['keterangan', 'Status: BUANG (Scrap) atau kosong (Repaired)', 'TIDAK', 'BUANG'],
            ['tebal_telapak', 'Kedalaman alur saat dilepas (mm)', 'TIDAK', '10'],
            ['penyebab', 'Penyebab / catatan pelepasan', 'TIDAK', 'TELAPAK RUSAK'],
            [],
            [],
            // === FORMAT B: Format Lama (Single-Event) — 1 baris = 1 event ===
            ['*** FORMAT B: FORMAT PER-EVENT (LEGACY) ***'],
            ['Gunakan format ini jika setiap baris adalah satu event (Installation ATAU Removal).'],
            [],
            ['serial_number', 'kode_kendaraan', 'movement_type', 'movement_date', 'position_code', 'odometer', 'hm', 'rtd', 'psi', 'failure_code', 'target_status', 'location', 'remark'],
            ['SN-BS-001', 'DT-101', 'Installation', date('Y-m-d', strtotime('-60 days')), 'LF', '50000', '2500', '15.2', '100', '', '', '', 'Awal pemasangan'],
            ['SN-BS-001', 'DT-101', 'Removal', date('Y-m-d', strtotime('-10 days')), 'LF', '55000', '2750', '11.8', '95', 'EXTN', 'Repaired', 'GUDANG PUSAT', 'Dilepas untuk vulkanisir'],
        ];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();

            // Title for Format A
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E40AF']],
            ]);
            $sheet->getStyle('A2:A3')->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '059669']],
            ]);

            // Format A headers (row 6)
            $sheet->getStyle('A6:J6')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ]);

            // Sample data rows (7-9)
            $sheet->getStyle('A7:J9')->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
            ]);

            // Guide header (row 11)
            $sheet->getStyle('A11:D11')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '475569']],
            ]);

            // Guide rows wajib/tidak coloring
            foreach (range(12, 21) as $row) {
                $wajib = $sheet->getCell("C{$row}")->getValue();
                if ($wajib === null) continue;
                if (str_contains($wajib, 'YA')) {
                    $color = 'FEE2E2'; $fontColor = 'DC2626';
                } else {
                    $color = 'DCFCE7'; $fontColor = '16A34A';
                }
                $sheet->getStyle("C{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                    'font' => ['bold' => true, 'color' => ['rgb' => $fontColor]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            }

            // Title for Format B
            $sheet->getStyle('A24')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'D97706']],
            ]);

            // Format B headers (row 27)
            $sheet->getStyle('A27:M27')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D97706']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ]);

            // Format B sample data
            $sheet->getStyle('A28:M29')->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFBEB']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FDE68A']]],
            ]);

            $sheet->freezePane('A2');
        }];
    }
}
