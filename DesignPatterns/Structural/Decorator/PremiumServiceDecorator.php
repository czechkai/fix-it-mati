<?php
/**
 * Decorator Pattern - Premium Service Decorator
 * 
 * Adds premium service features
 */

namespace FixItMati\DesignPatterns\Structural\Decorator;

class PremiumServiceDecorator extends RequestDecorator
{
    private float $premiumFee = 1500.0;
    
    /**
     * Get enhanced description
     */
    public function getDescription(): string
    {
        return $this->request->getDescription() . " (⭐ Premium Service)";
    }
    
    /**
     * Get cost with premium fee
     */
    public function getCost(): float
    {
        return $this->request->getCost() + $this->premiumFee;
    }
    
    /**
     * Get data with premium details
     */
    public function getData(): array
    {
        $data = $this->request->getData();
        $data['service_level'] = 'premium';
        $data['premium_fee'] = $this->premiumFee;
        $data['features'] = [
            'priority_scheduling',
            'dedicated_technician',
            'same_day_service',
            'quality_guarantee',
            'follow_up_call'
        ];
        return $data;
    }
    
    /**
     * Process with premium features
     */
    public function process(): array
    {
        $result = $this->request->process();
        
        // Add premium features
        $result['features'][] = 'premium_service';
        $result['priority_level'] = 'premium';
        $result['estimated_response'] = '4 hours';
        $result['dedicated_technician'] = true;
        $result['quality_guarantee'] = true;
        $result['follow_up_included'] = true;
        
        return $result;
    }
}
