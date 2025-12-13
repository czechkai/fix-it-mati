<?php
/**
 * Seed Sample Notifications
 * Creates sample notifications for all customer users
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "🔔 Creating sample notifications...\n\n";
    
    // Get all customer users
    $stmt = $db->query("SELECT id, email, first_name FROM users WHERE role = 'customer' ORDER BY created_at LIMIT 10");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "❌ No customer users found.\n";
        exit(1);
    }
    
    echo "Found " . count($users) . " customer users\n\n";
    
    // Sample notification templates
    $notifications = [
        [
            'type' => 'request_status',
            'title' => 'Service Request Updated',
            'message' => 'Your water service request has been assigned to a technician.',
            'data' => [
                'category' => 'water',
                'action_label' => 'View Request',
                'action_url' => 'active-requests.php',
                'icon_type' => 'update'
            ]
        ],
        [
            'type' => 'payment',
            'title' => 'Payment Due Soon',
            'message' => 'Your water bill of ₱1,245.50 is due in 3 days.',
            'data' => [
                'category' => 'water',
                'action_label' => 'Pay Now',
                'action_url' => 'payments.php',
                'icon_type' => 'billing'
            ]
        ],
        [
            'type' => 'announcement',
            'title' => 'Scheduled Maintenance',
            'message' => 'Water service will be temporarily interrupted tomorrow from 8 AM to 12 PM in your area.',
            'data' => [
                'category' => 'water',
                'action_label' => 'View Details',
                'action_url' => 'announcements.php',
                'icon_type' => 'urgent'
            ]
        ],
        [
            'type' => 'system',
            'title' => 'Welcome to FixItMati!',
            'message' => 'Thank you for using FixItMati. You can now submit service requests and manage your utilities online.',
            'data' => [
                'category' => 'system',
                'action_label' => 'Get Started',
                'action_url' => 'user-dashboard.php',
                'icon_type' => 'info'
            ]
        ],
        [
            'type' => 'request_status',
            'title' => 'Request Completed',
            'message' => 'Your electricity meter issue has been resolved. Please rate your experience.',
            'data' => [
                'category' => 'electricity',
                'action_label' => 'Rate Service',
                'action_url' => 'active-requests.php',
                'icon_type' => 'update'
            ]
        ],
        [
            'type' => 'payment',
            'title' => 'Payment Received',
            'message' => 'Your payment of ₱2,150.00 for electricity has been received. Thank you!',
            'data' => [
                'category' => 'electricity',
                'action_label' => 'View Receipt',
                'action_url' => 'payment-history.php',
                'icon_type' => 'billing'
            ]
        ]
    ];
    
    $createdCount = 0;
    
    foreach ($users as $user) {
        echo "Creating notifications for {$user['first_name']} ({$user['email']})...\n";
        
        // Create 3-4 notifications per user with varied read status
        $notificationCount = rand(3, 4);
        $selectedNotifications = array_rand(array_flip(array_keys($notifications)), $notificationCount);
        
        if (!is_array($selectedNotifications)) {
            $selectedNotifications = [$selectedNotifications];
        }
        
        foreach ($selectedNotifications as $index) {
            $notification = $notifications[$index];
            
            // Randomly set some as read, some as unread
            $isRead = (rand(1, 100) > 60); // 40% will be unread
            
            $sql = "INSERT INTO notifications (
                user_id, type, title, message, data, channel, status, is_read, read_at, sent_at, created_at
            ) VALUES (
                :user_id, :type, :title, :message, :data, :channel, :status, :is_read, :read_at, :sent_at, :created_at
            )";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'user_id' => $user['id'],
                'type' => $notification['type'],
                'title' => $notification['title'],
                'message' => $notification['message'],
                'data' => json_encode($notification['data']),
                'channel' => 'in_app',
                'status' => 'sent',
                'is_read' => $isRead ? 'true' : 'false',
                'read_at' => $isRead ? date('Y-m-d H:i:s', strtotime('-' . rand(1, 48) . ' hours')) : null,
                'sent_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 72) . ' hours')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 72) . ' hours'))
            ]);
            
            $createdCount++;
        }
        
        echo "  ✓ Created {$notificationCount} notifications\n";
    }
    
    echo "\n✅ Successfully created {$createdCount} sample notifications!\n\n";
    
    // Show summary
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total,
            COUNT(*) FILTER (WHERE is_read = FALSE) as unread,
            COUNT(*) FILTER (WHERE is_read = TRUE) as read
        FROM notifications
    ");
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "📊 Notification Summary:\n";
    echo "   Total: {$summary['total']}\n";
    echo "   Unread: {$summary['unread']}\n";
    echo "   Read: {$summary['read']}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
