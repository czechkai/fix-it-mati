<?php
/**
 * Command Pattern - Update Request Status Command
 * 
 * Concrete command for updating service request status with undo/redo support
 */

namespace FixItMati\DesignPatterns\Behavioral\Command;

use FixItMati\Models\ServiceRequest;

class UpdateRequestStatusCommand implements Command
{
    private ServiceRequest $requestModel;
    private string $requestId;
    private string $newStatus;
    private string $userId;
    private ?string $notes;
    
    // State for undo
    private ?string $oldStatus = null;
    private bool $executed = false;
    
    public function __construct(
        string $requestId,
        string $newStatus,
        string $userId,
        ?string $notes = null
    ) {
        $this->requestModel = new ServiceRequest();
        $this->requestId = $requestId;
        $this->newStatus = $newStatus;
        $this->userId = $userId;
        $this->notes = $notes;
    }
    
    /**
     * Execute: Update request status
     */
    public function execute(): bool
    {
        // Get current status before changing
        $request = $this->requestModel->find($this->requestId);
        if (!$request) {
            return false;
        }
        
        $this->oldStatus = $request['status'];
        
        // Update status
        $result = $this->requestModel->updateStatus(
            $this->requestId,
            $this->newStatus,
            $this->userId,
            $this->notes ?? "Status changed to {$this->newStatus}"
        );
        
        if ($result) {
            $this->executed = true;
        }
        
        return $result;
    }
    
    /**
     * Undo: Revert to previous status
     */
    public function undo(): bool
    {
        if (!$this->executed || $this->oldStatus === null) {
            return false;
        }
        
        // Revert to old status
        $result = $this->requestModel->updateStatus(
            $this->requestId,
            $this->oldStatus,
            $this->userId,
            "Undo: Reverted from {$this->newStatus} to {$this->oldStatus}"
        );
        
        if ($result) {
            $this->executed = false;
        }
        
        return $result;
    }
    
    /**
     * Redo: Re-apply the status change
     */
    public function redo(): bool
    {
        if ($this->executed) {
            return true; // Already executed
        }
        
        return $this->execute();
    }
    
    /**
     * Get command description
     */
    public function getDescription(): string
    {
        return "Update request {$this->requestId} status to {$this->newStatus}";
    }
    
    /**
     * Get command data
     */
    public function getData(): array
    {
        return [
            'type' => 'update_status',
            'request_id' => $this->requestId,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'user_id' => $this->userId,
            'notes' => $this->notes,
            'executed' => $this->executed
        ];
    }
}
