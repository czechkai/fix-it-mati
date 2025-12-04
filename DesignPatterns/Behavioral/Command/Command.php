<?php
/**
 * Command Pattern - Interface
 * 
 * Encapsulates a request as an object, allowing you to parameterize clients
 * with different requests, queue or log requests, and support undoable operations.
 */

namespace FixItMati\DesignPatterns\Behavioral\Command;

interface Command
{
    /**
     * Execute the command
     */
    public function execute(): bool;
    
    /**
     * Undo the command (reverse the operation)
     */
    public function undo(): bool;
    
    /**
     * Redo the command (re-execute after undo)
     */
    public function redo(): bool;
    
    /**
     * Get command description
     */
    public function getDescription(): string;
    
    /**
     * Get command data for logging/history
     */
    public function getData(): array;
}
