<?php
/**
 * Fix profile image - Update database with existing uploaded file
 */

echo "=== PROFILE IMAGE DATABASE FIX ===\n\n";

// Database connection settings
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'fix_it_mati';
$user = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: 'password';

try {
    // Connect to database
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Connected to database\n\n";
    
    // Get user
    $stmt = $pdo->prepare('SELECT id, email, first_name, last_name, profile_image, updated_at FROM users WHERE email = ?');
    $stmt->execute(['adie.lacson@gmail.com']);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "❌ User not found\n";
        exit(1);
    }
    
    echo "User Info:\n";
    echo "  ID: {$user['id']}\n";
    echo "  Email: {$user['email']}\n";
    echo "  Current profile_image: " . ($user['profile_image'] ?: 'NULL') . "\n\n";
    
    // Check for uploaded files
    $uploadDir = __DIR__ . '/uploads/profiles';
    $pattern = $uploadDir . '/profile_' . $user['id'] . '_*.*';
    $files = glob($pattern);
    
    if (empty($files)) {
        echo "❌ No profile images found in uploads folder\n";
        echo "   Pattern checked: $pattern\n";
        exit(1);
    }
    
    echo "✅ Found " . count($files) . " profile image(s):\n";
    foreach ($files as $file) {
        $filename = basename($file);
        $size = round(filesize($file) / 1024, 2);
        $modified = date('Y-m-d H:i:s', filemtime($file));
        echo "   - $filename ($size KB, modified: $modified)\n";
    }
    
    // Use the most recent file
    $latestFile = basename($files[count($files) - 1]);
    echo "\n";
    echo "📝 Will update database with: $latestFile\n";
    
    // Update database
    $stmt = $pdo->prepare('UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ?');
    $result = $stmt->execute([$latestFile, $user['id']]);
    
    if ($result) {
        echo "\n✅ SUCCESS! Database updated!\n";
        echo "   profile_image set to: $latestFile\n\n";
        echo "🎉 Now refresh your browser pages:\n";
        echo "   - http://localhost:8000/public/debug-login.php\n";
        echo "   - http://localhost:8000/public/active-requests.php\n";
        echo "   - http://localhost:8000/public/announcements.php\n\n";
        echo "   Your profile image should now appear everywhere!\n";
    } else {
        echo "\n❌ Failed to update database\n";
        exit(1);
    }
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
