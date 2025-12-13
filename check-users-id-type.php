<?php
require_once __DIR__ . '/autoload.php';
use FixItMati\Core\Database;

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'users' AND column_name = 'id'");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "users.id data type: " . $result['data_type'] . "\n";
