<?php
/**
 * Command Pattern - Command Invoker
 * 
 * Manages command execution, undo/redo history
 */

namespace FixItMati\DesignPatterns\Behavioral\Command;

class CommandInvoker
{
    private array $history = [];
    private int $currentPosition = -1;
    private int $maxHistorySize = 50;
    
    /**
     * Execute a command and add to history
     */
    public function execute(Command $command): bool
    {
        $result = $command->execute();
        
        if ($result) {
            // Remove any commands after current position (if we undid some commands)
            if ($this->currentPosition < count($this->history) - 1) {
                $this->history = array_slice($this->history, 0, $this->currentPosition + 1);
            }
            
            // Add command to history
            $this->history[] = $command;
            $this->currentPosition++;
            
            // Limit history size
            if (count($this->history) > $this->maxHistorySize) {
                array_shift($this->history);
                $this->currentPosition--;
            }
        }
        
        return $result;
    }
    
    /**
     * Undo last command
     */
    public function undo(): bool
    {
        if (!$this->canUndo()) {
            return false;
        }
        
        $command = $this->history[$this->currentPosition];
        $result = $command->undo();
        
        if ($result) {
            $this->currentPosition--;
        }
        
        return $result;
    }
    
    /**
     * Redo previously undone command
     */
    public function redo(): bool
    {
        if (!$this->canRedo()) {
            return false;
        }
        
        $this->currentPosition++;
        $command = $this->history[$this->currentPosition];
        $result = $command->redo();
        
        if (!$result) {
            $this->currentPosition--;
        }
        
        return $result;
    }
    
    /**
     * Check if undo is possible
     */
    public function canUndo(): bool
    {
        return $this->currentPosition >= 0;
    }
    
    /**
     * Check if redo is possible
     */
    public function canRedo(): bool
    {
        return $this->currentPosition < count($this->history) - 1;
    }
    
    /**
     * Get command history
     */
    public function getHistory(): array
    {
        return array_map(function($command) {
            return [
                'description' => $command->getDescription(),
                'data' => $command->getData()
            ];
        }, $this->history);
    }
    
    /**
     * Get current position in history
     */
    public function getCurrentPosition(): int
    {
        return $this->currentPosition;
    }
    
    /**
     * Clear all history
     */
    public function clearHistory(): void
    {
        $this->history = [];
        $this->currentPosition = -1;
    }
    
    /**
     * Get history size
     */
    public function getHistorySize(): int
    {
        return count($this->history);
    }
}
