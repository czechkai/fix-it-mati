<?php

namespace FixItMati\DesignPatterns\Behavioral\State;

/**
 * StateFactory
 * 
 * Factory to create and manage state instances.
 * Ensures only one instance of each state exists (Flyweight pattern benefit).
 */
class StateFactory
{
    private static array $states = [];

    /**
     * Get state instance by name
     */
    public static function getState(string $stateName): RequestState
    {
        if (!isset(self::$states[$stateName])) {
            self::$states[$stateName] = self::createState($stateName);
        }

        return self::$states[$stateName];
    }

    /**
     * Create a new state instance
     */
    private static function createState(string $stateName): RequestState
    {
        return match ($stateName) {
            'pending' => new PendingState(),
            'reviewed' => new ReviewedState(),
            'assigned' => new AssignedState(),
            'in_progress' => new InProgressState(),
            'completed' => new CompletedState(),
            'cancelled' => new CancelledState(),
            default => throw new \InvalidArgumentException("Unknown state: $stateName")
        };
    }

    /**
     * Get all available states
     */
    public static function getAllStates(): array
    {
        return [
            'pending',
            'reviewed',
            'assigned',
            'in_progress',
            'completed',
            'cancelled'
        ];
    }

    /**
     * Validate if a state name is valid
     */
    public static function isValidState(string $stateName): bool
    {
        return in_array($stateName, self::getAllStates());
    }
}
