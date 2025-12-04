<?php
require_once __DIR__ . '/autoload.php';
use FixItMati\Core\Database;

$db = Database::getInstance();
$stmt = $db->getConnection()->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'service_requests' ORDER BY ordinal_position");
echo "service_requests table columns:\n";
while ($row = $stmt->fetch()) {
    echo "- " . $row['column_name'] . "\n";
}
