<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance();

echo "Resetting all customer passwords to 'password123'...\n\n";

$newPasswordHash = password_hash('password123', PASSWORD_DEFAULT);

$users = $db->query("SELECT id, email FROM users WHERE role = 'customer'");

foreach ($users as $user) {
    $db->execute(
        "UPDATE users SET password_hash = ? WHERE id = ?",
        [$newPasswordHash, $user['id']]
    );
    
    echo "✅ Reset password for {$user['email']}\n";
}

echo "\n✅ All passwords reset to 'password123'\n";
echo "\nYou can now login with any of these accounts:\n";
foreach ($users as $user) {
    echo "  • {$user['email']} / password123\n";
}
