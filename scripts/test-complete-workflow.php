<?php
require_once __DIR__ . '/../autoload.php';

use FixItMati\Models\User;
use FixItMati\Models\Payment;

echo "=== Testing Complete Invoice Creation Workflow ===\n\n";

// Step 1: Get all users
echo "Step 1: Getting all users...\n";
$userModel = new User();
$users = $userModel->getAllCitizens();
echo "Found " . count($users) . " users\n";

if (empty($users)) {
    die("No users found!\n");
}

// Pick first user with a name
$selectedUser = null;
foreach ($users as $user) {
    if (!empty($user['full_name']) && trim($user['full_name']) !== '') {
        $selectedUser = $user;
        break;
    }
}

if (!$selectedUser) {
    $selectedUser = $users[0];
}

echo "Selected user: {$selectedUser['full_name']} ({$selectedUser['email']})\n\n";

// Step 2: Create invoice
echo "Step 2: Creating invoice...\n";
$paymentModel = new Payment();

$invoiceData = [
    'user_id' => $selectedUser['id'],
    'bill_type' => 'water',
    'bill_month' => date('Y-m'),
    'description' => 'Monthly Water Bill - Test Invoice',
    'amount' => 1234.56,
    'due_date' => date('Y-m-d', strtotime('+15 days'))
];

echo "Invoice data:\n";
print_r($invoiceData);
echo "\n";

try {
    $invoice = $paymentModel->createInvoice($invoiceData);
    
    if ($invoice) {
        echo "✓ Invoice created successfully!\n";
        echo "  Payment ID: {$invoice['id']}\n";
        echo "  Amount: ₱" . number_format($invoice['amount'], 2) . "\n";
        echo "  Status: {$invoice['status']}\n";
        echo "  Due Date: {$invoice['due_date']}\n\n";
        
        // Step 3: Verify notification was created
        echo "Step 3: Verifying notification...\n";
        $db = \FixItMati\Core\Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("
            SELECT id, type, title, message, status, channel, created_at
            FROM notifications
            WHERE user_id = :user_id
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $selectedUser['id']]);
        $notification = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($notification) {
            echo "✓ Notification created!\n";
            echo "  Type: {$notification['type']}\n";
            echo "  Title: {$notification['title']}\n";
            echo "  Message: {$notification['message']}\n";
            echo "  Channel: {$notification['channel']}\n";
            echo "  Status: {$notification['status']}\n\n";
        } else {
            echo "✗ No notification found\n\n";
        }
        
        echo "=== TEST PASSED ===\n";
        echo "Invoice created and user notified successfully!\n";
        
    } else {
        echo "✗ Failed to create invoice\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
