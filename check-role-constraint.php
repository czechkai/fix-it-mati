<?php

require_once 'autoload.php';

use FixItMati\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Checking role constraint...\n\n";
    
    $stmt = $db->query("
        SELECT conname, pg_get_constraintdef(oid) as definition 
        FROM pg_constraint 
        WHERE conrelid = 'users'::regclass 
        AND contype = 'c'
    ");
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Constraint: " . $row['conname'] . "\n";
        echo "Definition: " . $row['definition'] . "\n\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
