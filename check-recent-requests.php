<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance();

echo "Most Recent Requests (Last 10):\n";
echo str_repeat("-", 100) . "\n";
printf("%-30s | %-12s | %-30s | %-20s\n", "Title", "Status", "User Email", "Created At");
echo str_repeat("-", 100) . "\n";

$recent = $db->query("
    SELECT sr.id, sr.title, sr.status, sr.created_at, u.email 
    FROM service_requests sr 
    LEFT JOIN users u ON sr.user_id = u.id 
    ORDER BY sr.created_at DESC 
    LIMIT 10
");

foreach ($recent as $r) {
    printf("%-30s | %-12s | %-30s | %-20s\n",
        substr($r['title'], 0, 30),
        $r['status'],
        substr($r['email'], 0, 30),
        substr($r['created_at'], 0, 19)
    );
}

echo "\n\nChecking test.customer@example.com requests:\n";
echo str_repeat("-", 100) . "\n";

$testUserRequests = $db->query("
    SELECT sr.id, sr.title, sr.status, sr.created_at 
    FROM service_requests sr 
    LEFT JOIN users u ON sr.user_id = u.id 
    WHERE u.email = 'test.customer@example.com'
    ORDER BY sr.created_at DESC
");

if (empty($testUserRequests)) {
    echo "No requests found for test.customer@example.com\n";
} else {
    printf("%-30s | %-12s | %-20s\n", "Title", "Status", "Created At");
    echo str_repeat("-", 100) . "\n";
    foreach ($testUserRequests as $r) {
        printf("%-30s | %-12s | %-20s\n",
            substr($r['title'], 0, 30),
            $r['status'],
            substr($r['created_at'], 0, 19)
        );
    }
}
