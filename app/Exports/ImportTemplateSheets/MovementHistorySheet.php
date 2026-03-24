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
            ['serial_number', 'kode_kendaraan', 'movement_type', 'movement_date', 'position_code', 'odometer', 'hm', 'rtd', 'psi', 'failure_code', 'target_status', 'location', 'remark'],
            ['SN-BS-001', 'DT-101', 'Installation', date('Y-m-d', strtotime('-60 days')), 'LF', '50000', '2500', '15.2', '100', '', '', '', 'Awal pemasangan'],
            ['SN-BS-001', 'DT-101', 'Removal', date('Y-m-d', strtotime('-10 days')), 'LF', '55000', '2750', '11.8', '95', 'EXTN', 'Repaired', 'GUDANG PUSAT', 'Dilepas untuk vulkanisir'],
            [],
            ['=== PANDUAN PENGISIAN ==='],
            ['Kolom', 'Keterangan', 'Wajib?', 'Contoh Nilai'],
            ['serial_number', 'Nomor seri ban. Harus sudah ada di Master Ban', 'YA', 'SN-BS-001'],
            ['kode_kendaraan', 'Kode unit kendaraan. Harus ada di Master Kendaraan', 'YA (Install)', 'DT-101'],
            ['movement_type', 'Jenis pergerakan: Installation atau Removal', 'YA', 'Installation'],
            ['movement_date', 'Tanggal pergerakan (format YYYY-MM-DD)', 'YA', date('Y-m-d')],
            ['position_code', 'Kode posisi ban (contoh: LF, RF, LR1, RR2)', 'YA (Install)', 'LF'],
            ['odometer', 'Pembacaan odometer kendaraan (km, angka)', 'YA', '50000'],
            ['hm', 'Pembacaan hour meter (jam, angka)', 'TIDAK', '2500'],
            ['rtd', 'Kedalaman rur saat pergerakan (mm)', 'TIDAK', '15.2'],
            ['psi', 'Tekanan angin ban (PSI)', 'TIDAK', '100'],
            ['failure_code', 'Kode kerusakan ban (dari modul Failure Codes)', 'TIDAK', 'EXTN'],
            ['target_status', 'Status setelah Removal: Repaired / Scrap / New', 'TIDAK (Removal)', 'Repaired'],
            ['location', 'Lokasi gudang tujuan saat Removal', 'TIDAK (Removal)', 'GUDANG PUSAT'],
            ['remark', 'Keterangan tambahan', 'TIDAK', 'Dilepas untuk vulkanisir'],
        ];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $sheet->getStyle('A1:M1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D97706']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ]);
            $sheet->getStyle('A2:M3')->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFBEB']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FDE68A']]],
            ]);
            $sheet->getStyle('A5')->applyFromArray(['font' => ['bold' => true, 'size' => 12]]);
            $sheet->getStyle('A6:D6')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '475569']],
            ]);
            $sheet->getStyle('A7:D19')->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
            ]);
            foreach (range(7, 19) as $row) {
                $wajib = $sheet->getCell("C{$row}")->getValue();
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
            $sheet->freezePane('A2');
        }];
    }
}
