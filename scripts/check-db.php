<?php
require_once 'autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance()->getConnection();

// Count records
$stmt = $db->query('SELECT COUNT(*) as total FROM service_requests');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total records: " . $result['total'] . PHP_EOL;
echo PHP_EOL;

// First, get service_requests columns
$stmt = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'service_requests' ORDER BY ordinal_position");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "service_requests columns: " . implode(', ', $columns) . PHP_EOL;
echo PHP_EOL;

// Get users table columns
$stmt = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");
$userColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "users table columns: " . implode(', ', $userColumns) . PHP_EOL;
echo PHP_EOL;

// Get sample records
$stmt = $db->query('SELECT * FROM service_requests ORDER BY created_at DESC LIMIT 3');
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($records)) {
    echo "No records found in service_requests table!" . PHP_EOL;
} else {
    echo "Sample records:" . PHP_EOL;
    echo json_encode($records, JSON_PRETTY_PRINT) . PHP_EOL;
}
