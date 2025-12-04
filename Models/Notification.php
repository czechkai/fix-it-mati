<?php

namespace FixItMati\Models;

use FixItMati\Core\Database;
use PDO;

/**
 * Notification Model
 * 
 * Handles database operations for notifications
 */
class Notification
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new notification
     * 
     * @param array $data
     * @return array|null
     */
    public function create(array $data): ?array
    {
        $sql = "INSERT INTO notifications (
            user_id, type, title, message, data, channel, status, sent_at
        ) VALUES (
            :user_id, :type, :title, :message, :data, :channel, :status, :sent_at
        ) RETURNING *";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $data['user_id'],
                'type' => $data['type'],
                'title' => $data['title'],
                'message' => $data['message'],
                'data' => isset($data['data']) ? json_encode($data['data']) : null,
                'channel' => $data['channel'] ?? 'in_app',
                'status' => $data['status'] ?? 'pending',
                'sent_at' => $data['sent_at'] ?? null
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (\Exception $e) {
            error_log("Failed to create notification: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get notifications for a user
     * 
     * @param string $userId
     * @param array $filters
     * @return array
     */
    public function getByUser(string $userId, array $filters = []): array
    {
        $conditions = ['user_id = :user_id'];
        $params = ['user_id' => $userId];

        if (isset($filters['is_read'])) {
            $conditions[] = 'is_read = :is_read';
            $params['is_read'] = $filters['is_read'];
        }

        if (isset($filters['type'])) {
            $conditions[] = 'type = :type';
            $params['type'] = $filters['type'];
        }

        $whereClause = implode(' AND ', $conditions);
        $limit = $filters['limit'] ?? 50;
        $offset = $filters['offset'] ?? 0;

        $sql = "SELECT * FROM notifications 
                WHERE $whereClause 
                ORDER BY created_at DESC 
                LIMIT $limit OFFSET $offset";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            error_log("Failed to get notifications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark notification as read
     * 
     * @param string $id
     * @param string $userId
     * @return bool
     */
    public function markAsRead(string $id, string $userId): bool
    {
        $sql = "UPDATE notifications 
                SET is_read = TRUE, read_at = CURRENT_TIMESTAMP 
                WHERE id = :id AND user_id = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'id' => $id,
                'user_id' => $userId
            ]);

        } catch (\Exception $e) {
            error_log("Failed to mark notification as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark all notifications as read for a user
     * 
     * @param string $userId
     * @return bool
     */
    public function markAllAsRead(string $userId): bool
    {
        $sql = "UPDATE notifications 
                SET is_read = TRUE, read_at = CURRENT_TIMESTAMP 
                WHERE user_id = :user_id AND is_read = FALSE";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['user_id' => $userId]);

        } catch (\Exception $e) {
            error_log("Failed to mark all notifications as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unread notification count
     * 
     * @param string $userId
     * @return int
     */
    public function getUnreadCount(string $userId): int
    {
        $sql = "SELECT COUNT(*) as count FROM notifications 
                WHERE user_id = :user_id AND is_read = FALSE";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) ($result['count'] ?? 0);

        } catch (\Exception $e) {
            error_log("Failed to get unread count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete a notification
     * 
     * @param string $id
     * @param string $userId
     * @return bool
     */
    public function delete(string $id, string $userId): bool
    {
        $sql = "DELETE FROM notifications WHERE id = :id AND user_id = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'id' => $id,
                'user_id' => $userId
            ]);

        } catch (\Exception $e) {
            error_log("Failed to delete notification: " . $e->getMessage());
            return false;
        }
    }
}
