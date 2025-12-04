<?php

namespace FixItMati\DesignPatterns\Structural\Bridge;

use FixItMati\DesignPatterns\Behavioral\Strategy\NotificationStrategy;

/**
 * System Notification (Refined Abstraction)
 * 
 * Design Pattern: Bridge (Refined Abstraction)
 * Purpose: Specific type of notification for system events
 * 
 * This extends the Notification abstraction for system-level notifications.
 */
class SystemNotification extends Notification
{
    /**
     * Send system notification
     * 
     * @param array $recipient
     * @param array $data
     * @return array
     */
    public function send(array $recipient, array $data): array
    {
        $title = $data['title'] ?? 'System Notification';
        $message = $data['message'] ?? '';
        $notificationData = array_merge($data, ['type' => 'system']);

        return $this->strategy->send($recipient, $title, $message, $notificationData);
    }
}
