<?php
/**
 * Database Connection Tester
 * Use this to verify your database credentials before running the full setup
 */

echo "====================================\n";
echo "  Database Connection Tester\n";
echo "====================================\n\n";

// Check if config file exists
if (!file_exists(__DIR__ . '/config/database.php')) {
    echo "❌ ERROR: config/database.php not found!\n\n";
    echo "Please create it first:\n";
    echo "  1. copy config\\database.template.php config\\database.php\n";
    echo "  2. Edit config/database.php with your credentials\n";
    echo "  3. Run this script again\n\n";
    exit(1);
}

// Load config
require_once __DIR__ . '/config/database.php';

// Check if constants are defined
if (!defined('DB_HOST') || !defined('DB_PORT') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASSWORD')) {
    echo "❌ ERROR: Database constants not defined!\n\n";
    echo "Please check your config/database.php file.\n";
    echo "Make sure these are defined:\n";
    echo "  - DB_HOST\n";
    echo "  - DB_PORT\n";
    echo "  - DB_NAME\n";
    echo "  - DB_USER\n";
    echo "  - DB_PASSWORD\n\n";
    exit(1);
}

echo "Configuration loaded:\n";
echo "  Host: " . DB_HOST . "\n";
echo "  Port: " . DB_PORT . "\n";
echo "  Database: " . DB_NAME . "\n";
echo "  User: " . DB_USER . "\n";
echo "  Password: " . str_repeat('*', min(strlen(DB_PASSWORD), 8)) . "\n\n";

// Check if PDO PostgreSQL is available
echo "Checking PostgreSQL PDO driver...\n";
if (!extension_loaded('pdo_pgsql')) {
    echo "❌ ERROR: PostgreSQL PDO driver not installed!\n\n";
    echo "To fix this:\n";
    echo "  Windows:\n";
    echo "    1. Find php.ini: php --ini\n";
    echo "    2. Open php.ini and uncomment: extension=pdo_pgsql\n";
    echo "    3. Restart terminal\n\n";
    echo "  Linux:\n";
    echo "    sudo apt-get install php-pgsql\n\n";
    echo "  Mac:\n";
    echo "    brew install php-pgsql\n\n";
    exit(1);
}
echo "✓ PostgreSQL PDO driver is installed\n\n";

// Test DNS resolution
echo "Testing DNS resolution for " . DB_HOST . "...\n";
$ip = gethostbyname(DB_HOST);
if ($ip === DB_HOST) {
    echo "❌ ERROR: Cannot resolve hostname!\n\n";
    echo "This means:\n";
    echo "  - The database host URL is incorrect\n";
    echo "  - Your internet connection is down\n";
    echo "  - The Supabase project doesn't exist\n\n";
    echo "Please check:\n";
    echo "  1. Is the host URL correct? (should start with 'db.')\n";
    echo "  2. Get credentials from Supabase dashboard\n";
    echo "  3. See SUPABASE_SETUP.md for detailed instructions\n\n";
    exit(1);
}
echo "✓ Hostname resolves to: $ip\n\n";

// Attempt connection
echo "Attempting database connection...\n";

try {
    // Try with SSL first (Supabase requires SSL)
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require";
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database connection successful!\n\n";
    
    // Test a simple query
    $result = $pdo->query("SELECT version()");
    $version = $result->fetchColumn();
    echo "PostgreSQL Version: " . substr($version, 0, 50) . "...\n\n";
    
    echo "====================================\n";
    echo "✅ ALL CHECKS PASSED!\n";
    echo "====================================\n\n";
    echo "Your database is configured correctly.\n";
    echo "You can now run: quick-setup.bat\n\n";
    
    exit(0);
    
} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
    
    echo "❌ Connection FAILED!\n\n";
    echo "Error: $errorMsg\n\n";
    
    // Provide specific help
    if (strpos($errorMsg, 'password authentication failed') !== false) {
        echo "CAUSE: Invalid username or password\n";
        echo "FIX: Check DB_USER and DB_PASSWORD in config/database.php\n\n";
    } elseif (strpos($errorMsg, 'database') !== false && strpos($errorMsg, 'does not exist') !== false) {
        echo "CAUSE: Database doesn't exist\n";
        echo "FIX: Check DB_NAME in config/database.php (usually 'postgres' for Supabase)\n\n";
    } elseif (strpos($errorMsg, 'could not connect to server') !== false) {
        echo "CAUSE: Cannot reach database server\n";
        echo "FIX:\n";
        echo "  - Check if DB_HOST and DB_PORT are correct\n";
        echo "  - Check your internet connection\n";
        echo "  - Check if Supabase project is active (not paused)\n\n";
    } elseif (strpos($errorMsg, 'SSL') !== false || strpos($errorMsg, 'sslmode') !== false) {
        echo "CAUSE: SSL connection issue\n";
        echo "FIX: Supabase requires SSL. Check firewall/network settings\n\n";
    } else {
        echo "FIX: See SUPABASE_SETUP.md for detailed troubleshooting\n\n";
    }
    
    exit(1);
}
