<?php

namespace App\Exports\Monitoring;

use App\Models\TyreMonitoringSession;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SessionExport implements WithMultipleSheets
{
    use Exportable;

    protected $session;

    public function __construct($sessionId)
    {
        $this->session = TyreMonitoringSession::with(['vehicle', 'installations', 'checks', 'removal'])
            ->findOrFail($sessionId);
    }

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: Installation
        $sheets[] = new InstallationSheet($this->session);

        // Sheet 2+: Checks grouped by check_number (Sorted ASC)
        $checksByNumber = $this->session->checks->sortBy('check_number')->groupBy('check_number');
        foreach ($checksByNumber as $checkNumber => $checks) {
            $sheets[] = new CheckSheet($this->session, $checkNumber, $checks);
        }

        // Last sheet: Removal
        if ($this->session->removal) {
            $sheets[] = new RemovalSheet($this->session);
        }

        return $sheets;
    }
}
