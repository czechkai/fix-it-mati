<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance();

echo "Checking User and Request Ownership:\n\n";

// Get all users
$users = $db->query("SELECT id, email, role FROM users ORDER BY email");
echo "Users in Database:\n";
echo str_repeat("-", 80) . "\n";
foreach ($users as $user) {
    $requestCount = $db->query("SELECT COUNT(*) as count FROM service_requests WHERE user_id = ?", [$user['id']]);
    $activeCount = $db->query("SELECT COUNT(*) as count FROM service_requests WHERE user_id = ? AND status IN ('pending', 'in_progress')", [$user['id']]);
    
    echo sprintf("%-40s %-20s Total: %d, Active: %d\n", 
        $user['email'], 
        "({$user['role']})",
        $requestCount[0]['count'],
        $activeCount[0]['count']
    );
}

echo "\n";
echo "Request Ownership:\n";
echo str_repeat("-", 80) . "\n";

$requests = $db->query("
    SELECT sr.id, sr.title, sr.status, sr.user_id, u.email 
    FROM service_requests sr 
    LEFT JOIN users u ON sr.user_id = u.id 
    WHERE sr.status IN ('pending', 'in_progress')
    ORDER BY sr.created_at DESC
");

foreach ($requests as $req) {
    echo sprintf("%-12s %-30s %-15s %s\n",
        substr($req['id'], 0, 8) . '...',
        substr($req['title'], 0, 30),
        $req['status'],
        $req['email'] ?? 'NO USER'
    );
}
