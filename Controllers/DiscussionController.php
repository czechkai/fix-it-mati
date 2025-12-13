<?php

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\Models\Discussion;

/**
 * DiscussionController
 * 
 * Handles HTTP requests for community discussions.
 */
class DiscussionController
{
    private Discussion $discussionModel;

    public function __construct()
    {
        $this->discussionModel = new Discussion();
    }

    /**
     * Get all discussions
     * GET /api/discussions
     */
    public function index(Request $request): Response
    {
        try {
            $user = $request->user();
            $category = $request->query('category');
            $sort = $request->query('sort', 'newest');
            
            $discussions = $this->discussionModel->getAll($category, $sort, $user['id'] ?? null);
            
            return Response::success($discussions);
        } catch (\Exception $e) {
            return Response::error('Failed to fetch discussions: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single discussion with comments
     * GET /api/discussions/:id
     */
    public function show(Request $request): Response
    {
        try {
            $user = $request->user();
            $id = $request->param('id');
            
            $discussion = $this->discussionModel->find($id, $user['id'] ?? null);
            
            if (!$discussion) {
                return Response::error('Discussion not found', 404);
            }
            
            // Get comments
            $comments = $this->discussionModel->getComments($id);
            $discussion['comments'] = $comments;
            
            return Response::success($discussion);
        } catch (\Exception $e) {
            return Response::error('Failed to fetch discussion: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new discussion
     * POST /api/discussions
     */
    public function create(Request $request): Response
    {
        $user = $request->user();
        $data = $request->all();

        // Validate
        if (empty($data['title']) || empty($data['content']) || empty($data['category'])) {
            return Response::error('Title, content, and category are required', 400);
        }

        $validCategories = ['Water Supply', 'Electricity', 'Billing', 'General'];
        if (!in_array($data['category'], $validCategories)) {
            return Response::error('Invalid category', 400);
        }

        try {
            $discussion = $this->discussionModel->create([
                'user_id' => $user['id'],
                'category' => $data['category'],
                'title' => $data['title'],
                'content' => $data['content']
            ]);

            if ($discussion) {
                return Response::created($discussion, 'Discussion created successfully');
            } else {
                return Response::error('Failed to create discussion', 500);
            }
        } catch (\Exception $e) {
            return Response::error('Failed to create discussion: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Upvote a discussion
     * POST /api/discussions/:id/upvote
     */
    public function upvote(Request $request): Response
    {
        $user = $request->user();
        $discussionId = $request->param('id');

        try {
            $result = $this->discussionModel->toggleUpvote($discussionId, $user['id']);
            
            return Response::success([
                'upvotes' => $result['upvotes'],
                'user_upvoted' => $result['user_upvoted']
            ]);
        } catch (\Exception $e) {
            return Response::error('Failed to upvote: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Add comment to discussion
     * POST /api/discussions/:id/comments
     */
    public function addComment(Request $request): Response
    {
        $user = $request->user();
        $discussionId = $request->param('id');
        $data = $request->all();

        if (empty($data['content'])) {
            return Response::error('Comment content is required', 400);
        }

        try {
            $comment = $this->discussionModel->addComment([
                'discussion_id' => $discussionId,
                'user_id' => $user['id'],
                'content' => $data['content']
            ]);

            if ($comment) {
                return Response::created($comment, 'Comment added successfully');
            } else {
                return Response::error('Failed to add comment', 500);
            }
        } catch (\Exception $e) {
            return Response::error('Failed to add comment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark comment as solution
     * POST /api/discussions/:id/comments/:commentId/mark-solution
     */
    public function markSolution(Request $request): Response
    {
        $user = $request->user();
        $discussionId = $request->param('id');
        $commentId = $request->param('commentId');

        try {
            // Verify user owns the discussion
            $discussion = $this->discussionModel->find($discussionId);
            if (!$discussion) {
                return Response::error('Discussion not found', 404);
            }

            if ($discussion['user_id'] !== $user['id'] && $user['role'] !== 'admin') {
                return Response::error('Only the discussion author can mark a solution', 403);
            }

            $result = $this->discussionModel->markCommentAsSolution($discussionId, $commentId);
            
            if ($result) {
                return Response::success(null, 'Comment marked as solution');
            } else {
                return Response::error('Failed to mark solution', 500);
            }
        } catch (\Exception $e) {
            return Response::error('Failed to mark solution: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete discussion
     * DELETE /api/discussions/:id
     */
    public function delete(Request $request): Response
    {
        $user = $request->user();
        $discussionId = $request->param('id');

        try {
            $discussion = $this->discussionModel->find($discussionId);
            if (!$discussion) {
                return Response::error('Discussion not found', 404);
            }

            // Only author or admin can delete
            if ($discussion['user_id'] !== $user['id'] && $user['role'] !== 'admin') {
                return Response::error('You do not have permission to delete this discussion', 403);
            }

            $result = $this->discussionModel->delete($discussionId);
            
            if ($result) {
                return Response::success(null, 'Discussion deleted successfully');
            } else {
                return Response::error('Failed to delete discussion', 500);
            }
        } catch (\Exception $e) {
            return Response::error('Failed to delete discussion: ' . $e->getMessage(), 500);
        }
    }
}
