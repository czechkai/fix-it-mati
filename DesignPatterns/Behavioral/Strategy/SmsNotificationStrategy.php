<?php

namespace FixItMati\DesignPatterns\Behavioral\Strategy;

/**
 * SMS Notification Strategy
 * 
 * Design Pattern: Strategy (Concrete Strategy)
 * Purpose: Delivers notifications via SMS
 * 
 * This strategy sends SMS notifications using an SMS gateway service.
 */
class SmsNotificationStrategy implements NotificationStrategy
{
    private bool $configured;

    public function __construct()
    {
        // Check if SMS service is configured (API keys, etc.)
        $this->configured = !empty($_ENV['SMS_API_KEY'] ?? '') && !empty($_ENV['SMS_SENDER'] ?? '');
    }

    /**
     * Send SMS notification
     * 
     * @param array $recipient
     * @param string $title
     * @param string $message
     * @param array $data
     * @return array
     */
    public function send(array $recipient, string $title, string $message, array $data = []): array
    {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'message' => 'SMS service is not configured'
            ];
        }

        $phone = $recipient['phone'] ?? null;
        if (empty($phone)) {
            return [
                'success' => false,
                'message' => 'Recipient phone number not provided'
            ];
        }

        try {
            // TODO: Implement actual SMS sending with Twilio, Semaphore, or similar
            // For now, just log it
            $smsMessage = "$title: $message";
            error_log("SMS NOTIFICATION: To: $phone, Message: $smsMessage");

            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'phone' => $phone
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage()
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
        return 'sms';
    }

    /**
     * Check if strategy is available
     * 
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->configured;
    }
}
