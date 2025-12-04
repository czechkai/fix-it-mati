<?php
/**
 * Command Pattern - Assign Technician Command
 * 
 * Concrete command for assigning technician with undo support
 */

namespace FixItMati\DesignPatterns\Behavioral\Command;

use FixItMati\Models\ServiceRequest;

class AssignTechnicianCommand implements Command
{
    private ServiceRequest $requestModel;
    private string $requestId;
    private string $technicianId;
    private string $adminId;
    private ?string $notes;
    
    // State for undo
    private ?string $previousTechnicianId = null;
    private ?string $previousStatus = null;
    private bool $executed = false;
    
    public function __construct(
        string $requestId,
        string $technicianId,
        string $adminId,
        ?string $notes = null
    ) {
        $this->requestModel = new ServiceRequest();
        $this->requestId = $requestId;
        $this->technicianId = $technicianId;
        $this->adminId = $adminId;
        $this->notes = $notes;
    }
    
    /**
     * Execute: Assign technician
     */
    public function execute(): bool
    {
        // Get current assignment before changing
        $request = $this->requestModel->find($this->requestId);
        if (!$request) {
            return false;
        }
        
        $this->previousTechnicianId = $request['assigned_technician_id'] ?? null;
        $this->previousStatus = $request['status'];
        
        // Update assignment
        $updateData = ['assigned_technician_id' => $this->technicianId];
        $result = $this->requestModel->update($this->requestId, $updateData);
        
        if ($result) {
            // Update status to assigned
            $this->requestModel->updateStatus(
                $this->requestId,
                'assigned',
                $this->adminId,
                $this->notes ?? "Technician assigned"
            );
            $this->executed = true;
        }
        
        return $result;
    }
    
    /**
     * Undo: Remove technician assignment
     */
    public function undo(): bool
    {
        if (!$this->executed) {
            return false;
        }
        
        // Revert assignment
        $updateData = ['assigned_technician_id' => $this->previousTechnicianId];
        $result = $this->requestModel->update($this->requestId, $updateData);
        
        if ($result && $this->previousStatus) {
            // Revert status
            $this->requestModel->updateStatus(
                $this->requestId,
                $this->previousStatus,
                $this->adminId,
                "Undo: Technician assignment reverted"
            );
            $this->executed = false;
        }
        
        return $result;
    }
    
    /**
     * Redo: Re-assign technician
     */
    public function redo(): bool
    {
        if ($this->executed) {
            return true;
        }
        
        return $this->execute();
    }
    
    /**
     * Get command description
     */
    public function getDescription(): string
    {
        return "Assign technician {$this->technicianId} to request {$this->requestId}";
    }
    
    /**
     * Get command data
     */
    public function getData(): array
    {
        return [
            'type' => 'assign_technician',
            'request_id' => $this->requestId,
            'previous_technician_id' => $this->previousTechnicianId,
            'new_technician_id' => $this->technicianId,
            'admin_id' => $this->adminId,
            'notes' => $this->notes,
            'executed' => $this->executed
        ];
    }
}
