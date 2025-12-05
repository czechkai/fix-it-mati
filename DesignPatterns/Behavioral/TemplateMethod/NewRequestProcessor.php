<?php
/**
 * Template Method Pattern - New Request Processor
 * 
 * Concrete implementation for processing new requests
 */

namespace FixItMati\DesignPatterns\Behavioral\TemplateMethod;

class NewRequestProcessor extends RequestProcessorTemplate
{
    /**
     * Validate that request is in pending state
     */
    protected function performSpecificValidation(): bool
    {
        if ($this->requestData['status'] !== 'pending') {
            $this->result['error'] = 'Request must be in pending status';
            return false;
        }
        
        return true;
    }
    
    /**
     * Execute: Assign priority and estimate costs
     */
    protected function execute(): void
    {
        // Determine priority based on category
        $priority = $this->determinePriority();
        
        // Estimate cost
        $estimatedCost = $this->estimateCost();
        
        // Update request
        $this->requestModel->update($this->requestData['id'], [
            'priority' => $priority,
            'estimated_cost' => $estimatedCost,
            'processed_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->result['success'] = true;
        $this->result['priority'] = $priority;
        $this->result['estimated_cost'] = $estimatedCost;
        $this->result['message'] = 'New request processed successfully';
    }
    
    /**
     * Pre-processing: Check for similar requests
     */
    protected function preProcess(): void
    {
        $category = $this->requestData['category'] ?? '';
        // In production: Query database for count
        $similarCount = 0; // Mock count
        $this->result['similar_requests_count'] = $similarCount;
    }
    
    /**
     * Post-processing: Create initial timeline entry
     */
    protected function postProcess(): void
    {
        // In production: Create entry in request_updates table
        $this->result['timeline_created'] = true;
    }
    
    /**
     * Determine priority based on category
     */
    private function determinePriority(): string
    {
        $category = $this->requestData['category'] ?? '';
        
        $highPriorityCategories = ['Electricity', 'Water Supply'];
        
        return in_array($category, $highPriorityCategories) ? 'high' : 'normal';
    }
    
    /**
     * Estimate cost based on category
     */
    private function estimateCost(): float
    {
        $category = $this->requestData['category'] ?? '';
        
        $baseCosts = [
            'Water Supply' => 1500.0,
            'Electricity' => 2000.0
        ];
        
        return $baseCosts[$category] ?? 1000.0;
    }
}
