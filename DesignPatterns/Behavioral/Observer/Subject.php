<?php

namespace FixItMati\DesignPatterns\Behavioral\Observer;

/**
 * Subject Interface
 * 
 * Design Pattern: Observer
 * Purpose: Defines the interface for objects that notify observers of changes
 * 
 * The Subject maintains a list of observers and notifies them of state changes.
 */
interface Subject
{
    /**
     * Attach an observer
     * 
     * @param Observer $observer
     * @return void
     */
    public function attach(Observer $observer): void;

    /**
     * Detach an observer
     * 
     * @param Observer $observer
     * @return void
     */
    public function detach(Observer $observer): void;

    /**
     * Notify all observers
     * 
     * @param string $eventName
     * @param array $eventData
     * @return void
     */
    public function notify(string $eventName, array $eventData): void;
}
