<?php
/**
 * Memento Pattern - Caretaker
 * 
 * Manages mementos without examining their contents
 */

namespace FixItMati\DesignPatterns\Behavioral\Memento;

class RequestCaretaker
{
    private array $mementos = [];
    private int $maxSnapshots = 10;
    
    /**
     * Save a memento
     */
    public function saveMemento(string $key, RequestMemento $memento): void
    {
        // If we exceed max snapshots, remove oldest
        if (count($this->mementos) >= $this->maxSnapshots) {
            array_shift($this->mementos);
        }
        
        $this->mementos[$key] = $memento;
    }
    
    /**
     * Get a memento by key
     */
    public function getMemento(string $key): ?RequestMemento
    {
        return $this->mementos[$key] ?? null;
    }
    
    /**
     * Get all mementos
     */
    public function getAllMementos(): array
    {
        return $this->mementos;
    }
    
    /**
     * Get memento count
     */
    public function getCount(): int
    {
        return count($this->mementos);
    }
    
    /**
     * Clear all mementos
     */
    public function clearAll(): void
    {
        $this->mementos = [];
    }
    
    /**
     * Get latest memento
     */
    public function getLatest(): ?RequestMemento
    {
        if (empty($this->mementos)) {
            return null;
        }
        
        return end($this->mementos);
    }
    
    /**
     * List all snapshots with info
     */
    public function listSnapshots(): array
    {
        $snapshots = [];
        
        foreach ($this->mementos as $key => $memento) {
            $snapshots[$key] = $memento->getInfo();
        }
        
        return $snapshots;
    }
    
    /**
     * Remove a specific memento
     */
    public function removeMemento(string $key): bool
    {
        if (isset($this->mementos[$key])) {
            unset($this->mementos[$key]);
            return true;
        }
        
        return false;
    }
}
