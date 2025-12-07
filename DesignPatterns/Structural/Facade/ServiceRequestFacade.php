<?php

namespace FixItMati\DesignPatterns\Structural\Facade;

use FixItMati\Models\ServiceRequest;
use FixItMati\Models\User;
use FixItMati\DesignPatterns\Behavioral\State\StateFactory;
use FixItMati\Services\NotificationService;

/**
 * ServiceRequestFacade
 * 
 * Facade Pattern: Provides a simplified interface to the complex subsystem
 * of service request management, state transitions, and validations.
 * 
 * This hides the complexity of:
 * - State validation and transitions
 * - User permission checks
 * - Data validation
 * - Timeline management
 * - Notification triggers (Observer Pattern integration)
 */
class ServiceRequestFacade
{
    private ServiceRequest $requestModel;
    private User $userModel;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->requestModel = new ServiceRequest();
        $this->userModel = new User();
        $this->notificationService = NotificationService::getInstance();
    }

    /**
     * Submit a new service request
     * Simplified interface that handles validation and state setup
     */
    public function submitRequest(string $userId, array $data): array
    {
        // Validate user exists
        $user = $this->userModel->find($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        // Validate required fields (matching database schema)
        $required = ['category', 'title', 'description', 'location'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'error' => "Field '$field' is required"];
            }
        }

        // Validate category
        $validCategories = ['water', 'electricity'];
        if (!in_array($data['category'], $validCategories)) {
            return ['success' => false, 'error' => 'Invalid category'];
        }

        // Add user_id and ensure status is set
        $data['user_id'] = $userId;
        $data['status'] = 'pending';
        
        // Set priority if not provided
        if (empty($data['priority'])) {
            $data['priority'] = 'normal';
        }

        // Create request
        $request = $this->requestModel->create($data);
        
        if (!$request) {
            return ['success' => false, 'error' => 'Failed to create request'];
        }

        // Trigger notification event (Observer Pattern)
        $this->notificationService->trigger('request.created', [
            'request' => $request,
            'user' => $user
        ]);

        return [
            'success' => true,
            'request' => $request,
            'message' => 'Request submitted successfully'
        ];
    }

    /**
     * Get request details with timeline
     */
    public function getRequestDetails(string $requestId, string $userId, string $userRole): array
    {
        $request = $this->requestModel->find($requestId);
        
        if (!$request) {
            return ['success' => false, 'error' => 'Request not found'];
        }

        // Check permissions
        $canView = $this->canViewRequest($request, $userId, $userRole);
        if (!$canView) {
            return ['success' => false, 'error' => 'Permission denied'];
        }

        // Get timeline
        $timeline = $this->requestModel->getUpdates($requestId);

        // Get current state info
        $currentState = StateFactory::getState($request['status']);

        return [
            'success' => true,
            'request' => $request,
            'timeline' => $timeline,
            'state_info' => [
                'current' => $request['status'],
                'description' => $currentState->getDescription(),
                'allowed_transitions' => $currentState->getAllowedTransitions()
            ]
        ];
    }

    /**
     * List requests with filters
     */
    public function listRequests(string $userId, string $userRole, array $filters = []): array
    {
        // Apply role-based filters
        if ($userRole === 'customer') {
            // Customers only see their own requests
            $filters['user_id'] = $userId;
        } elseif ($userRole === 'technician') {
            // Technicians see assigned requests
            $filters['assigned_to'] = $userId;
        }
        // Admins see all requests

        $requests = $this->requestModel->getAll($filters);

        return [
            'success' => true,
            'requests' => $requests,
            'count' => count($requests)
        ];
    }

    /**
     * Review and approve request (admin only)
     */
    public function reviewRequest(string $requestId, string $adminId, string $priority, ?string $notes = null): array
    {
        $request = $this->requestModel->find($requestId);
        
        if (!$request) {
            return ['success' => false, 'error' => 'Request not found'];
        }

        // Update priority
        $this->requestModel->update($requestId, ['priority' => $priority]);

        // Change status to reviewed
        $updated = $this->requestModel->updateStatus($requestId, 'reviewed', $adminId, $notes ?? 'Request reviewed and approved');

        if (!$updated) {
            return ['success' => false, 'error' => 'Failed to update status'];
        }

        $updatedRequest = $this->requestModel->find($requestId);

        // Trigger notification event
        $this->notificationService->trigger('request.reviewed', [
            'request' => $updatedRequest,
            'admin_id' => $adminId
        ]);

        return [
            'success' => true,
            'message' => 'Request reviewed successfully',
            'request' => $updatedRequest
        ];
    }

    /**
     * Assign technician to request (admin only)
     */
    public function assignTechnician(string $requestId, string $technicianId, string $adminId, ?string $notes = null): array
    {
        $request = $this->requestModel->find($requestId);
        
        if (!$request) {
            return ['success' => false, 'error' => 'Request not found'];
        }

        // Validate technician exists and has correct role
        $technician = $this->userModel->find($technicianId);
        if (!$technician || $technician->role !== 'technician') {
            return ['success' => false, 'error' => 'Invalid technician'];
        }

        // Update assigned_to
        $this->requestModel->update($requestId, ['assigned_to' => $technicianId]);

        // Change status to assigned
        $updated = $this->requestModel->updateStatus(
            $requestId, 
            'assigned', 
            $adminId, 
            $notes ?? "Assigned to {$technician->full_name}"
        );

        if (!$updated) {
            return ['success' => false, 'error' => 'Failed to assign technician'];
        }

        $updatedRequest = $this->requestModel->find($requestId);

        // Trigger notification event
        $this->notificationService->trigger('request.assigned', [
            'request' => $updatedRequest,
            'technician' => $technician->toArray(),
            'admin_id' => $adminId
        ]);

        return [
            'success' => true,
            'message' => 'Technician assigned successfully',
            'request' => $updatedRequest
        ];
    }

    /**
     * Start work on request (technician only)
     */
    public function startWork(string $requestId, string $technicianId, ?string $notes = null): array
    {
        $request = $this->requestModel->find($requestId);
        
        if (!$request) {
            return ['success' => false, 'error' => 'Request not found'];
        }

        // Verify technician is assigned
        if ($request['assigned_to'] != $technicianId) {
            return ['success' => false, 'error' => 'You are not assigned to this request'];
        }

        // Change status to in_progress
        $updated = $this->requestModel->updateStatus(
            $requestId, 
            'in_progress', 
            $technicianId, 
            $notes ?? 'Work started'
        );

        if (!$updated) {
            return ['success' => false, 'error' => 'Failed to start work'];
        }

        $updatedRequest = $this->requestModel->find($requestId);

        // Trigger notification event
        $this->notificationService->trigger('request.in_progress', [
            'request' => $updatedRequest,
            'technician_id' => $technicianId
        ]);

        return [
            'success' => true,
            'message' => 'Work started',
            'request' => $updatedRequest
        ];
    }

    /**
     * Complete request (technician/admin)
     */
    public function completeRequest(string $requestId, string $userId, ?string $notes = null): array
    {
        $request = $this->requestModel->find($requestId);
        
        if (!$request) {
            return ['success' => false, 'error' => 'Request not found'];
        }

        // Change status to completed
        $updated = $this->requestModel->updateStatus(
            $requestId, 
            'completed', 
            $userId, 
            $notes ?? 'Work completed'
        );

        if (!$updated) {
            return ['success' => false, 'error' => 'Failed to complete request'];
        }

        $updatedRequest = $this->requestModel->find($requestId);

        // Trigger notification event
        $this->notificationService->trigger('request.completed', [
            'request' => $updatedRequest,
            'completed_by' => $userId
        ]);

        return [
            'success' => true,
            'message' => 'Request completed successfully',
            'request' => $updatedRequest
        ];
    }

    /**
     * Cancel request
     */
    public function cancelRequest(string $requestId, string $userId, string $userRole, ?string $reason = null): array
    {
        $request = $this->requestModel->find($requestId);
        
        if (!$request) {
            return ['success' => false, 'error' => 'Request not found'];
        }

        // Check permissions
        $canCancel = $this->canCancelRequest($request, $userId, $userRole);
        if (!$canCancel) {
            return ['success' => false, 'error' => 'Permission denied'];
        }

        // Change status to cancelled
        $updated = $this->requestModel->updateStatus(
            $requestId, 
            'cancelled', 
            $userId, 
            $reason ?? 'Request cancelled'
        );

        if (!$updated) {
            return ['success' => false, 'error' => 'Failed to cancel request'];
        }

        $updatedRequest = $this->requestModel->find($requestId);

        // Trigger notification event
        $this->notificationService->trigger('request.cancelled', [
            'request' => $updatedRequest,
            'cancelled_by' => $userId,
            'reason' => $reason
        ]);

        return [
            'success' => true,
            'message' => 'Request cancelled',
            'request' => $updatedRequest
        ];
    }

    /**
     * Get dashboard statistics
     */
    public function getStatistics(string $userId, string $userRole): array
    {
        $filters = [];

        if ($userRole === 'customer') {
            $filters['user_id'] = $userId;
        } elseif ($userRole === 'technician') {
            $filters['assigned_to'] = $userId;
        }

        $stats = $this->requestModel->getStatistics($filters);

        return [
            'success' => true,
            'statistics' => $stats
        ];
    }

    /**
     * Check if user can view a request
     */
    private function canViewRequest(array $request, string $userId, string $userRole): bool
    {
        if ($userRole === 'admin') {
            return true;
        }

        if ($userRole === 'customer' && $request['user_id'] == $userId) {
            return true;
        }

        if ($userRole === 'technician' && $request['assigned_to'] == $userId) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can cancel a request
     */
    private function canCancelRequest(array $request, string $userId, string $userRole): bool
    {
        if ($userRole === 'admin') {
            return true;
        }

        if ($userRole === 'customer' && $request['user_id'] == $userId) {
            // Customers can only cancel pending or reviewed requests
            return in_array($request['status'], ['pending', 'reviewed']);
        }

        return false;
    }
}
