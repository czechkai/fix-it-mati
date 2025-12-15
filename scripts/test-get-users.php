<?php
require_once __DIR__ . '/../autoload.php';

use FixItMati\Models\User;

try {
    $userModel = new User();
    $users = $userModel->getAllCitizens();
    
    echo "Found " . count($users) . " users\n\n";
    
    foreach ($users as $user) {
        echo "- {$user['full_name']} ({$user['email']})\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
