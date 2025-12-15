<?php
require_once 'autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance()->getConnection();

// Get all users with their roles
$stmt = $db->query("SELECT id, email, first_name, last_name, role FROM users WHERE role IN ('admin', 'technician', 'staff') ORDER BY role, email LIMIT 20");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Users who can be assigned as technicians:\n\n";
foreach ($users as $user) {
    $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    if (empty($name)) {
        $name = $user['email'];
    }
    echo "ID: {$user['id']}\n";
    echo "Name: {$name}\n";
    echo "Email: {$user['email']}\n";
    echo "Role: {$user['role']}\n";
    echo "---\n";
}
