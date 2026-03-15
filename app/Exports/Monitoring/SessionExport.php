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
        $this->session = TyreMonitoringSession::with([
            'vehicle', 
            'installations' => function($q) {
                $q->leftJoin('tyre_position_details', 'tyre_monitoring_installation.position_id', '=', 'tyre_position_details.id')
                  ->orderBy('tyre_position_details.display_order')
                  ->select('tyre_monitoring_installation.*');
            },
            'installations.positionDetail',
            'installations.masterTyre.brand', 
            'installations.masterTyre.pattern', 
            'checks.positionDetail',
            'removal'
        ])->findOrFail($sessionId);
    }

    public function sheets(): array
    {
        $sheets = [];
        
        // Group checks by check_number and sort each group by position display order
        $checksByNumber = $this->session->checks->groupBy('check_number')->sortKeys();
        
        foreach ($checksByNumber as $cn => $group) {
            $checksByNumber[$cn] = $group->sortBy(function($check) {
                // Ensure consistent ordering based on position configuration display order
                return $check->positionDetail->display_order ?? 99;
            });
        }
        $totalChecks = $checksByNumber->count();
        $hasRemoval = !is_null($this->session->removal);

        // Build status trail: Installation Data, Check 1, Check 2, ..., Removal
        $statusParts = ['Installation Data'];
        foreach ($checksByNumber->keys() as $cn) {
            $statusParts[] = "Check {$cn}";
        }
        if ($hasRemoval) {
            $statusParts[] = 'Removal';
        }

        // Determine RTD column count (check how many are used)
        $rtdCount = 4; // default
        $allInstallations = $this->session->installations;
        if ($allInstallations->count() > 0) {
            $hasRtd4 = $allInstallations->contains(function ($inst) {
                return !is_null($inst->rtd_4) && $inst->rtd_4 > 0;
            });
            $hasRtd3 = $allInstallations->contains(function ($inst) {
                return !is_null($inst->rtd_3) && $inst->rtd_3 > 0;
            });
            if (!$hasRtd4 && !$hasRtd3) {
                $rtdCount = 2;
            } elseif (!$hasRtd4) {
                $rtdCount = 3;
            }
        }

        // Sheet 1: Installation
        $sheets[] = new InstallationSheet($this->session, $statusParts, 'Installation Data', $rtdCount);

        // Sheet 2+: Checks
        foreach ($checksByNumber as $checkNumber => $checks) {
            $currentStatus = "Check {$checkNumber}";
            $sheets[] = new CheckSheet($this->session, $checkNumber, $checks, $statusParts, $currentStatus, $rtdCount);
        }

        // Last sheet: Removal
        if ($hasRemoval) {
            $sheets[] = new RemovalSheet($this->session, $statusParts, 'Removal', $rtdCount);
        }

        return $sheets;
    }
}
