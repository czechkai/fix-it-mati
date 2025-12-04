<?php
/**
 * Memento Pattern - Memento
 * 
 * Stores internal state of an object without violating encapsulation
 */

namespace FixItMati\DesignPatterns\Behavioral\Memento;

class RequestMemento
{
    private array $state;
    private string $timestamp;
    private string $label;
    
    public function __construct(array $state, string $label = '')
    {
        $this->state = $state;
        $this->timestamp = date('Y-m-d H:i:s');
        $this->label = $label ?: 'Snapshot at ' . $this->timestamp;
    }
    
    /**
     * Get saved state
     */
    public function getState(): array
    {
        return $this->state;
    }
    
    /**
     * Get timestamp
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }
    
    /**
     * Get label
     */
    public function getLabel(): string
    {
        return $this->label;
    }
    
    /**
     * Get memento info
     */
    public function getInfo(): array
    {
        return [
            'label' => $this->label,
            'timestamp' => $this->timestamp,
            'status' => $this->state['status'] ?? 'unknown',
            'assigned_to' => $this->state['assigned_technician_id'] ?? null
        ];
    }
}
