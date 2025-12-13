<?php

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\Models\LinkedMeter;

/**
 * LinkedMeterController
 * 
 * Handles API requests for linked utility meters
 */
class LinkedMeterController
{
    private LinkedMeter $model;

    public function __construct()
    {
        $this->model = new LinkedMeter();
    }

    /**
     * Get all meters for the authenticated user
     * GET /api/linked-meters
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $userId = $user['id'];
        
        $meters = $this->model->getAllByUser($userId);
        
        return Response::success([
            'meters' => $meters,
            'total' => count($meters)
        ]);
    }

    /**
     * Get a single meter by ID
     * GET /api/linked-meters/{id}
     */
    public function show(Request $request, string $id): Response
    {
        $user = $request->user();
        $userId = $user['id'];
        $meter = $this->model->getById($id);
        
        if (!$meter) {
            return Response::notFound('Meter not found');
        }
        
        // Ensure user owns this meter
        if ($meter['user_id'] !== $userId) {
            return Response::unauthorized('You do not have permission to view this meter');
        }
        
        return Response::success(['meter' => $meter]);
    }

    /**
     * Link a new meter
     * POST /api/linked-meters
     */
    public function create(Request $request): Response
    {
        $user = $request->user();
        $userId = $user['id'];
        $data = $request->getBody();
        
        // Validation
        $required = ['provider', 'meter_type', 'account_number', 'account_holder_name'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return Response::badRequest("Field '{$field}' is required");
            }
        }
        
        // Validate meter type
        if (!in_array($data['meter_type'], ['water', 'electricity'])) {
            return Response::badRequest("Meter type must be 'water' or 'electricity'");
        }
        
        // Check if account already linked
        if ($this->model->accountExists($userId, $data['account_number'])) {
            return Response::badRequest('This account number is already linked to your profile');
        }
        
        // Add user_id to data
        $data['user_id'] = $userId;
        
        // Create meter
        $meter = $this->model->create($data);
        
        if (!$meter) {
            return Response::serverError('Failed to link meter');
        }
        
        return Response::created($meter, 'Meter linked successfully');
    }

    /**
     * Update a meter
     * PUT /api/linked-meters/{id}
     */
    public function update(Request $request, string $id): Response
    {
        $user = $request->user();
        $userId = $user['id'];
        $data = $request->getBody();
        
        // Check if meter exists and belongs to user
        $existingMeter = $this->model->getById($id);
        if (!$existingMeter) {
            return Response::notFound('Meter not found');
        }
        
        if ($existingMeter['user_id'] !== $userId) {
            return Response::unauthorized('You do not have permission to update this meter');
        }
        
        // Validation
        $required = ['provider'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return Response::badRequest("Field '{$field}' is required");
            }
        }
        
        // Update meter
        $updatedMeter = $this->model->update($id, $data);
        
        if (!$updatedMeter) {
            return Response::serverError('Failed to update meter');
        }
        
        return Response::success($updatedMeter, 'Meter updated successfully');
    }

    /**
     * Delete a meter
     * DELETE /api/linked-meters/{id}
     */
    public function delete(Request $request, string $id): Response
    {
        $user = $request->user();
        $userId = $user['id'];
        
        // Check if meter exists and belongs to user
        $meter = $this->model->getById($id);
        if (!$meter) {
            return Response::notFound('Meter not found');
        }
        
        if ($meter['user_id'] !== $userId) {
            return Response::unauthorized('You do not have permission to delete this meter');
        }
        
        // Delete meter
        $success = $this->model->delete($id, $userId);
        
        if (!$success) {
            return Response::serverError('Failed to unlink meter');
        }
        
        return Response::success(null, 'Meter unlinked successfully');
    }

    /**
     * Get meters by type
     * GET /api/linked-meters/type/{type}
     */
    public function getByType(Request $request, string $type): Response
    {
        $user = $request->user();
        $userId = $user['id'];
        
        if (!in_array($type, ['water', 'electricity'])) {
            return Response::badRequest("Type must be 'water' or 'electricity'");
        }
        
        $meters = $this->model->getByType($userId, $type);
        
        return Response::success([
            'meters' => $meters,
            'type' => $type,
            'total' => count($meters)
        ]);
    }
}
