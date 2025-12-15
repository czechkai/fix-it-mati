<?php
require_once __DIR__ . '/../autoload.php';

use FixItMati\Models\User;

try {
    echo "Testing User::all() method...\n\n";
    
    $users = User::all();
    
    echo "Found " . count($users) . " users\n\n";
    
    if (count($users) > 0) {
        echo "Sample users:\n";
        for ($i = 0; $i < min(5, count($users)); $i++) {
            $user = $users[$i];
            $name = $user->full_name ?? ($user->first_name . ' ' . $user->last_name);
            echo "- {$name} ({$user->email}) - Role: {$user->role}\n";
        }
    } else {
        echo "No users found in database\n";
    }
    
    // Test toArray method
    if (count($users) > 0) {
        echo "\nTesting toArray on first user:\n";
        $userArray = $users[0]->toArray();
        print_r($userArray);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
