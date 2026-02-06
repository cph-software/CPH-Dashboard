<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Repository and Service for a model';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');

        $this->info("Creating module for: {$name}...");

        // 1. Generate Repository
        $this->call('make:repository', ['name' => $name]);

        // 2. Generate Service
        $this->call('make:service', ['name' => $name]);

        $this->info("Module {$name} (Repository & Service) created successfully!");

        return 0;
    }
}
