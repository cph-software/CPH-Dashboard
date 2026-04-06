<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roleId = 2; // Manajerial
$role = App\Models\Role::find($roleId);
if (!$role) {
    echo "Role not found\n";
    exit;
}

$menus = $role->menus()->get();
echo "Menus for Role " . $role->name . ":\n";
foreach ($menus as $m) {
    echo "- " . $m->name . " (App: " . $m->aplikasi_id . ", Parent: " . $m->parent_id . ")\n";
}

echo "\nApplications for Role:\n";
$apps = getAplikasiPerRole($roleId);
foreach ($apps as $a) {
    echo "- " . $a->name . " (ID: " . $a->id . ")\n";
}
