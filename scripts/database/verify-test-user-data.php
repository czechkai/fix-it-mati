<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance();

// Get test.customer@example.com user ID
$user = $db->query("SELECT id FROM users WHERE email = 'test.customer@example.com'")[0];
$userId = $user['id'];

echo "User ID for test.customer@example.com: $userId\n\n";

// Check their requests directly
$requests = $db->query("
    SELECT id, title, category, status, priority, location, created_at
    FROM service_requests
    WHERE user_id = ?
    ORDER BY created_at DESC
", [$userId]);

echo "Total requests: " . count($requests) . "\n\n";

foreach ($requests as $req) {
    echo "ID: {$req['id']}\n";
    echo "Title: {$req['title']}\n";
    echo "Category: {$req['category']}\n";
    echo "Status: {$req['status']}\n";
    echo "Priority: {$req['priority']}\n";
    echo "Location: {$req['location']}\n";
    echo "Created: {$req['created_at']}\n";
    echo str_repeat("-", 80) . "\n";
}

// Test the API directly
echo "\nTesting API flow...\n";
require_once __DIR__ . '/Models/ServiceRequest.php';
$requestModel = new \FixItMati\Models\ServiceRequest($db);

$apiResult = $requestModel->getAll(['user_id' => $userId]);
echo "API Model returned: " . count($apiResult) . " requests\n\n";

foreach ($apiResult as $r) {
    echo "• {$r['title']} - {$r['status']}\n";
}
