<?php
/**
 * Seed Discussions Test Data
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    echo "🌱 Seeding discussions test data...\n\n";
    
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Get a sample user
    $stmt = $pdo->query("SELECT id, first_name, last_name FROM users LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "⚠️  No users found. Please create a user first.\n";
        exit(1);
    }
    
    $userId = $user['id'];
    $userName = $user['first_name'] . ' ' . $user['last_name'];
    echo "Using user: $userName ($userId)\n\n";
    
    // Sample discussions
    $discussions = [
        [
            'category' => 'Water Supply',
            'title' => 'Requirements for New Water Connection Application?',
            'content' => 'I just moved to Brgy. Dahican. What documents do I need to bring to the office for a new meter? Also, how long does the processing usually take?',
            'upvotes' => 24,
            'is_answered' => true,
            'answered_by' => 'Mati Water District'
        ],
        [
            'category' => 'Electricity',
            'title' => 'Low voltage in Purok 4 every evening',
            'content' => 'Has anyone else noticed the lights dimming around 6PM? It\'s been happening for a week now. My appliances are affected and I\'m worried about damage.',
            'upvotes' => 12,
            'is_answered' => false
        ],
        [
            'category' => 'General',
            'title' => 'Garbage collection schedule for holidays',
            'content' => 'Will the truck still pass by this coming Monday holiday? I have a lot of trash accumulated and need to know if I should put it out.',
            'upvotes' => 8,
            'is_answered' => true,
            'answered_by' => 'City Admin'
        ],
        [
            'category' => 'Billing',
            'title' => 'How to check my water bill online?',
            'content' => 'I heard we can now check bills online. Where can I find this feature? Do I need to register first?',
            'upvotes' => 15,
            'is_answered' => true,
            'answered_by' => 'Support Team'
        ],
        [
            'category' => 'Water Supply',
            'title' => 'Water interruption schedule this weekend?',
            'content' => 'I saw a notice about maintenance work. Can someone confirm the exact schedule so I can prepare?',
            'upvotes' => 5,
            'is_answered' => false
        ]
    ];
    
    $created = 0;
    foreach ($discussions as $disc) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO discussions (
                    user_id, category, title, content, upvotes, is_answered, answered_by
                ) VALUES (
                    :user_id, :category, :title, :content, :upvotes, :is_answered, :answered_by
                )
                RETURNING id, title
            ");
            
            $stmt->execute([
                'user_id' => $userId,
                'category' => $disc['category'],
                'title' => $disc['title'],
                'content' => $disc['content'],
                'upvotes' => $disc['upvotes'],
                'is_answered' => $disc['is_answered'] ? 'true' : 'false',
                'answered_by' => $disc['answered_by'] ?? null
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $created++;
                echo "✅ Created: {$result['title']}\n";
                echo "   Category: {$disc['category']} | Upvotes: {$disc['upvotes']}\n\n";
            }
        } catch (\PDOException $e) {
            echo "⚠️  Failed to create discussion: " . $e->getMessage() . "\n";
        }
    }
    
    echo "🎉 Seeding completed!\n";
    echo "📊 Total discussions created: $created\n";
    echo "📄 View them at: http://localhost:8000/discussions.php\n\n";
    
} catch (\PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
