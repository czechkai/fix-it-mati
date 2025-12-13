<?php
/**
 * Seed Additional Discussion Test Data with Comments
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "🌱 Seeding additional discussions test data...\n\n";
    
    // Get all users
    $stmt = $db->query("SELECT id, first_name, last_name FROM users ORDER BY RANDOM() LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        die("❌ No users found. Please create users first.\n");
    }
    
    echo "👥 Found " . count($users) . " users\n\n";
    
    // Get existing discussions
    $stmt = $db->query("SELECT id FROM discussions ORDER BY created_at DESC LIMIT 5");
    $discussions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($discussions)) {
        die("❌ No discussions found. Run seed-discussions.php first.\n");
    }
    
    echo "💬 Found " . count($discussions) . " discussions to add comments to\n\n";
    
    // Sample comments
    $commentTemplates = [
        "I have the same issue in my area. Has anyone found a solution?",
        "Thank you for posting this! I was wondering the same thing.",
        "You can call the office hotline at 123-4567 for immediate assistance.",
        "This usually happens during peak hours. Try again later in the day.",
        "I reported this last week and they fixed it within 2 days.",
        "Check your email for the confirmation. It might be in spam folder.",
        "The staff at the main office can help you with this request.",
        "I had a similar problem and resolved it by submitting a service request.",
        "For urgent matters, it's better to visit the office in person.",
        "Make sure to have your account number ready when you call.",
        "This is a common question. You can find the answer on their website FAQ.",
        "I recommend using the online portal instead of calling. It's faster.",
        "The customer service team is very helpful. They responded to me within 24 hours.",
        "Double-check your meter number. Sometimes there's a typo in the system.",
        "This issue affects multiple households in our barangay."
    ];
    
    $commentsAdded = 0;
    
    // Add 3-5 comments to each discussion
    foreach ($discussions as $discussion) {
        $numComments = rand(3, 5);
        
        for ($i = 0; $i < $numComments; $i++) {
            $randomUser = $users[array_rand($users)];
            $randomComment = $commentTemplates[array_rand($commentTemplates)];
            
            // Random chance of being marked as solution
            $isSolution = ($i === 0 && rand(0, 2) === 0); // 33% chance first comment is solution
            
            $sql = "INSERT INTO discussion_comments (discussion_id, user_id, content, is_solution, created_at) 
                    VALUES (:discussion_id, :user_id, :content, :is_solution, NOW() - INTERVAL '" . rand(1, 72) . " hours')";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'discussion_id' => $discussion['id'],
                'user_id' => $randomUser['id'],
                'content' => $randomComment,
                'is_solution' => $isSolution ? 'TRUE' : 'FALSE'
            ]);
            
            $commentsAdded++;
            
            // If marked as solution, update the discussion
            if ($isSolution) {
                $updateSql = "UPDATE discussions 
                             SET is_answered = TRUE, 
                                 answered_by = :answered_by 
                             WHERE id = :id";
                $updateStmt = $db->prepare($updateSql);
                $updateStmt->execute([
                    'id' => $discussion['id'],
                    'answered_by' => $randomUser['first_name'] . ' ' . $randomUser['last_name']
                ]);
            }
        }
    }
    
    // Add some random upvotes
    echo "👍 Adding random upvotes...\n";
    $upvotesAdded = 0;
    
    foreach ($discussions as $discussion) {
        $numUpvotes = rand(2, 8);
        
        for ($i = 0; $i < $numUpvotes; $i++) {
            $randomUser = $users[array_rand($users)];
            
            try {
                // Insert upvote
                $sql = "INSERT INTO discussion_upvotes (discussion_id, user_id) 
                       VALUES (:discussion_id, :user_id)
                       ON CONFLICT (discussion_id, user_id) DO NOTHING";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'discussion_id' => $discussion['id'],
                    'user_id' => $randomUser['id']
                ]);
                
                if ($stmt->rowCount() > 0) {
                    // Update upvote count
                    $updateSql = "UPDATE discussions SET upvotes = upvotes + 1 WHERE id = :id";
                    $updateStmt = $db->prepare($updateSql);
                    $updateStmt->execute(['id' => $discussion['id']]);
                    
                    $upvotesAdded++;
                }
            } catch (\PDOException $e) {
                // Skip duplicate upvotes
                if ($e->getCode() !== '23505') {
                    throw $e;
                }
            }
        }
    }
    
    echo "\n✅ Seeding complete!\n";
    echo "📊 Summary:\n";
    echo "   - Comments added: $commentsAdded\n";
    echo "   - Upvotes added: $upvotesAdded\n";
    echo "\n🌐 Visit: http://localhost:8000/discussions.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
