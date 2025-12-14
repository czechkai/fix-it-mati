<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance();

$user = $db->query("SELECT email, password_hash FROM users WHERE email = 'saerlibanon0@gmail.com'")[0];

echo "Email: {$user['email']}\n";
echo "Hash: " . substr($user['password_hash'], 0, 60) . "...\n";
echo "password123 works? " . (password_verify('password123', $user['password_hash']) ? "✅ YES" : "❌ NO") . "\n\n";

// Reset it again to be sure
$newHash = password_hash('password123', PASSWORD_DEFAULT);
$db->execute("UPDATE users SET password_hash = ? WHERE email = ?", [$newHash, 'saerlibanon0@gmail.com']);

echo "✅ Password reset again for saerlibanon0@gmail.com\n";

// Verify it works now
$user2 = $db->query("SELECT password_hash FROM users WHERE email = 'saerlibanon0@gmail.com'")[0];
echo "New verification: " . (password_verify('password123', $user2['password_hash']) ? "✅ YES" : "❌ NO") . "\n";
