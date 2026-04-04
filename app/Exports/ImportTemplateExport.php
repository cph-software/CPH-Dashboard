<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImportTemplateExport implements WithMultipleSheets
{
    protected string $module;

    public function __construct(string $module)
    {
        $this->module = $module;
    }

    public function sheets(): array
    {
        // Require the file containing the smaller classes since they don't match PSR-4 file naming
        require_once __DIR__ . '/ImportTemplateSheets/OtherTemplateSheets.php';
        switch ($this->module) {
            case 'Tyre Master':
                return [new ImportTemplateSheets\TyreMasterSheet()];
            case 'Vehicle Master':
                return [new ImportTemplateSheets\VehicleMasterSheet()];
            case 'Movement History':
                return [new ImportTemplateSheets\MovementHistorySheet()];
            case 'Tyre Brand':
                return [new ImportTemplateSheets\TyreBrandSheet()];
            case 'Tyre Size':
                return [new ImportTemplateSheets\TyreSizeSheet()];
            case 'Tyre Pattern':
                return [new ImportTemplateSheets\TyrePatternSheet()];
            case 'Failure Codes':
                return [new ImportTemplateSheets\FailureCodesSheet()];
            case 'Locations':
                return [new ImportTemplateSheets\LocationsSheet()];
            case 'Segments':
                return [new ImportTemplateSheets\SegmentsSheet()];
            default:
                return [new ImportTemplateSheets\TyreMasterSheet()];
        }
    }
}
