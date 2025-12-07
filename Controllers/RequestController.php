<?php

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\DesignPatterns\Structural\Facade\ServiceRequestFacade;

/**
 * RequestController
 * 
 * Handles HTTP requests for service request operations.
 * Uses Facade pattern to simplify complex operations.
 */
class RequestController
{
    private ServiceRequestFacade $facade;

    public function __construct()
    {
        $this->facade = new ServiceRequestFacade();
    }

    /**
     * Create a new service request
     * POST /api/requests
     */
    public function create(Request $request): Response
    {
        $user = $request->user();
        $data = $request->all();

        // Validate input
        $validation = $this->validateCreateRequest($data);
        if (!$validation['valid']) {
            return Response::error($validation['errors'], 400);
        }

        // Use facade to create request
        $result = $this->facade->submitRequest($user['id'], $data);

        if (!$result['success']) {
            return Response::error($result['error'], 400);
        }

        return Response::created($result['request'], $result['message']);
    }

    /**
     * Get all requests (filtered by user role)
     * GET /api/requests
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        // Get query parameters for filtering
        $filters = [
            'status' => $request->query('status'),
            'category' => $request->query('category'),
            'priority' => $request->query('priority'),
            'limit' => $request->query('limit', 20),
            'offset' => $request->query('offset', 0),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'DESC')
        ];

        // Remove null values
        $filters = array_filter($filters, fn($value) => $value !== null);

        $result = $this->facade->listRequests($user['id'], $user['role'], $filters);

        return Response::success($result);
    }

    /**
     * Get single request with details
     * GET /api/requests/{id}
     */
    public function show(Request $request): Response
    {
        $user = $request->user();
        $id = $request->param('id');

        if (!$id || !is_numeric($id)) {
            return Response::error('Invalid request ID', 400);
        }

        $result = $this->facade->getRequestDetails((int)$id, $user['id'], $user['role']);

        if (!$result['success']) {
            if ($result['error'] === 'Request not found') {
                return Response::notFound($result['error']);
            }
            return Response::forbidden($result['error']);
        }

        return Response::success($result);
    }

    /**
     * Update request details
     * PATCH /api/requests/{id}
     */
    public function update(Request $request): Response
    {
        $user = $request->user();
        $id = $request->param('id');
        $data = $request->all();

        if (!$id || !is_numeric($id)) {
            return Response::error('Invalid request ID', 400);
        }

        // Only admins can update request details
        if ($user['role'] !== 'admin') {
            return Response::forbidden('Only admins can update request details');
        }

        // Get current request
        $result = $this->facade->getRequestDetails((int)$id, $user['id'], $user['role']);
        if (!$result['success']) {
            return Response::notFound('Request not found');
        }

        // Update using model directly (simple updates don't need facade)
        $requestModel = new \FixItMati\Models\ServiceRequest();
        $updated = $requestModel->update((int)$id, $data);

        if (!$updated) {
            return Response::error('Failed to update request', 400);
        }

        $updatedRequest = $requestModel->find((int)$id);
        return Response::success(['request' => $updatedRequest], 'Request updated successfully');
    }

    /**
     * Review request (admin only)
     * POST /api/requests/{id}/review
     */
    public function review(Request $request): Response
    {
        $user = $request->user();
        $id = $request->param('id');
        $data = $request->all();

        if (!$id || !is_numeric($id)) {
            return Response::error('Invalid request ID', 400);
        }

        // Only admins can review
        if ($user['role'] !== 'admin') {
            return Response::forbidden('Only admins can review requests');
        }

        $priority = $data['priority'] ?? 'normal';
        $notes = $data['notes'] ?? null;

        $result = $this->facade->reviewRequest((int)$id, $user['id'], $priority, $notes);

        if (!$result['success']) {
            return Response::error($result['error'], 400);
        }

        return Response::success($result, $result['message']);
    }

