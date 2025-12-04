<?php

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\Services\NotificationService;

/**
 * NotificationController
 * 
 * Handles HTTP requests for notification operations.
 */
class NotificationController
{
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = NotificationService::getInstance();
    }

    /**
     * Get all notifications for the authenticated user
     * GET /api/notifications
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $filters = [
            'is_read' => $request->query('is_read'),
            'type' => $request->query('type'),
            'limit' => $request->query('limit', 50),
            'offset' => $request->query('offset', 0)
        ];

        // Remove null values
        $filters = array_filter($filters, fn($value) => $value !== null);

        $notifications = $this->notificationService->getUserNotifications($user['id'], $filters);

        return Response::success([
            'notifications' => $notifications,
            'total' => count($notifications),
            'unread_count' => $this->notificationService->getUnreadCount($user['id'])
        ]);
    }

    /**
     * Get unread notification count
     * GET /api/notifications/unread-count
     */
    public function unreadCount(Request $request): Response
    {
        $user = $request->user();
        $count = $this->notificationService->getUnreadCount($user['id']);

        return Response::success([
            'unread_count' => $count
        ]);
    }

    /**
     * Mark notification as read
     * PATCH /api/notifications/{id}/read
     */
    public function markAsRead(Request $request): Response
    {
        $user = $request->user();
        $id = $request->param('id');

        if (!$id) {
            return Response::badRequest('Notification ID is required');
        }

        $success = $this->notificationService->markAsRead($id, $user['id']);

        if ($success) {
            return Response::success(null, 'Notification marked as read');
        }

        return Response::notFound('Notification not found');
    }

    /**
     * Mark all notifications as read
     * POST /api/notifications/mark-all-read
     */
    public function markAllAsRead(Request $request): Response
    {
        $user = $request->user();

        $success = $this->notificationService->markAllAsRead($user['id']);

        if ($success) {
            return Response::success(null, 'All notifications marked as read');
        }

        return Response::error('Failed to mark notifications as read', 500);
    }

    /**
     * Delete notification
     * DELETE /api/notifications/{id}
     */
    public function delete(Request $request): Response
    {
        $user = $request->user();
        $id = $request->param('id');

        if (!$id) {
            return Response::badRequest('Notification ID is required');
        }

        $success = $this->notificationService->deleteNotification($id, $user['id']);

        if ($success) {
            return Response::success(null, 'Notification deleted');
        }

        return Response::notFound('Notification not found');
    }

    /**
     * Send test notification (for testing only)
     * POST /api/notifications/test
     */
    public function sendTest(Request $request): Response
    {
        $user = $request->user();
        $data = $request->all();

        $result = $this->notificationService->sendNotification(
            $user,
            'user',
            [
                'title' => $data['title'] ?? 'Test Notification',
                'message' => $data['message'] ?? 'This is a test notification',
                'type' => 'system'
            ],
            $data['channel'] ?? 'in_app'
        );

        if ($result['success']) {
            return Response::success($result, 'Test notification sent');
        }

        return Response::error($result['message'], 400);
    }
}
