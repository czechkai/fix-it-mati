<?php

namespace FixItMati\DesignPatterns\Structural\Bridge;

use FixItMati\DesignPatterns\Behavioral\Strategy\NotificationStrategy;

/**
 * Notification Abstraction (Bridge Pattern)
 * 
 * Design Pattern: Bridge
 * Purpose: Decouples abstraction from implementation
 * 
 * The Bridge pattern separates the notification interface from
 * the actual delivery mechanism, allowing them to vary independently.
 */
abstract class Notification
{
    protected NotificationStrategy $strategy;

    public function __construct(NotificationStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * Set the delivery strategy
     * 
     * @param NotificationStrategy $strategy
     * @return void
     */
    public function setStrategy(NotificationStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * Send notification (abstract method)
     * 
     * @param array $recipient
     * @param array $data
     * @return array
     */
    abstract public function send(array $recipient, array $data): array;
}
