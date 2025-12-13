<?php

namespace FixItMati\Models;

use FixItMati\Core\Database;
use FixItMati\DesignPatterns\Behavioral\State\StateFactory;

/**
 * ServiceRequest Model
 * 
 * Represents a service request in the system.
 * Handles CRUD operations and state management.
 */
class ServiceRequest
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new service request
     */
    public function create(array $data): ?array
    {
        $sql = "INSERT INTO service_requests (
            user_id, category, title, description,
            location, priority, status
        ) VALUES (
            :user_id, :category, :title, :description,
            :location, :priority, :status
        ) RETURNING *";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $data['user_id'],
                'category' => $data['category'],
                'title' => $data['title'],
                'description' => $data['description'],
                'location' => $data['location'],
                'priority' => $data['priority'] ?? 'normal',
                'status' => 'pending'
            ]);

            $request = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($request) {
                // Log initial state in request_updates
                $this->addUpdate($request['id'], $data['user_id'], null, 'pending', 'Request submitted');

                // Trigger state onEnter (if StateFactory is available)
                if (class_exists('FixItMati\DesignPatterns\Behavioral\State\StateFactory')) {
                    $state = StateFactory::getState('pending');
                    $state->onEnter($request);
                }
            }

            return $request;
        } catch (\PDOException $e) {
            error_log("Error creating service request: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find request by ID
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT sr.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name, 
                       u.email as customer_email,
                       CONCAT(t.first_name, ' ', t.last_name) as technician_name, 
                       t.email as technician_email
                FROM service_requests sr
                LEFT JOIN users u ON sr.user_id = u.id
                LEFT JOIN users t ON sr.assigned_to = t.id
                WHERE sr.id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $request = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($request) {
                // Parse photos array
                $request['photos'] = $this->parsePhotosArray($request['photos']);
                // Parse before_images array
                $request['before_images'] = $this->parsePhotosArray($request['before_images'] ?? null);
                // Parse after_images array
                $request['after_images'] = $this->parsePhotosArray($request['after_images'] ?? null);
            }

            return $request ?: null;
        } catch (\PDOException $e) {
            error_log("Error finding service request: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find request by tracking number
     */
    public function findByTrackingNumber(string $trackingNumber): ?array
    {
        $sql = "SELECT sr.*, 
                       u.name as customer_name, u.email as customer_email,
                       t.name as technician_name, t.email as technician_email
                FROM service_requests sr
                LEFT JOIN users u ON sr.user_id = u.id
                LEFT JOIN users t ON sr.assigned_to = t.id
                WHERE sr.tracking_number = :tracking_number";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['tracking_number' => $trackingNumber]);
            $request = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($request) {
                $request['photos'] = $this->parsePhotosArray($request['photos']);
            }

            return $request ?: null;
        } catch (\PDOException $e) {
            error_log("Error finding service request by tracking number: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all requests with optional filters
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT sr.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as customer_name, 
                       u.email as customer_email,
                       CONCAT(t.first_name, ' ', t.last_name) as technician_name
                FROM service_requests sr
                LEFT JOIN users u ON sr.user_id = u.id
                LEFT JOIN users t ON sr.assigned_to = t.id
                WHERE 1=1";

        $params = [];

        // Apply filters
        if (!empty($filters['user_id'])) {
            $sql .= " AND sr.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND sr.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['category'])) {
            $sql .= " AND sr.category = :category";
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['priority'])) {
            $sql .= " AND sr.priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        if (!empty($filters['assigned_to'])) {
            $sql .= " AND sr.assigned_to = :assigned_to";
            $params['assigned_to'] = $filters['assigned_to'];
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'DESC';
        $sql .= " ORDER BY sr.$sortBy $sortOrder";

        // Pagination
        if (isset($filters['limit'])) {
            $sql .= " LIMIT :limit";
            $params['limit'] = (int)$filters['limit'];

            if (isset($filters['offset'])) {
                $sql .= " OFFSET :offset";
                $params['offset'] = (int)$filters['offset'];
            }
        }

        try {
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters with proper types
            foreach ($params as $key => $value) {
                $type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue(":$key", $value, $type);
            }
            
            $stmt->execute();
            $requests = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Parse arrays for each request
            foreach ($requests as &$request) {
                $request['photos'] = $this->parsePhotosArray($request['photos'] ?? null);
                $request['before_images'] = $this->parsePhotosArray($request['before_images'] ?? null);
                $request['after_images'] = $this->parsePhotosArray($request['after_images'] ?? null);
            }

            return $requests;
        } catch (\PDOException $e) {
            error_log("Error getting service requests: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Alias for getAll - used by Facade
     */
    public function findAll(array $filters = []): array
    {
        return $this->getAll($filters);
    }

    /**
     * Update request status with state validation
     */
    public function updateStatus(int $id, string $newStatus, int $userId, ?string $notes = null): bool
    {
        // Get current request
        $request = $this->find($id);
        if (!$request) {
            return false;
        }

        $currentStatus = $request['status'];

        // Validate state transition
        $currentState = StateFactory::getState($currentStatus);
        if (!$currentState->canTransitionTo($newStatus)) {
            error_log("Invalid state transition from $currentStatus to $newStatus");
            return false;
        }

        // Trigger onExit for current state
        $currentState->onExit($request);

        // Update status
        $sql = "UPDATE service_requests SET status = :status WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'status' => $newStatus,
                'id' => $id
            ]);

            if ($result) {
                // Log update
                $this->addUpdate($id, $userId, $currentStatus, $newStatus, $notes);

                // Trigger onEnter for new state
                $newState = StateFactory::getState($newStatus);
                $updatedRequest = $this->find($id);
                $newState->onEnter($updatedRequest);
            }

            return $result;
        } catch (\PDOException $e) {
            error_log("Error updating request status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update request details
     */
    public function update(int $id, array $data): bool
    {
        $allowedFields = ['category', 'issue_type', 'title', 'description', 'location', 
                          'contact_phone', 'preferred_contact', 'priority', 'assigned_to', 
                          'admin_notes', 'estimated_completion', 'rating', 'feedback', 
                          'rated_at', 'resolution', 'technician_notes', 'resolved_at', 
                          'resolved_by'];

        $updates = [];
        $params = ['id' => $id];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE service_requests SET " . implode(', ', $updates) . " WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            error_log("Error updating service request: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete (cancel) a request
     */
    public function delete(int $id, int $userId): bool
    {
        return $this->updateStatus($id, 'cancelled', $userId, 'Request cancelled');
    }

    /**
     * Get request timeline/updates
     */
    public function getUpdates(int $requestId): array
    {
        $sql = "SELECT ru.*, u.name as user_name, u.role as user_role
                FROM request_updates ru
                LEFT JOIN users u ON ru.user_id = u.id
                WHERE ru.request_id = :request_id
                ORDER BY ru.created_at ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['request_id' => $requestId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error getting request updates: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add an update/timeline entry
     */
    private function addUpdate(string $requestId, string $userId, ?string $oldStatus, string $newStatus, ?string $notes): bool
    {
        $sql = "INSERT INTO request_updates (request_id, created_by, status, message)
                VALUES (:request_id, :created_by, :status, :message)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'request_id' => $requestId,
                'created_by' => $userId,
                'status' => $newStatus,
                'message' => $notes
            ]);
        } catch (\PDOException $e) {
            error_log("Error adding request update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate tracking number
     */
    private function generateTrackingNumber(): string
    {
        $year = date('Y');
        
        // Get count of requests this year
        $sql = "SELECT COUNT(*) as count FROM service_requests 
                WHERE EXTRACT(YEAR FROM created_at) = :year";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['year' => $year]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $count = ($result['count'] ?? 0) + 1;
        } catch (\PDOException $e) {
            $count = 1;
        }

        return sprintf('REQ-%s-%06d', $year, $count);
    }

    /**
     * Parse PostgreSQL array format to PHP array
     */
    private function parsePhotosArray(?string $photos): array
    {
        if (empty($photos)) {
            return [];
        }

        // Remove curly braces and split
        $photos = trim($photos, '{}');
        if (empty($photos)) {
            return [];
        }

        return explode(',', $photos);
    }

    /**
     * Get statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'reviewed' THEN 1 END) as reviewed,
                    COUNT(CASE WHEN status = 'assigned' THEN 1 END) as assigned,
                    COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
                    COUNT(CASE WHEN priority = 'urgent' THEN 1 END) as urgent,
                    COUNT(CASE WHEN priority = 'high' THEN 1 END) as high
                FROM service_requests
                WHERE 1=1";

        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['assigned_to'])) {
            $sql .= " AND assigned_to = :assigned_to";
            $params['assigned_to'] = $filters['assigned_to'];
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            error_log("Error getting statistics: " . $e->getMessage());
            return [];
        }
    }
}
