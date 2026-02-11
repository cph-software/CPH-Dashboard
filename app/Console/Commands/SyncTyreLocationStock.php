<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TyreLocation;
use App\Models\Tyre;

class SyncTyreLocationStock extends Command
{
    protected $signature = 'tyre:sync-location-stock';
    protected $description = 'Sync current stock count for all tyre locations';

    public function handle()
    {
        $this->info('Syncing tyre location stock...');
        
        $locations = TyreLocation::all();
        
        foreach ($locations as $location) {
            // Count tyres that are in this location and NOT installed
            $count = Tyre::where('work_location_id', $location->id)
                ->where('status', '!=', 'Installed')
                ->count();
            
            $location->update(['current_stock' => $count]);
            
            $this->line("Location: {$location->location_name} - Stock: {$count}");
        }
        
        $this->info('Stock sync completed!');
        return 0;
    }
}
