<?php
require_once __DIR__ . '/../autoload.php';

use FixItMati\Models\User;
use FixItMati\Controllers\PaymentController;

// First test user model
$userModel = new User();
$users = $userModel->getAllCitizens();

echo "Found " . count($users) . " users:\n";
foreach ($users as $user) {
    echo "  - {$user['full_name']} ({$user['email']})\n";
}

echo "\n\nTesting PaymentController::getAllUsers():\n";

// Mock the request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer test';

// Create controller
$controller = new PaymentController();

try {
    // Call the method directly
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getAllUsers');
    $method->setAccessible(true);
    
    ob_start();
    $method->invoke($controller);
    $output = ob_get_clean();
    
    echo "Response:\n";
    echo $output . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
