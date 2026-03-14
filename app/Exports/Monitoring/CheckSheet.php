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
    protected $statusParts;
    protected $currentStatus;
    protected $rtdCount;

    public function __construct($session, $checkNumber, $checks, $statusParts, $currentStatus, $rtdCount)
    {
        $this->session = $session;
        $this->checkNumber = $checkNumber;
        $this->checks = $checks;
        $this->statusParts = $statusParts;
        $this->currentStatus = $currentStatus;
        $this->rtdCount = $rtdCount;
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

            // Get brand/pattern from installation record
            $inst = $this->session->installations->where('serial_number', $check->serial_number)->first();
            $check->brand_name = $inst->masterTyre->brand->brand_name ?? ($inst->brand ?? '-');
            $check->pattern_name = $inst->masterTyre->pattern->name ?? ($inst->pattern ?? '-');

            $calculatedChecks[] = $check;
        }

        return view('exports.monitoring.check', [
            'session' => $this->session,
            'checkNumber' => $this->checkNumber,
            'checks' => $calculatedChecks,
            'statusParts' => $this->statusParts,
            'currentStatus' => $this->currentStatus,
            'rtdCount' => $this->rtdCount,
        ]);
    }

    public function title(): string
    {
        return 'Check ' . $this->checkNumber;
    }
}
