<?php
/**
 * Test Create Invoice Functionality
 */

require_once __DIR__ . '/../autoload.php';

use FixItMati\Core\Database;
use FixItMati\Models\Payment;

$db = Database::getInstance()->getConnection();

// Get a test user
$userSql = "SELECT id, email, first_name, last_name FROM users WHERE role = 'customer' LIMIT 1";
$stmt = $db->query($userSql);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "No customer users found.\n";
    exit(1);
}

echo "Testing invoice creation for user: {$user['email']}\n\n";

// Create invoice using Payment model
$paymentModel = new Payment();

$invoiceData = [
    'user_id' => $user['id'],
    'bill_type' => 'water',
    'amount' => 575.50,
    'due_date' => date('Y-m-d', strtotime('+14 days')),
    'description' => 'Water consumption for December 2025',
    'status' => 'unpaid'
];

echo "Creating invoice...\n";
echo "- Bill Type: Water Bill\n";
echo "- Amount: ₱{$invoiceData['amount']}\n";
echo "- Due Date: {$invoiceData['due_date']}\n\n";

$result = $paymentModel->createInvoice($invoiceData);

if ($result) {
    echo "✅ Invoice created successfully!\n";
    echo "   Invoice ID: {$result['id']}\n";
    echo "   Amount: ₱{$result['amount']}\n";
    echo "   Status: {$result['status']}\n\n";
    
    // Check if notification was created
    $notifSql = "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1";
    $stmt = $db->prepare($notifSql);
    $stmt->execute(['user_id' => $user['id']]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($notification) {
        echo "✅ Notification created!\n";
        echo "   Title: {$notification['title']}\n";
        echo "   Message: {$notification['message']}\n";
        echo "   Type: {$notification['type']}\n";
    } else {
        echo "❌ No notification found\n";
    }
} else {
    echo "❌ Failed to create invoice\n";
}
