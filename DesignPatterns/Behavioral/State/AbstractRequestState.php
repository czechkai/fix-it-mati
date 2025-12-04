<?php

namespace FixItMati\DesignPatterns\Behavioral\State;

/**
 * Abstract Base State
 * 
 * Provides common functionality for all concrete states.
 */
abstract class AbstractRequestState implements RequestState
{
    protected string $name;
    protected string $description;
    protected array $allowedTransitions = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAllowedTransitions(): array
    {
        return $this->allowedTransitions;
    }

    public function canTransitionTo(string $newState): bool
    {
        return in_array($newState, $this->allowedTransitions);
    }

    public function onEnter(array $requestData): void
    {
        // Default: do nothing
        // Override in subclasses if needed
    }

    public function onExit(array $requestData): void
    {
        // Default: do nothing
        // Override in subclasses if needed
    }
}
