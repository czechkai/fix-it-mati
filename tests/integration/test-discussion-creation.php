<?php

require_once 'config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check discussions table schema
    $stmt = $db->query("
        SELECT column_name, data_type, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'discussions' 
        ORDER BY ordinal_position
    ");
    
    echo "📋 Discussions Table Schema:\n";
    echo str_repeat('-', 80) . "\n";
    
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo sprintf(
            "%-20s %-15s Nullable: %-5s Default: %s\n",
            $col['column_name'],
            $col['data_type'],
            $col['is_nullable'],
            $col['column_default'] ?? 'none'
        );
    }
    
    echo "\n";
    
    // Try creating a test discussion
    echo "🧪 Testing discussion creation...\n";
    
    // Get a test user
    $userStmt = $db->query("SELECT id FROM users WHERE role = 'customer' LIMIT 1");
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ No customer user found!\n";
        exit(1);
    }
    
    echo "✓ Found user: {$user['id']}\n";
    
    // Try to insert
    $testSql = "INSERT INTO discussions (user_id, category, title, content)
                VALUES (:user_id, :category, :title, :content)
                RETURNING *";
    
    try {
        $testStmt = $db->prepare($testSql);
        $result = $testStmt->execute([
            'user_id' => $user['id'],
            'category' => 'water',
            'title' => 'Test Discussion',
            'content' => 'Test content'
        ]);
        
        $discussion = $testStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($discussion) {
            echo "✓ Successfully created discussion: {$discussion['id']}\n";
            
            // Clean up
            $db->prepare("DELETE FROM discussions WHERE id = :id")->execute(['id' => $discussion['id']]);
            echo "✓ Test discussion deleted\n";
        } else {
            echo "❌ Failed to create discussion (no error but no result)\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error creating test discussion: " . $e->getMessage() . "\n";
        echo "Error Code: " . $e->getCode() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
