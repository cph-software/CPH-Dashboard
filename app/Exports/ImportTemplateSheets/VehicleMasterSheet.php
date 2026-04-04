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

class VehicleMasterSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    public function title(): string
    {
        return 'Vehicle Master';
    }

    public function array(): array
    {
        return [
            ['kode_kendaraan', 'no_polisi', 'model_kendaraan', 'brand_kendaraan', 'site_location', 'curb_weight', 'payload_capacity', 'segment', 'total_positions', 'layout', 'status'],
            ['DT-101', 'B 1234 ABC', 'DUMP TRUCK', 'HINO', 'SITE A', '15000', '20', 'Coal Hauling', '10', '6 Roda (2+4)', 'Active'],
            ['DT-102', 'B 5678 DEF', 'DUMP TRUCK', 'SCANIA', 'SITE B', '18000', '30', 'Overburden', '10', '6 Roda (2+4)', 'Active'],
            ['HX-301', 'DC 9999 ZZ', 'HAUL TRUCK', 'KOMATSU', 'SITE A', '135000', '220', 'OB Haul', '4', '4 Roda (2+2)', 'Active'],
            [],
            ['*** PANDUAN PENGISIAN ***'],
            ['Kolom', 'Keterangan', 'Wajib?', 'Contoh Nilai'],
            ['kode_kendaraan', 'Kode unik unit kendaraan (No. Lambung)', 'YA', 'DT-101'],
            ['no_polisi', 'Nomor plat kendaraan', 'TIDAK', 'B 1234 ABC'],
            ['model_kendaraan', 'Jenis/model kendaraan', 'YA', 'DUMP TRUCK'],
            ['brand_kendaraan', 'Merek kendaraan', 'TIDAK', 'HINO'],
            ['site_location', 'Lokasi operasional unit', 'TIDAK', 'SITE A'],
            ['curb_weight', 'Berat kosong kendaraan (kg, angka)', 'TIDAK', '15000'],
            ['payload_capacity', 'Kapasitas muat (ton, angka)', 'TIDAK', '20'],
            ['segment', 'Nama segmen operasional', 'TIDAK', 'Coal Hauling'],
            ['total_positions', 'Jumlah posisi ban total pada unit', 'TIDAK', '10'],
            ['layout', 'Nama konfigurasi ban (harus sama persis dengan Master Axle)', 'TIDAK', '6 Roda (2+4)'],
            ['status', 'Status unit: Active / Inactive', 'TIDAK', 'Active'],
        ];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $sheet->getStyle('A1:K1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '16A34A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ]);
            $sheet->getStyle('A2:K4')->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDF4']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BBF7D0']]],
            ]);
            $sheet->getStyle('A7')->applyFromArray(['font' => ['bold' => true, 'size' => 12]]);
            $sheet->getStyle('A8:D8')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '475569']],
            ]);
            $sheet->getStyle('A9:D18')->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
            ]);
            foreach (range(9, 18) as $row) {
                $wajib = $sheet->getCell("C{$row}")->getValue();
                $color = ($wajib === 'YA') ? 'FEE2E2' : 'DCFCE7';
                $fontColor = ($wajib === 'YA') ? 'DC2626' : '16A34A';
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
