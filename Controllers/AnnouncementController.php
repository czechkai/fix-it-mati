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
     * Get all announcements (admin only - includes drafts and archived)
     */
    public function getAll(Request $request): Response
    {
        $user = $request->user();

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'staff')) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized. Admin or staff access required.'
            ], 403);
        }

        try {
            $limit = (int) $request->query('limit', 100);
            $announcements = $this->model->getAll($limit);

            return Response::json([
                'success' => true,
                'data' => [
                    'announcements' => $announcements,
                    'count' => count($announcements)
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Error fetching all announcements: " . $e->getMessage());
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
            if (empty($request->input($field))) {
                return Response::json([
                    'success' => false,
                    'message' => "Missing required field: {$field}"
                ], 400);
            }
        }

        try {
            $data = [
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'category' => $request->input('category'),
                'type' => $request->input('type', 'news'),
                'status' => $request->input('status', 'draft'),
                'affected_areas' => $request->input('affected_areas', []),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
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
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'category' => $request->input('category'),
                'type' => $request->input('type'),
                'status' => $request->input('status'),
                'affected_areas' => $request->input('affected_areas'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date')
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
     * This creates a discussion post with the announcement and adds the user's comment
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

        $announcementId = $request->input('announcement_id');
        $comment = $request->input('comment');

        if (!$announcementId || !$comment) {
            // Log for debugging
            error_log("Missing parameters - announcement_id: " . ($announcementId ?? 'null') . ", comment: " . ($comment ?? 'null'));
            error_log("Request body: " . json_encode($request->all()));
            
            return Response::json([
                'success' => false,
                'message' => 'Announcement ID and comment are required',
                'debug' => [
                    'received' => [
                        'announcement_id' => $announcementId,
                        'comment' => $comment
                    ]
                ]
            ], 400);
        }

        try {
            // Get the announcement
            $announcement = $this->model->find($announcementId);
            
            if (!$announcement) {
                return Response::json([
                    'success' => false,
                    'message' => 'Announcement not found'
                ], 404);
            }

            // Check if discussion already exists for this announcement
            $discussionModel = new \FixItMati\Models\Discussion();
            $existingDiscussionId = $this->model->getDiscussionId($announcementId);
            
            if ($existingDiscussionId) {
                // Add comment to existing discussion
                $commentModel = new \FixItMati\Models\Discussion();
                $commentResult = $commentModel->addComment([
                    'discussion_id' => $existingDiscussionId,
                    'user_id' => $user['id'],
                    'content' => $comment
                ]);
                
                if (!$commentResult) {
                    throw new \Exception("Failed to add comment to discussion");
                }
                
                return Response::json([
                    'success' => true,
                    'message' => 'Comment added to discussion',
                    'data' => [
                        'discussion_id' => $existingDiscussionId,
                        'comment' => $commentResult
                    ]
                ], 201);
            }
            
            // Map announcement category to discussion category
            $categoryMap = [
                'water' => 'Water Supply',
                'water supply' => 'Water Supply',
                'electricity' => 'Electricity',
                'electric' => 'Electricity',
                'billing' => 'Billing',
                'general' => 'General'
            ];
            
            $announcementCategory = strtolower($announcement['category']);
            $discussionCategory = $categoryMap[$announcementCategory] ?? 'General';
            
            // Create new discussion from announcement with clean formatting
            $discussionData = [
                'user_id' => $user['id'],
                'category' => $discussionCategory,
                'title' => $announcement['title'],
                'content' => $announcement['content'] . "\n\n---\n\n📢 *This discussion was started from an official announcement*"
            ];
            
            $discussion = $discussionModel->create($discussionData);
            
            if (!$discussion) {
                throw new \Exception("Failed to create discussion");
            }
            
            // Link announcement to discussion
            $this->model->linkToDiscussion($announcementId, $discussion['id']);
            
            // Add user's comment
            $commentResult = $discussionModel->addComment([
                'discussion_id' => $discussion['id'],
                'user_id' => $user['id'],
                'content' => $comment
            ]);
            
            if (!$commentResult) {
                throw new \Exception("Failed to add comment");
            }

            return Response::json([
                'success' => true,
                'message' => 'Discussion created with your comment',
                'data' => [
                    'discussion_id' => $discussion['id'],
                    'discussion' => $discussion,
                    'comment' => $commentResult
                ]
            ], 201);
        } catch (\Exception $e) {
            error_log("Error adding comment to announcement: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Failed to add comment: ' . $e->getMessage()
            ], 500);
        }
    }
}
