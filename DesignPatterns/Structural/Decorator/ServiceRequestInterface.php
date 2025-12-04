<?php
/**
 * Decorator Pattern - Component Interface
 * 
 * Base interface for service requests
 */

namespace FixItMati\DesignPatterns\Structural\Decorator;

interface ServiceRequestInterface
{
    /**
     * Get request data
     */
    public function getData(): array;
    
    /**
     * Get request description
     */
    public function getDescription(): string;
    
    /**
     * Get request cost/priority
     */
    public function getCost(): float;
    
    /**
     * Process request
     */
    public function process(): array;
}
