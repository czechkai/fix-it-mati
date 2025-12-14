<?php
/**
 * Run Notification System Migration (003)
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    echo "========================================\n";
    echo "Running Notification System Migration\n";
    echo "========================================\n\n";

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Read migration file
    $migrationFile = __DIR__ . '/database/003_create_notifications.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    if ($sql === false) {
        throw new Exception("Failed to read migration file");
    }
    
    echo "Executing migration...\n";
    
    // Begin transaction
    $pdo->beginTransaction();
    
    try {
        // Execute the migration
        $pdo->exec($sql);
        
        // Commit transaction
        $pdo->commit();
        
        echo "\n✅ Migration completed successfully!\n\n";
        
        // Verify tables
        echo "Verifying tables...\n";
        
        $tables = ['notifications', 'notification_preferences', 'notification_templates'];
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "  ✓ Table '$table' exists (rows: $count)\n";
        }
        
        // Show template count
        $stmt = $pdo->query("SELECT COUNT(*) FROM notification_templates");
        $templateCount = $stmt->fetchColumn();
        echo "\n📧 $templateCount notification templates installed\n";
        
        echo "\n========================================\n";
        echo "Migration Complete!\n";
        echo "========================================\n";
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        throw new Exception("Migration failed: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
