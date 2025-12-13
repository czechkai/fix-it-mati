<?php
/**
 * Seed Linked Meters Test Data
 * Creates sample meters for testing
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    echo "🌱 Seeding Linked Meters Test Data...\n";
    echo str_repeat("=", 50) . "\n\n";
    
    $db = Database::getInstance()->getConnection();
    
    // Get test user ID
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => 'test.customer@example.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ Test user not found. Please run seed-all-data.php first.\n";
        exit(1);
    }
    
    $userId = $user['id'];
    echo "✓ Found test user: {$userId}\n\n";
    
    // Clear existing test meters
    $stmt = $db->prepare("DELETE FROM linked_meters WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    echo "✓ Cleared existing test meters\n\n";
    
    // Sample meters
    $meters = [
        [
            'user_id' => $userId,
            'provider' => 'Mati Water District',
            'meter_type' => 'water',
            'account_number' => 'MWD-092-221-55',
            'account_holder_name' => 'Juan Dela Cruz',
            'alias' => 'My Home',
            'address' => '123 Main St. Ext, Brgy. Central, Mati City',
            'status' => 'active',
            'last_reading' => '125.50',
            'last_bill_amount' => '450.00',
            'last_bill_date' => date('Y-m-d', strtotime('-15 days'))
        ],
        [
            'user_id' => $userId,
            'provider' => 'Davao Light',
            'meter_type' => 'electricity',
            'account_number' => 'DL-8821-0021',
            'account_holder_name' => 'Juan Dela Cruz',
            'alias' => 'Rental Unit 1',
            'address' => 'Purok 4, Brgy. Dahican, Mati City',
            'status' => 'active',
            'last_reading' => '1850.25',
            'last_bill_amount' => '2100.00',
            'last_bill_date' => date('Y-m-d', strtotime('-10 days'))
        ],
        [
            'user_id' => $userId,
            'provider' => 'MORESCO',
            'meter_type' => 'electricity',
            'account_number' => 'MOR-2024-5512',
            'account_holder_name' => 'Juan Dela Cruz',
            'alias' => 'Business Office',
            'address' => 'Purok 2, Brgy. Poblacion, Mati City',
            'status' => 'active',
            'last_reading' => '3200.75',
            'last_bill_amount' => '3850.00',
            'last_bill_date' => date('Y-m-d', strtotime('-5 days'))
        ]
    ];
    
    // Insert meters
    $sql = "INSERT INTO linked_meters (
        user_id, provider, meter_type, account_number, 
        account_holder_name, alias, address, status,
        last_reading, last_bill_amount, last_bill_date
    ) VALUES (
        :user_id, :provider, :meter_type, :account_number,
        :account_holder_name, :alias, :address, :status,
        :last_reading, :last_bill_amount, :last_bill_date
    )";
    
    $stmt = $db->prepare($sql);
    
    foreach ($meters as $index => $meter) {
        $stmt->execute($meter);
        echo "✓ Created meter " . ($index + 1) . ": {$meter['alias']} ({$meter['provider']})\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✨ Linked meters seeded successfully!\n";
    echo "   Total meters: " . count($meters) . "\n";
    echo "   User: test.customer@example.com\n\n";
    
    // Display summary
    echo "📊 Meters Summary:\n";
    $stmt = $db->prepare("
        SELECT meter_type, COUNT(*) as count 
        FROM linked_meters 
        WHERE user_id = :user_id 
        GROUP BY meter_type
    ");
    $stmt->execute(['user_id' => $userId]);
    $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($summary as $row) {
        echo "   - {$row['meter_type']}: {$row['count']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
