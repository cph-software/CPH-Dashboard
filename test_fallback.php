<?php

// Test script untuk Excel export service dengan fallback
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test export function
echo "Testing Excel export service with fallback logic...\n\n";

// Test Excel format
$request = new Illuminate\Http\Request([
    'type' => 'assets',
    'format' => 'excel',
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31'
]);

$controller = new App\Http\Controllers\TyrePerformance\DashboardController();

try {
    echo "Testing Excel export with fallback service...\n";
    $response = $controller->export($request);
    
    echo "Export Response Status: " . $response->getStatusCode() . "\n";
    echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
    echo "Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
    
    // Check if using Maatwebsite Excel or XML fallback
    $content = $response->getContent();
    if (strpos($content, '<?xml') === 0) {
        echo "✅ Using Maatwebsite Excel (binary format)\n";
    } else {
        echo "✅ Using XML fallback (compatible format)\n";
        echo "Content preview (first 200 chars):\n";
        echo substr($content, 0, 200) . "...\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";
