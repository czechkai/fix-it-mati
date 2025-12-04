<?php
/**
 * Memento Pattern - Originator
 * 
 * Creates mementos and can restore state from them
 */

namespace FixItMati\DesignPatterns\Behavioral\Memento;

use FixItMati\Models\ServiceRequest;

class RequestOriginator
{
    private ServiceRequest $requestModel;
    private string $requestId;
    private array $currentState = [];
    
    public function __construct(string $requestId)
    {
        $this->requestModel = new ServiceRequest();
        $this->requestId = $requestId;
        $this->loadCurrentState();
    }
    
    /**
     * Load current state from database
     */
    private function loadCurrentState(): void
    {
        $request = $this->requestModel->find($this->requestId);
        if ($request) {
            $this->currentState = $request;
        }
    }
    
    /**
     * Create a memento with current state
     */
    public function createMemento(string $label = ''): RequestMemento
    {
        $this->loadCurrentState();
        return new RequestMemento($this->currentState, $label);
    }
    
    /**
     * Restore state from memento
     */
    public function restoreFromMemento(RequestMemento $memento): bool
    {
        $state = $memento->getState();
        
        // Update request with saved state
        $updateData = [
            'status' => $state['status'] ?? $this->currentState['status'],
            'priority' => $state['priority'] ?? $this->currentState['priority'],
            'assigned_technician_id' => $state['assigned_technician_id'] ?? null,
            'title' => $state['title'] ?? $this->currentState['title'],
            'description' => $state['description'] ?? $this->currentState['description'],
            'location' => $state['location'] ?? $this->currentState['location']
        ];
        
        $result = $this->requestModel->update($this->requestId, $updateData);
        
        if ($result) {
            // Add update record
            $this->requestModel->updateStatus(
                $this->requestId,
                $updateData['status'],
                $state['user_id'] ?? 'system',
                "Restored from snapshot: {$memento->getLabel()}"
            );
            
            $this->currentState = $state;
        }
        
        return $result;
    }
    
    /**
     * Get current state
     */
    public function getCurrentState(): array
    {
        $this->loadCurrentState();
        return $this->currentState;
    }
    
    /**
     * Get request ID
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }
}
