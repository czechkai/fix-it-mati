<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance();

// Get test.customer@example.com user
$users = $db->query("SELECT id FROM users WHERE email = 'test.customer@example.com'");

if (empty($users)) {
    die("User test.customer@example.com not found!\n");
}

$userId = $users[0]['id'];

echo "Creating sample requests for test.customer@example.com...\n\n";

$requests = [
    [
        'title' => 'Water pressure very low',
        'description' => 'Water pressure in bathroom is extremely low, barely trickling out',
        'category' => 'water',
        'status' => 'pending',
        'priority' => 'high',
        'location' => 'Bathroom'
    ],
    [
        'title' => 'Electrical outlet sparking',
        'description' => 'Outlet in kitchen is sparking when plugging in appliances',
        'category' => 'electricity',
        'status' => 'pending',
        'priority' => 'urgent',
        'location' => 'Kitchen'
    ],
    [
        'title' => 'No water supply',
        'description' => 'No water coming from any taps since this morning',
        'category' => 'water',
        'status' => 'pending',
        'priority' => 'urgent',
        'location' => 'Entire house'
    ],
    [
        'title' => 'Broken water meter',
        'description' => 'Water meter display is not showing any reading',
        'category' => 'water',
        'status' => 'in_progress',
        'priority' => 'medium',
        'location' => 'Front yard'
    ]
];

foreach ($requests as $request) {
    $id = $db->query("SELECT gen_random_uuid() as id")[0]['id'];
    
    $db->execute(
        "INSERT INTO service_requests (id, user_id, title, description, category, status, priority, location, created_at, updated_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
        [
            $id,
            $userId,
            $request['title'],
            $request['description'],
            $request['category'],
            $request['status'],
            $request['priority'],
            $request['location']
        ]
    );
    
    echo "✓ Created: {$request['title']} ({$request['status']})\n";
}

echo "\nDone! Created " . count($requests) . " requests for test.customer@example.com\n";
