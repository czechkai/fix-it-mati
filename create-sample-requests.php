<?php
/**
 * Script to create sample service requests for testing
 */

require_once __DIR__ . '/config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Get the test user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->execute(['newuser@mati.gov.ph']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Error: Test user not found. Please login first.\n");
}

$userId = $user['id'];
echo "Creating sample requests for user ID: $userId\n\n";

// Sample requests (Water and Electricity only)
$sampleRequests = [
    [
        'title' => 'Water Supply Interruption',
        'description' => 'There has been no water supply in our area since early morning. Multiple households are affected and we urgently need water delivery or repair services.',
        'category' => 'water',
        'priority' => 'high',
        'location' => 'Purok 1, Barangay Central, Mati City',
        'contact_name' => 'Juan Dela Cruz',
        'contact_phone' => '09123456789',
        'status' => 'pending'
    ],
    [
        'title' => 'Power Outage in Residential Area',
        'description' => 'Complete power outage affecting our entire block. No electricity since 6 AM. Several families with medical equipment need urgent restoration.',
        'category' => 'electricity',
        'priority' => 'urgent',
        'location' => 'Main Street, Purok 2, Barangay Poblacion',
        'contact_name' => 'Maria Santos',
        'contact_phone' => '09187654321',
        'status' => 'in_progress'
    ],
    [
        'title' => 'Low Water Pressure',
        'description' => 'Water pressure has been extremely low for the past 3 days. Barely enough water coming through the pipes for daily needs.',
        'category' => 'water',
        'priority' => 'medium',
        'location' => 'Purok 4, Barangay Dahican',
        'contact_name' => 'Pedro Reyes',
        'contact_phone' => '09198765432',
        'status' => 'pending'
    ],
    [
        'title' => 'Faulty Electric Meter',
        'description' => 'Electric meter shows abnormally high readings. Bill increased by 300% without change in usage patterns. Meter inspection needed.',
        'category' => 'electricity',
        'priority' => 'high',
        'location' => 'Purok 3, Barangay Sainz',
        'contact_name' => 'Ana Lopez',
        'contact_phone' => '09176543210',
        'status' => 'pending'
    ],
    [
        'title' => 'Broken Water Pipe',
        'description' => 'A water pipe burst near the basketball court causing flooding in the area. Immediate repair is needed to prevent further damage.',
        'category' => 'water',
        'priority' => 'urgent',
        'location' => 'Barangay Central Basketball Court',
        'contact_name' => 'Roberto Garcia',
        'contact_phone' => '09165432109',
        'status' => 'in_progress'
    ],
    [
        'title' => 'Flickering Street Lights',
        'description' => 'Street lights keep flickering and turning off randomly creating unsafe conditions for pedestrians and motorists at night.',
        'category' => 'electricity',
        'priority' => 'medium',
        'location' => 'Provincial Road, Barangay Matiao',
        'contact_name' => 'Carlos Mendoza',
        'contact_phone' => '09156789012',
        'status' => 'pending'
    ]
];

$stmt = $conn->prepare("
    INSERT INTO service_requests 
    (user_id, title, description, category, priority, location, status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
");

$created = 0;
foreach ($sampleRequests as $request) {
    try {
        $stmt->execute([
            $userId,
            $request['title'],
            $request['description'],
            $request['category'],
            $request['priority'],
            $request['location'],
            $request['status']
        ]);
        $created++;
        echo "✓ Created: {$request['title']}\n";
    } catch (PDOException $e) {
        echo "✗ Failed: {$request['title']} - {$e->getMessage()}\n";
    }
}

echo "\n========================================\n";
echo "Successfully created $created sample requests!\n";
echo "========================================\n";
echo "\nYou can now:\n";
echo "1. View them in the dashboard (http://localhost:8000/user-dashboard.php)\n";
echo "2. Filter by category (water, electricity)\n";
echo "3. Filter by status (pending, in_progress)\n";
echo "4. Test the create-request form (http://localhost:8000/create-request.php)\n";
