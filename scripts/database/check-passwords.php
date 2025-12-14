<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance();

echo "Checking User Credentials:\n\n";

// First check columns
$cols = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");
echo "Users table columns:\n";
foreach ($cols as $c) {
    echo "  - {$c['column_name']}\n";
}
echo "\n";

$users = $db->query("SELECT email, password_hash FROM users WHERE role = 'customer' ORDER BY email");

foreach ($users as $user) {
    echo "Email: {$user['email']}\n";
    echo "Password hash: " . substr($user['password_hash'], 0, 60) . "...\n";
    
    // Test if password123 matches
    $testPassword = 'password123';
    $matches = password_verify($testPassword, $user['password_hash']);
    echo "password123 works? " . ($matches ? "✅ YES" : "❌ NO") . "\n\n";
}

// Also test creating a new hash
echo "\nTest hash for 'password123':\n";
echo password_hash('password123', PASSWORD_DEFAULT) . "\n";
