<?php

namespace FixItMati\DesignPatterns\Behavioral\Observer;

/**
 * Email Notification Observer
 * 
 * Design Pattern: Observer (Concrete Observer)
 * Purpose: Sends email notifications when events occur
 * 
 * This observer listens for events and sends email notifications
 * to users based on their preferences.
 */
class EmailNotificationObserver implements Observer
{
    /**
     * Handle notification event
     * 
     * @param string $eventName
     * @param array $eventData
     * @return void
     */
    public function update(string $eventName, array $eventData): void
    {
        // Check if user has email notifications enabled
        // This would check notification_preferences table

        // Map events to email handlers
        $handlers = [
            'request.created' => [$this, 'sendRequestCreatedEmail'],
            'request.assigned' => [$this, 'sendRequestAssignedEmail'],
            'request.completed' => [$this, 'sendRequestCompletedEmail'],
            'payment.due' => [$this, 'sendPaymentDueEmail'],
        ];

        if (isset($handlers[$eventName])) {
            call_user_func($handlers[$eventName], $eventData);
        }
    }

    /**
     * Send request created email
     */
    private function sendRequestCreatedEmail(array $data): void
    {
        // Implementation would use PHPMailer or similar
        // For now, just log
        error_log("EMAIL: Request created for user {$data['request']['user_id']}");
        
        // TODO: Implement actual email sending
        // $this->sendEmail($user['email'], $subject, $body);
    }

    /**
     * Send request assigned email
     */
    private function sendRequestAssignedEmail(array $data): void
    {
        error_log("EMAIL: Technician assigned to request {$data['request']['id']}");
    }

    /**
     * Send request completed email
     */
    private function sendRequestCompletedEmail(array $data): void
    {
        error_log("EMAIL: Request completed {$data['request']['id']}");
    }

    /**
     * Send payment due email
     */
    private function sendPaymentDueEmail(array $data): void
    {
        error_log("EMAIL: Payment due for user {$data['payment']['user_id']}");
    }

    /**
     * Send email (placeholder for actual implementation)
     */
    private function sendEmail(string $to, string $subject, string $body): bool
    {
        // This would use PHPMailer or similar email library
        // For now, just return true
        return true;
    }
}
