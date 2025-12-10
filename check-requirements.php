<?php
/**
 * Environment Requirements Checker
 * Run this before starting the project to ensure all requirements are met
 */

$errors = [];
$warnings = [];
$success = [];

echo "====================================\n";
echo "   FixItMati Setup Requirements     \n";
echo "====================================\n\n";

// Check PHP Version
echo "Checking PHP Version...\n";
$phpVersion = PHP_VERSION;
if (version_compare($phpVersion, '7.4.0', '>=')) {
    $success[] = "✓ PHP Version: $phpVersion";
} else {
    $errors[] = "✗ PHP Version too old: $phpVersion (requires 7.4.0+)";
}

// Check required extensions
echo "Checking PHP Extensions...\n";
$requiredExtensions = [
    'pdo' => ['desc' => 'PDO (Database abstraction)', 'required' => true],
    'pdo_pgsql' => ['desc' => 'PostgreSQL PDO Driver', 'required' => true],
    'json' => ['desc' => 'JSON support', 'required' => true]
];

$optionalExtensions = [
    'mbstring' => 'Multibyte string support (recommended)',
    'openssl' => 'OpenSSL for encryption (recommended)'
];

foreach ($requiredExtensions as $ext => $info) {
    if (extension_loaded($ext)) {
        $success[] = "✓ Extension '$ext': Installed ({$info['desc']})";
    } else {
        $errors[] = "✗ Extension '$ext': MISSING ({$info['desc']})";
    }
}

foreach ($optionalExtensions as $ext => $description) {
    if (extension_loaded($ext)) {
        $success[] = "✓ Extension '$ext': Installed ($description)";
    } else {
        $warnings[] = "⚠ Extension '$ext': Not installed ($description)";
    }
}

// Check database config
echo "\nChecking Database Configuration...\n";
if (file_exists(__DIR__ . '/config/database.php')) {
    $success[] = "✓ Database config file exists";
    
    // Check if using define() constants or class-based config
    $configContent = file_get_contents(__DIR__ . '/config/database.php');
    
    if (strpos($configContent, "define('DB_HOST'") !== false) {
        // Using define() constants
        require_once __DIR__ . '/config/database.php';
        
        if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
            $success[] = "✓ Database constants defined";
            
            // Try to connect
            if (extension_loaded('pdo_pgsql')) {
                try {
                    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require";
                    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
                    $success[] = "✓ Database connection successful";
                } catch (PDOException $e) {
                    $errors[] = "✗ Database connection failed: " . $e->getMessage();
                }
            }
        } else {
            $warnings[] = "⚠ Database constants not properly defined";
        }
    } else {
        // Using class-based config (with .env)
        $success[] = "✓ Database config uses class-based configuration";
        $warnings[] = "⚠ Make sure you have a .env file with database credentials";
    }
} else {
    $errors[] = "✗ Database config file missing (config/database.php)";
    $warnings[] = "⚠ Run 'copy config\\database.template.php config\\database.php'";
}

// Check writable directories
echo "\nChecking Directory Permissions...\n";
$writableDirs = ['logs', 'database/migrations'];
foreach ($writableDirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (file_exists($path)) {
        if (is_writable($path)) {
            $success[] = "✓ Directory '$dir' is writable";
        } else {
            $warnings[] = "⚠ Directory '$dir' is not writable";
        }
    } else {
        $warnings[] = "⚠ Directory '$dir' does not exist";
    }
}

// Print results
echo "\n====================================\n";
echo "           RESULTS                  \n";
echo "====================================\n\n";

if (!empty($success)) {
    echo "SUCCESS:\n";
    foreach ($success as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "WARNINGS:\n";
    foreach ($warnings as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "ERRORS (MUST FIX):\n";
    foreach ($errors as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

// Final verdict
if (empty($errors)) {
    echo "====================================\n";
    echo "✓ ALL REQUIREMENTS MET!\n";
    echo "====================================\n";
    echo "\nYou can start the server with:\n";
    echo "  php -S localhost:8000\n\n";
    exit(0);
} else {
    echo "====================================\n";
    echo "✗ SETUP INCOMPLETE\n";
    echo "====================================\n";
    echo "\nPlease fix the errors above before running the project.\n\n";
    
    // Provide specific help for common issues
    if (!extension_loaded('pdo_pgsql')) {
        echo "HOW TO FIX: Install PostgreSQL PDO Driver\n";
        echo "----------------------------------------\n";
        echo "Windows:\n";
        echo "  1. Locate your php.ini file: php --ini\n";
        echo "  2. Open php.ini and find the line: ;extension=pdo_pgsql\n";
        echo "  3. Remove the semicolon to enable it: extension=pdo_pgsql\n";
        echo "  4. Also enable: extension=pgsql\n";
        echo "  5. Restart your terminal and run this script again\n\n";
        echo "Linux/Mac:\n";
        echo "  sudo apt-get install php-pgsql  (Ubuntu/Debian)\n";
        echo "  brew install php-pgsql           (Mac with Homebrew)\n\n";
    }
    
    exit(1);
}
