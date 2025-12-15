<?php
require_once __DIR__ . '/../autoload.php';

use FixItMati\Models\User;

// Test getting a user and verifying
try {
    // Get a customer user
    $users = User::all(['role' => 'customer']);
    
    if (empty($users)) {
        echo "No customer users found\n";
        exit;
    }
    
    $user = $users[0];
    echo "Testing with user: {$user->email} (ID: {$user->id})\n";
    echo "Current status: " . ($user->status ?? 'not set') . "\n";
    echo "Account verified: " . ($user->account_verified ?? 'not set') . "\n\n";
    
    // Check what columns exist in users table
    $db = \FixItMati\Core\Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->query("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_name = 'users' 
        AND column_name IN ('status', 'account_verified')
    ");
    
    echo "Columns in users table:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['column_name']} ({$row['data_type']})\n";
    }
    
    // Try to update
    echo "\nAttempting to update...\n";
    $result = $user->update([
        'status' => 'verified'
    ]);
    
    if ($result) {
        echo "✓ Update successful!\n";
    } else {
        echo "✗ Update failed\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
