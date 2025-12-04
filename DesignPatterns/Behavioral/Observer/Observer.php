<?php

namespace FixItMati\DesignPatterns\Behavioral\Observer;

/**
 * Observer Interface
 * 
 * Design Pattern: Observer
 * Purpose: Defines the interface for objects that should be notified of changes
 * 
 * Part of the Observer pattern where observers subscribe to subjects
 * and get notified when events occur.
 */
interface Observer
{
    /**
     * Receive update from subject
     * 
     * @param string $eventName The name of the event
     * @param array $eventData Data associated with the event
     * @return void
     */
    public function update(string $eventName, array $eventData): void;
}
