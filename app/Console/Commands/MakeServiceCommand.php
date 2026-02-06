<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name}';
    protected $description = 'Create a new service class';

    public function handle()
    {
        $name = $this->argument('name');
        $this->createService($name);
    }

    protected function createService($name)
    {
        $path = app_path("Services/{$name}Service.php");

        if (File::exists($path)) {
            $this->error("Service {$name} already exists!");
            return;
        }

        $stub = "<?php

namespace App\Services;

use App\Repositories\\{$name}Repository;

class {$name}Service extends BaseService
{
    public function __construct({$name}Repository \$repository)
    {
        parent::__construct(\$repository);
    }
}
";

        File::put($path, $stub);
        $this->info("Service {$name}Service created successfully.");
    }
}
