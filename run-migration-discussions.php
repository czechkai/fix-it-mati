<?php
/**
 * Run Migration: Create Discussions Tables
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    echo "🔄 Running migration: Create discussions tables...\n\n";
    
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Read the migration file
    $migrationFile = __DIR__ . '/database/migrations/008_create_discussions.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Execute the migration
    $pdo->exec($sql);
    
    echo "✅ Migration completed successfully!\n\n";
    
    echo "📋 Created tables:\n";
    echo "   - discussions (main discussion threads)\n";
    echo "   - discussion_comments (replies to discussions)\n";
    echo "   - discussion_upvotes (tracks user upvotes)\n\n";
    
    echo "✅ Indexes and triggers created\n";
    echo "✅ Community Discussions feature is now ready!\n";
    echo "📄 Visit: http://localhost:8000/discussions.php\n\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "📋 SQL State: " . $e->getCode() . "\n\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}
