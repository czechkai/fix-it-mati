<?php
/**
 * Seed Sample Billing Data for Testing
 * Run: php scripts/seed-billing-data.php
 */

require_once __DIR__ . '/../autoload.php';

use FixItMati\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Seeding billing data...\n\n";
    
    // Get a sample user (customer role)
    $userSql = "SELECT id, email, 
                CASE 
                    WHEN first_name IS NOT NULL AND last_name IS NOT NULL THEN CONCAT(first_name, ' ', last_name)
                    ELSE email
                END as full_name 
                FROM users WHERE role = 'customer' LIMIT 1";
    $stmt = $db->query($userSql);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "No customer users found. Creating sample user...\n";
        
        // Create a sample user
        $createUserSql = "INSERT INTO users (
            email, 
            first_name, 
            last_name, 
            phone, 
            address, 
            account_number, 
            role, 
            password_hash,
            created_at,
            updated_at
        ) VALUES (
            'juan.delacruz@example.com',
            'Juan',
            'Dela Cruz',
            '09171234567',
            'Barangay Centro, Mati City',
            'ACC-' || LPAD(FLOOR(RANDOM() * 999999 + 1)::TEXT, 6, '0'),
            'customer',
            :password_hash,
            NOW(),
            NOW()
        ) RETURNING id, email, first_name, last_name";
        
        $stmt = $db->prepare($createUserSql);
        $stmt->execute(['password_hash' => password_hash('password123', PASSWORD_BCRYPT)]);
        $userResult = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $user = [
            'id' => $userResult['id'],
            'email' => $userResult['email'],
            'full_name' => $userResult['first_name'] . ' ' . $userResult['last_name']
        ];
        
        echo "Created user: {$user['full_name']} ({$user['email']})\n";
    } else {
        echo "Using existing user: {$user['full_name']} ({$user['email']})\n";
    }
    
    // Create sample payments
    $billTypes = [
        ['type' => 'water', 'desc' => 'Water Bill', 'amount' => 450.00],
        ['type' => 'electricity', 'desc' => 'Electric Bill', 'amount' => 2100.00],
        ['type' => 'garbage', 'desc' => 'Garbage Collection Fee', 'amount' => 150.00]
    ];
    
    echo "\nCreating sample payments...\n";
    
    foreach ($billTypes as $bill) {
        // Create payment
        $paymentSql = "INSERT INTO payments (
            user_id,
            bill_month,
            amount,
            status,
            due_date,
            created_at,
            updated_at
        ) VALUES (
            :user_id,
            :bill_month,
            :amount,
            :status,
            :due_date,
            NOW(),
            NOW()
        ) RETURNING id";
        
        $stmt = $db->prepare($paymentSql);
        $stmt->execute([
            'user_id' => $user['id'],
            'bill_month' => date('F Y'),
            'amount' => $bill['amount'],
            'status' => 'unpaid',
            'due_date' => date('Y-m-d', strtotime('+7 days'))
        ]);
        
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Add payment item
        $itemSql = "INSERT INTO payment_items (
            payment_id,
            description,
            amount,
            category
        ) VALUES (
            :payment_id,
            :description,
            :amount,
            :category
        )";
        
        $stmt = $db->prepare($itemSql);
        $stmt->execute([
            'payment_id' => $payment['id'],
            'description' => $bill['desc'] . ' - ' . date('F Y'),
            'amount' => $bill['amount'],
            'category' => $bill['type']
        ]);
        
        echo "  ✓ Created {$bill['desc']}: ₱{$bill['amount']}\n";
    }
    
    // Create sample transactions with different statuses
    $transactionStatuses = [
        ['status' => 'completed', 'amount' => 450.00, 'method' => 'GCash'],
        ['status' => 'pending', 'amount' => 2100.00, 'method' => 'Bank Transfer'],
        ['status' => 'completed', 'amount' => 850.00, 'method' => 'Cash'],
        ['status' => 'failed', 'amount' => 320.00, 'method' => 'Maya'],
    ];
    
    echo "\nCreating sample transactions...\n";
    
    foreach ($transactionStatuses as $txn) {
        $transactionSql = "INSERT INTO transactions (
            user_id,
            amount,
            type,
            status,
            reference_number,
            notes,
            created_at
        ) VALUES (
            :user_id,
            :amount,
            'payment',
            :status,
            :reference_number,
            :notes,
            NOW() - INTERVAL '1 day' * :days_ago
        )";
        
        $stmt = $db->prepare($transactionSql);
        $stmt->execute([
            'user_id' => $user['id'],
            'amount' => $txn['amount'],
            'status' => $txn['status'],
            'reference_number' => 'TRX-' . strtoupper(substr(uniqid(), -8)),
            'notes' => "Payment via {$txn['method']}",
            'days_ago' => rand(0, 5)
        ]);
        
        echo "  ✓ Created transaction: {$txn['status']} - ₱{$txn['amount']} ({$txn['method']})\n";
    }
    
    // Get some more users and create additional transactions
    $moreUsersSql = "SELECT id FROM users WHERE role = 'customer' AND id != :user_id LIMIT 3";
    $stmt = $db->prepare($moreUsersSql);
    $stmt->execute(['user_id' => $user['id']]);
    $moreUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($moreUsers) > 0) {
        echo "\nCreating transactions for other users...\n";
        
        foreach ($moreUsers as $otherUser) {
            $amount = rand(300, 2500);
            $statuses = ['completed', 'pending', 'failed'];
            $status = $statuses[array_rand($statuses)];
            
            $transactionSql = "INSERT INTO transactions (
                user_id,
                amount,
                type,
                status,
                reference_number,
                created_at
            ) VALUES (
                :user_id,
                :amount,
                'payment',
                :status,
                :reference_number,
                NOW() - INTERVAL '1 hour' * :hours_ago
            )";
            
            $stmt = $db->prepare($transactionSql);
            $stmt->execute([
                'user_id' => $otherUser['id'],
                'amount' => $amount,
                'status' => $status,
                'reference_number' => 'TRX-' . strtoupper(substr(uniqid(), -8)),
                'hours_ago' => rand(1, 72)
            ]);
            
            echo "  ✓ Created transaction: {$status} - ₱{$amount}\n";
        }
    }
    
    echo "\n✅ Billing data seeded successfully!\n";
    echo "\nYou can now:\n";
    echo "1. Visit http://localhost:8000/admin/billing.php\n";
    echo "2. View and manage transactions\n";
    echo "3. Approve/reject pending payments\n";
    echo "4. Create new invoices\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
