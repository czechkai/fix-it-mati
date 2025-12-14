<?php
/**
 * Migration: Split full_name into first_name and last_name
 */

require_once 'config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Starting migration...\n";
    
    // Check if first_name and last_name columns exist
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'users' AND column_name IN ('first_name', 'last_name', 'full_name')");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $hasFirstName = in_array('first_name', $columns);
    $hasLastName = in_array('last_name', $columns);
    $hasFullName = in_array('full_name', $columns);
    
    echo "Current columns: " . implode(', ', $columns) . "\n";
    
    // Add first_name and last_name if they don't exist
    if (!$hasFirstName) {
        echo "Adding first_name column...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN first_name VARCHAR(100)");
    }
    
    if (!$hasLastName) {
        echo "Adding last_name column...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN last_name VARCHAR(100)");
    }
    
    // Migrate data from full_name to first_name/last_name if full_name exists
    if ($hasFullName) {
        echo "Migrating data from full_name to first_name/last_name...\n";
        
        $stmt = $pdo->query("SELECT id, full_name FROM users WHERE full_name IS NOT NULL");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $updateStmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
        
        foreach ($users as $user) {
            $parts = explode(' ', trim($user['full_name']), 2);
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';
            
            $updateStmt->execute([$firstName, $lastName, $user['id']]);
            echo "  Updated user {$user['id']}: {$firstName} {$lastName}\n";
        }
        
        // Drop full_name column
        echo "Dropping full_name column...\n";
        $pdo->exec("ALTER TABLE users DROP COLUMN full_name");
    }
    
    echo "\nMigration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
