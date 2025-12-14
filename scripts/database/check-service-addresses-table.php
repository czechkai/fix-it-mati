<?php
require_once __DIR__ . '/autoload.php';
use FixItMati\Core\Database;

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'service_addresses')");
echo $stmt->fetchColumn() ? 'EXISTS' : 'NOT_EXISTS';
