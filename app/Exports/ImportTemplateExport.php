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

        return match ($this->module) {
            'Tyre Master'      => [new ImportTemplateSheets\TyreMasterSheet()],
            'Vehicle Master'   => [new ImportTemplateSheets\VehicleMasterSheet()],
            'Movement History' => [new ImportTemplateSheets\MovementHistorySheet()],
            'Tyre Brand'       => [new ImportTemplateSheets\TyreBrandSheet()],
            'Tyre Size'        => [new ImportTemplateSheets\TyreSizeSheet()],
            'Tyre Pattern'     => [new ImportTemplateSheets\TyrePatternSheet()],
            'Failure Codes'    => [new ImportTemplateSheets\FailureCodesSheet()],
            'Locations'        => [new ImportTemplateSheets\LocationsSheet()],
            'Segments'         => [new ImportTemplateSheets\SegmentsSheet()],
            default            => [new ImportTemplateSheets\TyreMasterSheet()],
        };
    }
}
