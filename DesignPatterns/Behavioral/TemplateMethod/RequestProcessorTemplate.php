<?php
/**
 * Template Method Pattern - Abstract Request Processor
 * 
 * Defines skeleton of request processing algorithm
 * PRODUCTION READY: Includes database transactions and error recovery
 */

namespace FixItMati\DesignPatterns\Behavioral\TemplateMethod;

use FixItMati\Models\ServiceRequest;
use FixItMati\Core\Database;

abstract class RequestProcessorTemplate
{
    protected ServiceRequest $requestModel;
    protected ?Database $db = null;
    protected array $requestData;
    protected array $result = [];
    protected bool $useTransaction = true;
    
    public function __construct()
    {
        $this->requestModel = new ServiceRequest();
        
        try {
            $this->db = Database::getInstance();
        } catch (\Exception $e) {
            // Database not available, will run in mock mode
            $this->useTransaction = false;
        }
    }
    
    /**
     * Template method - defines the algorithm structure
     * This is the main method that should NOT be overridden
     * 
     * @param string $requestId
     * @return array
     */
    final public function processRequest(string $requestId): array
    {
        $transactionStarted = false;
        
        try {
            // Start database transaction
            if ($this->useTransaction && $this->db) {
                $this->db->beginTransaction();
                $transactionStarted = true;
            }
            
            // Step 1: Load request data
            $this->loadRequest($requestId);
            
            // Step 2: Validate request
            if (!$this->validateRequest()) {
                if ($transactionStarted) {
                    $this->db->rollback();
                }
                return $this->handleValidationFailure();
            }
            
            // Step 3: Perform pre-processing (hook - optional)
            $this->preProcess();
            
            // Step 4: Execute main processing (must be implemented by subclasses)
            $this->execute();
            
            // Step 5: Perform post-processing (hook - optional)
            $this->postProcess();
            
            // Commit transaction before notifications
            if ($transactionStarted) {
                $this->db->commit();
            }
            
            // Step 6: Send notifications (outside transaction)
            $this->sendNotifications();
            
            // Step 7: Log the operation
            $this->logOperation();
            
            return $this->result;
            
        } catch (\Exception $e) {
            // Rollback on any error
            if ($transactionStarted) {
                $this->db->rollback();
            }
            
            $this->logError($e);
            
            return [
                'success' => false,
                'error' => 'Processing failed: ' . $e->getMessage(),
                'request_id' => $requestId,
            ];
        }
    }
    
    /**
     * Load request from database
     * Common for all processors
     */
    protected function loadRequest(string $requestId): void
    {
        $this->requestData = $this->requestModel->find($requestId) ?? [];
        $this->result['request_id'] = $requestId;
    }
    
    /**
     * Validate request - can be overridden for specific validation
     * 
     * @return bool
     */
    protected function validateRequest(): bool
    {
        if (empty($this->requestData)) {
            $this->result['error'] = 'Request not found';
            return false;
        }
        
        return $this->performSpecificValidation();
    }
    
    /**
     * Specific validation - must be implemented by subclasses
     * 
     * @return bool
     */
    abstract protected function performSpecificValidation(): bool;
    
    /**
     * Execute main processing - must be implemented by subclasses
     * 
     * @return void
     */
    abstract protected function execute(): void;
    
    /**
     * Pre-processing hook - can be overridden
     * 
     * @return void
     */
    protected function preProcess(): void
    {
        // Default: do nothing
        // Subclasses can override to add pre-processing
    }
    
    /**
     * Post-processing hook - can be overridden
     * 
     * @return void
     */
    protected function postProcess(): void
    {
        // Default: do nothing
        // Subclasses can override to add post-processing
    }
    
    /**
     * Send notifications - can be overridden
     * 
     * @return void
     */
    protected function sendNotifications(): void
    {
        // Default implementation
        $this->result['notifications_sent'] = true;
    }
    
    /**
     * Log operation - common for all processors
     * 
     * @return void
     */
    protected function logOperation(): void
    {
        $this->result['logged_at'] = date('Y-m-d H:i:s');
        $this->result['processor'] = static::class;
        
        // Log to file for audit trail
        $this->writeAuditLog([
            'timestamp' => date('Y-m-d H:i:s'),
            'processor' => basename(str_replace('\\', '/', static::class)),
            'request_id' => $this->result['request_id'] ?? null,
            'success' => $this->result['success'] ?? true,
            'action' => $this->getActionName(),
        ]);
    }
    
    /**
     * Log errors
     */
    protected function logError(\Exception $e): void
    {
        $logDir = __DIR__ . '/../../../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/processor_errors_' . date('Y-m-d') . '.log';
        $logEntry = sprintf(
            "[%s] ERROR in %s: %s\nStack: %s\n\n",
            date('Y-m-d H:i:s'),
            static::class,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Write audit log
     */
    protected function writeAuditLog(array $data): void
    {
        $logDir = __DIR__ . '/../../../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/processor_audit_' . date('Y-m-d') . '.log';
        $logEntry = json_encode($data, JSON_UNESCAPED_SLASHES) . "\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Get action name for logging
     */
    protected function getActionName(): string
    {
        $className = basename(str_replace('\\', '/', static::class));
        return str_replace('Processor', '', $className);
    }
    
    /**
     * Handle validation failure
     * 
     * @return array
     */
    protected function handleValidationFailure(): array
    {
        $this->result['success'] = false;
        $this->result['message'] = $this->result['error'] ?? 'Validation failed';
        return $this->result;
    }
}
