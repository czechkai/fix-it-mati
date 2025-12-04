<?php
/**
 * Decorator Pattern - Urgent Request Decorator
 * 
 * Adds urgent priority features to a request
 */

namespace FixItMati\DesignPatterns\Structural\Decorator;

class UrgentRequestDecorator extends RequestDecorator
{
    private float $urgentFee = 500.0;
    
    /**
     * Get enhanced description
     */
    public function getDescription(): string
    {
        return "🚨 URGENT: " . $this->request->getDescription();
    }
    
    /**
     * Get cost with urgent fee
     */
    public function getCost(): float
    {
        return $this->request->getCost() + $this->urgentFee;
    }
    
    /**
     * Process with urgent features
     */
    public function process(): array
    {
        $result = $this->request->process();
        
        // Add urgent features
        $result['priority'] = 'urgent';
        $result['features'][] = 'priority_handling';
        $result['features'][] = '24_hour_response';
        $result['estimated_response_time'] = '2 hours';
        $result['urgent_fee'] = $this->urgentFee;
        
        return $result;
    }
}
