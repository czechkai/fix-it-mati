<?php
/**
 * Check if users have passwords set
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance()->getConnection();

echo "=== CHECKING USER PASSWORDS ===\n\n";

$stmt = $db->query("SELECT email, password_hash FROM users ORDER BY created_at LIMIT 5");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    echo "Email: {$user['email']}\n";
    echo "Has password: " . (!empty($user['password_hash']) ? 'YES' : 'NO') . "\n";
    if (!empty($user['password_hash'])) {
        echo "Hash (first 20 chars): " . substr($user['password_hash'], 0, 20) . "...\n";
    }
    echo str_repeat('-', 50) . "\n";
}
