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

/**
 * Helper trait for consistent styling across all template sheets.
 */
trait TemplateSheetStyler
{
    protected function applyStandardStyles(AfterSheet $event, string $headerRange, string $headerColor, int $lastRow, int $guideStartRow, int $guideEndRow, int $guideCols): void
    {
        $sheet = $event->sheet->getDelegate();
        $dataEndRow = $lastRow;

        // Header row
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);

        // Sample data rows
        if ($dataEndRow >= 2) {
            $sheet->getStyle('A2:' . $sheet->getHighestColumn() . $dataEndRow)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
            ]);
        }

        // Guide header
        $guideHeaderRow = $guideStartRow + 1;
        $guideColLetter = chr(64 + $guideCols);
        $sheet->getStyle("A{$guideHeaderRow}:{$guideColLetter}{$guideHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '475569']],
        ]);
        $sheet->getStyle("A{$guideStartRow}")->applyFromArray(['font' => ['bold' => true, 'size' => 11]]);

        // Guide rows
        $guideDataStart = $guideHeaderRow + 1;
        $sheet->getStyle("A{$guideDataStart}:{$guideColLetter}{$guideEndRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        // Required column coloring
        for ($row = $guideDataStart; $row <= $guideEndRow; $row++) {
            $wajib = $sheet->getCell("C{$row}")->getValue();
            if (str_contains((string)$wajib, 'YA')) { $color = 'FEE2E2'; $fontColor = 'DC2626'; }
            else { $color = 'DCFCE7'; $fontColor = '16A34A'; }
            $sheet->getStyle("C{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                'font' => ['bold' => true, 'color' => ['rgb' => $fontColor]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        $sheet->freezePane('A2');
    }
}

class TyreBrandSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    use TemplateSheetStyler;
    public function title(): string { return 'Tyre Brand'; }
    public function array(): array
    {
        return [
            ['brand_name', 'status'],
            ['BRIDGESTONE', 'Active'],
            ['GITI', 'Active'],
            ['MICHELIN', 'Active'],
            [],
            ['=== PANDUAN ==='],
            ['Kolom', 'Keterangan', 'Wajib?', 'Contoh'],
            ['brand_name', 'Nama brand ban (huruf kapital)', 'YA', 'BRIDGESTONE'],
            ['status', 'Status brand: Active / Inactive', 'TIDAK', 'Active'],
        ];
    }
    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $this->applyStandardStyles($event, 'A1:B1', '7C3AED', 4, 6, 9, 4);
        }];
    }
}

class TyreSizeSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    use TemplateSheetStyler;
    public function title(): string { return 'Tyre Size'; }
    public function array(): array
    {
        return [
            ['size', 'brand_name', 'type', 'std_otd', 'ply_rating'],
            ['11.00-20', 'BRIDGESTONE', 'Bias', '16.5', '16'],
            ['10.00-20', 'GITI', 'Bias', '15.0', '14'],
            ['R25 29.5', 'MICHELIN', 'Radial', '42.0', '32'],
            [],
            ['=== PANDUAN ==='],
            ['Kolom', 'Keterangan', 'Wajib?', 'Contoh'],
            ['size', 'Ukuran ban (sesuai standar)', 'YA', '11.00-20'],
            ['brand_name', 'Nama brand (harus ada di Master Brand)', 'TIDAK', 'BRIDGESTONE'],
            ['type', 'Tipe konstruksi: Bias / Radial', 'TIDAK', 'Bias'],
            ['std_otd', 'Standard Original Tread Depth (mm)', 'TIDAK', '16.5'],
            ['ply_rating', 'Angka ply rating', 'TIDAK', '16'],
        ];
    }
    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $this->applyStandardStyles($event, 'A1:E1', '0891B2', 4, 6, 12, 4);
        }];
    }
}

class TyrePatternSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    use TemplateSheetStyler;
    public function title(): string { return 'Tyre Pattern'; }
    public function array(): array
    {
        return [
            ['pattern_name', 'brand', 'status'],
            ['G580', 'BRIDGESTONE', 'Active'],
            ['GTL971', 'GITI', 'Active'],
            ['XDM2', 'MICHELIN', 'Active'],
            [],
            ['=== PANDUAN ==='],
            ['Kolom', 'Keterangan', 'Wajib?', 'Contoh'],
            ['pattern_name', 'Nama kembangan/tipe ban', 'YA', 'G580'],
            ['brand', 'Nama brand terkait', 'TIDAK', 'BRIDGESTONE'],
            ['status', 'Status: Active / Inactive', 'TIDAK', 'Active'],
        ];
    }
    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $this->applyStandardStyles($event, 'A1:C1', 'BE185D', 4, 6, 10, 4);
        }];
    }
}

class FailureCodesSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    use TemplateSheetStyler;
    public function title(): string { return 'Failure Codes'; }
    public function array(): array
    {
        return [
            ['failure_code', 'failure_name', 'default_category'],
            ['CUT', 'Cut Separation', 'Major Damage'],
            ['EXTN', 'External Damage', 'Road Hazard'],
            ['WEAR', 'Irregular Wear', 'Operational'],
            ['BEAD', 'Bead Damage', 'Mechanical Damage'],
            ['BURS', 'Burst/Blowout', 'Major Damage'],
            [],
            ['=== PANDUAN ==='],
            ['Kolom', 'Keterangan', 'Wajib?', 'Contoh'],
            ['failure_code', 'Kode unik kerusakan (singkatan kapital)', 'YA', 'CUT'],
            ['failure_name', 'Nama/deskripsi kerusakan', 'YA', 'Cut Separation'],
            ['default_category', 'Kategori kerusakan (grup)', 'TIDAK', 'Major Damage'],
        ];
    }
    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $this->applyStandardStyles($event, 'A1:C1', 'DC2626', 6, 8, 12, 4);
        }];
    }
}

class LocationsSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    use TemplateSheetStyler;
    public function title(): string { return 'Locations'; }
    public function array(): array
    {
        return [
            ['location_name', 'location_type', 'capacity'],
            ['GUDANG PUSAT', 'Warehouse', '300'],
            ['GUDANG SITE A', 'Warehouse', '150'],
            ['WORKSHOP SITE B', 'Service', '50'],
            ['TEMPAT PEMBUANGAN', 'Disposal', '500'],
            [],
            ['=== PANDUAN ==='],
            ['Kolom', 'Keterangan', 'Wajib?', 'Contoh'],
            ['location_name', 'Nama lokasi (huruf kapital, unik)', 'YA', 'GUDANG PUSAT'],
            ['location_type', 'Tipe: Warehouse / Service / Disposal', 'YA', 'Warehouse'],
            ['capacity', 'Kapasitas maksimal ban di lokasi ini (angka)', 'TIDAK', '300'],
        ];
    }
    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $this->applyStandardStyles($event, 'A1:C1', '059669', 5, 7, 11, 4);
        }];
    }
}

class SegmentsSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    use TemplateSheetStyler;
    public function title(): string { return 'Segments'; }
    public function array(): array
    {
        return [
            ['segment_id', 'segment_name', 'location_name', 'terrain_type', 'status'],
            ['SEG/HAUL/01', 'Coal Hauling', 'GUDANG SITE A', 'Muddy', 'Active'],
            ['SEG/OB/01', 'Overburden', 'GUDANG SITE A', 'Rocky', 'Active'],
            ['SEG/INFRA/01', 'Infrastructure', 'GUDANG PUSAT', 'Asphalt', 'Active'],
            [],
            ['=== PANDUAN ==='],
            ['Kolom', 'Keterangan', 'Wajib?', 'Contoh'],
            ['segment_id', 'ID segmen unik (kode singkat)', 'YA', 'SEG/HAUL/01'],
            ['segment_name', 'Nama lengkap segmen operasional', 'YA', 'Coal Hauling'],
            ['location_name', 'Nama lokasi terkait (harus ada di Master Lokasi)', 'TIDAK', 'GUDANG SITE A'],
            ['terrain_type', 'Jenis medan: Muddy / Rocky / Asphalt / Unknown', 'TIDAK', 'Muddy'],
            ['status', 'Status: Active / Inactive', 'TIDAK', 'Active'],
        ];
    }
    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $this->applyStandardStyles($event, 'A1:E1', '0F766E', 4, 6, 12, 4);
        }];
    }
}
