<?php

namespace FixItMati\DesignPatterns\Behavioral\State;

/**
 * RequestState Interface
 * 
 * Defines the contract for all request states.
 * Each state knows what transitions are allowed and handles state-specific behavior.
 */
interface RequestState
{
    /**
     * Get the state name
     */
    public function getName(): string;

    /**
     * Check if transition to another state is allowed
     */
    public function canTransitionTo(string $newState): bool;

    /**
     * Get allowed next states
     */
    public function getAllowedTransitions(): array;

    /**
     * Get the state description for users
     */
    public function getDescription(): string;

    /**
     * Perform any actions when entering this state
     */
    public function onEnter(array $requestData): void;

    /**
     * Perform any actions when exiting this state
     */
    public function onExit(array $requestData): void;
}
