<?php
require_once __DIR__ . '/../autoload.php';

use FixItMati\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Creating technician_teams table...\n";
    
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/../database/004_create_technician_teams.sql');
    
    // Execute the SQL
    $db->exec($sql);
    
    echo "✓ Table created successfully!\n";
    
    // Verify
    $count = $db->query("SELECT COUNT(*) as count FROM technician_teams")->fetch(PDO::FETCH_ASSOC);
    echo "✓ Sample data inserted: " . $count['count'] . " teams\n";
    
    // Show the teams
    $teams = $db->query("SELECT name, department, status FROM technician_teams")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nTeams created:\n";
    foreach ($teams as $team) {
        echo "  - {$team['name']} ({$team['department']}) - {$team['status']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
