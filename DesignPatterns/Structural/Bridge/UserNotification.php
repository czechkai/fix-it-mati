<?php

namespace FixItMati\DesignPatterns\Structural\Bridge;

use FixItMati\DesignPatterns\Behavioral\Strategy\NotificationStrategy;

/**
 * User Notification (Refined Abstraction)
 * 
 * Design Pattern: Bridge (Refined Abstraction)
 * Purpose: Specific type of notification for user-related events
 * 
 * This extends the Notification abstraction for user-related notifications
 * like request status changes, assignments, etc.
 */
class UserNotification extends Notification
{
    /**
     * Send user notification
     * 
     * @param array $recipient
     * @param array $data
     * @return array
     */
    public function send(array $recipient, array $data): array
    {
        $title = $data['title'] ?? 'Notification';
        $message = $data['message'] ?? '';
        
        // Add user-specific formatting or processing
        $notificationData = array_merge($data, [
            'type' => $data['type'] ?? 'user',
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        return $this->strategy->send($recipient, $title, $message, $notificationData);
    }
}
