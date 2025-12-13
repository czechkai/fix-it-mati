<?php
/**
 * Seed sample service addresses for testing
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    echo "════════════════════════════════════════════════════\n";
    echo "   SEEDING SERVICE ADDRESSES TEST DATA\n";
    echo "════════════════════════════════════════════════════\n\n";

    $db = Database::getInstance()->getConnection();
    
    // Get a test user (first user in database)
    $stmt = $db->query("SELECT id, email FROM users LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ No users found in database. Please create a user first.\n";
        exit(1);
    }
    
    echo "👤 Using user: {$user['email']}\n\n";
    
    // Sample addresses
    $addresses = [
        [
            'label' => 'Home',
            'type' => 'Residential',
            'barangay' => 'Brgy. Central',
            'street' => '123 Main Street Extension',
            'details' => 'Blue gate near the bakery',
            'is_default' => true
        ],
        [
            'label' => 'Rental Apartment',
            'type' => 'Residential',
            'barangay' => 'Brgy. Dahican',
            'street' => 'Purok 4, Coastal Road',
            'details' => '2nd floor, door 4',
            'is_default' => false
        ],
        [
            'label' => 'Downtown Office',
            'type' => 'Commercial',
            'barangay' => 'Brgy. Matiao',
            'street' => 'Rizal Avenue',
            'details' => 'Beside City Hardware',
            'is_default' => false
        ]
    ];
    
    // Clear existing addresses for this user
    $stmt = $db->prepare("DELETE FROM service_addresses WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user['id']]);
    echo "🗑️  Cleared existing addresses\n\n";
    
    // Insert addresses
    $sql = "INSERT INTO service_addresses (
        user_id, label, type, barangay, street, details, is_default
    ) VALUES (
        :user_id, :label, :type, :barangay, :street, :details, :is_default
    ) RETURNING id, label";
    
    $stmt = $db->prepare($sql);
    
    foreach ($addresses as $address) {
        $stmt->execute([
            'user_id' => $user['id'],
            'label' => $address['label'],
            'type' => $address['type'],
            'barangay' => $address['barangay'],
            'street' => $address['street'],
            'details' => $address['details'],
            'is_default' => $address['is_default'] ? 'true' : 'false'
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Created: {$result['label']}\n";
    }
    
    echo "\n✨ Successfully seeded " . count($addresses) . " service addresses!\n\n";
    
    // Verify
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM service_addresses WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user['id']]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "📊 Total addresses for {$user['email']}: {$count}\n\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
