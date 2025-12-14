<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Get user
$stmt = $conn->prepare("SELECT id, email FROM users WHERE email = ?");
$stmt->execute(['newuser@mati.gov.ph']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "User: {$user['email']}\n";
echo "User ID: {$user['id']}\n\n";

// Check requests
$stmt = $conn->prepare("SELECT id, title, category, status, user_id FROM service_requests WHERE user_id = ?");
$stmt->execute([$user['id']]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Requests for this user: " . count($requests) . "\n\n";

foreach ($requests as $req) {
    echo "- {$req['title']} ({$req['category']}, {$req['status']})\n";
}

// Check all requests in database
$stmt = $conn->query("SELECT COUNT(*) as total FROM service_requests");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\nTotal requests in database: {$result['total']}\n";
