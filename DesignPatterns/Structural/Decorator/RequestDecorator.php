<?php
/**
 * Decorator Pattern - Base Decorator
 * 
 * Abstract decorator that implements the component interface
 */

namespace FixItMati\DesignPatterns\Structural\Decorator;

abstract class RequestDecorator implements ServiceRequestInterface
{
    protected ServiceRequestInterface $request;
    
    public function __construct(ServiceRequestInterface $request)
    {
        $this->request = $request;
    }
    
    /**
     * Get request data (delegates to wrapped request)
     */
    public function getData(): array
    {
        return $this->request->getData();
    }
    
    /**
     * Get description (delegates to wrapped request)
     */
    public function getDescription(): string
    {
        return $this->request->getDescription();
    }
    
    /**
     * Get cost (delegates to wrapped request)
     */
    public function getCost(): float
    {
        return $this->request->getCost();
    }
    
    /**
     * Process (delegates to wrapped request)
     */
    public function process(): array
    {
        return $this->request->process();
    }
}