    /**
     * Assign technician (admin only)
     * POST /api/requests/{id}/assign
     */
    public function assign(Request $request): Response
    {
        $user = $request->user();
        $id = $request->param('id');
        $data = $request->all();

        if (!$id || !is_numeric($id)) {
            return Response::error('Invalid request ID', 400);
        }

        // Only admins can assign
        if ($user['role'] !== 'admin') {
            return Response::forbidden('Only admins can assign technicians');
        }

        if (empty($data['technician_id'])) {
            return Response::error('Technician ID is required', 400);
        }

        $notes = $data['notes'] ?? null;

        $result = $this->facade->assignTechnician(
            (int)$id, 
            (int)$data['technician_id'], 
            $user['id'], 
            $notes
        );

        if (!$result['success']) {
            return Response::error($result['error'], 400);
        }

        return Response::success($result, $result['message']);
    }

    /**
     * Start work on request (technician only)
     * POST /api/requests/{id}/start
     */
    public function start(Request $request): Response
    {
        $user = $request->user();
        $id = $request->param('id');
        $data = $request->all();

        if (!$id || !is_numeric($id)) {
            return Response::error('Invalid request ID', 400);
        }

        // Only technicians can start work
        if ($user['role'] !== 'technician') {
            return Response::forbidden('Only technicians can start work');
        }

        $notes = $data['notes'] ?? null;

        $result = $this->facade->startWork((int)$id, $user['id'], $notes);

        if (!$result['success']) {
            return Response::error($result['error'], 400);
        }

        return Response::success($result, $result['message']);
    }

    /**
     * Complete request (technician/admin)
     * POST /api/requests/{id}/complete
     */
    public function complete(Request $request): Response
    {
        $user = $request->user();
        $id = $request->param('id');
        $data = $request->all();

        if (!$id || !is_numeric($id)) {
            return Response::error('Invalid request ID', 400);
        }

        // Only technicians and admins can complete
        if (!in_array($user['role'], ['technician', 'admin'])) {
            return Response::forbidden('Only technicians and admins can complete requests');
        }

        $notes = $data['notes'] ?? null;

        $result = $this->facade->completeRequest((int)$id, $user['id'], $notes);

        if (!$result['success']) {
            return Response::error($result['error'], 400);
        }

        return Response::success($result, $result['message']);
    }

    /**
     * Cancel request
     * DELETE /api/requests/{id}
     */
    public function cancel(Request $request): Response
    {
        $user = $request->user();
        $id = $request->param('id');
        $data = $request->all();

        if (!$id || !is_numeric($id)) {
            return Response::error('Invalid request ID', 400);
        }

        $reason = $data['reason'] ?? null;

        $result = $this->facade->cancelRequest((int)$id, $user['id'], $user['role'], $reason);

        if (!$result['success']) {
            if ($result['error'] === 'Request not found') {
                return Response::notFound($result['error']);
            }
            return Response::forbidden($result['error']);
        }

        return Response::success($result, $result['message']);
    }

    /**
     * Get request statistics
     * GET /api/requests/statistics
     */
    public function statistics(Request $request): Response
    {
        $user = $request->user();

        $result = $this->facade->getStatistics($user['id'], $user['role']);

        return Response::success($result);
    }

    /**
     * Validate create request data
     */
    private function validateCreateRequest(array $data): array
    {
        $errors = [];

        $required = ['category', 'title', 'description', 'location'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[] = "Field '$field' is required";
            }
        }

        if (!empty($data['category'])) {
            $validCategories = ['water', 'electricity'];
            if (!in_array($data['category'], $validCategories)) {
                $errors[] = "Invalid category. Must be one of: " . implode(', ', $validCategories);
            }
        }
        
        if (!empty($data['priority'])) {
            $validPriorities = ['low', 'normal', 'medium', 'high', 'urgent'];
            if (!in_array($data['priority'], $validPriorities)) {
                $errors[] = "Invalid priority. Must be one of: " . implode(', ', $validPriorities);
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
