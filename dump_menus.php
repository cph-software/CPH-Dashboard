<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$menus = App\Models\Menu::all();
echo "ALL MENUS IN DATABASE:\n";
foreach ($menus as $m) {
    echo "- ID: {$m->id} | Name: {$m->name} | App ID: {$m->aplikasi_id} | Parent: {$m->parent_id} | URL: {$m->url} | Icon: {$m->icon}\n";
}

echo "\nALL APPLICATIONS:\n";
$apps = App\Models\Aplikasi::all();
foreach ($apps as $a) {
    echo "- ID: {$a->id} | Name: {$a->name}\n";
}
