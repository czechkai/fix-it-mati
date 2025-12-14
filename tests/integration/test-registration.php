<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/autoload.php';

use FixItMati\Services\AuthService;

echo "Testing Registration...\n\n";

$authService = AuthService::getInstance();

$testData = [
    'email' => 'testuser' . time() . '@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'first_name' => 'Test',
    'last_name' => 'User',
    'role' => 'customer'
];

echo "Test data:\n";
print_r($testData);
echo "\n";

try {
    $result = $authService->register($testData);
    
    echo "Result:\n";
    print_r($result);
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
}
