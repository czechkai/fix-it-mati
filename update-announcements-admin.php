<?php

require_once 'config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get admin user
    $adminStmt = $db->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $adminStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "❌ No admin user found!\n";
        exit(1);
    }
    
    echo "✓ Found admin user: {$admin['id']}\n";
    
    // Update all announcements to be created by admin
    $stmt = $db->prepare('UPDATE announcements SET created_by = :admin_id');
    $stmt->execute(['admin_id' => $admin['id']]);
    
    echo "✓ Updated " . $stmt->rowCount() . " announcements to admin user\n";
    
    // Verify
    $verifyStmt = $db->query("
        SELECT a.id, a.title, u.email, u.role 
        FROM announcements a 
        LEFT JOIN users u ON a.created_by = u.id 
        WHERE a.status = 'published'
    ");
    
    echo "\n📋 Current announcements:\n";
    foreach ($verifyStmt->fetchAll(PDO::FETCH_ASSOC) as $announcement) {
        echo "  - {$announcement['title']} (by {$announcement['email']} - {$announcement['role']})\n";
    }
    
    echo "\n✅ All announcements now created by admin!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
