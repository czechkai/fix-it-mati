<?php
/**
 * Decorator Pattern - Extended Support Decorator
 * 
 * Adds extended post-service support
 */

namespace FixItMati\DesignPatterns\Structural\Decorator;

class ExtendedSupportDecorator extends RequestDecorator
{
    private int $supportDays;
    private float $supportFee;
    
    public function __construct(ServiceRequestInterface $request, int $days = 30)
    {
        parent::__construct($request);
        $this->supportDays = $days;
        $this->supportFee = $days * 25.0; // ₱25 per day
    }
    
    /**
     * Get enhanced description
     */
    public function getDescription(): string
    {
        return $this->request->getDescription() . " (💬 {$this->supportDays}-day support)";
    }
    
    /**
     * Get cost with support fee
     */
    public function getCost(): float
    {
        return $this->request->getCost() + $this->supportFee;
    }
    
    /**
     * Get data with support details
     */
    public function getData(): array
    {
        $data = $this->request->getData();
        $data['support'] = [
            'days' => $this->supportDays,
            'fee' => $this->supportFee,
            'channels' => ['phone', 'email', 'chat'],
            'response_time' => '24 hours',
            'expires_at' => date('Y-m-d', strtotime("+{$this->supportDays} days"))
        ];
        return $data;
    }
    
    /**
     * Process with support features
     */
    public function process(): array
    {
        $result = $this->request->process();
        
        // Add support features
        $result['features'][] = 'extended_support';
        $result['support_days'] = $this->supportDays;
        $result['support_fee'] = $this->supportFee;
        $result['support_channels'] = ['phone', 'email', 'chat'];
        $result['support_response_time'] = '24 hours';
        
        return $result;
    }
}
