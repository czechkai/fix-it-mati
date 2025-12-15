<?php
/**
 * Database Configuration for FixItMati
 * Implements SINGLETON DESIGN PATTERN
 * 
 * Design Pattern: Singleton
 * Purpose: Ensures only one database connection exists throughout the application
 * Benefits: 
 * - Prevents multiple database connections
 * - Saves resources
 * - Provides global access point
 */ 

namespace FixItMati\Core;

use PDO;
use PDOException;   
use Exception;

class Database {
    // Singleton instance
    private static $instance = null;
    
    private $conn;
    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;

    /**
     * Private constructor prevents direct instantiation
     * This is KEY to Singleton pattern
     */
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
     * Prevent cloning of the instance
     * Part of Singleton pattern implementation
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the instance
     * Part of Singleton pattern implementation
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Load .env file into environment variables
     */
    private function loadEnv(): void {
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
     * Get singleton instance (SINGLETON PATTERN)
     * This is the only way to get a Database instance
     * 
     * @return Database The single instance
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get database connection
     * Implements lazy loading - connection created only when needed
     * 
     * @return PDO Database connection
     */
    public function getConnection(): PDO {
        if ($this->conn === null) {
            try {
                // Check if PDO PostgreSQL driver is available
                if (!extension_loaded('pdo_pgsql')) {
                    throw new Exception("PostgreSQL PDO driver not installed. Please install php-pgsql extension. Run 'php check-requirements.php' for detailed instructions.");
                }
                
                // Validate configuration
                if (empty($this->host) || empty($this->dbname) || empty($this->username)) {
                    throw new Exception("Database configuration incomplete. Please check config/database.php or .env file.");
                }
                
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname};sslmode=require";
                $this->conn = new PDO($dsn, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                $errorMsg = $e->getMessage();
                
                // Provide helpful error messages
                if (strpos($errorMsg, 'could not find driver') !== false) {
                    throw new Exception("Database connection failed: PostgreSQL PDO driver not found. Please run 'php check-requirements.php' for setup instructions.");
                } elseif (strpos($errorMsg, 'could not connect to server') !== false) {
                    throw new Exception("Database connection failed: Cannot reach PostgreSQL server at {$this->host}:{$this->port}. Please ensure PostgreSQL is running.");
                } elseif (strpos($errorMsg, 'password authentication failed') !== false) {
                    throw new Exception("Database connection failed: Invalid credentials. Please check DB_USER and DB_PASSWORD in your configuration.");
                } elseif (strpos($errorMsg, 'database') !== false && strpos($errorMsg, 'does not exist') !== false) {
                    throw new Exception("Database connection failed: Database '{$this->dbname}' does not exist. Please create it first.");
                } else {
                    throw new Exception("Database connection failed: " . $errorMsg);
                }
            }
        }
        return $this->conn;
    }

    /**
     * Test database connection
     * 
     * @return array Connection test result
     */
    public function testConnection(): array {
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
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Execute a query and return results
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array Query results
     */
    public function query(string $sql, array $params = []): array {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    /**
     * Execute an insert/update/delete query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return int Number of affected rows
     */
    public function execute(string $sql, array $params = []): int {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch(PDOException $e) {
            throw new Exception("Execute failed: " . $e->getMessage());
        }
    }

    /**
     * Get last inserted ID
     * 
     * @return string Last insert ID
     */
    public function lastInsertId(): string {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): void {
        $this->getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): void {
        $this->getConnection()->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): void {
        $this->getConnection()->rollBack();
    }
}
