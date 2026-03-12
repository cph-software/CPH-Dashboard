<?php

namespace App\Exports\Monitoring;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;
use App\Services\TyreMonitoringCalculator;

class CheckSheet implements FromView, WithTitle
{
    protected $session;
    protected $checkNumber;
    protected $checks;

    public function __construct($session, $checkNumber, $checks)
    {
        $this->session = $session;
        $this->checkNumber = $checkNumber;
        $this->checks = $checks;
    }

    public function view(): View
    {
        $calculatedChecks = [];
        foreach ($this->checks as $check) {
            $calc = TyreMonitoringCalculator::calculate(
                $this->session->original_rtd,
                $this->session->install_date,
                $check
            );
            $check->calculated = $calc;
            $calculatedChecks[] = $check;
        }

        return view('exports.monitoring.check', [
            'session' => $this->session,
            'checkNumber' => $this->checkNumber,
            'checks' => $calculatedChecks
        ]);
    }

    public function title(): string
    {
        return 'Check ' . $this->checkNumber;
    }
}
