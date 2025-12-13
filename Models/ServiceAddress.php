<?php

namespace FixItMati\Models;

use FixItMati\Core\Database;
use PDO;

/**
 * ServiceAddress Model
 * 
 * Handles CRUD operations for user service addresses
 */
class ServiceAddress
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new service address
     */
    public function create(array $data): ?array
    {
        $sql = "INSERT INTO service_addresses (
            user_id, label, type, barangay, street, 
            details, latitude, longitude, is_default
        ) VALUES (
            :user_id, :label, :type, :barangay, :street,
            :details, :latitude, :longitude, :is_default
        ) RETURNING *";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $data['user_id'],
                'label' => $data['label'],
                'type' => $data['type'],
                'barangay' => $data['barangay'],
                'street' => $data['street'],
                'details' => $data['details'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'is_default' => $data['is_default'] ?? false
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error creating service address: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all addresses for a user
     */
    public function getAllByUser(string $userId): array
    {
        $sql = "SELECT * FROM service_addresses 
                WHERE user_id = :user_id 
                ORDER BY is_default DESC, created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching user addresses: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single address by ID
     */
    public function getById(string $id): ?array
    {
        $sql = "SELECT * FROM service_addresses WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Error fetching address: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user's default address
     */
    public function getDefaultByUser(string $userId): ?array
    {
        $sql = "SELECT * FROM service_addresses 
                WHERE user_id = :user_id AND is_default = true 
                LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Error fetching default address: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update an address
     */
    public function update(string $id, array $data): ?array
    {
        $sql = "UPDATE service_addresses SET
                label = :label,
                type = :type,
                barangay = :barangay,
                street = :street,
                details = :details,
                latitude = :latitude,
                longitude = :longitude,
                is_default = :is_default
                WHERE id = :id
                RETURNING *";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'label' => $data['label'],
                'type' => $data['type'],
                'barangay' => $data['barangay'],
                'street' => $data['street'],
                'details' => $data['details'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'is_default' => $data['is_default'] ?? false
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error updating address: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Set an address as default (unsets other defaults automatically via trigger)
     */
    public function setDefault(string $id, string $userId): bool
    {
        $sql = "UPDATE service_addresses 
                SET is_default = true 
                WHERE id = :id AND user_id = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id, 'user_id' => $userId]);
        } catch (\PDOException $e) {
            error_log("Error setting default address: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete an address
     */
    public function delete(string $id, string $userId): bool
    {
        // Prevent deleting the default address if it's the only one
        $sql = "SELECT COUNT(*) as count FROM service_addresses WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        if ($count <= 1) {
            error_log("Cannot delete the only address");
            return false;
        }

        $sql = "DELETE FROM service_addresses 
                WHERE id = :id AND user_id = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id, 'user_id' => $userId]);
        } catch (\PDOException $e) {
            error_log("Error deleting address: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get addresses by barangay
     */
    public function getByBarangay(string $barangay): array
    {
        $sql = "SELECT * FROM service_addresses 
                WHERE barangay = :barangay 
                ORDER BY created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['barangay' => $barangay]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching addresses by barangay: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count addresses for a user
     */
    public function countByUser(string $userId): int
    {
        $sql = "SELECT COUNT(*) as count FROM service_addresses WHERE user_id = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (\PDOException $e) {
            error_log("Error counting user addresses: " . $e->getMessage());
            return 0;
        }
    }
}
