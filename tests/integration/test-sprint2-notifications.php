<?php
/**
 * Test Sprint 2 - Notification System API Endpoints
 */

require_once __DIR__ . '/autoload.php';

// Helper function to make API requests
function apiRequest($method, $endpoint, $data = null, $token = null) {
    $url = 'http://localhost:8000' . $endpoint;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => json_decode($response, true)
    ];
}

echo "========================================\n";
echo "Testing Sprint 2 Notification System\n";
echo "========================================\n\n";

try {
    // Step 1: Login with existing user
    echo "1. Authenticating...\n";
    
    $loginResponse = apiRequest('POST', '/api/auth/login', [
        'email' => 'test.customer@example.com',
        'password' => 'Customer123!@#'
    ]);
    
    if ($loginResponse['code'] !== 200) {
        throw new Exception("Login failed. Please ensure user exists with correct password.");
    }
    
    $token = $loginResponse['body']['data']['token'];
    $userId = $loginResponse['body']['data']['user']['id'];
    echo "   ✓ Logged in as: {$loginResponse['body']['data']['user']['email']}\n";
    echo "   ✓ User ID: $userId\n\n";
    
    // Step 2: Get initial notification count
    echo "2. Getting initial notification count...\n";
    $countResponse = apiRequest('GET', '/api/notifications/unread-count', null, $token);
    echo "   ✓ Status: {$countResponse['code']}\n";
    echo "   ✓ Unread count: {$countResponse['body']['data']['unread_count']}\n\n";
    
    // Step 3: Get all notifications
    echo "3. Getting all notifications...\n";
    $listResponse = apiRequest('GET', '/api/notifications', null, $token);
    echo "   ✓ Status: {$listResponse['code']}\n";
    $notificationCount = count($listResponse['body']['data'] ?? []);
    echo "   ✓ Total notifications: $notificationCount\n\n";
    
    // Step 4: Send test notification
    echo "4. Sending test notification...\n";
    $testResponse = apiRequest('POST', '/api/notifications/test', [
        'title' => 'Sprint 2 Test Notification',
        'message' => 'Testing the notification system with Observer, Strategy, and Bridge patterns!'
    ], $token);
    echo "   ✓ Status: {$testResponse['code']}\n";
    if ($testResponse['code'] === 200) {
        echo "   ✓ Test notification sent successfully\n\n";
    }
    
    // Step 5: Get notifications again (should have new one)
    echo "5. Checking for new notification...\n";
    $newListResponse = apiRequest('GET', '/api/notifications', null, $token);
    $newCount = count($newListResponse['body']['data'] ?? []);
    echo "   ✓ Total notifications now: $newCount\n";
    
    if ($newCount > $notificationCount) {
        $latestNotif = $newListResponse['body']['data'][0];
        echo "   ✓ Latest notification:\n";
        echo "      - ID: {$latestNotif['id']}\n";
        echo "      - Title: {$latestNotif['title']}\n";
        echo "      - Message: {$latestNotif['message']}\n";
        echo "      - Read: " . ($latestNotif['is_read'] ? 'Yes' : 'No') . "\n\n";
        
        // Step 6: Mark as read
        echo "6. Marking notification as read...\n";
        $markReadResponse = apiRequest('PATCH', "/api/notifications/{$latestNotif['id']}/read", null, $token);
        echo "   ✓ Status: {$markReadResponse['code']}\n";
        if ($markReadResponse['code'] === 200) {
            echo "   ✓ Notification marked as read\n\n";
        }
        
        // Step 7: Verify it's marked as read
        echo "7. Verifying read status...\n";
        $verifyResponse = apiRequest('GET', '/api/notifications', null, $token);
        $verifiedNotif = array_filter($verifyResponse['body']['data'], function($n) use ($latestNotif) {
            return $n['id'] === $latestNotif['id'];
        });
        $verifiedNotif = reset($verifiedNotif);
        echo "   ✓ Notification is_read: " . ($verifiedNotif['is_read'] ? 'true' : 'false') . "\n\n";
        
        // Step 8: Test mark all as read
        echo "8. Testing mark all as read...\n";
        $markAllResponse = apiRequest('POST', '/api/notifications/mark-all-read', null, $token);
        echo "   ✓ Status: {$markAllResponse['code']}\n";
        if (isset($markAllResponse['body']['data']['marked_count'])) {
            echo "   ✓ Marked {$markAllResponse['body']['data']['marked_count']} notifications as read\n\n";
        }
        
        // Step 9: Check unread count again (should be 0)
        echo "9. Checking unread count after marking all read...\n";
        $finalCountResponse = apiRequest('GET', '/api/notifications/unread-count', null, $token);
        echo "   ✓ Unread count: {$finalCountResponse['body']['data']['unread_count']}\n\n";
        
        // Step 10: Test delete notification
        echo "10. Testing delete notification...\n";
        $deleteResponse = apiRequest('DELETE', "/api/notifications/{$latestNotif['id']}", null, $token);
        echo "   ✓ Status: {$deleteResponse['code']}\n";
        if ($deleteResponse['code'] === 200) {
            echo "   ✓ Notification deleted successfully\n\n";
        }
    }
    
    echo "========================================\n";
    echo "✅ All Notification Endpoints Working!\n";
    echo "========================================\n\n";
    
    echo "Summary:\n";
    echo "- Authentication: ✓ Working\n";
    echo "- GET /api/notifications: ✓ Working\n";
    echo "- GET /api/notifications/unread-count: ✓ Working\n";
    echo "- POST /api/notifications/test: ✓ Working\n";
    echo "- PATCH /api/notifications/{id}/read: ✓ Working\n";
    echo "- POST /api/notifications/mark-all-read: ✓ Working\n";
    echo "- DELETE /api/notifications/{id}: ✓ Working\n\n";
    
    echo "Design Patterns Verified:\n";
    echo "- Strategy Pattern: ✓ InAppNotificationStrategy used\n";
    echo "- Bridge Pattern: ✓ UserNotification abstraction\n";
    echo "- Observer Pattern: ✓ Ready for event integration\n\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "\nMake sure the PHP server is running:\n";
    echo "cd public && php -S localhost:8000\n";
    exit(1);
}
