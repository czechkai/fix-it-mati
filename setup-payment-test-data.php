<?php
/**
 * Setup Payment Test Data
 * Creates a test user and payment data if needed
 */

require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Models/Payment.php';
require_once __DIR__ . '/Models/User.php';

// Load environment variables
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

use FixItMati\Core\Database;
use FixItMati\Models\Payment;
use FixItMati\Models\User;

try {
    $db = Database::getInstance()->getConnection();
    
    // Check for existing users
    $userStmt = $db->query("SELECT id, email FROM users WHERE role = 'user' LIMIT 1");
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ No users found in database.\n";
        echo "Please register a user first via: http://localhost:8000/register.php\n";
        echo "Then run this script again.\n";
        exit(1);
    }
    
    $userId = $user['id'];
    $userEmail = $user['email'];
    
    echo "✓ Found user: {$userEmail} (ID: {$userId})\n\n";
    
    // Check for existing payments
    $paymentModel = new Payment();
    $existingBills = $paymentModel->getCurrentBills($userId);
    
    if (count($existingBills) > 0) {
        echo "ℹ User already has " . count($existingBills) . " unpaid bill(s):\n";
        foreach ($existingBills as $bill) {
            echo "  - {$bill['bill_month']}: ₱" . number_format($bill['amount'], 2) . " ({$bill['status']})\n";
        }
        echo "\n";
        $totalDue = $paymentModel->getTotalDue($userId);
        echo "✓ Total Amount Due: ₱" . number_format($totalDue, 2) . "\n";
        echo "\n✓ Payment data already exists. No action needed.\n";
        exit(0);
    }
    
    // Create sample payments
    echo "Creating sample payment data...\n\n";
    
    // December 2024 bill (current)
    echo "1. Creating December 2024 bill...\n";
    $payment1 = $paymentModel->createPayment([
        'user_id' => $userId,
        'bill_month' => 'December 2024',
        'amount' => 1250.00,
        'status' => 'unpaid',
        'due_date' => '2024-12-25'
    ]);
    
    if ($payment1) {
        echo "   ✓ Payment created: {$payment1['id']}\n";
        
        $paymentModel->addPaymentItems($payment1['id'], [
            [
                'description' => 'Mati Water District - 24 m³',
                'amount' => 450.00,
                'category' => 'water'
            ],
            [
                'description' => 'Davao Light - 128 kWh',
                'amount' => 800.00,
                'category' => 'electricity'
            ]
        ]);
        
        echo "   ✓ Payment items added\n\n";
    } else {
        echo "   ❌ Failed to create payment\n\n";
    }
    
    // November 2024 bill (overdue)
    echo "2. Creating November 2024 bill (overdue)...\n";
    $payment2 = $paymentModel->createPayment([
        'user_id' => $userId,
        'bill_month' => 'November 2024',
        'amount' => 980.00,
        'status' => 'overdue',
        'due_date' => '2024-11-20'
    ]);
    
    if ($payment2) {
        echo "   ✓ Payment created: {$payment2['id']}\n";
        
        $paymentModel->addPaymentItems($payment2['id'], [
            [
                'description' => 'Mati Water District - 20 m³',
                'amount' => 380.00,
                'category' => 'water'
            ],
            [
                'description' => 'Davao Light - 105 kWh',
                'amount' => 600.00,
                'category' => 'electricity'
            ]
        ]);
        
        echo "   ✓ Payment items added\n\n";
    }
    
    // Get final total
    $totalDue = $paymentModel->getTotalDue($userId);
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✓ Sample payment data created successfully!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "Summary:\n";
    echo "  User: {$userEmail}\n";
    echo "  Total Amount Due: ₱" . number_format($totalDue, 2) . "\n";
    echo "  Number of Bills: 2\n\n";
    echo "You can now test the payments page:\n";
    echo "  → http://localhost:8000/payments.php\n";
    echo "  → http://localhost:8000/user-dashboard.php\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
