<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$menus = \App\Models\Menu::whereNull('parent_id')->with('children')->get();
echo "Total Parents: " . $menus->count() . "\n";

foreach ($menus as $menu) {
    echo "Parent: " . $menu->name . " (ID: $menu->id) - Children Count: " . $menu->children->count() . "\n";
    foreach ($menu->children as $child) {
        echo "   - Child: " . $child->name . " (Parent ID: $child->parent_id)\n";
    }
}
