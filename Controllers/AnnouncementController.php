<?php

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\Models\Announcement;

/**
 * Announcement Controller
 * 
 * Handles CRUD operations for announcements
 */
class AnnouncementController
{
    private Announcement $model;

    public function __construct()
    {
        $this->model = new Announcement();
    }

    /**
     * Get all published announcements (for public/users)
     */
    public function getPublished(Request $request): Response
    {
        try {
            $limit = (int) $request->query('limit', 50);
            $announcements = $this->model->getPublished($limit);

            return Response::json([
                'success' => true,
                'data' => [
                    'announcements' => $announcements,
                    'count' => count($announcements)
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Error fetching published announcements: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to fetch announcements'
            ], 500);
        }
    }

    /**
     * Get active announcements (published and within date range)
     */
    public function getActive(Request $request): Response
    {
        try {
            $announcements = $this->model->getActive();

            return Response::json([
                'success' => true,
                'data' => [
                    'announcements' => $announcements,
                    'count' => count($announcements)
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Error fetching active announcements: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to fetch active announcements'
            ], 500);
        }
    }

    /**
     * Get announcement by ID
     */
    public function show(Request $request): Response
    {
        $id = $request->param('id');

        if (!$id) {
            return Response::json([
                'success' => false,
                'message' => 'Announcement ID is required'
            ], 400);
        }

        try {
            $announcement = $this->model->find($id);

            if (!$announcement) {
                return Response::json([
                    'success' => false,
                    'message' => 'Announcement not found'
                ], 404);
            }

            // Get comments
            $comments = $this->model->getComments($id);

            return Response::json([
                'success' => true,
                'data' => [
                    'announcement' => $announcement,
                    'comments' => $comments
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Error fetching announcement: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to fetch announcement'
            ], 500);
        }
    }

    /**
     * Get announcements by category
     */
    public function getByCategory(Request $request): Response
    {
        $category = $request->param('category');

        if (!$category) {
            return Response::json([
                'success' => false,
                'message' => 'Category is required'
            ], 400);
        }

        try {
            $announcements = $this->model->getByCategory($category, 'published');

            return Response::json([
                'success' => true,
                'data' => [
                    'announcements' => $announcements,
                    'count' => count($announcements),
                    'category' => $category
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Error fetching announcements by category: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to fetch announcements'
            ], 500);
        }
    }

    /**
     * Create announcement (admin only)
     */
    public function create(Request $request): Response
    {
        $user = $request->user();

        if (!$user || $user['role'] !== 'admin') {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        // Validate required fields
        $required = ['title', 'content', 'category'];
        foreach ($required as $field) {
            if (empty($request->param($field))) {
                return Response::json([
                    'success' => false,
                    'message' => "Missing required field: {$field}"
                ], 400);
            }
        }

        try {
            $data = [
                'title' => $request->param('title'),
                'content' => $request->param('content'),
                'category' => $request->param('category'),
                'type' => $request->param('type', 'news'),
                'status' => $request->param('status', 'draft'),
                'affected_areas' => $request->param('affected_areas', []),
                'start_date' => $request->param('start_date'),
                'end_date' => $request->param('end_date'),
                'created_by' => $user['id']
            ];

            $announcement = $this->model->create($data);

            if (!$announcement) {
                throw new \Exception("Failed to create announcement");
            }

            return Response::json([
                'success' => true,
                'message' => 'Announcement created successfully',
                'data' => $announcement
            ], 201);
        } catch (\Exception $e) {
            error_log("Error creating announcement: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to create announcement'
            ], 500);
        }
    }

    /**
     * Update announcement (admin only)
     */
    public function update(Request $request): Response
    {
        $user = $request->user();

        if (!$user || $user['role'] !== 'admin') {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $id = $request->param('id');

        if (!$id) {
            return Response::json([
                'success' => false,
                'message' => 'Announcement ID is required'
            ], 400);
        }

        try {
            $data = array_filter([
                'title' => $request->param('title'),
                'content' => $request->param('content'),
                'category' => $request->param('category'),
                'type' => $request->param('type'),
                'status' => $request->param('status'),
                'affected_areas' => $request->param('affected_areas'),
                'start_date' => $request->param('start_date'),
                'end_date' => $request->param('end_date')
            ], fn($value) => $value !== null);

            $announcement = $this->model->update($id, $data);

            if (!$announcement) {
                return Response::json([
                    'success' => false,
                    'message' => 'Announcement not found or update failed'
                ], 404);
            }

            return Response::json([
                'success' => true,
                'message' => 'Announcement updated successfully',
                'data' => $announcement
            ]);
        } catch (\Exception $e) {
            error_log("Error updating announcement: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to update announcement'
            ], 500);
        }
    }

    /**
     * Delete announcement (admin only)
     */
    public function delete(Request $request): Response
    {
        $user = $request->user();

        if (!$user || $user['role'] !== 'admin') {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $id = $request->param('id');

        if (!$id) {
            return Response::json([
                'success' => false,
                'message' => 'Announcement ID is required'
            ], 400);
        }

        try {
            $deleted = $this->model->delete($id);

            if (!$deleted) {
                return Response::json([
                    'success' => false,
                    'message' => 'Announcement not found'
                ], 404);
            }

            return Response::json([
                'success' => true,
                'message' => 'Announcement deleted successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Error deleting announcement: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to delete announcement'
            ], 500);
        }
    }

    /**
     * Add comment to announcement
     */
    public function addComment(Request $request): Response
    {
        $user = $request->user();

        if (!$user) {
            return Response::json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $announcementId = $request->param('announcement_id');
        $comment = $request->param('comment');

        if (!$announcementId || !$comment) {
            return Response::json([
                'success' => false,
                'message' => 'Announcement ID and comment are required'
            ], 400);
        }

        try {
            $result = $this->model->addComment($announcementId, $user['id'], $comment);

            if (!$result) {
                throw new \Exception("Failed to add comment");
            }

            return Response::json([
                'success' => true,
                'message' => 'Comment added successfully',
                'data' => $result
            ], 201);
        } catch (\Exception $e) {
            error_log("Error adding comment: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to add comment'
            ], 500);
        }
    }
}
