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

class TemplateReferenceSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    protected string $title;
    protected array $sections;

    /**
     * @param string $title
     * @param array $sections Format:
     *   [
     *      'Title Section 1' => ['Item 1', 'Item 2', ...],
     *      'Title Section 2' => [['col1' => 'val', 'col2' => 'val'], ...]
     *   ]
     */
    public function __construct(string $title, array $sections)
    {
        $this->title = $title;
        $this->sections = $sections;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function array(): array
    {
        $maxRows = 0;
        $colHeaders = [];
        $colData = [];

        foreach ($this->sections as $sectionTitle => $items) {
            $colHeaders[] = strtoupper($sectionTitle);
            $normalizedItems = [];
            foreach ($items as $item) {
                if (is_array($item)) {
                    $normalizedItems[] = implode(' - ', array_filter($item));
                } else {
                    $normalizedItems[] = (string)$item;
                }
            }
            if (empty($normalizedItems)) {
                $normalizedItems[] = '(Belum ada data)';
            }
            $colData[] = $normalizedItems;
            if (count($normalizedItems) > $maxRows) {
                $maxRows = count($normalizedItems);
            }
        }

        $rows = [
            ['DAFTAR REFERENSI MASTER DATA TERDAFTAR'],
            ['Gunakan nilai di bawah ini untuk memastikan konsistensi data yang diimport.'],
            [],
            $colHeaders
        ];

        for ($r = 0; $r < $maxRows; $r++) {
            $row = [];
            for ($c = 0; $c < count($colHeaders); $c++) {
                $row[] = $colData[$c][$r] ?? '';
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestCol = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                // Header Banner
                $sheet->mergeCells("A1:{$highestCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E293B']],
                ]);

                $sheet->mergeCells("A2:{$highestCol}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '64748B']],
                ]);

                // Table Headers (Row 4)
                $sheet->getStyle("A4:{$highestCol}4")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F766E']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(24);

                // Table Content (Row 5 to N)
                if ($highestRow >= 5) {
                    $sheet->getStyle("A5:{$highestCol}{$highestRow}")->applyFromArray([
                        'font' => ['size' => 10],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                    ]);

                    for ($r = 5; $r <= $highestRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(19);
                        $bg = ($r % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
                        $sheet->getStyle("A{$r}:{$highestCol}{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                        ]);
                    }
                }

                $sheet->freezePane('A5');
            }
        ];
    }
}
