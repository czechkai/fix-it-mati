<?php
require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "🔍 Checking discussions tables...\n\n";
    
    // Check discussions table
    $stmt = $db->query("SELECT COUNT(*) as count FROM discussions");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Discussions table: {$count['count']} records\n";
    
    // Check discussion_comments table
    $stmt2 = $db->query("SELECT COUNT(*) as count FROM discussion_comments");
    $count2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo "✅ Discussion comments table: {$count2['count']} records\n";
    
    // Check discussion_upvotes table
    $stmt3 = $db->query("SELECT COUNT(*) as count FROM discussion_upvotes");
    $count3 = $stmt3->fetch(PDO::FETCH_ASSOC);
    echo "✅ Discussion upvotes table: {$count3['count']} records\n\n";
    
    // Show sample discussions
    $stmt4 = $db->query("SELECT d.id, d.title, d.category, d.upvotes, d.is_answered, 
                               CONCAT(u.first_name, ' ', u.last_name) as author,
                               (SELECT COUNT(*) FROM discussion_comments WHERE discussion_id = d.id) as comments_count
                        FROM discussions d 
                        LEFT JOIN users u ON d.user_id = u.id 
                        ORDER BY d.created_at DESC LIMIT 5");
    $discussions = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($discussions)) {
        echo "📋 Recent discussions:\n";
        foreach ($discussions as $disc) {
            echo sprintf(
                "   • %s [%s] - %d upvotes, %d comments, %s\n",
                $disc['title'],
                $disc['category'],
                $disc['upvotes'],
                $disc['comments_count'],
                $disc['is_answered'] ? '✓ Answered' : '○ Open'
            );
        }
    } else {
        echo "⚠️  No discussions found. Run: php seed-discussions.php\n";
    }
    
    echo "\n✅ All tables are working!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
