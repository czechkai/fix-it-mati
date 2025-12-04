<?php
/**
 * Template Method Pattern - Completion Processor
 * 
 * Concrete implementation for completing requests
 */

namespace FixItMati\DesignPatterns\Behavioral\TemplateMethod;

class CompletionProcessor extends RequestProcessorTemplate
{
    private array $completionData = [];
    
    /**
     * Set completion data
     */
    public function setCompletionData(array $data): void
    {
        $this->completionData = $data;
    }
    
    /**
     * Validate request can be completed
     */
    protected function performSpecificValidation(): bool
    {
        if ($this->requestData['status'] !== 'in_progress') {
            $this->result['error'] = 'Only in-progress requests can be completed';
            return false;
        }
        
        if (empty($this->completionData['actual_cost'])) {
            $this->result['error'] = 'Actual cost is required';
            return false;
        }
        
        return true;
    }
    
    /**
     * Execute: Mark request as completed
     */
    protected function execute(): void
    {
        $updateData = [
            'status' => 'completed',
            'actual_cost' => $this->completionData['actual_cost'],
            'completion_notes' => $this->completionData['notes'] ?? null,
            'completed_at' => date('Y-m-d H:i:s')
        ];
        
        $this->requestModel->update($this->requestData['id'], $updateData);
        
        $this->result['success'] = true;
        $this->result['new_status'] = 'completed';
        $this->result['actual_cost'] = $this->completionData['actual_cost'];
        $this->result['message'] = 'Request completed successfully';
    }
    
    /**
     * Pre-processing: Verify all work items completed
     */
    protected function preProcess(): void
    {
        // In production: Check work items checklist
        $this->result['work_items_verified'] = true;
        $this->result['materials_used'] = $this->completionData['materials'] ?? [];
    }
    
    /**
     * Post-processing: Generate completion report and invoice
     */
    protected function postProcess(): void
    {
        // Generate completion report
        $this->generateCompletionReport();
        
        // Create invoice
        $this->createInvoice();
        
        // Request customer feedback
        $this->result['feedback_requested'] = true;
    }
    
    /**
     * Send notifications: Notify customer of completion
     */
    protected function sendNotifications(): void
    {
        parent::sendNotifications();
        
        $this->result['customer_notified'] = true;
        $this->result['notification_type'] = 'completion_confirmation';
    }
    
    /**
     * Generate completion report
     */
    private function generateCompletionReport(): void
    {
        $reportId = 'RPT-' . uniqid();
        
        $this->result['report_generated'] = true;
        $this->result['report_id'] = $reportId;
    }
    
    /**
     * Create invoice
     */
    private function createInvoice(): void
    {
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . uniqid();
        
        $this->result['invoice_created'] = true;
        $this->result['invoice_number'] = $invoiceNumber;
        $this->result['amount_due'] = $this->completionData['actual_cost'];
    }
}
