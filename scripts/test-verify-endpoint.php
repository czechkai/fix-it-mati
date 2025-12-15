<?php
// Test the verify endpoint directly
require_once __DIR__ . '/../autoload.php';

use FixItMati\Models\User;
use FixItMati\Controllers\UserController;
use FixItMati\Core\Request;

// Get a user to test with
$users = User::all(['role' => 'customer']);
if (empty($users)) {
    echo "No users to test with\n";
    exit;
}

$testUser = $users[0];
echo "Testing verification with user: {$testUser->email}\n";
echo "User ID: {$testUser->id}\n";
echo "Current role: {$testUser->role}\n\n";

// Create mock request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer test';

$request = new Request();

// Mock admin user
$reflection = new ReflectionClass($request);
$property = $reflection->getProperty('user');
$property->setAccessible(true);
$property->setValue($request, [
    'id' => 'admin-id',
    'role' => 'admin'
]);

// Create controller
$controller = new UserController();

try {
    $response = $controller->verifyUser($request, ['id' => $testUser->id]);
    
    ob_start();
    $response->send();
    $output = ob_get_clean();
    
    echo "Response:\n$output\n";
    
    // Reload user to check if it updated
    $updatedUser = User::find($testUser->id);
    echo "\nUpdated user role: {$updatedUser->role}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
