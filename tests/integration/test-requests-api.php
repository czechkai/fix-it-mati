<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

// Simulate an API request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/api/requests';

// Get a valid user token from database
$db = Database::getInstance();
$users = $db->query("SELECT id, email, role FROM users LIMIT 1");

if (empty($users)) {
    die("No users found in database\n");
}

$user = $users[0];
echo "Testing API as user: {$user['email']} (Role: {$user['role']})\n\n";

// Create a mock JWT token
require_once __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;

$key = $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this-in-production';
$payload = [
    'user_id' => $user['id'],
    'email' => $user['email'],
    'role' => $user['role'],
    'iat' => time(),
    'exp' => time() + (60 * 60 * 24) // 24 hours
];
$token = JWT::encode($payload, $key, 'HS256');

echo "Generated Token: " . substr($token, 0, 50) . "...\n\n";

// Simulate the API call
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

// Now call the API endpoint
echo "Calling GET /api/requests...\n";
echo str_repeat("-", 80) . "\n";

// Include the API router
ob_start();
require __DIR__ . '/public/api/index.php';
$output = ob_get_clean();

echo "\nAPI Response:\n";
echo $output;
echo "\n";
