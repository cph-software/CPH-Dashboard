<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// ASUMSI: Kita debug User ID 1 (biasanya Super Admin)
// Ganti ID ini jika Anda ingin debug user spesifik
$userId = 1;
$user = \App\Models\User::find($userId);

if (!$user) {
    echo "User ID $userId not found.\n";
    exit;
}

$roleId = $user->role_id;
$role = \App\Models\Role::find($roleId);

echo "DEBUGGING PERMISSIONS FOR USER: {$user->name} (Role ID: {$roleId} - {$role->name})\n";
echo str_repeat("=", 50) . "\n\n";

// 1. CEK APLIKASI_ROLE (Aplikasi yang diizinkan untuk Role ini)
$assignedApps = \DB::table('aplikasi_role')
    ->join('aplikasi', 'aplikasi_role.aplikasi_id', '=', 'aplikasi.id')
    ->where('role_id', $roleId)
    ->select('aplikasi.id', 'aplikasi.name')
    ->get();

$assignedAppIds = $assignedApps->pluck('id')->toArray();

echo "1. APLIKASI ASSIGNED TO ROLE (Table: aplikasi_role)\n";
echo "Total: " . count($assignedApps) . "\n";
foreach ($assignedApps as $app) {
    echo " - [App ID: {$app->id}] {$app->name}\n";
}
echo "\n";

// 2. CEK ROLE_MENU (Menu yang diizinkan untuk Role ini)
$assignedRoleMenus = \App\Models\RoleMenu::where('role_id', $roleId)
    ->with('menu')
    ->get();

echo "2. MENUS ASSIGNED TO ROLE (Table: role_menu)\n";
echo "Total RoleMenu Entries: " . $assignedRoleMenus->count() . "\n";

// Group menu by Aplikasi ID
$menusByApp = [];
foreach ($assignedRoleMenus as $rm) {
    if ($rm->menu) {
        $appId = $rm->menu->aplikasi_id;
        if (!isset($menusByApp[$appId])) {
            $menusByApp[$appId] = [];
        }
        $menusByApp[$appId][] = $rm->menu;
    }
}

// 3. ANALISIS KONSISTENSI
echo "\n3. CONSISTENCY CHECK\n";

// Cek Aplikasi yang punya Menu tapi tidak ada di aplikasi_role
$appsWithMenusButNotAssigned = array_diff(array_keys($menusByApp), $assignedAppIds);

if (count($appsWithMenusButNotAssigned) > 0) {
    echo "\n[WARNING] Found Menus assigned for Apps that are NOT in aplikasi_role!\n";
    echo "These menus will NOT appear in the sidebar because the App itself is hidden.\n";

    foreach ($appsWithMenusButNotAssigned as $appId) {
        $appName = \App\Models\Aplikasi::find($appId)->name ?? 'Unknown';
        echo " - App ID: $appId ($appName) has " . count($menusByApp[$appId]) . " assigned menus.\n";
    }
} else {
    echo "\n[OK] All Apps with assigned menus are present in aplikasi_role.\n";
}

// Cek Aplikasi di aplikasi_role tapi tidak punya menu
$appsAssignedButNoMenus = array_diff($assignedAppIds, array_keys($menusByApp));

if (count($appsAssignedButNoMenus) > 0) {
    echo "\n[INFO] Found Apps in aplikasi_role but NO menus assigned in role_menu.\n";
    echo "These Apps will appear in sidebar (if logic allows empty apps) or be hidden (if logic requires >0 menus).\n";

    foreach ($appsAssignedButNoMenus as $appId) {
        $appName = \DB::table('aplikasi')->where('id', $appId)->value('name');

        // Cek apakah sebenarnya ada menu di database untuk app ini (tapi belum di-assign)
        $msg = "No menus assigned.";
        $totalMenusInDb = \App\Models\Menu::where('aplikasi_id', $appId)->count();
        if ($totalMenusInDb > 0) {
            $msg = "Has $totalMenusInDb menus in DB, but NONE assigned to this Role.";
        } else {
            $msg = "Also has 0 menus in DB.";
        }

        echo " - App ID: $appId ($appName): $msg\n";
    }
} else {
    echo "\n[OK] All assigned Apps have at least one assigned menu.\n";
}

echo "\nDone.\n";
