<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

$db = Database::getInstance();

echo "Creating pending requests for all users...\n\n";

// Get all customer users
$users = $db->query("SELECT id, email FROM users WHERE role = 'customer' ORDER BY email");

$requestTemplates = [
    [
        'title' => 'Water pressure very low',
        'description' => 'Water pressure in bathroom is extremely low',
        'category' => 'water',
        'priority' => 'high',
        'location' => 'Bathroom'
    ],
    [
        'title' => 'Power interruption',
        'description' => 'Frequent power interruptions in the area',
        'category' => 'electricity',
        'priority' => 'urgent',
        'location' => 'Living room'
    ],
    [
        'title' => 'No water supply',
        'description' => 'No water coming from taps since this morning',
        'category' => 'water',
        'priority' => 'urgent',
        'location' => 'Kitchen'
    ]
];

$totalCreated = 0;

foreach ($users as $user) {
    // Check if user already has pending requests
    $existing = $db->query(
        "SELECT COUNT(*) as count FROM service_requests WHERE user_id = ? AND status IN ('pending', 'in_progress')",
        [$user['id']]
    );
    
    if ($existing[0]['count'] > 0) {
        echo "  {$user['email']}: Already has {$existing[0]['count']} active request(s), skipping\n";
        continue;
    }
    
    // Create 2 requests for this user
    for ($i = 0; $i < 2; $i++) {
        $template = $requestTemplates[$i];
        $id = $db->query("SELECT gen_random_uuid() as id")[0]['id'];
        
        $db->execute(
            "INSERT INTO service_requests (id, user_id, title, description, category, status, priority, location, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, NOW(), NOW())",
            [
                $id,
                $user['id'],
                $template['title'],
                $template['description'],
                $template['category'],
                $template['priority'],
                $template['location']
            ]
        );
        
        $totalCreated++;
    }
    
    echo "  ✓ {$user['email']}: Created 2 pending requests\n";
}

echo "\n✅ Done! Created $totalCreated total requests\n";
