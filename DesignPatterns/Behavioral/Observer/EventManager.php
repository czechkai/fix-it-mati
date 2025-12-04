<?php

namespace FixItMati\DesignPatterns\Behavioral\Observer;

/**
 * Event Manager (Subject Implementation)
 * 
 * Design Pattern: Observer
 * Purpose: Manages observers and notifies them of events
 * 
 * This is the concrete Subject that maintains a list of observers
 * and triggers notifications when events occur.
 */
class EventManager implements Subject
{
    /**
     * @var array<string, Observer[]> Map of event names to observers
     */
    private array $observers = [];

    /**
     * Attach an observer to a specific event
     * 
     * @param Observer $observer
     * @param string|null $eventName Specific event to observe, or null for all events
     * @return void
     */
    public function attach(Observer $observer, ?string $eventName = null): void
    {
        $key = $eventName ?? '*';
        
        if (!isset($this->observers[$key])) {
            $this->observers[$key] = [];
        }

        $this->observers[$key][] = $observer;
    }

    /**
     * Detach an observer
     * 
     * @param Observer $observer
     * @return void
     */
    public function detach(Observer $observer): void
    {
        foreach ($this->observers as $eventName => $observers) {
            $this->observers[$eventName] = array_filter(
                $observers,
                fn($o) => $o !== $observer
            );
        }
    }

    /**
     * Notify all observers of an event
     * 
     * @param string $eventName
     * @param array $eventData
     * @return void
     */
    public function notify(string $eventName, array $eventData): void
    {
        // Notify specific event observers
        if (isset($this->observers[$eventName])) {
            foreach ($this->observers[$eventName] as $observer) {
                $observer->update($eventName, $eventData);
            }
        }

        // Notify wildcard observers (subscribed to all events)
        if (isset($this->observers['*'])) {
            foreach ($this->observers['*'] as $observer) {
                $observer->update($eventName, $eventData);
            }
        }
    }

    /**
     * Get all registered observers
     * 
     * @return array
     */
    public function getObservers(): array
    {
        return $this->observers;
    }

    /**
     * Clear all observers
     * 
     * @return void
     */
    public function clearObservers(): void
    {
        $this->observers = [];
    }
}
