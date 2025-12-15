<?php
require_once 'autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance()->getConnection();
$stmt = $db->query('SELECT DISTINCT status FROM service_requests ORDER BY status');
$statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo 'Status values in database: ' . implode(', ', $statuses) . PHP_EOL;
