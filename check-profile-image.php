<?php
require_once __DIR__ . '/autoload.php';

echo "=== DATABASE PROFILE IMAGE CHECK ===\n\n";

try {
    $db = new Database();
    
    // Check user's profile_image field
    $result = $db->query(
        'SELECT id, email, first_name, last_name, profile_image, updated_at FROM users WHERE email = ?',
        ['adie.lacson@gmail.com']
    );
    
    if ($result && count($result) > 0) {
        $user = $result[0];
        echo "✅ User found in database:\n";
        echo "   ID: " . $user['id'] . "\n";
        echo "   Email: " . $user['email'] . "\n";
        echo "   First Name: " . ($user['first_name'] ?? 'NULL') . "\n";
        echo "   Last Name: " . ($user['last_name'] ?? 'NULL') . "\n";
        echo "   Profile Image: " . ($user['profile_image'] ?? 'NULL') . "\n";
        echo "   Updated At: " . $user['updated_at'] . "\n\n";
        
        if (empty($user['profile_image'])) {
            echo "❌ profile_image is NULL or empty in database!\n\n";
            
            // Check if files exist in uploads folder
            $uploadDir = __DIR__ . '/../uploads/profiles';
            $userId = $user['id'];
            $files = glob($uploadDir . '/profile_' . $userId . '_*.*');
            
            if (count($files) > 0) {
                echo "⚠️  But profile images EXIST in uploads folder:\n";
                foreach ($files as $file) {
                    $filename = basename($file);
                    $filesize = filesize($file);
                    $modified = date('Y-m-d H:i:s', filemtime($file));
                    echo "   - $filename (". round($filesize/1024, 2) ." KB, modified: $modified)\n";
                }
                echo "\n";
                echo "🔧 SOLUTION: The database needs to be updated with the filename.\n";
                echo "   The most recent file is: " . basename($files[count($files)-1]) . "\n\n";
                
                // Ask if we should fix it
                echo "Do you want to update the database with the most recent image? (yes/no): ";
                $handle = fopen("php://stdin", "r");
                $line = trim(fgets($handle));
                
                if (strtolower($line) === 'yes' || strtolower($line) === 'y') {
                    $latestFile = basename($files[count($files)-1]);
                    $updated = $db->query(
                        'UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ?',
                        [$latestFile, $userId]
                    );
                    
                    if ($updated !== false) {
                        echo "\n✅ SUCCESS! Database updated with profile image: $latestFile\n";
                        echo "   Now refresh your browser and the image should appear!\n";
                    } else {
                        echo "\n❌ Failed to update database.\n";
                    }
                }
            } else {
                echo "❌ No profile images found in uploads folder either.\n";
                echo "   Upload path checked: $uploadDir\n";
            }
        } else {
            echo "✅ Profile image is set: " . $user['profile_image'] . "\n";
            
            // Verify file exists
            $imagePath = __DIR__ . '/../uploads/profiles/' . $user['profile_image'];
            if (file_exists($imagePath)) {
                $filesize = filesize($imagePath);
                echo "✅ Image file exists (" . round($filesize/1024, 2) . " KB)\n";
                echo "   Path: $imagePath\n";
            } else {
                echo "❌ Image file NOT FOUND at: $imagePath\n";
            }
        }
    } else {
        echo "❌ User not found with email: adie.lacson@gmail.com\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
}
