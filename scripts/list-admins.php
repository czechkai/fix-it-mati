<?php
require_once __DIR__ . '/../autoload.php';

$db = \FixItMati\Core\Database::getInstance();
$stmt = $db->getConnection()->query("SELECT email, role FROM users WHERE role = 'admin'");

echo "Admin users:\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- {$row['email']} ({$row['role']})\n";
}
