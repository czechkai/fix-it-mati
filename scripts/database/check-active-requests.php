<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance();

echo "Checking Service Requests in Database:\n\n";

// First check table structure
$columns = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'service_requests' ORDER BY ordinal_position");
echo "Columns in service_requests table:\n";
foreach ($columns as $col) {
    echo "  - {$col['column_name']}\n";
}
echo "\n";

// Get all requests
$allRequests = $db->query("SELECT id, user_id, title, category, status, created_at FROM service_requests ORDER BY created_at DESC LIMIT 10");

echo "Total Requests in Database: " . count($allRequests) . "\n\n";

if (count($allRequests) > 0) {
    echo "Recent Requests:\n";
    echo str_repeat("-", 100) . "\n";
    printf("%-12s %-30s %-15s %-12s %-20s\n", "ID", "Title", "Category", "Status", "Created At");
    echo str_repeat("-", 100) . "\n";
    
    foreach ($allRequests as $req) {
        printf("%-12s %-30s %-15s %-12s %-20s\n", 
            substr($req['id'], 0, 8) . '...', 
            substr($req['title'], 0, 30),
            $req['category'], 
            $req['status'],
            substr($req['created_at'], 0, 19)
        );
    }
    echo "\n";
    
    // Count by status
    $statusCounts = $db->query("SELECT status, COUNT(*) as count FROM service_requests GROUP BY status");
    echo "Requests by Status:\n";
    foreach ($statusCounts as $status) {
        echo "  {$status['status']}: {$status['count']}\n";
    }
    
    // Check active requests specifically
    $activeRequests = $db->query("SELECT COUNT(*) as count FROM service_requests WHERE status IN ('pending', 'in_progress')");
    echo "\nActive Requests (pending/in_progress): {$activeRequests[0]['count']}\n";
} else {
    echo "No requests found in database!\n";
}
