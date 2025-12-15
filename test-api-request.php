<?php

/**
 * Test API Request Creation
 * Run this to test the request creation directly
 */

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load autoloader
require_once __DIR__ . '/autoload.php';

use FixItMati\Models\ServiceRequest;
use FixItMati\Models\User;
use FixItMati\DesignPatterns\Structural\Facade\ServiceRequestFacade;

echo "=== Testing Service Request Creation ===\n\n";

// Test 1: Check if we can connect to database
echo "1. Testing database connection...\n";
try {
    $db = FixItMati\Core\Database::getInstance()->getConnection();
    echo "✓ Database connected successfully\n\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check table structure
echo "2. Checking service_requests table structure...\n";
try {
    $stmt = $db->query("SELECT column_name, data_type, is_nullable 
                        FROM information_schema.columns 
                        WHERE table_name = 'service_requests' 
                        ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Columns found:\n";
    foreach ($columns as $col) {
        echo "  - {$col['column_name']} ({$col['data_type']}) " .
            ($col['is_nullable'] === 'NO' ? '[REQUIRED]' : '[OPTIONAL]') . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "✗ Failed to check table structure: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Get a test user
echo "3. Getting test user...\n";
try {
    $userModel = new User();
    $stmt = $db->query("SELECT id, email FROM users WHERE role = 'user' LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "✗ No user found in database\n";
        exit(1);
    }

    echo "✓ Using user: {$user['email']} (ID: {$user['id']})\n\n";
} catch (Exception $e) {
    echo "✗ Failed to get user: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Create a test request using the facade
echo "4. Creating test request using ServiceRequestFacade...\n";
try {
    $facade = new ServiceRequestFacade();

    $requestData = [
        'category' => 'water',
        'title' => 'Test Request - ' . date('Y-m-d H:i:s'),
        'description' => 'This is a test request created to debug the API issue.',
        'location' => '123 Test Street, Test Barangay, Mati City',
        'priority' => 'normal'
    ];

    echo "Request data:\n";
    print_r($requestData);

    $result = $facade->submitRequest($user['id'], $requestData);

    if ($result['success']) {
        echo "✓ Request created successfully!\n";
        echo "Request details:\n";
        print_r($result['request']);
    } else {
        echo "✗ Failed to create request: {$result['error']}\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
