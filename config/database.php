<?php
/**
 * Database Configuration for FixItMati
 * Loads environment variables and provides database connection
 */

class Database {
    private static $instance = null;
    private $conn;
    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;

    private function __construct() {
        // Load environment variables
        $this->loadEnv();
        
        $this->host = getenv('DB_HOST');
        $this->port = getenv('DB_PORT');
        $this->dbname = getenv('DB_NAME');
        $this->username = getenv('DB_USER');
        $this->password = getenv('DB_PASSWORD');
    }

    /**
     * Load .env file into environment variables
     */
    private function loadEnv() {
        $envFile = __DIR__ . '/../.env';
        
        if (!file_exists($envFile)) {
            throw new Exception('.env file not found. Please run setup.bat first.');
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Set environment variable
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Get database connection
     */
    public function getConnection() {
        if ($this->conn === null) {
            try {
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname};sslmode=require";
                $this->conn = new PDO($dsn, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
        return $this->conn;
    }

    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->query('SELECT version()');
            $result = $stmt->fetch();
            return [
                'success' => true,
                'message' => 'Database connection successful!',
                'version' => $result['version']
            ];
        } catch(Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get Supabase API configuration
     */
    public static function getSupabaseConfig() {
        return [
            'url' => getenv('SUPABASE_URL'),
            'anon_key' => getenv('SUPABASE_ANON_KEY'),
            'service_key' => getenv('SUPABASE_SERVICE_KEY')
        ];
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Example usage:
/*
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Your database queries here
    $stmt = $conn->prepare("SELECT * FROM your_table WHERE id = :id");
    $stmt->execute(['id' => 1]);
    $result = $stmt->fetch();
    
} catch(Exception $e) {
    error_log("Database error: " . $e->getMessage());
    // Handle error appropriately
}
*/
?>
