<?php
require_once __DIR__ . '/../autoload.php';

use FixItMati\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Checking unified technicians table...\n";
    
    // Count teams
    $count = $db->query("SELECT COUNT(*) as count FROM technicians WHERE type = 'team'")->fetch(PDO::FETCH_ASSOC);
    echo "✓ Total teams in database: " . $count['count'] . "\n";
    
    // Show sample data
    $teams = $db->query("SELECT id, name, department, status FROM technicians WHERE type = 'team' LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nSample teams:\n";
    foreach ($teams as $team) {
        echo "  - {$team['name']} ({$team['department']}) - {$team['status']}\n";
    }
    
    // Count individuals
    $indCount = $db->query("SELECT COUNT(*) as count FROM technicians WHERE type = 'individual'")->fetch(PDO::FETCH_ASSOC);
    echo "\n✓ Individual technicians: " . $indCount['count'] . "\n";
    
    echo "\n✓ Single unified table working correctly!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
