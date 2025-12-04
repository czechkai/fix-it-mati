<?php
/**
 * Decorator Pattern - Concrete Component
 * 
 * Basic service request implementation
 */

namespace FixItMati\DesignPatterns\Structural\Decorator;

class BasicServiceRequest implements ServiceRequestInterface
{
    protected array $requestData;
    protected float $baseCost;
    
    public function __construct(array $requestData, float $baseCost = 0.0)
    {
        $this->requestData = $requestData;
        $this->baseCost = $baseCost;
    }
    
    /**
     * Get request data
     */
    public function getData(): array
    {
        return $this->requestData;
    }
    
    /**
     * Get request description
     */
    public function getDescription(): string
    {
        return $this->requestData['title'] ?? 'Service Request';
    }
    
    /**
     * Get request cost
     */
    public function getCost(): float
    {
        return $this->baseCost;
    }
    
    /**
     * Process request
     */
    public function process(): array
    {
        return [
            'description' => $this->getDescription(),
            'cost' => $this->getCost(),
            'features' => ['basic_service']
        ];
    }
}
