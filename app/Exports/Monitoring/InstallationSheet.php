<?php

namespace App\Exports\Monitoring;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;

class InstallationSheet implements FromView, WithTitle
{
    protected $session;

    public function __construct($session)
    {
        $this->session = $session;
    }

    public function view(): View
    {
        return view('exports.monitoring.installation', [
            'session' => $this->session,
            'installations' => $this->session->installations
        ]);
    }

    public function title(): string
    {
        return 'Installation';
    }
}
