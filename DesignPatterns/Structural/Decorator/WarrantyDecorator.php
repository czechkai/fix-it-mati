<?php
/**
 * Decorator Pattern - Warranty Extension Decorator
 * 
 * Adds extended warranty coverage
 */

namespace FixItMati\DesignPatterns\Structural\Decorator;

class WarrantyDecorator extends RequestDecorator
{
    private int $warrantyMonths;
    private float $warrantyFee;
    
    public function __construct(ServiceRequestInterface $request, int $months = 12)
    {
        parent::__construct($request);
        $this->warrantyMonths = $months;
        $this->warrantyFee = $months * 150.0; // ₱150 per month
    }
    
    /**
     * Get enhanced description
     */
    public function getDescription(): string
    {
        return $this->request->getDescription() . " (🛡️ {$this->warrantyMonths}-month warranty)";
    }
    
    /**
     * Get cost with warranty fee
     */
    public function getCost(): float
    {
        return $this->request->getCost() + $this->warrantyFee;
    }
    
    /**
     * Get data with warranty details
     */
    public function getData(): array
    {
        $data = $this->request->getData();
        $data['warranty'] = [
            'months' => $this->warrantyMonths,
            'fee' => $this->warrantyFee,
            'coverage' => 'parts_and_labor',
            'expires_at' => date('Y-m-d', strtotime("+{$this->warrantyMonths} months"))
        ];
        return $data;
    }
    
    /**
     * Process with warranty features
     */
    public function process(): array
    {
        $result = $this->request->process();
        
        // Add warranty features
        $result['features'][] = 'extended_warranty';
        $result['warranty_months'] = $this->warrantyMonths;
        $result['warranty_fee'] = $this->warrantyFee;
        $result['warranty_coverage'] = 'parts_and_labor';
        
        return $result;
    }
}
