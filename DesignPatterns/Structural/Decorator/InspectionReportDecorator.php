<?php
/**
 * Decorator Pattern - Inspection Report Decorator
 * 
 * Adds detailed inspection report
 */

namespace FixItMati\DesignPatterns\Structural\Decorator;

class InspectionReportDecorator extends RequestDecorator
{
    private float $inspectionFee = 300.0;
    
    /**
     * Get enhanced description
     */
    public function getDescription(): string
    {
        return $this->request->getDescription() . " (📋 Detailed inspection report)";
    }
    
    /**
     * Get cost with inspection fee
     */
    public function getCost(): float
    {
        return $this->request->getCost() + $this->inspectionFee;
    }
    
    /**
     * Get data with inspection details
     */
    public function getData(): array
    {
        $data = $this->request->getData();
        $data['inspection'] = [
            'fee' => $this->inspectionFee,
            'type' => 'detailed_diagnostic',
            'includes' => [
                'comprehensive_assessment',
                'root_cause_analysis',
                'preventive_recommendations',
                'photo_documentation',
                'written_report'
            ]
        ];
        return $data;
    }
    
    /**
     * Process with inspection features
     */
    public function process(): array
    {
        $result = $this->request->process();
        
        // Add inspection features
        $result['features'][] = 'detailed_inspection';
        $result['inspection_fee'] = $this->inspectionFee;
        $result['report_type'] = 'comprehensive';
        $result['includes_recommendations'] = true;
        $result['written_report'] = true;
        
        return $result;
    }
}
