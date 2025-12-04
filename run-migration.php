<?php
/**
 * Run Database Migration
 * Executes SQL migration files
 */

// Load .env file manually
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

try {
    echo "🔄 Running database migration...\n\n";
    
    // Create database connection
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $_ENV['DB_HOST'],
        $_ENV['DB_PORT'],
        $_ENV['DB_NAME']
    );
    
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Read migration file
    $migrationFile = __DIR__ . '/database/002_create_service_requests.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Execute migration
    echo "📄 Executing: 002_create_service_requests.sql\n";
    $pdo->exec($sql);
    
    echo "✅ Migration completed successfully!\n\n";
    
    // Verify tables were created
    echo "📊 Verifying tables...\n";
    
    $tables = ['service_requests', 'request_updates'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_name = '$table'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            echo "  ✓ Table '$table' exists\n";
            
            // Show column count
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.columns WHERE table_name = '$table'");
            $colCount = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "    → {$colCount['count']} columns\n";
        } else {
            echo "  ✗ Table '$table' not found\n";
        }
    }
    
    echo "\n🎉 Database is ready for Sprint 1!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
