<?php

namespace FixItMati\DesignPatterns\Structural\Facade;

use FixItMati\Models\ServiceRequest;
use FixItMati\Models\User;
use FixItMati\Models\TechnicianTeam;
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
    private TechnicianTeam $technicianModel;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->requestModel = new ServiceRequest();
        $this->userModel = new User();
        $this->technicianModel = new TechnicianTeam();
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

        // Validate technician - check if it's a user with technician role OR a team from technicians table
        $technician = $this->userModel->find($technicianId);
        $technicianName = null;
        
        if ($technician && ($technician->role === 'technician' || $technician->role === 'admin' || $technician->role === 'staff')) {
            // Valid user with technician/admin/staff role
            $technicianName = $technician->full_name ?? ($technician->first_name . ' ' . $technician->last_name);
        } else {
            // Check if it's a team ID from technicians table
            $team = $this->technicianModel->find($technicianId);
            if (!$team) {
                return ['success' => false, 'error' => 'Invalid technician'];
            }
            $technicianName = $team['lead'] ?? $team['name'];
        }

        // Update assigned_technician_id
        $this->requestModel->update($requestId, ['assigned_technician_id' => $technicianId]);

        // Get current status and determine next status
        $currentStatus = $request['status'];
        $nextStatus = 'assigned';
        
        // If status is pending, first move to reviewed, then to assigned
        if ($currentStatus === 'pending') {
            $this->requestModel->updateStatus($requestId, 'reviewed', $adminId, 'Request reviewed and approved');
        }
        
        // Change status to assigned
        $updated = $this->requestModel->updateStatus(
            $requestId, 
            $nextStatus, 
            $adminId, 
            $notes ?? "Assigned to {$technicianName}"
        );

        if (!$updated) {
            return ['success' => false, 'error' => 'Failed to assign technician'];
        }

        $updatedRequest = $this->requestModel->find($requestId);

        // Trigger notification event (only if it's a user, not a team)
        if ($technician) {
            $this->notificationService->trigger('request.assigned', [
                'request' => $updatedRequest,
                'technician' => $technician->toArray(),
                'admin_id' => $adminId
            ]);
        }

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
        if ($request['assigned_technician_id'] != $technicianId) {
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

        // Change status to resolved
        $updated = $this->requestModel->updateStatus(
            $requestId, 
            'resolved', 
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

    /**
     * Get resolved/completed service requests
     */
    public function getResolvedRequests(string $userId, string $userRole): array
    {
        $filters = ['status' => 'completed'];

        // Filter by user role
        if ($userRole === 'customer') {
            $filters['user_id'] = $userId;
        } elseif ($userRole === 'technician') {
            $filters['assigned_to'] = $userId;
        }
        // Admin can see all

        $requests = $this->requestModel->findAll($filters);

        // Enrich with additional data
        $enrichedRequests = [];
        foreach ($requests as $req) {
            $enrichedRequests[] = $this->enrichRequestData($req);
        }

        return $enrichedRequests;
    }

    /**
     * Submit rating and feedback for a resolved request
     */
    public function submitRating(string $requestId, string $userId, int $rating, string $feedback = ''): array
    {
        // Get the request
        $request = $this->requestModel->find($requestId);
        if (!$request) {
            return ['success' => false, 'error' => 'Request not found'];
        }

        // Verify the request is completed
        if ($request['status'] !== 'completed') {
            return ['success' => false, 'error' => 'Can only rate completed requests'];
        }

        // Verify user owns this request
        if ($request['user_id'] !== $userId) {
            return ['success' => false, 'error' => 'You can only rate your own requests'];
        }

        // Check if already rated
        if (!empty($request['rating'])) {
            return ['success' => false, 'error' => 'This request has already been rated'];
        }

        // Update the request with rating and feedback
        $updated = $this->requestModel->update($requestId, [
            'rating' => $rating,
            'feedback' => $feedback,
            'rated_at' => date('Y-m-d H:i:s')
        ]);

        if (!$updated) {
            return ['success' => false, 'error' => 'Failed to save rating'];
        }

        // Get updated request
        $updatedRequest = $this->requestModel->find($requestId);

        return [
            'success' => true,
            'message' => 'Rating submitted successfully',
            'data' => $this->enrichRequestData($updatedRequest)
        ];
    }

    /**
     * Report a recurring issue based on a resolved request
     */
    public function reportRecurringIssue(string $originalRequestId, string $userId, array $data): array
    {
        // Get the original request
        $originalRequest = $this->requestModel->find($originalRequestId);
        if (!$originalRequest) {
            return ['success' => false, 'error' => 'Original request not found'];
        }

        // Verify user owns the original request
        if ($originalRequest['user_id'] !== $userId) {
            return ['success' => false, 'error' => 'You can only report recurring issues for your own requests'];
        }

        // Verify the original request is completed
        if ($originalRequest['status'] !== 'completed') {
            return ['success' => false, 'error' => 'Can only report recurring issues for completed requests'];
        }

        // Create new request based on original
        $newRequestData = [
            'user_id' => $userId,
            'category' => $data['category'] ?? $originalRequest['category'],
            'title' => $data['title'] ?? "Recurring: " . $originalRequest['title'],
            'description' => $data['description'] ?? "This is a recurring issue from ticket #{$originalRequest['ticket_number']}. " . $originalRequest['description'],
            'location' => $data['address'] ?? $originalRequest['location'],
            'priority' => $data['priority'] ?? 'normal',
            'original_request_id' => $originalRequestId
        ];

        // Submit the new request
        $result = $this->submitRequest($userId, $newRequestData);

        if (!$result['success']) {
            return $result;
        }

        return [
            'success' => true,
            'message' => 'Recurring issue reported successfully',
            'request' => $result['request']
        ];
    }

    /**
     * Enrich request data with user info and formatted fields
     */
    private function enrichRequestData(array $request): array
    {
        // Add user information
        if (!empty($request['user_id'])) {
            $user = $this->userModel->find($request['user_id']);
            if ($user) {
                $request['user_name'] = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
                $request['user_email'] = $user['email'] ?? '';
            }
        }

        // Add technician information
        if (!empty($request['assigned_to'])) {
            $technician = $this->userModel->find($request['assigned_to']);
            if ($technician) {
                $request['resolved_by'] = ($technician['first_name'] ?? '') . ' ' . ($technician['last_name'] ?? '');
            }
        }

        return $request;
    }
}
