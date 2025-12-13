<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance();

echo "Updating test.customer@example.com requests to pending...\n\n";

$updated = $db->execute("
    UPDATE service_requests 
    SET status = 'pending', updated_at = NOW() 
    WHERE user_id = (SELECT id FROM users WHERE email = 'test.customer@example.com') 
    AND status = 'completed'
");

echo "✓ Updated $updated requests to pending status\n\n";

// Show the updated requests
$requests = $db->query("
    SELECT title, status, created_at 
    FROM service_requests 
    WHERE user_id = (SELECT id FROM users WHERE email = 'test.customer@example.com')
    ORDER BY created_at DESC
");

echo "Current requests for test.customer@example.com:\n";
echo str_repeat("-", 80) . "\n";
foreach ($requests as $r) {
    echo "  • {$r['title']} - {$r['status']}\n";
}
