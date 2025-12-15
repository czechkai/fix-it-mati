<?php

namespace FixItMati\Models;

use FixItMati\Core\Database;
use PDO;

/**
 * TechnicianTeam Model
 * 
 * Handles database operations for technician teams
 */
class TechnicianTeam
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all technician teams
     */
    public function all(): array
    {
        $sql = "SELECT * FROM technicians WHERE type = 'team' ORDER BY created_at DESC";
        
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching teams: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get team by ID
     */
    public function find(string $id): ?array
    {
        $sql = "SELECT * FROM technicians WHERE id = :id AND type = 'team'";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Error finding team: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get teams by department
     */
    public function findByDepartment(string $department): array
    {
        $sql = "SELECT * FROM technicians WHERE type = 'team' AND department = :department ORDER BY created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['department' => $department]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error finding teams by department: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get teams by status
     */
    public function findByStatus(string $status): array
    {
        $sql = "SELECT * FROM technicians WHERE type = 'team' AND status = :status ORDER BY created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['status' => $status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error finding teams by status: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new team
     */
    public function create(array $data): ?array
    {
        $sql = "INSERT INTO technicians (
            type, name, department, lead, members, contact_number, status, 
            location, current_task, current_ticket, rating, tickets_resolved
        ) VALUES (
            'team', :name, :department, :lead, :members, :contact_number, :status,
            :location, :current_task, :current_ticket, :rating, :tickets_resolved
        ) RETURNING *";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'name' => $data['name'],
                'department' => $data['department'],
                'lead' => $data['lead'],
                'members' => $data['members'],
                'contact_number' => $data['contact_number'] ?? null,
                'status' => strtolower($data['status'] ?? 'available'),
                'location' => $data['location'] ?? 'HQ (Standby)',
                'current_task' => $data['current_task'] ?? null,
                'current_ticket' => $data['current_ticket'] ?? null,
                'rating' => $data['rating'] ?? 4.5,
                'tickets_resolved' => $data['tickets_resolved'] ?? 0
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error creating team: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update a team
     */
    public function update(string $id, array $data): bool
    {
        $allowedFields = [
            'name', 'department', 'lead', 'members', 'contact_number',
            'status', 'location', 'current_task', 'current_ticket',
            'rating', 'tickets_resolved'
        ];

        $updates = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updates[] = "$key = :$key";
                $params[$key] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE technicians SET " . implode(', ', $updates) . " 
                WHERE id = :id AND type = 'team'";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            error_log("Error updating team: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update team status
     */
    public function updateStatus(string $id, string $status): bool
    {
        $sql = "UPDATE technicians SET status = :status, updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id AND type = 'team'";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'id' => $id,
                'status' => $status
            ]);
        } catch (\PDOException $e) {
            error_log("Error updating team status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Assign task to team
     */
    public function assignTask(string $id, string $task, ?string $ticket = null, ?string $location = null): bool
    {
        $sql = "UPDATE technicians SET 
                status = 'busy',
                current_task = :task,
                current_ticket = :ticket,
                location = :location,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND type = 'team'";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'id' => $id,
                'task' => $task,
                'ticket' => $ticket,
                'location' => $location ?? 'On Site'
            ]);
        } catch (\PDOException $e) {
            error_log("Error assigning task: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Complete task and update stats
     */
    public function completeTask(string $id): bool
    {
        $sql = "UPDATE technicians SET 
                status = 'available',
                current_task = NULL,
                current_ticket = NULL,
                location = 'HQ (Standby)',
                tickets_resolved = tickets_resolved + 1,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND type = 'team'";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            error_log("Error completing task: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete/deactivate a team
     */
    public function delete(string $id): bool
    {
        $sql = "DELETE FROM technicians WHERE id = :id AND type = 'team'";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            error_log("Error deleting team: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get team statistics
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                COUNT(*) as total_teams,
                COUNT(CASE WHEN status = 'available' THEN 1 END) as available,
                COUNT(CASE WHEN status = 'busy' THEN 1 END) as busy,
                COUNT(CASE WHEN status = 'on_route' THEN 1 END) as on_route,
                SUM(tickets_resolved) as total_resolved,
                AVG(rating) as avg_rating
                FROM technicians WHERE type = 'team'";

        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            error_log("Error getting stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Convert to array (for API responses)
     */
    public function toArray(array $team): array
    {
        return [
            'id' => $team['id'],
            'name' => $team['name'],
            'department' => $team['department'],
            'lead' => $team['lead'],
            'members' => (int)$team['members'],
            'contact_number' => $team['contact_number'],
            'status' => $team['status'],
            'location' => $team['location'],
            'current_task' => $team['current_task'],
            'current_ticket' => $team['current_ticket'],
            'rating' => (float)$team['rating'],
            'tickets_resolved' => (int)$team['tickets_resolved'],
            'created_at' => $team['created_at'],
            'updated_at' => $team['updated_at'] ?? $team['created_at']
        ];
    }
}
