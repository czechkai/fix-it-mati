<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

echo "🔄 Running migration: Create user settings tables...\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Read and execute migration
    $sql = file_get_contents(__DIR__ . '/database/migrations/009_create_user_settings.sql');
    $db->exec($sql);
    
    echo "✅ Migration completed successfully!\n\n";
    echo "📋 Created tables:\n";
    echo "   - user_settings (notification preferences, app settings, security)\n";
    echo "   - payment_methods (saved payment options)\n";
    echo "   - household_members (shared access users)\n\n";
    echo "✅ Indexes and triggers created\n";
    echo "✅ Account Settings feature is now ready!\n";
    echo "📄 Visit: http://localhost:8000/settings.php\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
