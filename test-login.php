<?php
/**
 * Test login directly
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Services\AuthService;

$authService = AuthService::getInstance();

echo "=== TESTING LOGIN ===\n\n";

$email = 'test.customer@example.com';
$password = 'password123';

echo "Attempting login with:\n";
echo "Email: {$email}\n";
echo "Password: {$password}\n\n";

$result = $authService->login($email, $password, false);

echo "Result:\n";
print_r($result);

if ($result['success']) {
    echo "\n✓ Login successful!\n";
    $user = $authService->user();
    echo "User ID: {$user['id']}\n";
    echo "Role: {$user['role']}\n";
    
    // Test token generation
    $token = $authService->generateToken($user);
    echo "\nGenerated Token:\n{$token}\n";
} else {
    echo "\n✗ Login failed!\n";
    echo "Message: {$result['message']}\n";
    if (isset($result['errors'])) {
        echo "Errors: " . print_r($result['errors'], true) . "\n";
    }
}
