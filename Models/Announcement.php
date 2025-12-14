<?php

namespace FixItMati\Models;

use FixItMati\Core\Database;

/**
 * Announcement Model
 * 
 * Handles database operations for announcements
 */
class Announcement
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new announcement
     */
    public function create(array $data): ?array
    {
        $sql = "INSERT INTO announcements (
            title, content, category, type, status,
            affected_areas, start_date, end_date, created_by
        ) VALUES (
            :title, :content, :category, :type, :status,
            :affected_areas, :start_date, :end_date, :created_by
        ) RETURNING *";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'title' => $data['title'],
                'content' => $data['content'],
                'category' => $data['category'],
                'type' => $data['type'] ?? 'news',
                'status' => $data['status'] ?? 'draft',
                'affected_areas' => $this->formatArray($data['affected_areas'] ?? []),
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'created_by' => $data['created_by'] ?? null
            ]);

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error creating announcement: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all published announcements (admin only)
     */
    public function getPublished(int $limit = 50): array
    {
        $sql = "SELECT a.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as author_name,
                       u.role as author_role
                FROM announcements a
                LEFT JOIN users u ON a.created_by = u.id
                WHERE a.status = 'published'
                AND u.role = 'admin'
                ORDER BY a.created_at DESC
                LIMIT :limit";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching published announcements: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all announcements (for admin)
     */
    public function getAll(int $limit = 100): array
    {
        $sql = "SELECT a.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as author_name
                FROM announcements a
                LEFT JOIN users u ON a.created_by = u.id
                ORDER BY a.created_at DESC
                LIMIT :limit";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching all announcements: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get announcement by ID
     */
    public function find(string $id): ?array
    {
        $sql = "SELECT a.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as author_name,
                       u.email as author_email
                FROM announcements a
                LEFT JOIN users u ON a.created_by = u.id
                WHERE a.id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error finding announcement: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get announcements by category (admin only)
     */
    public function getByCategory(string $category, string $status = 'published'): array
    {
        $sql = "SELECT a.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as author_name,
                       u.role as author_role
                FROM announcements a
                LEFT JOIN users u ON a.created_by = u.id
                WHERE a.category = :category 
                AND a.status = :status
                AND u.role = 'admin'
                ORDER BY a.created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'category' => $category,
                'status' => $status
            ]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching announcements by category: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update announcement
     */
    public function update(string $id, array $data): ?array
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['title'])) {
            $fields[] = "title = :title";
            $params['title'] = $data['title'];
        }
        if (isset($data['content'])) {
            $fields[] = "content = :content";
            $params['content'] = $data['content'];
        }
        if (isset($data['category'])) {
            $fields[] = "category = :category";
            $params['category'] = $data['category'];
        }
        if (isset($data['type'])) {
            $fields[] = "type = :type";
            $params['type'] = $data['type'];
        }
        if (isset($data['status'])) {
            $fields[] = "status = :status";
            $params['status'] = $data['status'];
        }
        if (isset($data['affected_areas'])) {
            $fields[] = "affected_areas = :affected_areas";
            $params['affected_areas'] = $this->formatArray($data['affected_areas']);
        }
        if (isset($data['start_date'])) {
            $fields[] = "start_date = :start_date";
            $params['start_date'] = $data['start_date'];
        }
        if (isset($data['end_date'])) {
            $fields[] = "end_date = :end_date";
            $params['end_date'] = $data['end_date'];
        }

        $fields[] = "updated_at = NOW()";

        if (empty($fields)) {
            return null;
        }

        $sql = "UPDATE announcements SET " . implode(', ', $fields) . " WHERE id = :id RETURNING *";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error updating announcement: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete announcement
     */
    public function delete(string $id): bool
    {
        $sql = "DELETE FROM announcements WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log("Error deleting announcement: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get comments for an announcement
     */
    public function getComments(string $announcementId): array
    {
        $sql = "SELECT ac.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as user_name
                FROM announcement_comments ac
                LEFT JOIN users u ON ac.user_id = u.id
                WHERE ac.announcement_id = :announcement_id
                ORDER BY ac.created_at ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['announcement_id' => $announcementId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching comments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add comment to announcement
     */
    public function addComment(string $announcementId, string $userId, string $comment): ?array
    {
        $sql = "INSERT INTO announcement_comments (announcement_id, user_id, comment)
                VALUES (:announcement_id, :user_id, :comment)
                RETURNING *";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'announcement_id' => $announcementId,
                'user_id' => $userId,
                'comment' => $comment
            ]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error adding comment: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get active announcements (published and within date range, admin only)
     */
    public function getActive(): array
    {
        $sql = "SELECT a.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as author_name,
                       u.role as author_role
                FROM announcements a
                LEFT JOIN users u ON a.created_by = u.id
                WHERE a.status = 'published'
                AND u.role = 'admin'
                AND (a.start_date IS NULL OR a.start_date <= NOW())
                AND (a.end_date IS NULL OR a.end_date >= NOW())
                ORDER BY 
                    CASE a.type
                        WHEN 'urgent' THEN 1
                        WHEN 'warning' THEN 2
                        WHEN 'maintenance' THEN 3
                        ELSE 4
                    END,
                    a.created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching active announcements: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get discussion ID linked to announcement
     */
    public function getDiscussionId(string $announcementId): ?string
    {
        $sql = "SELECT discussion_id FROM announcements WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $announcementId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? $result['discussion_id'] : null;
        } catch (\PDOException $e) {
            error_log("Error getting discussion ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Link announcement to discussion
     */
    public function linkToDiscussion(string $announcementId, string $discussionId): bool
    {
        $sql = "UPDATE announcements SET discussion_id = :discussion_id WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'discussion_id' => $discussionId,
                'id' => $announcementId
            ]);
            return true;
        } catch (\PDOException $e) {
            error_log("Error linking to discussion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format array for PostgreSQL ARRAY type
     */
    private function formatArray(array $items): string
    {
        if (empty($items)) {
            return '{}';
        }
        $escaped = array_map(fn($item) => '"' . str_replace('"', '\"', $item) . '"', $items);
        return '{' . implode(',', $escaped) . '}';
    }
}
