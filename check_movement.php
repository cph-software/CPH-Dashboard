<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$m = \App\Models\TyreMovement::latest('id')->first();
echo json_encode($m->toArray(), JSON_PRETTY_PRINT);
