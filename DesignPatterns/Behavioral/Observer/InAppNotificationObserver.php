<?php

namespace FixItMati\DesignPatterns\Behavioral\Observer;

use FixItMati\Models\Notification;

/**
 * In-App Notification Observer
 * 
 * Design Pattern: Observer (Concrete Observer)
 * Purpose: Creates in-app notifications when events occur
 * 
 * This observer listens for events and creates database records
 * for in-app notifications that users can view in their dashboard.
 */
class InAppNotificationObserver implements Observer
{
    private Notification $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    /**
     * Handle notification event
     * 
     * @param string $eventName
     * @param array $eventData
     * @return void
     */
    public function update(string $eventName, array $eventData): void
    {
        // Map event names to notification creation
        $handlers = [
            'request.created' => [$this, 'handleRequestCreated'],
            'request.reviewed' => [$this, 'handleRequestReviewed'],
            'request.assigned' => [$this, 'handleRequestAssigned'],
            'request.in_progress' => [$this, 'handleRequestInProgress'],
            'request.completed' => [$this, 'handleRequestCompleted'],
            'request.cancelled' => [$this, 'handleRequestCancelled'],
            'payment.due' => [$this, 'handlePaymentDue'],
            'payment.received' => [$this, 'handlePaymentReceived'],
            'announcement.created' => [$this, 'handleAnnouncementCreated'],
        ];

        if (isset($handlers[$eventName])) {
            call_user_func($handlers[$eventName], $eventData);
        }
    }

    /**
     * Handle request created event
     */
    private function handleRequestCreated(array $data): void
    {
        $request = $data['request'];

        // Notify customer
        $this->createNotification([
            'user_id' => $request['user_id'],
            'type' => 'request_status',
            'title' => 'Service Request Submitted',
            'message' => "Your service request \"{$request['title']}\" has been submitted successfully.",
            'data' => [
                'request_id' => $request['id'],
                'tracking_number' => $request['tracking_number'] ?? null,
                'category' => $request['category']
            ],
            'channel' => 'in_app'
        ]);

        // Notify admins (would need admin user IDs from config or database)
        // Implementation depends on how you want to identify admins
    }

    /**
     * Handle request reviewed event
     */
    private function handleRequestReviewed(array $data): void
    {
        $request = $data['request'];

        $this->createNotification([
            'user_id' => $request['user_id'],
            'type' => 'request_status',
            'title' => 'Request Under Review',
            'message' => "Your service request \"{$request['title']}\" is now under review.",
            'data' => [
                'request_id' => $request['id'],
                'tracking_number' => $request['tracking_number'] ?? null
            ],
            'channel' => 'in_app'
        ]);
    }

    /**
     * Handle technician assigned event
     */
    private function handleRequestAssigned(array $data): void
    {
        $request = $data['request'];
        $technician = $data['technician'] ?? null;

        $message = "A technician has been assigned to your request \"{$request['title']}\".";
        if ($technician) {
            $message = "Technician {$technician['name']} has been assigned to your request \"{$request['title']}\".";
        }

        // Notify customer
        $this->createNotification([
            'user_id' => $request['user_id'],
            'type' => 'assignment',
            'title' => 'Technician Assigned',
            'message' => $message,
            'data' => [
                'request_id' => $request['id'],
                'tracking_number' => $request['tracking_number'] ?? null,
                'technician_id' => $technician['id'] ?? null,
                'technician_name' => $technician['name'] ?? null
            ],
            'channel' => 'in_app'
        ]);

        // Notify technician
        if ($technician && isset($technician['id'])) {
            $this->createNotification([
                'user_id' => $technician['id'],
                'type' => 'assignment',
                'title' => 'New Assignment',
                'message' => "You have been assigned to service request \"{$request['title']}\".",
                'data' => [
                    'request_id' => $request['id'],
                    'tracking_number' => $request['tracking_number'] ?? null
                ],
                'channel' => 'in_app'
            ]);
        }
    }

    /**
     * Handle request in progress event
     */
    private function handleRequestInProgress(array $data): void
    {
        $request = $data['request'];

        $this->createNotification([
            'user_id' => $request['user_id'],
            'type' => 'request_status',
            'title' => 'Work In Progress',
            'message' => "Work has started on your request \"{$request['title']}\".",
            'data' => [
                'request_id' => $request['id'],
                'tracking_number' => $request['tracking_number'] ?? null
            ],
            'channel' => 'in_app'
        ]);
    }

    /**
     * Handle request completed event
     */
    private function handleRequestCompleted(array $data): void
    {
        $request = $data['request'];

        $this->createNotification([
            'user_id' => $request['user_id'],
            'type' => 'request_status',
            'title' => 'Request Completed',
            'message' => "Your service request \"{$request['title']}\" has been completed.",
            'data' => [
                'request_id' => $request['id'],
                'tracking_number' => $request['tracking_number'] ?? null,
                'completion_notes' => $data['notes'] ?? null
            ],
            'channel' => 'in_app'
        ]);
    }

    /**
     * Handle request cancelled event
     */
    private function handleRequestCancelled(array $data): void
    {
        $request = $data['request'];

        $this->createNotification([
            'user_id' => $request['user_id'],
            'type' => 'request_status',
            'title' => 'Request Cancelled',
            'message' => "Your service request \"{$request['title']}\" has been cancelled.",
            'data' => [
                'request_id' => $request['id'],
                'tracking_number' => $request['tracking_number'] ?? null,
                'reason' => $data['reason'] ?? null
            ],
            'channel' => 'in_app'
        ]);
    }

    /**
     * Handle payment due event
     */
    private function handlePaymentDue(array $data): void
    {
        $payment = $data['payment'];

        $this->createNotification([
            'user_id' => $payment['user_id'],
            'type' => 'payment',
            'title' => 'Payment Due',
            'message' => "Your {$payment['service_type']} bill of ₱{$payment['amount']} is due on {$payment['due_date']}.",
            'data' => [
                'payment_id' => $payment['id'],
                'amount' => $payment['amount'],
                'due_date' => $payment['due_date']
            ],
            'channel' => 'in_app'
        ]);
    }

    /**
     * Handle payment received event
     */
    private function handlePaymentReceived(array $data): void
    {
        $payment = $data['payment'];

        $this->createNotification([
            'user_id' => $payment['user_id'],
            'type' => 'payment',
            'title' => 'Payment Received',
            'message' => "Payment of ₱{$payment['amount']} received for {$payment['service_type']}. Thank you!",
            'data' => [
                'payment_id' => $payment['id'],
                'amount' => $payment['amount'],
                'reference_number' => $payment['reference_number'] ?? null
            ],
            'channel' => 'in_app'
        ]);
    }

    /**
     * Handle announcement created event
     */
    private function handleAnnouncementCreated(array $data): void
    {
        $announcement = $data['announcement'];
        $recipients = $data['recipients'] ?? [];

        foreach ($recipients as $userId) {
            $this->createNotification([
                'user_id' => $userId,
                'type' => 'announcement',
                'title' => 'New Announcement',
                'message' => "{$announcement['title']}: " . substr($announcement['content'], 0, 100),
                'data' => [
                    'announcement_id' => $announcement['id'],
                    'category' => $announcement['category'] ?? null,
                    'priority' => $announcement['priority'] ?? 'normal'
                ],
                'channel' => 'in_app'
            ]);
        }
    }

    /**
     * Create a notification record
     */
    private function createNotification(array $data): void
    {
        try {
            $this->notificationModel->create($data);
        } catch (\Exception $e) {
            error_log("Failed to create notification: " . $e->getMessage());
        }
    }
}
