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

class TemplateGuideSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    protected string $moduleTitle;
    protected string $description;
    protected array $guidelines;
    protected string $themeColor;

    /**
     * @param string $moduleTitle e.g. "Tyre Master"
     * @param string $description General description / instructions
     * @param array $guidelines List of array items:
     *   ['column' => 'serial_number', 'label' => 'No Seri Ban', 'type' => 'Teks', 'required' => 'WAJIB', 'sample' => 'SN-BS-001', 'notes' => 'Nomor seri unik ban...']
     */
    public function __construct(string $moduleTitle, string $description, array $guidelines, string $themeColor = '1E40AF')
    {
        $this->moduleTitle = $moduleTitle;
        $this->description = $description;
        $this->guidelines = $guidelines;
        $this->themeColor = $themeColor;
    }

    public function title(): string
    {
        return 'Panduan Pengisian';
    }

    public function array(): array
    {
        $rows = [
            // Row 1: Title Banner
            ['PANDUAN & PETUNJUK IMPORT DATA: ' . strtoupper($this->moduleTitle)],
            // Row 2: Subtitle
            [$this->description],
            // Row 3: Important Notice
            ['PENTING: Jangan mengubah atau menghapus nama kolom header pada sheet pertama (Import Data). Isi data mulai dari baris kedua.'],
            // Row 4: Empty space
            [],
            // Row 5: Table Header
            ['No', 'Nama Kolom (Header)', 'Label / Deskripsi', 'Tipe Data', 'Status', 'Contoh Pengisian', 'Aturan & Catatan Validasi']
        ];

        $no = 1;
        foreach ($this->guidelines as $item) {
            $rows[] = [
                $no++,
                $item['column'],
                $item['label'],
                $item['type'],
                $item['required'],
                $item['sample'],
                $item['notes']
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = count($this->guidelines) + 5;

                // Title Banner (A1:G1)
                $sheet->mergeCells('A1:G1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E293B']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Subtitle (A2:G2)
                $sheet->mergeCells('A2:G2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '475569']],
                ]);

                // Notice (A3:G3)
                $sheet->mergeCells('A3:G3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'B45309']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FCD34D']]],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(22);

                // Table Header (Row 5)
                $sheet->getStyle('A5:G5')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '334155']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(24);

                // Table Data (Rows 6 to N)
                if ($lastRow >= 6) {
                    $sheet->getStyle("A6:G{$lastRow}")->applyFromArray([
                        'font' => ['size' => 10],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                    ]);

                    for ($r = 6; $r <= $lastRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(22);
                        
                        // Center align No (col A), Tipe Data (col D), Status (col E)
                        $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                        // Bold header name in column B
                        $sheet->getStyle("B{$r}")->getFont()->setBold(true);

                        // Alternating bg
                        $bg = ($r % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
                        $sheet->getStyle("A{$r}:D{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
                        $sheet->getStyle("F{$r}:G{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);

                        // Status Badge Color (WAJIB = Light Red, OPSIONAL = Light Green)
                        $statusVal = strtoupper(trim((string)$sheet->getCell("E{$r}")->getValue()));
                        if (str_contains($statusVal, 'WAJIB')) {
                            $sheet->getStyle("E{$r}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
                                'font' => ['bold' => true, 'color' => ['rgb' => 'DC2626']],
                            ]);
                        } else {
                            $sheet->getStyle("E{$r}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']],
                                'font' => ['bold' => true, 'color' => ['rgb' => '16A34A']],
                            ]);
                        }
                    }
                }

                $sheet->freezePane('A6');
            }
        ];
    }
}
