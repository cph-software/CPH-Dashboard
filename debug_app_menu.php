    <?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get All Apps
$apps = \App\Models\Aplikasi::all();
echo "Total Apps: " . $apps->count() . "\n";

foreach ($apps as $app) {
    echo "\n[APP] " . $app->name . " (ID: $app->id)\n";

    // Get Menus for this App (Parent Only)
    $menus = \App\Models\Menu::where('aplikasi_id', $app->id)
        ->whereNull('parent_id')
        ->get();

    if ($menus->count() == 0) {
        echo "  - NO PARENT MENUS FOUND\n";
    }

    foreach ($menus as $menu) {
        $status = $menu->is_active ? "Active" : "Inactive";
        $header = $menu->is_header ? "HEADER" : "Item";
        echo "  - [Menu ID: $menu->id] $menu->name ($status, $header)\n";
        echo "    Children: " . $menu->children->count() . "\n";
    }
}
