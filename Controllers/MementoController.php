<?php
/**
 * Memento Pattern Controller
 * 
 * Handles request state snapshots and restoration
 */

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\DesignPatterns\Behavioral\Memento\RequestOriginator;
use FixItMati\DesignPatterns\Behavioral\Memento\RequestCaretaker;
use FixItMati\Models\ServiceRequest;

class MementoController
{
    private RequestCaretaker $caretaker;
    private ServiceRequest $requestModel;
    
    public function __construct()
    {
        $this->caretaker = new RequestCaretaker();
        $this->requestModel = new ServiceRequest();
    }
    
    /**
     * Create a snapshot
     */
    public function createSnapshot(Request $request): Response
    {
        $requestId = $request->param('request_id');
        $label = $request->param('label', 'Snapshot');
        
        try {
            $requestData = $this->requestModel->find($requestId);
            if (!$requestData) {
                return Response::json([
                    'success' => false,
                    'message' => 'Request not found'
                ], 404);
            }
            
            $originator = new RequestOriginator($requestId);
            $memento = $originator->createMemento($label);
            
            // Save with unique key
            $snapshotKey = $requestId . '_' . time();
            $this->caretaker->saveMemento($snapshotKey, $memento);
            
            return Response::json([
                'success' => true,
                'message' => 'Snapshot created successfully',
                'data' => [
                    'snapshot_key' => $snapshotKey,
                    'timestamp' => $memento->getTimestamp(),
                    'label' => $memento->getLabel()
                ]
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to create snapshot: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * List all snapshots for a request
     */
    public function listSnapshots(Request $request): Response
    {
        $requestId = $request->param('request_id');
        
        try {
            $allSnapshots = $this->caretaker->listSnapshots();
            
            // Filter snapshots for this request
            $requestSnapshots = array_filter($allSnapshots, function($info, $key) use ($requestId) {
                return strpos($key, $requestId . '_') === 0;
            }, ARRAY_FILTER_USE_BOTH);
            
            return Response::json([
                'success' => true,
                'data' => [
                    'request_id' => $requestId,
                    'snapshots' => array_values($requestSnapshots)
                ]
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to list snapshots: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Restore from a snapshot
     */
    public function restoreSnapshot(Request $request): Response
    {
        $snapshotKey = $request->param('snapshot_key');
        $requestId = $request->param('request_id');
        
        try {
            $memento = $this->caretaker->getMemento($snapshotKey);
            if (!$memento) {
                return Response::json([
                    'success' => false,
                    'message' => 'Snapshot not found'
                ], 404);
            }
            
            $originator = new RequestOriginator($requestId);
            $restored = $originator->restoreFromMemento($memento);
            
            if (!$restored) {
                return Response::json([
                    'success' => false,
                    'message' => 'Failed to restore request state'
                ], 500);
            }
            
            // Get the restored data
            $requestData = $this->requestModel->find($requestId);
            
            return Response::json([
                'success' => true,
                'message' => 'Snapshot restored successfully',
                'data' => $requestData
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to restore snapshot: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete a snapshot
     */
    public function deleteSnapshot(Request $request): Response
    {
        $snapshotKey = $request->param('snapshot_key');
        
        try {
            $removed = $this->caretaker->removeMemento($snapshotKey);
            
            if (!$removed) {
                return Response::json([
                    'success' => false,
                    'message' => 'Snapshot not found'
                ], 404);
            }
            
            return Response::json([
                'success' => true,
                'message' => 'Snapshot deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to delete snapshot: ' . $e->getMessage()
            ], 500);
        }
    }
}
