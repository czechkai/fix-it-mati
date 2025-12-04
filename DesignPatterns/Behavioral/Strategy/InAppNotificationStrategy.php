<?php

namespace FixItMati\DesignPatterns\Behavioral\Strategy;

use FixItMati\Models\Notification;

/**
 * In-App Notification Strategy
 * 
 * Design Pattern: Strategy (Concrete Strategy)
 * Purpose: Delivers notifications within the application
 * 
 * This strategy creates database records that users can view
 * in their notification center.
 */
class InAppNotificationStrategy implements NotificationStrategy
{
    private Notification $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    /**
     * Send in-app notification
     * 
     * @param array $recipient
     * @param string $title
     * @param string $message
     * @param array $data
     * @return array
     */
    public function send(array $recipient, string $title, string $message, array $data = []): array
    {
        try {
            $notification = $this->notificationModel->create([
                'user_id' => $recipient['id'],
                'type' => $data['type'] ?? 'system',
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'channel' => 'in_app',
                'status' => 'sent',
                'sent_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'In-app notification created',
                'notification_id' => $notification['id'] ?? null
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create in-app notification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get channel name
     * 
     * @return string
     */
    public function getChannel(): string
    {
        return 'in_app';
    }

    /**
     * Check if strategy is available
     * 
     * @return bool
     */
    public function isAvailable(): bool
    {
        return true; // In-app notifications are always available
    }
}
