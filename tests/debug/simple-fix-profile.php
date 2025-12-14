<?php
/**
 * SIMPLE FIX: Update profile_image in database
 * Just navigate to this page in your browser: http://localhost:8000/simple-fix-profile.php
 */

// Get database credentials from environment or use defaults
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbPort = getenv('DB_PORT') ?: '5432';
$dbName = getenv('DB_NAME') ?: 'fix_it_mati';
$dbUser = getenv('DB_USER') ?: 'postgres';
$dbPass = getenv('DB_PASSWORD') ?: 'password';

// User to fix
$userEmail = 'adie.lacson@gmail.com';
$userId = '5f9b00be-dbdc-45b5-9df4-b2341cdfdb8b';

echo "<!DOCTYPE html><html><head><title>Fix Profile Image</title>";
echo "<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;background:#f5f5f5;}";
echo ".box{background:white;padding:30px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}";
echo ".success{color:#16a34a;font-size:18px;font-weight:600;}.error{color:#dc2626;font-size:18px;font-weight:600;}";
echo ".info{color:#475569;margin:10px 0;}</style></head><body><div class='box'>";

echo "<h1>🔧 Profile Image Database Fix</h1>";

try {
    // Check if files exist first
    $uploadDir = dirname(__DIR__) . '/uploads/profiles';
    $pattern = $uploadDir . '/profile_' . $userId . '_*.*';
    $files = glob($pattern);
    
    if (empty($files)) {
        echo "<p class='error'>❌ No profile image files found for this user.</p>";
        echo "<p class='info'>Upload directory: $uploadDir</p>";
        exit;
    }
    
    // Get the latest file
    $latestFile = basename($files[count($files) - 1]);
    $fileSize = round(filesize($files[count($files) - 1]) / 1024, 2);
    
    echo "<p class='info'>📁 Found " . count($files) . " profile image(s)</p>";
    echo "<p class='info'>📄 Latest file: <strong>$latestFile</strong> ($fileSize KB)</p>";
    echo "<hr style='margin:20px 0;border:none;border-top:1px solid #e2e8f0;'>";
    
    // Connect to database
    $dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<p class='info'>✅ Connected to database</p>";
    
    // Get current value
    $stmt = $pdo->prepare('SELECT id, email, profile_image FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<p class='error'>❌ User not found in database</p>";
        exit;
    }
    
    echo "<p class='info'>👤 User: {$user['email']}</p>";
    echo "<p class='info'>📸 Current profile_image: " . ($user['profile_image'] ?: '<em>NULL</em>') . "</p>";
    
    // Update database
    $stmt = $pdo->prepare('UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ?');
    $result = $stmt->execute([$latestFile, $userId]);
    
    if ($result) {
        echo "<hr style='margin:20px 0;border:none;border-top:1px solid #e2e8f0;'>";
        echo "<p class='success'>✅ SUCCESS! Database updated!</p>";
        echo "<p class='info'>✓ profile_image set to: <strong>$latestFile</strong></p>";
        echo "<p class='info'>✓ Image URL: <a href='/api/uploads/profiles/$latestFile' target='_blank'>/api/uploads/profiles/$latestFile</a></p>";
        
        // Show the image
        echo "<hr style='margin:20px 0;'>";
        echo "<p class='info'><strong>Preview:</strong></p>";
        echo "<img src='/api/uploads/profiles/$latestFile' style='width:150px;height:150px;border-radius:50%;object-fit:cover;border:3px solid #3b82f6;'>";
        
        echo "<hr style='margin:20px 0;'>";
        echo "<p class='success'>🎉 All done! Now test these pages:</p>";
        echo "<ul style='line-height:2;'>";
        echo "<li><a href='user-dashboard.php' style='color:#3b82f6;font-weight:600;'>Dashboard</a></li>";
        echo "<li><a href='active-requests.php' style='color:#3b82f6;font-weight:600;'>Active Requests</a></li>";
        echo "<li><a href='announcements.php' style='color:#3b82f6;font-weight:600;'>Announcements</a></li>";
        echo "<li><a href='edit-profile.php' style='color:#3b82f6;font-weight:600;'>Edit Profile</a></li>";
        echo "</ul>";
        echo "<p class='info'><strong>Important:</strong> Press Ctrl+F5 to hard refresh each page!</p>";
    } else {
        echo "<p class='error'>❌ Failed to update database</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p class='info'>Make sure PostgreSQL is running.</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div></body></html>";
