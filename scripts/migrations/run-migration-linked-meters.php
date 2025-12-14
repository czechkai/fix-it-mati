<?php
/**
 * Run Linked Meters Migration
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    echo "═══════════════════════════════════════════════════════\n";
    echo "   LINKED METERS TABLE MIGRATION\n";
    echo "═══════════════════════════════════════════════════════\n\n";

    $db = Database::getInstance()->getConnection();
    
    // Read migration file
    $migrationFile = __DIR__ . '/database/migrations/005_create_linked_meters.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Execute migration
    echo "📄 Executing: 005_create_linked_meters.sql\n";
    $db->exec($sql);
    
    echo "✅ Migration completed successfully!\n\n";
    
    // Verify table was created
    echo "📊 Verifying table...\n";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_name = 'linked_meters'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        echo "  ✓ Table 'linked_meters' exists\n";
        
        // Show column count
        $stmt = $db->query("SELECT COUNT(*) as count FROM information_schema.columns WHERE table_name = 'linked_meters'");
        $columnCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "  ✓ Table has {$columnCount} columns\n";
        
        // Show indexes
        $stmt = $db->query("SELECT indexname FROM pg_indexes WHERE tablename = 'linked_meters'");
        $indexes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "  ✓ Created " . count($indexes) . " indexes\n";
    } else {
        throw new Exception("Table verification failed!");
    }
    
    echo "\n✨ Linked meters migration complete!\n\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
