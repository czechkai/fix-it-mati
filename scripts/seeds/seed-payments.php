<?php

require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Models/Payment.php';

// Load environment variables manually
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

$db = Database::getInstance()->getConnection();

// Get a user ID for testing
$userStmt = $db->query("SELECT id FROM users WHERE role = 'user' LIMIT 1");
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo 'No user found for testing' . PHP_EOL;
    exit(1);
}

$userId = $user['id'];
echo 'Creating payments for user: ' . $userId . PHP_EOL . PHP_EOL;

// Create a payment model
$paymentModel = new Payment();

// Create October 2024 bill
echo 'Creating October 2024 bill...' . PHP_EOL;
$payment1 = $paymentModel->createPayment([
    'user_id' => $userId,
    'bill_month' => 'October 2024',
    'amount' => 1250.00,
    'status' => 'unpaid',
    'due_date' => '2024-10-25'
]);

if ($payment1) {
    echo 'Payment created: ' . $payment1['id'] . PHP_EOL;
    
    // Add water and electricity items
    $waterAdded = $paymentModel->addPaymentItems($payment1['id'], [
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
    
    echo 'Payment items added: ' . ($waterAdded ? 'YES' : 'NO') . PHP_EOL . PHP_EOL;
} else {
    echo 'Failed to create payment' . PHP_EOL;
}

// Create November 2024 bill (overdue)
echo 'Creating November 2024 bill (overdue)...' . PHP_EOL;
$payment2 = $paymentModel->createPayment([
    'user_id' => $userId,
    'bill_month' => 'November 2024',
    'amount' => 1380.00,
    'status' => 'overdue',
    'due_date' => '2024-11-20'
]);

if ($payment2) {
    echo 'Payment created: ' . $payment2['id'] . PHP_EOL;
    
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
    
    echo 'Payment items added' . PHP_EOL . PHP_EOL;
}

echo '✓ Sample payment data created successfully!' . PHP_EOL;
echo '✓ Total amount due: ₱' . number_format($paymentModel->getTotalDue($userId), 2) . PHP_EOL;
