<?php
/**
 * Update profile_image paths in the database
 * This script converts old paths (uploads/profiles/filename.jpg) to just filename (filename.jpg)
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Updating profile image paths in database...\n\n";
    
    // Get all users with profile images
    $stmt = $db->prepare("SELECT id, profile_image FROM users WHERE profile_image IS NOT NULL AND profile_image != ''");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updated = 0;
    $skipped = 0;
    
    foreach ($users as $user) {
        $oldPath = $user['profile_image'];
        
        // Extract just the filename if it contains a path
        if (strpos($oldPath, '/') !== false || strpos($oldPath, '\\') !== false) {
            // Get the filename from the path
            $filename = basename($oldPath);
            
            // Update the database
            $updateStmt = $db->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $updateStmt->execute([$filename, $user['id']]);
            
            echo "✓ User ID {$user['id']}: '{$oldPath}' → '{$filename}'\n";
            $updated++;
        } else {
            echo "- User ID {$user['id']}: '{$oldPath}' (already just filename, skipped)\n";
            $skipped++;
        }
    }
    
    echo "\n";
    echo "========================================\n";
    echo "Summary:\n";
    echo "  Total users with profile images: " . count($users) . "\n";
    echo "  Updated: {$updated}\n";
    echo "  Skipped: {$skipped}\n";
    echo "========================================\n";
    echo "\n✓ Database update complete!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
