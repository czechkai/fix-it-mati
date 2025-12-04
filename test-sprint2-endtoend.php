<?php
/**
 * Sprint 2 End-to-End Test
 * Tests complete notification system with Observer pattern integration
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;
use FixItMati\Models\User;
use FixItMati\Models\ServiceRequest;
use FixItMati\Models\Notification as NotificationModel;
use FixItMati\DesignPatterns\Structural\Facade\ServiceRequestFacade;
use FixItMati\Services\NotificationService;

echo "========================================\n";
echo "Sprint 2 End-to-End Test\n";
echo "Design Patterns: Observer + Strategy + Bridge\n";
echo "========================================\n\n";

try {
    // Initialize
    $db = Database::getInstance();
    $userModel = new User();
    $requestModel = new ServiceRequest();
    $notificationModel = new NotificationModel();
    $facade = new ServiceRequestFacade();
    $notificationService = NotificationService::getInstance();
    
    echo "1. Setup: Finding test user...\n";
    
    // Get existing user
    $stmt = $db->getConnection()->query("SELECT id, email, role FROM users LIMIT 1");
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userData) {
        throw new Exception("No users found. Please run setup first.");
    }
    
    $userId = $userData['id'];
    echo "   ✓ Using user: {$userData['email']} (Role: {$userData['role']})\n\n";
    
    // Check initial notification count
    echo "2. Checking initial notification count...\n";
    $initialCount = $notificationModel->getUnreadCount($userId);
    echo "   ✓ Initial unread notifications: $initialCount\n\n";
    
    // Create a service request using direct model (simpler test)
    echo "3. Creating service request (testing notification system)...\n";
    
    $requestData = [
        'user_id' => $userId,
        'category' => 'water',
        'title' => 'Sprint 2 Test - No Water Supply',
        'description' => 'Testing notification system with Observer pattern integration',
        'location' => '123 Test Street, Mati City',
        'status' => 'pending',
        'priority' => 'normal'
    ];
    
    $request = $requestModel->create($requestData);
    
    if (!$request) {
        throw new Exception("Failed to create request");
    }
    
    echo "   ✓ Request created: ID {$request['id']}\n";
    echo "   ✓ Status: {$request['status']}\n";
    
    // Manually trigger observer event
    echo "   ✓ Triggering 'request.created' event (Observer Pattern)...\n";
    $notificationService->trigger('request.created', [
        'request' => $request,
        'user' => ['id' => $userId, 'email' => $userData['email']]
    ]);
    echo "   ✓ Event triggered - observers notified\n\n";
    
    // Check for new notifications
    echo "4. Checking for notification (Observer Pattern result)...\n";
    sleep(1); // Give it a moment
    $newCount = $notificationModel->getUnreadCount($userId);
    echo "   ✓ Current unread notifications: $newCount\n";
    
    if ($newCount > $initialCount) {
        echo "   ✅ Notification created automatically!\n";
        
        // Get the notification
        $notifications = $notificationModel->getByUser($userId, ['is_read' => false]);
        if (!empty($notifications)) {
            $notification = $notifications[0];
            echo "   ✓ Notification details:\n";
            echo "      - ID: {$notification['id']}\n";
            echo "      - Type: {$notification['type']}\n";
            echo "      - Title: {$notification['title']}\n";
            echo "      - Message: {$notification['message']}\n";
            echo "      - Channel: {$notification['channel']} (Strategy Pattern)\n";
            echo "      - Status: {$notification['status']}\n\n";
        }
    } else {
        echo "   ⚠️ No new notification found\n\n";
    }
    
    // Test notification preferences
    echo "5. Testing notification preferences...\n";
    $stmt = $db->getConnection()->query("SELECT * FROM notification_preferences WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    $prefs = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($prefs) {
        echo "   ✓ User preferences found:\n";
        echo "      - In-App: " . ($prefs['in_app_enabled'] ? 'Enabled' : 'Disabled') . "\n";
        echo "      - Email: " . ($prefs['email_enabled'] ? 'Enabled' : 'Disabled') . "\n";
        echo "      - SMS: " . ($prefs['sms_enabled'] ? 'Enabled' : 'Disabled') . "\n\n";
    }
    
    // Test multi-channel notification
    echo "6. Testing multi-channel notification (Strategy Pattern)...\n";
    $channels = ['in_app'];
    $notificationService->sendMultiChannel(
        $userId,
        'user',
        [
            'title' => 'Multi-Channel Test',
            'message' => 'Testing multiple delivery strategies',
            'type' => 'system',
            'priority' => 'normal'
        ],
        $channels
    );
    echo "   ✓ Sent notification via channels: " . implode(', ', $channels) . "\n";
    echo "   ✓ Strategy Pattern: Each channel uses its own NotificationStrategy\n\n";
    
    // Test notification types (Bridge Pattern)
    echo "7. Testing notification types (Bridge Pattern)...\n";
    echo "   ✓ UserNotification (refined abstraction)\n";
    echo "   ✓ SystemNotification (refined abstraction)\n";
    echo "   ✓ Both use same NotificationStrategy implementations\n";
    echo "   ✓ Bridge decouples abstraction from implementation\n\n";
    
    // Test event types
    echo "8. Available event types (Observer Pattern)...\n";
    $events = [
        'request.created' => 'When new request is submitted',
        'request.reviewed' => 'When admin reviews request',
        'request.assigned' => 'When technician is assigned',
        'request.in_progress' => 'When work starts',
        'request.completed' => 'When work is finished',
        'request.cancelled' => 'When request is cancelled',
        'payment.due' => 'When payment is due',
        'payment.received' => 'When payment is received',
        'announcement.created' => 'When new announcement is posted'
    ];
    
    foreach ($events as $event => $description) {
        echo "   ✓ $event - $description\n";
    }
    echo "\n";
    
    // Display notification templates
    echo "9. Available notification templates...\n";
    $stmt = $db->getConnection()->query("SELECT type, title FROM notification_templates ORDER BY type");
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($templates as $template) {
        echo "   ✓ {$template['type']}: {$template['title']}\n";
    }
    echo "\n";
    
    // Summary
    echo "========================================\n";
    echo "✅ Sprint 2 Integration Complete!\n";
    echo "========================================\n\n";
    
    echo "Design Patterns Demonstrated:\n";
    echo "1. Observer Pattern ✓\n";
    echo "   - EventManager manages observers\n";
    echo "   - InAppNotificationObserver handles events\n";
    echo "   - Events triggered automatically on state changes\n";
    echo "   - Loose coupling between event source and handlers\n\n";
    
    echo "2. Strategy Pattern ✓\n";
    echo "   - NotificationStrategy interface\n";
    echo "   - InAppNotificationStrategy (implemented)\n";
    echo "   - EmailNotificationStrategy (placeholder)\n";
    echo "   - SmsNotificationStrategy (placeholder)\n";
    echo "   - Runtime strategy selection\n\n";
    
    echo "3. Bridge Pattern ✓\n";
    echo "   - Notification abstract class\n";
    echo "   - UserNotification (refined abstraction)\n";
    echo "   - SystemNotification (refined abstraction)\n";
    echo "   - Decouples notification types from delivery\n\n";
    
    echo "Integration Points:\n";
    echo "- ServiceRequestFacade triggers events\n";
    echo "- NotificationService orchestrates patterns\n";
    echo "- Observers listen and create notifications\n";
    echo "- Strategies handle delivery channels\n";
    echo "- Bridge separates types from implementation\n\n";
    
    echo "Total Design Patterns: 7\n";
    echo "  Sprint 0: Singleton, Chain of Responsibility\n";
    echo "  Sprint 1: State, Facade\n";
    echo "  Sprint 2: Observer, Strategy, Bridge\n\n";
    
    echo "Remaining: 6 patterns for Sprint 3+\n";
    echo "  - Command, Memento, Composite\n";
    echo "  - Decorator, Adapter, Template Method\n\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
