<?php

namespace App\Console\Commands;

use App\Exports\SimpleArrayExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TestExcelExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'excel:test-export {--path=exports/test-export.xlsx : Relative path in storage/app}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a small .xlsx file via maatwebsite/excel to verify exports work.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $relativePath = (string) $this->option('path');
        $dir = trim(str_replace('\\', '/', dirname($relativePath)), '.');

        if ($dir !== '' && $dir !== '/') {
            Storage::disk('local')->makeDirectory($dir);
        }

        $headings = ['No', 'Nama', 'Tanggal', 'Nilai'];
        $rows = [
            [1, 'Tes A', now()->format('Y-m-d H:i:s'), 123],
            [2, 'Tes B', now()->subDay()->format('Y-m-d H:i:s'), 456],
        ];

        Excel::store(new SimpleArrayExport($rows, $headings), $relativePath, 'local');

        $fullPath = Storage::disk('local')->path($relativePath);
        $size = @filesize($fullPath) ?: 0;

        // Quick integrity read: open the file and show A1 + A2
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $a1 = (string) $sheet->getCell('A1')->getValue();
        $a2 = (string) $sheet->getCell('A2')->getValue();

        $this->info('OK. File created: ' . $fullPath);
        $this->info('Size: ' . $size . ' bytes');
        $this->info('A1=' . $a1 . ', A2=' . $a2);

        return self::SUCCESS;
    }
}

