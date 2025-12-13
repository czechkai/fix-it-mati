<?php

namespace FixItMati\Models;

use FixItMati\Core\Database;
use PDO;

/**
 * Discussion Model
 * 
 * Represents a community discussion thread.
 */
class Discussion
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all discussions with filters
     */
    public function getAll(?string $category = null, string $sort = 'newest'): array
    {
        $sql = "SELECT d.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as author_name,
                       u.email as author_email,
                       (SELECT COUNT(*) FROM discussion_comments WHERE discussion_id = d.id) as comments_count
                FROM discussions d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE 1=1";

        $params = [];

        if ($category && $category !== 'All') {
            $sql .= " AND d.category = :category";
            $params['category'] = $category;
        }

        // Sorting
        if ($sort === 'top') {
            $sql .= " ORDER BY d.upvotes DESC, d.created_at DESC";
        } else if ($sort === 'unanswered') {
            $sql .= " AND d.is_answered = FALSE ORDER BY d.created_at DESC";
        } else {
            $sql .= " ORDER BY d.created_at DESC";
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error getting discussions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find discussion by ID
     */
    public function find(string $id): ?array
    {
        $sql = "SELECT d.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as author_name,
                       u.email as author_email,
                       (SELECT COUNT(*) FROM discussion_comments WHERE discussion_id = d.id) as comments_count
                FROM discussions d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Error finding discussion: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new discussion
     */
    public function create(array $data): ?array
    {
        $sql = "INSERT INTO discussions (user_id, category, title, content)
                VALUES (:user_id, :category, :title, :content)
                RETURNING *";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $data['user_id'],
                'category' => $data['category'],
                'title' => $data['title'],
                'content' => $data['content']
            ]);

            $discussion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($discussion) {
                return $this->find($discussion['id']);
            }
            
            return null;
        } catch (\PDOException $e) {
            error_log("Error creating discussion: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Toggle upvote for a discussion
     */
    public function toggleUpvote(string $discussionId, string $userId): array
    {
        try {
            $this->db->beginTransaction();

            // Check if user already upvoted
            $checkSql = "SELECT id FROM discussion_upvotes WHERE discussion_id = :discussion_id AND user_id = :user_id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute(['discussion_id' => $discussionId, 'user_id' => $userId]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Remove upvote
                $deleteSql = "DELETE FROM discussion_upvotes WHERE discussion_id = :discussion_id AND user_id = :user_id";
                $deleteStmt = $this->db->prepare($deleteSql);
                $deleteStmt->execute(['discussion_id' => $discussionId, 'user_id' => $userId]);

                // Decrement count
                $updateSql = "UPDATE discussions SET upvotes = upvotes - 1 WHERE id = :id";
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->execute(['id' => $discussionId]);

                $userUpvoted = false;
            } else {
                // Add upvote
                $insertSql = "INSERT INTO discussion_upvotes (discussion_id, user_id) VALUES (:discussion_id, :user_id)";
                $insertStmt = $this->db->prepare($insertSql);
                $insertStmt->execute(['discussion_id' => $discussionId, 'user_id' => $userId]);

                // Increment count
                $updateSql = "UPDATE discussions SET upvotes = upvotes + 1 WHERE id = :id";
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->execute(['id' => $discussionId]);

                $userUpvoted = true;
            }

            // Get updated count
            $countSql = "SELECT upvotes FROM discussions WHERE id = :id";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute(['id' => $discussionId]);
            $result = $countStmt->fetch(PDO::FETCH_ASSOC);

            $this->db->commit();

            return [
                'upvotes' => $result['upvotes'],
                'user_upvoted' => $userUpvoted
            ];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error toggling upvote: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get comments for a discussion
     */
    public function getComments(string $discussionId): array
    {
        $sql = "SELECT dc.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as author_name,
                       u.email as author_email,
                       u.role as author_role
                FROM discussion_comments dc
                LEFT JOIN users u ON dc.user_id = u.id
                WHERE dc.discussion_id = :discussion_id
                ORDER BY dc.is_solution DESC, dc.created_at ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['discussion_id' => $discussionId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error getting comments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add comment to discussion
     */
    public function addComment(array $data): ?array
    {
        $sql = "INSERT INTO discussion_comments (discussion_id, user_id, content)
                VALUES (:discussion_id, :user_id, :content)
                RETURNING *";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'discussion_id' => $data['discussion_id'],
                'user_id' => $data['user_id'],
                'content' => $data['content']
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error adding comment: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mark comment as solution
     */
    public function markCommentAsSolution(string $discussionId, string $commentId): bool
    {
        try {
            $this->db->beginTransaction();

            // Unmark all other comments
            $unmarkSql = "UPDATE discussion_comments SET is_solution = FALSE WHERE discussion_id = :discussion_id";
            $unmarkStmt = $this->db->prepare($unmarkSql);
            $unmarkStmt->execute(['discussion_id' => $discussionId]);

            // Mark this comment
            $markSql = "UPDATE discussion_comments SET is_solution = TRUE WHERE id = :id";
            $markStmt = $this->db->prepare($markSql);
            $markStmt->execute(['id' => $commentId]);

            // Get comment author info
            $commentSql = "SELECT u.first_name, u.last_name, u.role 
                          FROM discussion_comments dc
                          LEFT JOIN users u ON dc.user_id = u.id
                          WHERE dc.id = :id";
            $commentStmt = $this->db->prepare($commentSql);
            $commentStmt->execute(['id' => $commentId]);
            $comment = $commentStmt->fetch(PDO::FETCH_ASSOC);

            $answeredBy = $comment ? $comment['first_name'] . ' ' . $comment['last_name'] : 'User';

            // Update discussion
            $updateSql = "UPDATE discussions SET is_answered = TRUE, answered_by = :answered_by WHERE id = :id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([
                'id' => $discussionId,
                'answered_by' => $answeredBy
            ]);

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error marking solution: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete discussion
     */
    public function delete(string $id): bool
    {
        $sql = "DELETE FROM discussions WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            error_log("Error deleting discussion: " . $e->getMessage());
            return false;
        }
    }
}
