<?php
/**
 * Run Service Addresses Migration
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    echo "═══════════════════════════════════════════════════════\n";
    echo "   SERVICE ADDRESSES TABLE MIGRATION\n";
    echo "═══════════════════════════════════════════════════════\n\n";

    $db = Database::getInstance()->getConnection();
    
    // Read migration file
    $migrationFile = __DIR__ . '/database/migrations/004_create_service_addresses.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Execute migration
    echo "📄 Executing: 004_create_service_addresses.sql\n";
    $db->exec($sql);
    
    echo "✅ Migration completed successfully!\n\n";
    
    // Verify table was created
    echo "📊 Verifying table...\n";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_name = 'service_addresses'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        echo "  ✓ Table 'service_addresses' exists\n";
        
        // Show column count
        $stmt = $db->query("SELECT COUNT(*) as count FROM information_schema.columns WHERE table_name = 'service_addresses'");
        $columnCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "  ✓ Table has {$columnCount} columns\n";
        
        // Show indexes
        $stmt = $db->query("SELECT indexname FROM pg_indexes WHERE tablename = 'service_addresses'");
        $indexes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "  ✓ Created " . count($indexes) . " indexes\n";
    } else {
        throw new Exception("Table verification failed!");
    }
    
    echo "\n✨ Service addresses migration complete!\n\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
