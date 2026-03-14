<?php

namespace App\Exports\Monitoring;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;

class InstallationSheet implements FromView, WithTitle
{
    protected $session;
    protected $statusParts;
    protected $currentStatus;
    protected $rtdCount;

    public function __construct($session, $statusParts, $currentStatus, $rtdCount)
    {
        $this->session = $session;
        $this->statusParts = $statusParts;
        $this->currentStatus = $currentStatus;
        $this->rtdCount = $rtdCount;
    }

    public function view(): View
    {
        return view('exports.monitoring.installation', [
            'session' => $this->session,
            'installations' => $this->session->installations,
            'statusParts' => $this->statusParts,
            'currentStatus' => $this->currentStatus,
            'rtdCount' => $this->rtdCount,
        ]);
    }

    public function title(): string
    {
        return 'Installation';
    }
}
