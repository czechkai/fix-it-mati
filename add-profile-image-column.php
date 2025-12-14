<?php
/**
 * Add profile_image column to users table
 * Run this script once to add the profile_image column
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Adding profile_image column to users table...\n";
    
    // Check if column already exists
    $stmt = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'users' AND column_name = 'profile_image'");
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exists) {
        echo "✓ profile_image column already exists\n";
    } else {
        // Add the column
        $db->exec("ALTER TABLE users ADD COLUMN profile_image TEXT");
        echo "✓ profile_image column added successfully\n";
    }
    
    echo "\n✅ Migration complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
