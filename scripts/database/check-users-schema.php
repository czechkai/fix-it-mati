<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");

echo "Users table columns:\n";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - " . $row['column_name'] . "\n";
}
