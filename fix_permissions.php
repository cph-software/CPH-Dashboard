<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roleId = 77; // Based on debug output: Super Admin
$role = \App\Models\Role::find($roleId);

if (!$role) {
    echo "Role ID $roleId not found.\n";
    exit;
}

echo "Fixing permissions for Role: {$role->name} (ID: $roleId)...\n";

// 1. Get all active applications
$allApps = \App\Models\Aplikasi::all();
echo "Found " . $allApps->count() . " applications in database.\n";

// 2. Sync to aplikasi_role table
// We use syncWithoutDetaching to avoid removing existing ones (though there are none currently)
$appIds = $allApps->pluck('id')->toArray();
$role->aplikasi()->syncWithoutDetaching($appIds);

echo "SUCCESS: Attached " . count($appIds) . " applications to Role {$role->name}.\n";

// 3. Verify
$count = \DB::table('aplikasi_role')->where('role_id', $roleId)->count();
echo "Verification: Role now has access to $count applications.\n";
