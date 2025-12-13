<?php
/**
 * Run Migration: Add Ticket Number Column
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    echo "🔄 Running migration: Add ticket number column...\n\n";
    
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Read the migration file
    $migrationFile = __DIR__ . '/database/migrations/007_add_ticket_number.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Execute the migration
    $pdo->exec($sql);
    
    echo "✅ Migration completed successfully!\n\n";
    
    echo "📋 Added ticket_number column to service_requests table\n";
    echo "✅ Auto-generation trigger created\n";
    echo "✅ Existing records updated with ticket numbers\n\n";
    
    // Show sample ticket numbers
    echo "Sample ticket numbers:\n";
    $stmt = $pdo->query("SELECT ticket_number, title FROM service_requests ORDER BY created_at LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $row['ticket_number'] . ": " . substr($row['title'], 0, 50) . "...\n";
    }
    
    echo "\n✅ Done!\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "📋 SQL State: " . $e->getCode() . "\n\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}
