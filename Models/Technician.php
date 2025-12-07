<?php

namespace FixItMati\Models;

use FixItMati\Core\Database;

/**
 * Technician Model
 * 
 * Handles database operations for technicians
 */
class Technician
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new technician
     */
    public function create(array $data): ?array
    {
        $sql = "INSERT INTO technicians (
            user_id, specialization, status, phone, assigned_area
        ) VALUES (
            :user_id, :specialization, :status, :phone, :assigned_area
        ) RETURNING *";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $data['user_id'],
                'specialization' => $data['specialization'],
                'status' => $data['status'] ?? 'active',
                'phone' => $data['phone'] ?? null,
                'assigned_area' => $data['assigned_area'] ?? null
            ]);

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error creating technician: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all technicians
     */
    public function getAll(?string $status = null): array
    {
        $sql = "SELECT t.*, 
                       u.full_name, u.email, u.phone as user_phone
                FROM technicians t
                LEFT JOIN users u ON t.user_id = u.id";

        if ($status) {
            $sql .= " WHERE t.status = :status";
        }

        $sql .= " ORDER BY u.full_name ASC";

        try {
            $stmt = $this->db->prepare($sql);
            if ($status) {
                $stmt->execute(['status' => $status]);
            } else {
                $stmt->execute();
            }
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching technicians: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get technician by ID
     */
    public function find(string $id): ?array
    {
        $sql = "SELECT t.*, 
                       u.full_name, u.email, u.phone as user_phone, u.address
                FROM technicians t
                LEFT JOIN users u ON t.user_id = u.id
                WHERE t.id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error finding technician: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get technician by user ID
     */
    public function findByUserId(string $userId): ?array
    {
        $sql = "SELECT t.*, 
                       u.full_name, u.email, u.phone as user_phone
                FROM technicians t
                LEFT JOIN users u ON t.user_id = u.id
                WHERE t.user_id = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error finding technician by user ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get technicians by specialization
     */
    public function getBySpecialization(string $specialization, string $status = 'active'): array
    {
        $sql = "SELECT t.*, 
                       u.full_name, u.email, u.phone as user_phone
                FROM technicians t
                LEFT JOIN users u ON t.user_id = u.id
                WHERE t.specialization = :specialization
                AND t.status = :status
                ORDER BY u.full_name ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'specialization' => $specialization,
                'status' => $status
            ]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching technicians by specialization: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update technician
     */
    public function update(string $id, array $data): ?array
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['specialization'])) {
            $fields[] = "specialization = :specialization";
            $params['specialization'] = $data['specialization'];
        }
        if (isset($data['status'])) {
            $fields[] = "status = :status";
            $params['status'] = $data['status'];
        }
        if (isset($data['phone'])) {
            $fields[] = "phone = :phone";
            $params['phone'] = $data['phone'];
        }
        if (isset($data['assigned_area'])) {
            $fields[] = "assigned_area = :assigned_area";
            $params['assigned_area'] = $data['assigned_area'];
        }

        if (empty($fields)) {
            return null;
        }

        $sql = "UPDATE technicians SET " . implode(', ', $fields) . " WHERE id = :id RETURNING *";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error updating technician: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete technician
     */
    public function delete(string $id): bool
    {
        $sql = "DELETE FROM technicians WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log("Error deleting technician: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get technician's current workload (active requests)
     */
    public function getWorkload(string $technicianId): int
    {
        $sql = "SELECT COUNT(*) 
                FROM service_requests
                WHERE assigned_technician_id = :technician_id
                AND status IN ('pending', 'in_progress')";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['technician_id' => $technicianId]);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error getting technician workload: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get available technicians for assignment (active and low workload)
     */
    public function getAvailable(?string $specialization = null, int $maxWorkload = 5): array
    {
        $sql = "SELECT t.*, 
                       u.full_name, u.email, u.phone as user_phone,
                       COUNT(sr.id) as current_workload
                FROM technicians t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN service_requests sr ON t.user_id = sr.assigned_technician_id 
                    AND sr.status IN ('pending', 'in_progress')
                WHERE t.status = 'active'";

        if ($specialization) {
            $sql .= " AND t.specialization = :specialization";
        }

        $sql .= " GROUP BY t.id, u.id
                  HAVING COUNT(sr.id) < :max_workload
                  ORDER BY COUNT(sr.id) ASC, u.full_name ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $params = ['max_workload' => $maxWorkload];
            if ($specialization) {
                $params['specialization'] = $specialization;
            }
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching available technicians: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get technician's assigned requests
     */
    public function getAssignedRequests(string $technicianId, ?string $status = null): array
    {
        $sql = "SELECT sr.*, 
                       u.full_name as customer_name, u.email as customer_email
                FROM service_requests sr
                LEFT JOIN users u ON sr.user_id = u.id
                WHERE sr.assigned_technician_id = :technician_id";

        if ($status) {
            $sql .= " AND sr.status = :status";
        }

        $sql .= " ORDER BY sr.created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $params = ['technician_id' => $technicianId];
            if ($status) {
                $params['status'] = $status;
            }
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching assigned requests: " . $e->getMessage());
            return [];
        }
    }
}
