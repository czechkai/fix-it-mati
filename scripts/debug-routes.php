<?php
// Debug script to see what routes are registered

require_once __DIR__ . '/../autoload.php';

use FixItMati\Core\Router;
use FixItMati\Core\Request;

$router = new Router();

// Register the route
$router->post('/api/admin/billing/create-invoice', 'PaymentController@createInvoice');

// Use reflection to access private routes array
$reflection = new ReflectionClass($router);
$property = $reflection->getProperty('routes');
$property->setAccessible(true);
$routes = $property->getValue($router);

echo "Registered Routes:\n";
foreach ($routes as $route) {
    echo "Method: {$route['method']}\n";
    echo "Path: {$route['path']}\n";
    echo "Pattern: {$route['pattern']}\n";
    echo "Handler: {$route['handler']}\n";
    echo "\n";
}

// Test URI matching
$testUri = '/api/admin/billing/create-invoice';
echo "Testing URI: $testUri\n";

foreach ($routes as $route) {
    if (preg_match($route['pattern'], $testUri, $matches)) {
        echo "✓ MATCH! Route: {$route['path']}\n";
        print_r($matches);
    } else {
        echo "✗ No match for: {$route['path']}\n";
    }
}
