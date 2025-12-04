<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance();
$stmt = $db->getConnection()->query('SELECT id, email, role FROM users');
echo "Existing users:\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- Email: {$row['email']}, Role: {$row['role']}, ID: {$row['id']}\n";
}
