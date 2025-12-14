<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance()->getConnection();

echo "Users table columns:\n";
$stmt = $db->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - " . $row['column_name'] . " (" . $row['data_type'] . ")\n";
}

echo "\nService_requests table columns:\n";
$stmt = $db->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'service_requests' ORDER BY ordinal_position");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - " . $row['column_name'] . " (" . $row['data_type'] . ")\n";
}
