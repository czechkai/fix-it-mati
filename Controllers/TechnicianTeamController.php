<?php

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\Models\TechnicianTeam;
use FixItMati\Models\Notification;

/**
 * TechnicianTeam Controller
 * 
 * Handles all technician team management operations
 */
class TechnicianTeamController
{
    private TechnicianTeam $teamModel;
    private ?Notification $notificationModel;

    public function __construct()
    {
        $this->teamModel = new TechnicianTeam();
        
        // Notification model is optional
        if (class_exists('FixItMati\\Models\\Notification')) {
            $this->notificationModel = new Notification();
        } else {
            $this->notificationModel = null;
        }
    }

    /**
     * Get all technician teams
     */
    public function getAllTeams(Request $request): Response
    {
        try {
            $teams = $this->teamModel->all();
            
            return Response::json([
                'success' => true,
                'data' => array_map([$this->teamModel, 'toArray'], $teams),
                'message' => 'Teams retrieved successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Error in getAllTeams: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to retrieve teams'
            ], 500);
        }
    }

    /**
     * Get team by ID
     */
    public function getTeam(Request $request, string $id): Response
    {
        try {
            $team = $this->teamModel->find($id);
            
            if (!$team) {
                return Response::json([
                    'success' => false,
                    'message' => 'Team not found'
                ], 404);
            }

            return Response::json([
                'success' => true,
                'data' => $this->teamModel->toArray($team),
                'message' => 'Team retrieved successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Error in getTeam: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to retrieve team'
            ], 500);
        }
    }

    /**
     * Create new technician team
     */
    public function createTeam(Request $request): Response
    {
        try {
            $data = $request->getBody();

            // Validate required fields
            $requiredFields = ['name', 'department', 'lead', 'members'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return Response::json([
                        'success' => false,
                        'message' => "Missing required field: $field"
                    ], 400);
                }
            }

            // Validate department (only Water and Electric)
            if (!in_array($data['department'], ['Water', 'Electric'])) {
                return Response::json([
                    'success' => false,
                    'message' => 'Invalid department. Must be Water District or Davao Oriental Electric Cooperative (DORECO)'
                ], 400);
            }

            $team = $this->teamModel->create($data);

            if (!$team) {
                return Response::json([
                    'success' => false,
                    'message' => 'Failed to create team'
                ], 500);
            }

            return Response::json([
                'success' => true,
                'data' => $this->teamModel->toArray($team),
                'message' => 'Team registered successfully'
            ], 201);
        } catch (\Exception $e) {
            error_log("Error in createTeam: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to create team'
            ], 500);
        }
    }

    /**
     * Update team status
     */
    public function updateStatus(Request $request, string $id): Response
    {
        try {
            $data = $request->getBody();

            if (empty($data['status'])) {
                return Response::json([
                    'success' => false,
                    'message' => 'Status is required'
                ], 400);
            }

            $validStatuses = ['Available', 'Busy', 'On Route', 'Off Duty'];
            if (!in_array($data['status'], $validStatuses)) {
                return Response::json([
                    'success' => false,
                    'message' => 'Invalid status'
                ], 400);
            }

            $success = $this->teamModel->updateStatus($id, $data['status']);

            if (!$success) {
                return Response::json([
                    'success' => false,
                    'message' => 'Failed to update status'
                ], 500);
            }

            return Response::json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Error in updateStatus: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    /**
     * Assign task to team
     */
    public function assignTask(Request $request, string $id): Response
    {
        try {
            $data = $request->getBody();

            if (empty($data['task'])) {
                return Response::json([
                    'success' => false,
                    'message' => 'Task description is required'
                ], 400);
            }

            $success = $this->teamModel->assignTask(
                $id,
                $data['task'],
                $data['ticket'] ?? null,
                $data['location'] ?? null
            );

            if (!$success) {
                return Response::json([
                    'success' => false,
                    'message' => 'Failed to assign task'
                ], 500);
            }

            return Response::json([
                'success' => true,
                'message' => 'Task assigned successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Error in assignTask: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to assign task'
            ], 500);
        }
    }

    /**
     * Complete task
     */
    public function completeTask(Request $request, string $id): Response
    {
        try {
            $success = $this->teamModel->completeTask($id);

            if (!$success) {
                return Response::json([
                    'success' => false,
                    'message' => 'Failed to complete task'
                ], 500);
            }

            return Response::json([
                'success' => true,
                'message' => 'Task completed successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Error in completeTask: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to complete task'
            ], 500);
        }
    }

    /**
     * Update team details
     */
    public function updateTeam(Request $request, string $id): Response
    {
        try {
            $data = $request->getBody();

            $success = $this->teamModel->update($id, $data);

            if (!$success) {
                return Response::json([
                    'success' => false,
                    'message' => 'Failed to update team'
                ], 500);
            }

            return Response::json([
                'success' => true,
                'message' => 'Team updated successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Error in updateTeam: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to update team'
            ], 500);
        }
    }

    /**
     * Delete/deactivate team
     */
    public function deleteTeam(Request $request, string $id): Response
    {
        try {
            $team = $this->teamModel->find($id);

            if (!$team) {
                return Response::json([
                    'success' => false,
                    'message' => 'Team not found'
                ], 404);
            }

            $success = $this->teamModel->delete($id);

            if (!$success) {
                return Response::json([
                    'success' => false,
                    'message' => 'Failed to deactivate team'
                ], 500);
            }

            return Response::json([
                'success' => true,
                'message' => 'Team deactivated successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Error in deleteTeam: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to deactivate team'
            ], 500);
        }
    }

    /**
     * Get team statistics
     */
    public function getStats(Request $request): Response
    {
        try {
            $stats = $this->teamModel->getStats();

            return Response::json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Error in getStats: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to retrieve statistics'
            ], 500);
        }
    }
}
