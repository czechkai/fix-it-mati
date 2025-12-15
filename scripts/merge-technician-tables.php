<?php
require_once __DIR__ . '/../autoload.php';

use FixItMati\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Merging technician tables...\n";
    
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/../database/005_merge_technician_tables.sql');
    
    // Execute the SQL
    $db->exec($sql);
    
    echo "✓ Tables merged successfully!\n";
    
    // Verify
    $count = $db->query("SELECT COUNT(*) as count FROM technicians WHERE type = 'team'")->fetch(PDO::FETCH_ASSOC);
    echo "✓ Teams in database: " . $count['count'] . "\n";
    
    // Show the teams
    $teams = $db->query("SELECT name, department, status FROM technicians WHERE type = 'team'")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nTeams:\n";
    foreach ($teams as $team) {
        echo "  - {$team['name']} ({$team['department']}) - {$team['status']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
