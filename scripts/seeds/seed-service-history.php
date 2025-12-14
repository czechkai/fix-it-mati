<?php
/**
 * Seed Service History Test Data
 * Creates completed requests with ratings for testing the service history feature
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    echo "🌱 Seeding service history test data...\n\n";
    
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get a sample user
    $stmt = $pdo->query("SELECT id FROM users WHERE email LIKE '%@example.com%' LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "⚠️  No users found. Please create a user first.\n";
        exit(1);
    }
    
    $userId = $user['id'];
    echo "Using user ID: $userId\n\n";
    
    // Update some existing pending requests to completed with ratings
    $stmt = $pdo->prepare("
        UPDATE service_requests 
        SET 
            status = 'completed',
            resolved_at = NOW() - INTERVAL '7 days',
            resolved_by = 'John Smith (Technician)',
            resolution = 'Issue resolved successfully. Replaced faulty component and tested system.',
            technician_notes = 'Customer was satisfied with the work. No follow-up needed.',
            rating = :rating,
            feedback = :feedback,
            rated_at = NOW() - INTERVAL '6 days'
        WHERE id = (
            SELECT id FROM service_requests 
            WHERE status IN ('pending', 'in-progress') 
            AND user_id = :user_id
            LIMIT 1
        )
        RETURNING id, ticket_number, title
    ");
    
    // Add some ratings
    $testCases = [
        ['rating' => 5, 'feedback' => 'Excellent service! The technician was professional and fixed the issue quickly.'],
        ['rating' => 4, 'feedback' => 'Good work overall. Would have liked faster response time.'],
        ['rating' => 3, 'feedback' => 'Issue was resolved but took longer than expected.'],
        ['rating' => 5, 'feedback' => 'Very satisfied with the service. Highly recommend!'],
        ['rating' => 4, 'feedback' => 'Professional and efficient. Thank you!']
    ];
    
    $updated = 0;
    foreach ($testCases as $index => $testCase) {
        try {
            $stmt->execute([
                'user_id' => $userId,
                'rating' => $testCase['rating'],
                'feedback' => $testCase['feedback']
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $updated++;
                echo "✅ Updated: {$result['ticket_number']} - {$result['title']}\n";
                echo "   Rating: {$testCase['rating']}/5 stars\n";
                echo "   Feedback: {$testCase['feedback']}\n\n";
            }
        } catch (\PDOException $e) {
            // No more requests to update
            break;
        }
    }
    
    if ($updated == 0) {
        echo "ℹ️  No pending requests found to update.\n";
        echo "   Creating new completed requests...\n\n";
        
        // Create new completed requests
        $categories = ['Electricity', 'Water Supply', 'Waste Management'];
        $locations = ['123 Main St', '456 Oak Ave', '789 Pine Road'];
        
        for ($i = 0; $i < 3; $i++) {
            $stmt = $pdo->prepare("
                INSERT INTO service_requests (
                    user_id, category, title, description, location, 
                    status, resolved_at, resolved_by, resolution, 
                    technician_notes, rating, feedback, rated_at
                ) VALUES (
                    :user_id, :category, :title, :description, :location,
                    'completed', NOW() - INTERVAL ':days days', 
                    'Technician Team', :resolution,
                    'Work completed successfully', :rating, :feedback, NOW() - INTERVAL ':days_feedback days'
                )
                RETURNING id, ticket_number, title
            ");
            
            $testCase = $testCases[$i % count($testCases)];
            $category = $categories[$i % count($categories)];
            
            $stmt->execute([
                'user_id' => $userId,
                'category' => $category,
                'title' => "Test $category Issue",
                'description' => "This is a test completed request for service history.",
                'location' => $locations[$i % count($locations)],
                'days' => ($i + 1) * 5,
                'days_feedback' => ($i + 1) * 5 - 1,
                'resolution' => "Test resolution for $category issue. All fixed!",
                'rating' => $testCase['rating'],
                'feedback' => $testCase['feedback']
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $updated++;
                echo "✅ Created: {$result['ticket_number']} - {$result['title']}\n";
            }
        }
    }
    
    echo "\n🎉 Seeding completed!\n";
    echo "📊 Total completed requests with ratings: $updated\n";
    echo "📄 View them at: http://localhost:8000/service-history.php\n\n";
    
} catch (\PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
