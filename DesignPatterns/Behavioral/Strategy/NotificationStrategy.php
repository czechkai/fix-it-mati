<?php

namespace FixItMati\DesignPatterns\Behavioral\Strategy;

/**
 * Notification Strategy Interface
 * 
 * Design Pattern: Strategy
 * Purpose: Defines interface for different notification delivery methods
 * 
 * The Strategy pattern allows selecting the notification algorithm
 * at runtime (in-app, email, SMS, etc.)
 */
interface NotificationStrategy
{
    /**
     * Send notification using this strategy
     * 
     * @param array $recipient Recipient information
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data
     * @return array Result with success status and message
     */
    public function send(array $recipient, string $title, string $message, array $data = []): array;

    /**
     * Get the channel name for this strategy
     * 
     * @return string
     */
    public function getChannel(): string;

    /**
     * Check if this strategy is available
     * 
     * @return bool
     */
    public function isAvailable(): bool;
}
