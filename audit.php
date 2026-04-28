<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$companyId = 3; // PT TUNJUNG INTI MANDIRI
$tyres = \App\Models\Tyre::where('tyre_company_id', $companyId)->count();
$installed = \App\Models\Tyre::where('tyre_company_id', $companyId)->where('status', 'Installed')->count();
$spare = \App\Models\Tyre::where('tyre_company_id', $companyId)->where('status', 'Spare')->count();
$scrapped = \App\Models\Tyre::where('tyre_company_id', $companyId)->where('status', 'Scrapped')->count();
$repaired = \App\Models\Tyre::where('tyre_company_id', $companyId)->where('status', 'Repaired')->count();

// Get the unique statuses actually present
$allStatuses = \App\Models\Tyre::where('tyre_company_id', $companyId)->select('status', \DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status')->toArray();

echo json_encode([
    'company_id' => $companyId,
    'total_tyres' => $tyres,
    'statuses' => ['Installed' => $installed, 'Spare' => $spare, 'Scrapped' => $scrapped, 'Repaired' => $repaired],
    'actual_statuses' => $allStatuses,
    'sum_statuses' => ($installed + $spare + $scrapped + $repaired)
], JSON_PRETTY_PRINT);
