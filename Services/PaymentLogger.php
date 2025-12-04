<?php
/**
 * Payment Logger
 * 
 * Centralized logging for payment operations
 */

namespace FixItMati\Services;

class PaymentLogger
{
    private string $logDir;
    private string $logLevel;
    
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    
    private array $levelPriority = [
        self::LEVEL_DEBUG => 0,
        self::LEVEL_INFO => 1,
        self::LEVEL_WARNING => 2,
        self::LEVEL_ERROR => 3,
    ];
    
    public function __construct()
    {
        $this->logDir = __DIR__ . '/../logs';
        $this->logLevel = $_ENV['PAYMENT_LOG_LEVEL'] ?? self::LEVEL_INFO;
        
        // Create logs directory if it doesn't exist
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }
    
    /**
     * Log payment transaction
     */
    public function logTransaction(string $gateway, string $action, array $data, bool $success): void
    {
        $level = $success ? self::LEVEL_INFO : self::LEVEL_ERROR;
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'gateway' => $gateway,
            'action' => $action,
            'success' => $success,
            'transaction_id' => $data['transaction_id'] ?? null,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? null,
            'error' => $data['error'] ?? null,
        ];
        
        $this->log($level, "Payment {$action}", $logEntry);
    }
    
    /**
     * Log webhook event
     */
    public function logWebhook(string $gateway, string $eventType, array $data): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'gateway' => $gateway,
            'event_type' => $eventType,
            'data' => $data,
        ];
        
        $this->log(self::LEVEL_INFO, "Webhook received", $logEntry);
    }
    
    /**
     * Log API error
     */
    public function logError(string $gateway, string $message, array $context = []): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'gateway' => $gateway,
            'message' => $message,
            'context' => $context,
        ];
        
        $this->log(self::LEVEL_ERROR, "Payment error", $logEntry);
    }
    
    /**
     * Debug logging
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }
    
    /**
     * Generic log method
     */
    private function log(string $level, string $message, array $data = []): void
    {
        // Check if this level should be logged
        if ($this->levelPriority[$level] < $this->levelPriority[$this->logLevel]) {
            return;
        }
        
        $logFile = $this->logDir . '/payment_' . date('Y-m-d') . '.log';
        
        $logEntry = sprintf(
            "[%s] %s: %s %s\n",
            strtoupper($level),
            date('Y-m-d H:i:s'),
            $message,
            !empty($data) ? json_encode($data, JSON_UNESCAPED_SLASHES) : ''
        );
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Get recent logs
     */
    public function getRecentLogs(int $lines = 100): array
    {
        $logFile = $this->logDir . '/payment_' . date('Y-m-d') . '.log';
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $content = file($logFile);
        return array_slice($content, -$lines);
    }
    
    /**
     * Clear old logs (older than 30 days)
     */
    public function clearOldLogs(int $daysToKeep = 30): int
    {
        $cleared = 0;
        $cutoffTime = time() - ($daysToKeep * 86400);
        
        $files = glob($this->logDir . '/payment_*.log');
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $cleared++;
            }
        }
        
        return $cleared;
    }
}
