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

class TyreMasterSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    public function title(): string
    {
        return 'Tyre Master';
    }

    public function array(): array
    {
        return [
            // Row 1: Header
            [
                'serial_number', 'brand', 'size', 'pattern', 'segment_name',
                'ply_rating', 'initial_rtd', 'price', 'status', 'in_warehouse', 'location_name'
            ],
            // Row 2-4: Sample data
            [
                'SN-BS-001', 'BRIDGESTONE', '11.00-20', 'G580', 'Coal Hauling',
                '16', '16.50', '5500000', 'New', 'Yes', 'GUDANG PUSAT'
            ],
            [
                'SN-GT-002', 'GITI', '10.00-20', 'GTL971', 'Overburden',
                '14', '15.00', '4200000', 'New', 'Yes', 'GUDANG SITE A'
            ],
            [
                'SN-MC-003', 'MICHELIN', 'R25 29.5', 'XDM2', 'OB Haul',
                '32', '42.00', '18000000', 'Repaired', 'Yes', 'GUDANG PUSAT'
            ],
            // Row 6: Guide
            [], // blank
            ['=== PANDUAN PENGISIAN ==='],
            ['Kolom', 'Keterangan', 'Wajib?', 'Contoh Nilai'],
            ['serial_number', 'Nomor seri unik dari ban (SN)', 'YA', 'SN-BS-001'],
            ['brand', 'Nama merek ban (akan dibuat otomatis jika belum ada)', 'YA', 'BRIDGESTONE'],
            ['size', 'Ukuran ban sesuai standar', 'YA', '11.00-20'],
            ['pattern', 'Nama kembangan ban', 'TIDAK', 'G580'],
            ['segment_name', 'Nama segmen/grup operasional ban', 'TIDAK', 'Coal Hauling'],
            ['ply_rating', 'Angka ply rating ban (angka)', 'TIDAK', '16'],
            ['initial_rtd', 'Original Tread Depth saat baru (mm, desimal)', 'YA', '16.50'],
            ['price', 'Harga beli ban (angka, tanpa titik/koma)', 'TIDAK', '5500000'],
            ['status', 'Status awal ban: New / Repaired / Scrap / Installed', 'YA', 'New'],
            ['in_warehouse', 'Apakah ban ada di gudang? (Yes/No)', 'YA', 'Yes'],
            ['location_name', 'Nama lokasi gudang tempat ban disimpan', 'TIDAK', 'GUDANG PUSAT'],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestCol = $sheet->getHighestColumn();

                // Style header row (A1:K1)
                $sheet->getStyle('A1:K1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Sample data rows (A2:K4)
                $sheet->getStyle('A2:K4')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
                ]);

                // "Guide" Section header (row 8)
                $sheet->getStyle('A7')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E3A5F']],
                ]);
                $sheet->getStyle('A8:D8')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '475569']],
                ]);
                $sheet->getStyle('A9:D19')->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);
                // Alternating row colors for guide
                foreach (range(9, 19) as $i => $row) {
                    if ($i % 2 === 0) {
                        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                        ]);
                    }
                }

                // "Wajib?" column coloring (YA = red, TIDAK = green)
                foreach (range(9, 19) as $row) {
                    $wajib = $sheet->getCell("C{$row}")->getValue();
                    $color = ($wajib === 'YA') ? 'FEE2E2' : 'DCFCE7';
                    $fontColor = ($wajib === 'YA') ? 'DC2626' : '16A34A';
                    $sheet->getStyle("C{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'font' => ['bold' => true, 'color' => ['rgb' => $fontColor]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }

                // Freeze header row
                $sheet->freezePane('A2');
            }
        ];
    }
}
