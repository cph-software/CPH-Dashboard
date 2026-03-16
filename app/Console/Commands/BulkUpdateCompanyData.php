<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BulkUpdateCompanyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tyre:bulk-company-update 
                            {--company_id= : The specific company ID to assign} 
                            {--random : Assign random existing company IDs to data}
                            {--area_map= : JSON string mapping area names to company IDs, e.g. \'{"Antam":7,"GSI":1}\'}';

    protected $description = 'Assign company_id to legacy records that currently have NULL company_id';

    public function handle()
    {
        $companyId = $this->option('company_id');
        $isRandom = $this->option('random');
        $areaMap = $this->option('area_map');

        if (!$companyId && !$isRandom && !$areaMap) {
            $this->error('Please provide --company_id, --random, or --area_map');
            return 1;
        }

        $tables = [
            'tyres',
            'master_import_kendaraan',
            'tyre_movements',
            'tyre_examinations',
            'activity_logs',
            'tyre_failure_codes',
            'tyre_monitoring_vehicle',
            'tyre_monitoring_session',
            'tyre_monitoring_check',
            'tyre_monitoring_installation',
            'tyre_monitoring_removal',
            'tyre_monitoring_images',
            'tyre_failure_aliases',
        ];

        $companies = \App\Models\TyreCompany::pluck('id')->toArray();
        if (empty($companies)) {
            $this->error('No companies found in database.');
            return 1;
        }

        foreach ($tables as $table) {
            $this->info("Processing table: $table...");
            
            $query = \DB::table($table)->whereNull('tyre_company_id');
            $count = $query->count();

            if ($count === 0) {
                $this->line(" - No NULL records found. Skipping.");
                continue;
            }

            if ($areaMap) {
                $mappings = json_decode($areaMap, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error("Invalid JSON in --area_map");
                    return 1;
                }
                
                // Check if 'area' column exists in this table
                if (!\Schema::hasColumn($table, 'area')) {
                    $this->warn(" - Table '$table' does not have an 'area' column. Skipping mapping.");
                    continue;
                }

                foreach ($mappings as $area => $id) {
                    $updated = \DB::table($table)
                        ->whereNull('tyre_company_id')
                        ->where('area', $area)
                        ->update(['tyre_company_id' => $id]);
                    $this->line(" - Mapped area '$area' to ID $id: $updated records updated.");
                }
            } else if ($isRandom) {
                // For local: process row by row in chunks to randomize
                $updatedCount = 0;
                \DB::table($table)->whereNull('tyre_company_id')->chunkById(100, function ($records) use ($table, $companies, &$updatedCount) {
                    foreach ($records as $record) {
                        $randomId = $companies[array_rand($companies)];
                        \DB::table($table)->where('id', $record->id)->update(['tyre_company_id' => $randomId]);
                        $updatedCount++;
                    }
                });
                $this->info(" - Randomized $updatedCount records.");
            } else {
                // Bulk update to specific ID
                $updated = \DB::table($table)->whereNull('tyre_company_id')->update(['tyre_company_id' => $companyId]);
                $this->info(" - Updated $updated records to company ID $companyId.");
            }
        }

        $this->info('Bulk update completed successfully!');
        return 0;
    }
}
