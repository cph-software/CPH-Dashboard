<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRepositoryCommand extends Command
{
    protected $signature = 'make:repository {name}';
    protected $description = 'Create a new repository class';

    public function handle()
    {
        $name = $this->argument('name');
        $this->createRepository($name);
    }

    protected function createRepository($name)
    {
        $path = app_path("Repositories/{$name}Repository.php");

        if (File::exists($path)) {
            $this->error("Repository {$name} already exists!");
            return;
        }

        $stub = "<?php

namespace App\Repositories;

use App\Models\\$name;

class {$name}Repository extends BaseRepository
{
    public function __construct($name \$model)
    {
        parent::__construct(\$model);
    }
}
";

        File::put($path, $stub);
        $this->info("Repository {$name}Repository created successfully.");
    }
}
