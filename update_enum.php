<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    DB::statement("ALTER TABLE tyre_position_details MODIFY COLUMN axle_type ENUM('Front', 'Rear', 'Spare', 'Trailer', 'Middle') NOT NULL DEFAULT 'Rear'");
    echo "Successfully updated axle_type enum.\n";
} catch (\Exception $e) {
    echo "Error updating axle_type enum: " . $e->getMessage() . "\n";
}
