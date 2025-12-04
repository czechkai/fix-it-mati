<?php

namespace FixItMati\Services;

use FixItMati\DesignPatterns\Behavioral\Observer\EventManager;
use FixItMati\DesignPatterns\Behavioral\Observer\InAppNotificationObserver;
use FixItMati\DesignPatterns\Behavioral\Observer\EmailNotificationObserver;
use FixItMati\DesignPatterns\Behavioral\Strategy\InAppNotificationStrategy;
use FixItMati\DesignPatterns\Behavioral\Strategy\EmailNotificationStrategy;
use FixItMati\DesignPatterns\Behavioral\Strategy\SmsNotificationStrategy;
use FixItMati\DesignPatterns\Structural\Bridge\UserNotification;
use FixItMati\DesignPatterns\Structural\Bridge\SystemNotification;
use FixItMati\Models\Notification as NotificationModel;

/**
 * Notification Service
 * 
 * Orchestrates the notification system using Observer, Strategy, and Bridge patterns.
 * 
 * Design Patterns Used:
 * - Observer: EventManager notifies observers of events
 * - Strategy: Different notification delivery methods
 * - Bridge: Decouples notification types from delivery mechanisms
 */
class NotificationService
{
    private static ?NotificationService $instance = null;
    private EventManager $eventManager;
    private NotificationModel $notificationModel;
    private array $strategies = [];

    private function __construct()
    {
        $this->eventManager = new EventManager();
        $this->notificationModel = new NotificationModel();
        
        // Register observers
        $this->registerObservers();
        
        // Initialize strategies
        $this->initializeStrategies();
    }

    /**
     * Get singleton instance
     * 
     * @return NotificationService
     */
    public static function getInstance(): NotificationService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register observers for different events
     */
    private function registerObservers(): void
    {
        // Register in-app notification observer
        $inAppObserver = new InAppNotificationObserver();
        $this->eventManager->attach($inAppObserver);

        // Register email notification observer
        $emailObserver = new EmailNotificationObserver();
        $this->eventManager->attach($emailObserver);
    }

    /**
     * Initialize notification strategies
     */
    private function initializeStrategies(): void
    {
        $this->strategies['in_app'] = new InAppNotificationStrategy();
        $this->strategies['email'] = new EmailNotificationStrategy();
        $this->strategies['sms'] = new SmsNotificationStrategy();
    }

    /**
     * Trigger an event
     * 
     * @param string $eventName
     * @param array $eventData
     * @return void
     */
    public function trigger(string $eventName, array $eventData): void
    {
        $this->eventManager->notify($eventName, $eventData);
    }

    /**
     * Send notification using Strategy and Bridge patterns
     * 
     * @param array $recipient
     * @param string $type Type of notification (user, system)
     * @param array $data Notification data
     * @param string $channel Delivery channel (in_app, email, sms)
     * @return array
     */
    public function sendNotification(array $recipient, string $type, array $data, string $channel = 'in_app'): array
    {
        // Get the appropriate strategy
        $strategy = $this->strategies[$channel] ?? $this->strategies['in_app'];

        if (!$strategy->isAvailable()) {
            return [
                'success' => false,
                'message' => "Notification channel '$channel' is not available"
            ];
        }

        // Use Bridge pattern to select notification type
        if ($type === 'system') {
            $notification = new SystemNotification($strategy);
        } else {
            $notification = new UserNotification($strategy);
        }

        // Send the notification
        return $notification->send($recipient, $data);
    }

    /**
     * Send notification through multiple channels
     * 
     * @param array $recipient
     * @param string $type
     * @param array $data
     * @param array $channels
     * @return array
     */
    public function sendMultiChannel(array $recipient, string $type, array $data, array $channels = ['in_app']): array
    {
        $results = [];

        foreach ($channels as $channel) {
            $results[$channel] = $this->sendNotification($recipient, $type, $data, $channel);
        }

        return [
            'success' => !empty(array_filter($results, fn($r) => $r['success'])),
            'results' => $results
        ];
    }

    /**
     * Get user notifications
     * 
     * @param string $userId
     * @param array $filters
     * @return array
     */
    public function getUserNotifications(string $userId, array $filters = []): array
    {
        return $this->notificationModel->getByUser($userId, $filters);
    }

    /**
     * Mark notification as read
     * 
     * @param string $notificationId
     * @param string $userId
     * @return bool
     */
    public function markAsRead(string $notificationId, string $userId): bool
    {
        return $this->notificationModel->markAsRead($notificationId, $userId);
    }

    /**
     * Mark all notifications as read
     * 
     * @param string $userId
     * @return bool
     */
    public function markAllAsRead(string $userId): bool
    {
        return $this->notificationModel->markAllAsRead($userId);
    }

    /**
     * Get unread notification count
     * 
     * @param string $userId
     * @return int
     */
    public function getUnreadCount(string $userId): int
    {
        return $this->notificationModel->getUnreadCount($userId);
    }

    /**
     * Delete notification
     * 
     * @param string $notificationId
     * @param string $userId
     * @return bool
     */
    public function deleteNotification(string $notificationId, string $userId): bool
    {
        return $this->notificationModel->delete($notificationId, $userId);
    }
}
