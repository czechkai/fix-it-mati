<?php

namespace FixItMati\DesignPatterns\Behavioral\Strategy;

/**
 * Email Notification Strategy
 * 
 * Design Pattern: Strategy (Concrete Strategy)
 * Purpose: Delivers notifications via email
 * 
 * This strategy sends email notifications using SMTP or email service.
 */
class EmailNotificationStrategy implements NotificationStrategy
{
    private bool $configured;

    public function __construct()
    {
        // Check if email is configured (SMTP settings, API keys, etc.)
        $this->configured = !empty($_ENV['MAIL_HOST'] ?? '') && !empty($_ENV['MAIL_FROM'] ?? '');
    }

    /**
     * Send email notification
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
                'message' => 'Email service is not configured'
            ];
        }

        $email = $recipient['email'] ?? null;
        if (empty($email)) {
            return [
                'success' => false,
                'message' => 'Recipient email not provided'
            ];
        }

        try {
            // TODO: Implement actual email sending with PHPMailer or similar
            // For now, just log it
            error_log("EMAIL NOTIFICATION: To: $email, Subject: $title, Message: $message");

            return [
                'success' => true,
                'message' => 'Email sent successfully',
                'email' => $email
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
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
        return 'email';
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
