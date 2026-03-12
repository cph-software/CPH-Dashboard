<?php

namespace App\Exports\Monitoring;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;

class RemovalSheet implements FromView, WithTitle
{
    protected $session;

    public function __construct($session)
    {
        $this->session = $session;
    }

    public function view(): View
    {
        return view('exports.monitoring.removal', [
            'session' => $this->session,
            'removal' => $this->session->removal
        ]);
    }

    public function title(): string
    {
        return 'Removal';
    }
}
