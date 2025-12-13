<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

echo "=== COMPREHENSIVE DIAGNOSTIC CHECK ===\n\n";

// 1. Database Connection
echo "1. DATABASE CONNECTION\n";
echo str_repeat("-", 80) . "\n";
try {
    $db = Database::getInstance();
    $result = $db->query("SELECT current_database(), current_user, version()");
    echo "✅ Database connected successfully\n";
    echo "   Database: {$result[0]['current_database']}\n";
    echo "   User: {$result[0]['current_user']}\n";
    echo "   Version: " . substr($result[0]['version'], 0, 50) . "...\n\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: {$e->getMessage()}\n\n";
    exit(1);
}

// 2. Check Users Table
echo "2. USERS TABLE\n";
echo str_repeat("-", 80) . "\n";
$users = $db->query("SELECT id, email, role FROM users WHERE role = 'customer' ORDER BY email");
echo "Total customers: " . count($users) . "\n\n";
foreach ($users as $user) {
    echo "  • {$user['email']} (ID: " . substr($user['id'], 0, 8) . "...)\n";
}
echo "\n";

// 3. Check Service Requests Table
echo "3. SERVICE REQUESTS TABLE\n";
echo str_repeat("-", 80) . "\n";
$allRequests = $db->query("SELECT COUNT(*) as total FROM service_requests");
$pendingRequests = $db->query("SELECT COUNT(*) as total FROM service_requests WHERE status IN ('pending', 'in_progress')");
echo "Total requests: {$allRequests[0]['total']}\n";
echo "Active requests (pending/in_progress): {$pendingRequests[0]['total']}\n\n";

// 4. Check Requests by User
echo "4. ACTIVE REQUESTS BY USER\n";
echo str_repeat("-", 80) . "\n";
$requestsByUser = $db->query("
    SELECT u.email, u.id as user_id, COUNT(sr.id) as request_count
    FROM users u
    LEFT JOIN service_requests sr ON u.id = sr.user_id AND sr.status IN ('pending', 'in_progress')
    WHERE u.role = 'customer'
    GROUP BY u.id, u.email
    ORDER BY request_count DESC, u.email
");

foreach ($requestsByUser as $row) {
    $count = $row['request_count'];
    $indicator = $count > 0 ? "✅" : "⚠️";
    echo "  $indicator {$row['email']}: $count active request(s)\n";
}
echo "\n";

// 5. Show Sample Requests
echo "5. SAMPLE ACTIVE REQUESTS (Last 5)\n";
echo str_repeat("-", 80) . "\n";
$samples = $db->query("
    SELECT sr.title, sr.status, u.email, sr.created_at
    FROM service_requests sr
    LEFT JOIN users u ON sr.user_id = u.id
    WHERE sr.status IN ('pending', 'in_progress')
    ORDER BY sr.created_at DESC
    LIMIT 5
");

if (empty($samples)) {
    echo "  ⚠️ No active requests found in database\n";
} else {
    foreach ($samples as $req) {
        echo "  • {$req['title']}\n";
        echo "    Owner: {$req['email']}, Status: {$req['status']}\n";
        echo "    Created: {$req['created_at']}\n\n";
    }
}

// 6. Check Request Model
echo "6. REQUEST MODEL TEST\n";
echo str_repeat("-", 80) . "\n";
try {
    require_once __DIR__ . '/Models/ServiceRequest.php';
    $requestModel = new \FixItMati\Models\ServiceRequest($db);
    
    // Test getAll with no filters
    $allFromModel = $requestModel->getAll([]);
    echo "✅ Request Model works\n";
    echo "   getAll() returned: " . count($allFromModel) . " requests\n\n";
    
    // Test with user_id filter for first customer
    if (!empty($users)) {
        $testUser = $users[0];
        $userRequests = $requestModel->getAll(['user_id' => $testUser['id']]);
        echo "   getAll(['user_id' => '{$testUser['email']}']) returned: " . count($userRequests) . " requests\n\n";
    }
} catch (Exception $e) {
    echo "❌ Request Model error: {$e->getMessage()}\n\n";
}

// 7. Test API Endpoint Simulation
echo "7. API ENDPOINT SIMULATION\n";
echo str_repeat("-", 80) . "\n";
try {
    require_once __DIR__ . '/DesignPatterns/Structural/Facade/ServiceRequestFacade.php';
    require_once __DIR__ . '/Models/User.php';
    
    $userModel = new \FixItMati\Models\User($db);
    $facade = new \FixItMati\DesignPatterns\Structural\Facade\ServiceRequestFacade(
        $requestModel,
        $userModel,
        null // notification service not needed for this test
    );
    
    // Test for first customer
    if (!empty($users)) {
        $testUser = $users[0];
        $result = $facade->listRequests($testUser['id'], 'customer', []);
        
        echo "✅ Facade listRequests() works\n";
        echo "   User: {$testUser['email']}\n";
        echo "   Role: customer\n";
        echo "   Result: success={$result['success']}, count={$result['count']}\n";
        echo "   Requests returned: " . count($result['requests']) . "\n\n";
        
        if ($result['count'] > 0) {
            echo "   Sample request:\n";
            $sample = $result['requests'][0];
            echo "   • Title: {$sample['title']}\n";
            echo "   • Status: {$sample['status']}\n";
            echo "   • Category: {$sample['category']}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Facade error: {$e->getMessage()}\n";
    echo "   Stack trace:\n   " . str_replace("\n", "\n   ", $e->getTraceAsString()) . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "DIAGNOSTIC COMPLETE\n";
