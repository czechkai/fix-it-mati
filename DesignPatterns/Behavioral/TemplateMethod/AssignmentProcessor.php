<?php
/**
 * Template Method Pattern - Assignment Processor
 * 
 * Concrete implementation for assigning technicians to requests
 */

namespace FixItMati\DesignPatterns\Behavioral\TemplateMethod;

class AssignmentProcessor extends RequestProcessorTemplate
{
    private ?string $technicianId = null;
    
    /**
     * Set technician ID
     */
    public function setTechnicianId(string $technicianId): void
    {
        $this->technicianId = $technicianId;
    }
    
    /**
     * Validate request is assignable
     */
    protected function performSpecificValidation(): bool
    {
        if ($this->requestData['status'] !== 'pending') {
            $this->result['error'] = 'Only pending requests can be assigned';
            return false;
        }
        
        if (!$this->technicianId) {
            $this->result['error'] = 'Technician ID is required';
            return false;
        }
        
        if (!empty($this->requestData['assigned_technician_id'])) {
            $this->result['error'] = 'Request already assigned';
            return false;
        }
        
        return true;
    }
    
    /**
     * Execute: Assign technician and update status
     */
    protected function execute(): void
    {
        // Update request with technician assignment
        $this->requestModel->update($this->requestData['id'], [
            'assigned_technician_id' => $this->technicianId,
            'status' => 'assigned',
            'assigned_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->result['success'] = true;
        $this->result['technician_id'] = $this->technicianId;
        $this->result['new_status'] = 'assigned';
        $this->result['message'] = 'Technician assigned successfully';
    }
    
    /**
     * Pre-processing: Check technician availability
     */
    protected function preProcess(): void
    {
        // In production: Check technician's current workload
        $this->result['technician_available'] = true;
        $this->result['current_workload'] = 3; // Mock data
    }
    
    /**
     * Post-processing: Schedule initial visit
     */
    protected function postProcess(): void
    {
        // In production: Create calendar entry for technician
        $this->result['visit_scheduled'] = true;
        $this->result['estimated_start'] = date('Y-m-d', strtotime('+2 days'));
    }
    
    /**
     * Send notifications: Notify both customer and technician
     */
    protected function sendNotifications(): void
    {
        parent::sendNotifications();
        
        $this->result['customer_notified'] = true;
        $this->result['technician_notified'] = true;
    }
}
