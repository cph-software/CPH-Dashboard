<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// List of base migrations to mark as done
$files = File::files(database_path('migrations'));
$batch = DB::table('migrations')->max('batch') + 1;

foreach ($files as $file) {
    $filename = $file->getFilenameWithoutExtension();
    
    // Check if filename starts with 2026_02_06_022322 OR matches specific files
    if (strpos($filename, '2026_02_06_022322') === 0 
        || $filename === '2026_02_07_061310_add_tyre_position_configuration_id_to_master_import_kendaraan_table'
        || $filename === '2026_02_09_081735_add_failure_code_id_to_tyre_movements_table') {
        
        $exists = DB::table('migrations')->where('migration', $filename)->exists();
        
        if (!$exists) {
            echo "Marking as migrated: $filename\n";
            DB::table('migrations')->insert([
                'migration' => $filename,
                'batch' => $batch
            ]);
        }
    }
}

echo "Done marking base migrations.\n";
