<?php
/**
 * Request Processor Controller
 * 
 * Handles request processing workflows (Template Method Pattern)
 */

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\DesignPatterns\Behavioral\TemplateMethod\NewRequestProcessor;
use FixItMati\DesignPatterns\Behavioral\TemplateMethod\AssignmentProcessor;
use FixItMati\DesignPatterns\Behavioral\TemplateMethod\CompletionProcessor;

class ProcessorController
{
    /**
     * Process new request
     */
    public function processNewRequest(Request $request): Response
    {
        $requestId = $request->param('request_id');
        
        if (!$requestId) {
            return Response::json([
                'success' => false,
                'message' => 'Request ID is required'
            ], 400);
        }
        
        try {
            $processor = new NewRequestProcessor();
            $result = $processor->processRequest($requestId);
            
            return Response::json($result);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process technician assignment
     */
    public function processAssignment(Request $request): Response
    {
        $requestId = $request->param('request_id');
        $technicianId = $request->param('technician_id');
        
        if (!$requestId || !$technicianId) {
            return Response::json([
                'success' => false,
                'message' => 'Request ID and technician ID are required'
            ], 400);
        }
        
        try {
            $processor = new AssignmentProcessor();
            $processor->setTechnicianId($technicianId);
            $result = $processor->processRequest($requestId);
            
            return Response::json($result);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Assignment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process request completion
     */
    public function processCompletion(Request $request): Response
    {
        $requestId = $request->param('request_id');
        $actualCost = $request->param('actual_cost');
        $notes = $request->param('notes');
        $materials = $request->param('materials', []);
        
        if (!$requestId || !$actualCost) {
            return Response::json([
                'success' => false,
                'message' => 'Request ID and actual cost are required'
            ], 400);
        }
        
        try {
            $processor = new CompletionProcessor();
            $processor->setCompletionData([
                'actual_cost' => (float) $actualCost,
                'notes' => $notes,
                'materials' => $materials
            ]);
            
            $result = $processor->processRequest($requestId);
            
            return Response::json($result);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Completion processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get available processors
     */
    public function getProcessors(Request $request): Response
    {
        return Response::json([
            'success' => true,
            'data' => [
                'processors' => [
                    [
                        'type' => 'new_request',
                        'name' => 'New Request Processor',
                        'description' => 'Process newly submitted requests',
                        'steps' => [
                            'Load request',
                            'Validate',
                            'Check similar requests',
                            'Determine priority',
                            'Estimate cost',
                            'Create timeline',
                            'Send notifications',
                            'Log operation'
                        ]
                    ],
                    [
                        'type' => 'assignment',
                        'name' => 'Assignment Processor',
                        'description' => 'Assign technician to requests',
                        'steps' => [
                            'Load request',
                            'Validate',
                            'Check technician availability',
                            'Assign technician',
                            'Update status',
                            'Schedule visit',
                            'Send notifications',
                            'Log operation'
                        ]
                    ],
                    [
                        'type' => 'completion',
                        'name' => 'Completion Processor',
                        'description' => 'Complete service requests',
                        'steps' => [
                            'Load request',
                            'Validate',
                            'Verify work items',
                            'Mark as completed',
                            'Generate report',
                            'Create invoice',
                            'Send notifications',
                            'Log operation'
                        ]
                    ]
                ]
            ]
        ]);
    }
}
