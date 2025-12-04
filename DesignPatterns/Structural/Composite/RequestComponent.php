<?php
/**
 * Composite Pattern - Component Interface
 * 
 * Defines interface for objects in composition
 */

namespace FixItMati\DesignPatterns\Structural\Composite;

interface RequestComponent
{
    /**
     * Get component ID
     */
    public function getId(): string;
    
    /**
     * Get component type
     */
    public function getType(): string;
    
    /**
     * Get display information
     */
    public function getInfo(): array;
    
    /**
     * Update status (applies to all children in composite)
     */
    public function updateStatus(string $status, string $userId, ?string $notes = null): bool;
    
    /**
     * Get all request IDs (for both leaf and composite)
     */
    public function getAllRequestIds(): array;
    
    /**
     * Get count of requests
     */
    public function getCount(): int;
}
