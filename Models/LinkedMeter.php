<?php

namespace FixItMati\Models;

use FixItMati\Core\Database;
use PDO;

/**
 * LinkedMeter Model
 * 
 * Handles CRUD operations for linked utility meters
 */
class LinkedMeter
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new linked meter
     */
    public function create(array $data): ?array
    {
        $sql = "INSERT INTO linked_meters (
            user_id, provider, meter_type, account_number, 
            account_holder_name, alias, address, status,
            last_reading, last_bill_amount, last_bill_date, metadata
        ) VALUES (
            :user_id, :provider, :meter_type, :account_number,
            :account_holder_name, :alias, :address, :status,
            :last_reading, :last_bill_amount, :last_bill_date, :metadata
        ) RETURNING *";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $data['user_id'],
                'provider' => $data['provider'],
                'meter_type' => $data['meter_type'],
                'account_number' => $data['account_number'],
                'account_holder_name' => $data['account_holder_name'],
                'alias' => $data['alias'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => $data['status'] ?? 'active',
                'last_reading' => $data['last_reading'] ?? null,
                'last_bill_amount' => $data['last_bill_amount'] ?? null,
                'last_bill_date' => $data['last_bill_date'] ?? null,
                'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error creating linked meter: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all meters for a user
     */
    public function getAllByUser(string $userId): array
    {
        $sql = "SELECT * FROM linked_meters 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching user meters: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single meter by ID
     */
    public function getById(string $id): ?array
    {
        $sql = "SELECT * FROM linked_meters WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Error fetching meter: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update a meter
     */
    public function update(string $id, array $data): ?array
    {
        $sql = "UPDATE linked_meters SET
                provider = :provider,
                alias = :alias,
                address = :address,
                status = :status,
                last_reading = :last_reading,
                last_bill_amount = :last_bill_amount,
                last_bill_date = :last_bill_date,
                metadata = :metadata
                WHERE id = :id
                RETURNING *";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'provider' => $data['provider'],
                'alias' => $data['alias'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => $data['status'] ?? 'active',
                'last_reading' => $data['last_reading'] ?? null,
                'last_bill_amount' => $data['last_bill_amount'] ?? null,
                'last_bill_date' => $data['last_bill_date'] ?? null,
                'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error updating meter: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a meter
     */
    public function delete(string $id, string $userId): bool
    {
        $sql = "DELETE FROM linked_meters 
                WHERE id = :id AND user_id = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id, 'user_id' => $userId]);
        } catch (\PDOException $e) {
            error_log("Error deleting meter: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get meters by type
     */
    public function getByType(string $userId, string $type): array
    {
        $sql = "SELECT * FROM linked_meters 
                WHERE user_id = :user_id AND meter_type = :type
                ORDER BY created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId, 'type' => $type]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching meters by type: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count meters for a user
     */
    public function countByUser(string $userId): int
    {
        $sql = "SELECT COUNT(*) as count FROM linked_meters WHERE user_id = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (\PDOException $e) {
            error_log("Error counting user meters: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if account number exists for user
     */
    public function accountExists(string $userId, string $accountNumber): bool
    {
        $sql = "SELECT COUNT(*) as count FROM linked_meters 
                WHERE user_id = :user_id AND account_number = :account_number";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId, 'account_number' => $accountNumber]);
            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
        } catch (\PDOException $e) {
            error_log("Error checking account existence: " . $e->getMessage());
            return false;
        }
    }
}
