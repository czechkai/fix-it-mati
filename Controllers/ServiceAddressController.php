<?php

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\Models\ServiceAddress;

/**
 * ServiceAddressController
 * 
 * Handles API requests for service addresses
 */
class ServiceAddressController
{
    private ServiceAddress $model;

    public function __construct()
    {
        $this->model = new ServiceAddress();
    }

    /**
     * Get all addresses for the authenticated user
     * GET /api/service-addresses
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $userId = $user['id'];
        
        $addresses = $this->model->getAllByUser($userId);
        
        return Response::success([
            'addresses' => $addresses,
            'total' => count($addresses)
        ]);
    }

    /**
     * Get a single address by ID
     * GET /api/service-addresses/{id}
     */
    public function show(Request $request, string $id): Response
    {
        $user = $request->user();
        $userId = $user['id'];
        $address = $this->model->getById($id);
        
        if (!$address) {
            return Response::notFound('Address not found');
        }
        
        // Ensure user owns this address
        if ($address['user_id'] !== $userId) {
            return Response::unauthorized('You do not have permission to view this address');
        }
        
        return Response::success(['address' => $address]);
    }

    /**
     * Create a new address
     * POST /api/service-addresses
     */
    public function create(Request $request): Response
    {
        $user = $request->user();
        $userId = $user['id'];
        $data = $request->getBody();
        
        // Validation
        $required = ['label', 'type', 'barangay', 'street'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return Response::badRequest("Field '{$field}' is required");
            }
        }
        
        // Validate type
        if (!in_array($data['type'], ['Residential', 'Commercial'])) {
            return Response::badRequest("Type must be 'Residential' or 'Commercial'");
        }
        
        // Add user_id to data
        $data['user_id'] = $userId;
        
        // Create address
        $address = $this->model->create($data);
        
        if (!$address) {
            return Response::serverError('Failed to create address');
        }
        
        return Response::created($address, 'Address created successfully');
    }

    /**
     * Update an address
     * PUT /api/service-addresses/{id}
     */
    public function update(Request $request, string $id): Response
    {
        $user = $request->user();
        $userId = $user['id'];
        $data = $request->getBody();
        
        // Check if address exists and belongs to user
        $existingAddress = $this->model->getById($id);
        if (!$existingAddress) {
            return Response::notFound('Address not found');
        }
        
        if ($existingAddress['user_id'] !== $userId) {
            return Response::unauthorized('You do not have permission to update this address');
        }
        
        // Validation
        $required = ['label', 'type', 'barangay', 'street'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return Response::badRequest("Field '{$field}' is required");
            }
        }
        
        // Validate type
        if (!in_array($data['type'], ['Residential', 'Commercial'])) {
            return Response::badRequest("Type must be 'Residential' or 'Commercial'");
        }
        
        // Update address
        $updatedAddress = $this->model->update($id, $data);
        
        if (!$updatedAddress) {
            return Response::serverError('Failed to update address');
        }
        
        return Response::success($updatedAddress, 'Address updated successfully');
    }

    /**
     * Set an address as default
     * PATCH /api/service-addresses/{id}/set-default
     */
    public function setDefault(Request $request, string $id): Response
    {
        $user = $request->user();
        $userId = $user['id'];
        
        // Check if address exists and belongs to user
        $address = $this->model->getById($id);
        if (!$address) {
            return Response::notFound('Address not found');
        }
        
        if ($address['user_id'] !== $userId) {
            return Response::unauthorized('You do not have permission to modify this address');
        }
        
        // Set as default
        $success = $this->model->setDefault($id, $userId);
        
        if (!$success) {
            return Response::serverError('Failed to set default address');
        }
        
        // Get updated address
        $updatedAddress = $this->model->getById($id);
        
        return Response::success($updatedAddress, 'Default address updated successfully');
    }

    /**
     * Delete an address
     * DELETE /api/service-addresses/{id}
     */
    public function delete(Request $request, string $id): Response
    {
        $user = $request->user();
        $userId = $user['id'];
        
        // Check if address exists and belongs to user
        $address = $this->model->getById($id);
        if (!$address) {
            return Response::notFound('Address not found');
        }
        
        if ($address['user_id'] !== $userId) {
            return Response::unauthorized('You do not have permission to delete this address');
        }
        
        // Delete address
        $success = $this->model->delete($id, $userId);
        
        if (!$success) {
            return Response::badRequest('Cannot delete your only address or an error occurred');
        }
        
        return Response::success(null, 'Address deleted successfully');
    }

    /**
     * Get the default address
     * GET /api/service-addresses/default
     */
    public function getDefault(Request $request): Response
    {
        $user = $request->user();
        $userId = $user['id'];
        
        $address = $this->model->getDefaultByUser($userId);
        
        if (!$address) {
            return Response::notFound('No default address set');
        }
        
        return Response::success(['address' => $address]);
    }
}
