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

class TemplateDataSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    protected string $title;
    protected array $headers;
    protected array $samples;
    protected string $themeColor;

    public function __construct(string $title, array $headers, array $samples, string $themeColor = '1E40AF')
    {
        $this->title = $title;
        $this->headers = $headers;
        $this->samples = $samples;
        $this->themeColor = $themeColor;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function array(): array
    {
        $rows = [$this->headers];
        foreach ($this->samples as $sample) {
            $rows[] = $sample;
        }
        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestCol = $sheet->getHighestColumn();
                $totalRows = count($this->samples) + 1;

                // Style Header Row (A1:LastCol1)
                $sheet->getStyle("A1:{$highestCol}1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
                        'name' => 'Calibri'
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $this->themeColor],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => false,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'FFFFFF'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(26);

                // Style Sample Data Rows (A2:LastColN)
                if ($totalRows >= 2) {
                    $sheet->getStyle("A2:{$highestCol}{$totalRows}")->applyFromArray([
                        'font' => [
                            'size' => 10,
                            'name' => 'Calibri'
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CBD5E1'],
                            ],
                        ],
                    ]);

                    for ($r = 2; $r <= $totalRows; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(20);
                        $bg = ($r % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
                        $sheet->getStyle("A{$r}:{$highestCol}{$r}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $bg],
                            ],
                        ]);
                    }
                }

                // Freeze Header Row
                $sheet->freezePane('A2');
            }
        ];
    }
}
