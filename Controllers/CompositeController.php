<?php
/**
 * Composite Pattern Controller
 * 
 * Handles batch operations on grouped service requests
 */

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\DesignPatterns\Structural\Composite\RequestGroup;
use FixItMati\DesignPatterns\Structural\Composite\SingleRequest;
use FixItMati\Models\ServiceRequest;

class CompositeController
{
    private ServiceRequest $requestModel;
    
    public function __construct()
    {
        $this->requestModel = new ServiceRequest();
    }
    
    /**
     * Create a request group
     */
    public function createGroup(Request $request): Response
    {
        $requestIds = $request->param('request_ids', []);
        $groupName = $request->param('group_name', 'Request Group');
        
        if (empty($requestIds)) {
            return Response::json([
                'success' => false,
                'message' => 'No requests provided'
            ], 400);
        }
        
        try {
            $groupId = uniqid('group_');
            $group = new RequestGroup($groupId, $groupName);
            
            foreach ($requestIds as $requestId) {
                $requestData = $this->requestModel->find($requestId);
                if ($requestData) {
                    $singleRequest = new SingleRequest($requestId);
                    $group->add($singleRequest);
                }
            }
            
            return Response::json([
                'success' => true,
                'message' => 'Group created successfully',
                'data' => [
                    'group_id' => $groupId,
                    'group_name' => $groupName,
                    'total_requests' => $group->getCount(),
                    'info' => $group->getInfo()
                ]
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to create group: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update status of all requests in a group
     */
    public function updateGroupStatus(Request $request): Response
    {
        $requestIds = $request->param('request_ids', []);
        $newStatus = $request->param('status');
        $notes = $request->param('notes');
        
        if (empty($requestIds)) {
            return Response::json([
                'success' => false,
                'message' => 'No requests provided'
            ], 400);
        }
        
        if (!$newStatus) {
            return Response::json([
                'success' => false,
                'message' => 'Status is required'
            ], 400);
        }
        
        try {
            $groupId = uniqid('group_');
            $group = new RequestGroup($groupId, 'Batch Update');
            
            foreach ($requestIds as $requestId) {
                $requestData = $this->requestModel->find($requestId);
                if ($requestData) {
                    $singleRequest = new SingleRequest($requestId);
                    $group->add($singleRequest);
                }
            }
            
            // Update all requests in the group
            $group->updateStatus($newStatus, $notes);
            
            return Response::json([
                'success' => true,
                'message' => "Updated {$group->getCount()} requests to status: {$newStatus}",
                'data' => [
                    'total_updated' => $group->getCount(),
                    'request_ids' => $group->getAllRequestIds(),
                    'new_status' => $newStatus
                ]
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to update group: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get group information
     */
    public function getGroupInfo(Request $request): Response
    {
        $requestIds = $request->param('request_ids', []);
        
        if (empty($requestIds)) {
            return Response::json([
                'success' => false,
                'message' => 'No requests provided'
            ], 400);
        }
        
        try {
            $groupId = uniqid('group_');
            $group = new RequestGroup($groupId, 'Info Group');
            
            foreach ($requestIds as $requestId) {
                $requestData = $this->requestModel->find($requestId);
                if ($requestData) {
                    $singleRequest = new SingleRequest($requestId);
                    $group->add($singleRequest);
                }
            }
            
            return Response::json([
                'success' => true,
                'data' => [
                    'total_requests' => $group->getCount(),
                    'request_ids' => $group->getAllRequestIds(),
                    'info' => $group->getInfo()
                ]
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to get group info: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create nested groups
     */
    public function createNestedGroup(Request $request): Response
    {
        $groupData = $request->param('groups', []);
        
        if (empty($groupData)) {
            return Response::json([
                'success' => false,
                'message' => 'No groups provided'
            ], 400);
        }
        
        try {
            $mainGroupId = uniqid('main_group_');
            $mainGroup = new RequestGroup($mainGroupId, 'Main Group');
            
            foreach ($groupData as $subGroupData) {
                $subGroupId = uniqid('sub_group_');
                $subGroup = new RequestGroup($subGroupId, $subGroupData['name']);
                
                foreach ($subGroupData['request_ids'] as $requestId) {
                    $requestData = $this->requestModel->find($requestId);
                    if ($requestData) {
                        $singleRequest = new SingleRequest($requestId);
                        $subGroup->add($singleRequest);
                    }
                }
                
                $mainGroup->add($subGroup);
            }
            
            return Response::json([
                'success' => true,
                'message' => 'Nested group created successfully',
                'data' => [
                    'total_requests' => $mainGroup->getCount(),
                    'info' => $mainGroup->getInfo()
                ]
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to create nested group: ' . $e->getMessage()
            ], 500);
        }
    }
}
