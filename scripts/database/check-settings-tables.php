<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance();

echo "Checking Settings Tables:\n\n";

$tables = ['user_settings', 'payment_methods', 'household_members'];

foreach ($tables as $table) {
    $result = $db->query("SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = '$table'
    )");
    
    $exists = $result[0]['exists'] ?? false;
    
    echo "✓ Table '$table': " . ($exists ? "EXISTS" : "MISSING") . "\n";
    
    if ($exists) {
        // Get column count
        $colResult = $db->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_name = '$table'");
        echo "  └─ Columns: {$colResult[0]['count']}\n";
    }
}

echo "\n";
