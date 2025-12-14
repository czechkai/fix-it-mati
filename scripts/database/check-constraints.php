<?php

require_once 'config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check category constraint
    $stmt = $db->query("
        SELECT conname, pg_get_constraintdef(oid) as definition 
        FROM pg_constraint 
        WHERE conrelid = 'discussions'::regclass 
        AND contype = 'c'
    ");
    
    echo "📋 Discussions Table Constraints:\n";
    echo str_repeat('-', 80) . "\n";
    
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $constraint) {
        echo "Constraint: {$constraint['conname']}\n";
        echo "Definition: {$constraint['definition']}\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
