<?php
/**
 * Decorator Pattern Controller
 * 
 * Handles dynamic feature enhancements for service requests
 */

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\DesignPatterns\Structural\Decorator\BasicServiceRequest;
use FixItMati\DesignPatterns\Structural\Decorator\UrgentRequestDecorator;
use FixItMati\DesignPatterns\Structural\Decorator\WarrantyDecorator;
use FixItMati\DesignPatterns\Structural\Decorator\PremiumServiceDecorator;
use FixItMati\DesignPatterns\Structural\Decorator\PhotoDocumentationDecorator;
use FixItMati\DesignPatterns\Structural\Decorator\InspectionReportDecorator;
use FixItMati\DesignPatterns\Structural\Decorator\ExtendedSupportDecorator;
use FixItMati\Models\ServiceRequest as ServiceRequestModel;

class DecoratorController
{
    private ServiceRequestModel $requestModel;
    
    public function __construct()
    {
        $this->requestModel = new ServiceRequestModel();
    }
    
    /**
     * Enhance a request with features
     */
    public function enhanceRequest(Request $request): Response
    {
        $requestId = $request->param('request_id');
        $features = $request->param('features', []);
        
        if (empty($features)) {
            return Response::json([
                'success' => false,
                'message' => 'No features specified'
            ], 400);
        }
        
        try {
            $requestData = $this->requestModel->find($requestId);
            if (!$requestData) {
                return Response::json([
                    'success' => false,
                    'message' => 'Request not found'
                ], 404);
            }
            
            // Start with basic request
            $enhancedRequest = new BasicServiceRequest(
                $requestData,
                (float) ($requestData['estimated_cost'] ?? 0)
            );
            
            // Apply decorators based on requested features
            foreach ($features as $feature => $params) {
                $enhancedRequest = $this->applyDecorator($enhancedRequest, $feature, $params);
            }
            
            // Get enhanced data
            $enhancedData = [
                'description' => $enhancedRequest->getDescription(),
                'cost' => $enhancedRequest->getCost(),
                'data' => $enhancedRequest->getData(),
                'processing_result' => $enhancedRequest->process()
            ];
            
            return Response::json([
                'success' => true,
                'message' => 'Request enhanced successfully',
                'data' => $enhancedData
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to enhance request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Apply decorator to request
     */
    private function applyDecorator($request, string $feature, $params)
    {
        switch ($feature) {
            case 'urgent':
                return new UrgentRequestDecorator($request);
                
            case 'warranty':
                $months = $params['months'] ?? 12;
                return new WarrantyDecorator($request, $months);
                
            case 'premium':
                return new PremiumServiceDecorator($request);
                
            case 'photos':
                $photos = $params['photos'] ?? [];
                return new PhotoDocumentationDecorator($request, $photos);
                
            case 'inspection':
                return new InspectionReportDecorator($request);
                
            case 'support':
                $days = $params['days'] ?? 30;
                return new ExtendedSupportDecorator($request, $days);
                
            default:
                return $request;
        }
    }
    
    /**
     * Get cost estimate with features
     */
    public function getCostEstimate(Request $request): Response
    {
        $requestId = $request->param('request_id');
        $features = $request->param('features', []);
        
        try {
            $requestData = $this->requestModel->find($requestId);
            if (!$requestData) {
                return Response::json([
                    'success' => false,
                    'message' => 'Request not found'
                ], 404);
            }
            
            $baseCost = (float) ($requestData['estimated_cost'] ?? 0);
            
            // Start with basic request
            $enhancedRequest = new BasicServiceRequest(
                $requestData,
                $baseCost
            );
            
            // Apply decorators to calculate cost
            foreach ($features as $feature => $params) {
                $enhancedRequest = $this->applyDecorator($enhancedRequest, $feature, $params);
            }
            
            $totalCost = $enhancedRequest->getCost();
            $additionalCost = $totalCost - $baseCost;
            
            return Response::json([
                'success' => true,
                'data' => [
                    'base_cost' => $baseCost,
                    'additional_cost' => $additionalCost,
                    'total_cost' => $totalCost,
                    'features_applied' => array_keys($features)
                ]
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to calculate estimate: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get available features
     */
    public function getAvailableFeatures(Request $request): Response
    {
        $features = [
            'urgent' => [
                'name' => 'Urgent Priority',
                'description' => '2-hour response time',
                'cost' => 500.0,
                'params' => []
            ],
            'warranty' => [
                'name' => 'Extended Warranty',
                'description' => 'Parts and labor coverage',
                'cost_per_month' => 150.0,
                'params' => ['months' => 12]
            ],
            'premium' => [
                'name' => 'Premium Service',
                'description' => 'Priority scheduling, dedicated technician, quality guarantee',
                'cost' => 1500.0,
                'params' => []
            ],
            'photos' => [
                'name' => 'Photo Documentation',
                'description' => 'Visual evidence and records',
                'cost' => 0.0,
                'params' => ['photos' => []]
            ],
            'inspection' => [
                'name' => 'Detailed Inspection Report',
                'description' => 'Comprehensive diagnostic and recommendations',
                'cost' => 300.0,
                'params' => []
            ],
            'support' => [
                'name' => 'Extended Support',
                'description' => '24-hour response via phone, email, chat',
                'cost_per_day' => 25.0,
                'params' => ['days' => 30]
            ]
        ];
        
        return Response::json([
            'success' => true,
            'data' => [
                'features' => $features
            ]
        ]);
    }
}
