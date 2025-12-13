<?php
/**
 * Run Migration: Add Service History Columns
 * Adds rating, feedback, and resolution tracking to service_requests table
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    echo "🔄 Running migration: Add service history columns...\n\n";
    
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Read the migration file
    $migrationFile = __DIR__ . '/database/migrations/006_add_service_history_columns.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Execute the migration
    $pdo->exec($sql);
    
    echo "✅ Migration completed successfully!\n\n";
    
    // Show the added columns
    echo "📋 Added columns to service_requests table:\n";
    echo "   - rating (1-5 stars)\n";
    echo "   - feedback (customer comments)\n";
    echo "   - rated_at (timestamp)\n";
    echo "   - resolution (how issue was resolved)\n";
    echo "   - technician_notes (internal notes)\n";
    echo "   - resolved_at (completion timestamp)\n";
    echo "   - resolved_by (technician name)\n";
    echo "   - before_images (photos before work)\n";
    echo "   - after_images (photos after work)\n";
    echo "   - original_request_id (for recurring issues)\n\n";
    
    echo "✅ Indexes created for better performance\n";
    echo "✅ Triggers set up for automatic resolved_at tracking\n";
    echo "✅ Existing completed requests updated with resolved_at timestamp\n\n";
    
    echo "🎉 Service History feature is now ready!\n";
    echo "📄 You can now view resolved issues at: http://localhost:8000/service-history.php\n\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "📋 SQL State: " . $e->getCode() . "\n\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}
