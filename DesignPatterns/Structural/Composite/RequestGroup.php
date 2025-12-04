<?php
/**
 * Composite Pattern - Composite
 * 
 * Represents group of service requests (composite node)
 */

namespace FixItMati\DesignPatterns\Structural\Composite;

class RequestGroup implements RequestComponent
{
    private string $groupId;
    private string $groupName;
    private array $children = [];
    private array $metadata = [];
    
    public function __construct(string $groupId, string $groupName, array $metadata = [])
    {
        $this->groupId = $groupId;
        $this->groupName = $groupName;
        $this->metadata = $metadata;
    }
    
    /**
     * Add child component
     */
    public function add(RequestComponent $component): void
    {
        $this->children[] = $component;
    }
    
    /**
     * Remove child component
     */
    public function remove(RequestComponent $component): void
    {
        $this->children = array_filter($this->children, function($child) use ($component) {
            return $child->getId() !== $component->getId();
        });
        
        // Re-index array
        $this->children = array_values($this->children);
    }
    
    /**
     * Get children
     */
    public function getChildren(): array
    {
        return $this->children;
    }
    
    /**
     * Get component ID
     */
    public function getId(): string
    {
        return $this->groupId;
    }
    
    /**
     * Get component type
     */
    public function getType(): string
    {
        return 'group';
    }
    
    /**
     * Get display information
     */
    public function getInfo(): array
    {
        $childrenInfo = array_map(function($child) {
            return $child->getInfo();
        }, $this->children);
        
        return [
            'id' => $this->groupId,
            'type' => 'group',
            'name' => $this->groupName,
            'count' => $this->getCount(),
            'metadata' => $this->metadata,
            'children' => $childrenInfo
        ];
    }
    
    /**
     * Update status for all children
     */
    public function updateStatus(string $status, string $userId, ?string $notes = null): bool
    {
        $allSuccess = true;
        
        foreach ($this->children as $child) {
            $result = $child->updateStatus($status, $userId, $notes);
            if (!$result) {
                $allSuccess = false;
            }
        }
        
        return $allSuccess;
    }
    
    /**
     * Get all request IDs from all children
     */
    public function getAllRequestIds(): array
    {
        $allIds = [];
        
        foreach ($this->children as $child) {
            $allIds = array_merge($allIds, $child->getAllRequestIds());
        }
        
        return $allIds;
    }
    
    /**
     * Get total count of requests
     */
    public function getCount(): int
    {
        $count = 0;
        
        foreach ($this->children as $child) {
            $count += $child->getCount();
        }
        
        return $count;
    }
    
    /**
     * Get group name
     */
    public function getGroupName(): string
    {
        return $this->groupName;
    }
    
    /**
     * Set group name
     */
    public function setGroupName(string $name): void
    {
        $this->groupName = $name;
    }
    
    /**
     * Get metadata
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }
    
    /**
     * Set metadata
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }
    
    /**
     * Check if group has children
     */
    public function hasChildren(): bool
    {
        return !empty($this->children);
    }
    
    /**
     * Get child count
     */
    public function getChildCount(): int
    {
        return count($this->children);
    }
}
