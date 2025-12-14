<?php
// Quick test to check user and create payments

require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Models/Payment.php';

use FixItMati\Core\Database;
use FixItMati\Models\Payment;

// Load env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Check users
    echo "=== CHECKING USERS ===" . PHP_EOL;
    $stmt = $db->query("SELECT id, email, role FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "No users found!" . PHP_EOL;
        exit(1);
    }
    
    foreach ($users as $u) {
        echo $u['email'] . " (" . $u['role'] . ") - " . $u['id'] . PHP_EOL;
    }
    
    // Use the first user
    $userId = $users[0]['id'];
    echo PHP_EOL . "Using user: " . $users[0]['email'] . " (" . $userId . ")" . PHP_EOL . PHP_EOL;
    
    // Check existing payments
    echo "=== CHECKING EXISTING PAYMENTS ===" . PHP_EOL;
    $checkStmt = $db->prepare("SELECT id, bill_month, amount, status FROM payments WHERE user_id = ? LIMIT 5");
    $checkStmt->execute([$userId]);
    $existing = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($existing)) {
        echo "Found " . count($existing) . " existing payments:" . PHP_EOL;
        foreach ($existing as $p) {
            echo "  - " . $p['bill_month'] . ": ₱" . $p['amount'] . " (" . $p['status'] . ")" . PHP_EOL;
        }
        echo PHP_EOL . "Payments already exist. Skipping creation." . PHP_EOL;
        exit(0);
    }
    
    echo "No payments found. Creating new ones..." . PHP_EOL . PHP_EOL;
    
    // Create payments
    $paymentModel = new Payment();
    
    echo "Creating October 2024 bill..." . PHP_EOL;
    $payment1 = $paymentModel->createPayment([
        'user_id' => $userId,
        'bill_month' => 'October 2024',
        'amount' => 1250.00,
        'status' => 'unpaid',
        'due_date' => '2024-10-25'
    ]);
    
    if ($payment1) {
        echo "✓ Payment created: " . $payment1['id'] . PHP_EOL;
        
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
        echo "✓ Payment items added" . PHP_EOL . PHP_EOL;
    }
    
    echo "Creating November 2024 bill..." . PHP_EOL;
    $payment2 = $paymentModel->createPayment([
        'user_id' => $userId,
        'bill_month' => 'November 2024',
        'amount' => 1380.00,
        'status' => 'overdue',
        'due_date' => '2024-11-20'
    ]);
    
    if ($payment2) {
        echo "✓ Payment created: " . $payment2['id'] . PHP_EOL;
        
        $paymentModel->addPaymentItems($payment2['id'], [
            [
                'description' => 'Mati Water District - 28 m³',
                'amount' => 520.00,
                'category' => 'water'
            ],
            [
                'description' => 'Davao Light - 145 kWh',
                'amount' => 860.00,
                'category' => 'electricity'
            ]
        ]);
        echo "✓ Payment items added" . PHP_EOL . PHP_EOL;
    }
    
    $totalDue = $paymentModel->getTotalDue($userId);
    echo "=== SUCCESS ===" . PHP_EOL;
    echo "Total amount due: ₱" . number_format($totalDue, 2) . PHP_EOL;
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
