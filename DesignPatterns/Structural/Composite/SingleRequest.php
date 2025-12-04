<?php
/**
 * Composite Pattern - Leaf
 * 
 * Represents individual service request (leaf node)
 */

namespace FixItMati\DesignPatterns\Structural\Composite;

use FixItMati\Models\ServiceRequest;

class SingleRequest implements RequestComponent
{
    private string $requestId;
    private ServiceRequest $requestModel;
    private ?array $requestData = null;
    
    public function __construct(string $requestId)
    {
        $this->requestId = $requestId;
        $this->requestModel = new ServiceRequest();
        $this->loadData();
    }
    
    /**
     * Load request data
     */
    private function loadData(): void
    {
        $this->requestData = $this->requestModel->find($this->requestId);
    }
    
    /**
     * Get component ID
     */
    public function getId(): string
    {
        return $this->requestId;
    }
    
    /**
     * Get component type
     */
    public function getType(): string
    {
        return 'single';
    }
    
    /**
     * Get display information
     */
    public function getInfo(): array
    {
        if (!$this->requestData) {
            return [
                'id' => $this->requestId,
                'type' => 'single',
                'error' => 'Request not found'
            ];
        }
        
        return [
            'id' => $this->requestId,
            'type' => 'single',
            'title' => $this->requestData['title'],
            'status' => $this->requestData['status'],
            'category' => $this->requestData['category'],
            'priority' => $this->requestData['priority'] ?? 'normal',
            'created_at' => $this->requestData['created_at']
        ];
    }
    
    /**
     * Update status
     */
    public function updateStatus(string $status, string $userId, ?string $notes = null): bool
    {
        return $this->requestModel->updateStatus(
            $this->requestId,
            $status,
            $userId,
            $notes ?? "Status updated to {$status}"
        );
    }
    
    /**
     * Get all request IDs (just this one)
     */
    public function getAllRequestIds(): array
    {
        return [$this->requestId];
    }
    
    /**
     * Get count (always 1 for leaf)
     */
    public function getCount(): int
    {
        return 1;
    }
    
    /**
     * Get full request data
     */
    public function getRequestData(): ?array
    {
        return $this->requestData;
    }
}
