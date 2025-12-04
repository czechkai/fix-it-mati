<?php

namespace FixItMati\DesignPatterns\Behavioral\State;

/**
 * Completed State
 * 
 * Work has been completed successfully.
 * This is a terminal state - no further transitions allowed.
 */
class CompletedState extends AbstractRequestState
{
    public function __construct()
    {
        $this->name = 'completed';
        $this->description = 'Request completed successfully';
        $this->allowedTransitions = []; // Terminal state
    }

    public function onEnter(array $requestData): void
    {
        // Notify customer of completion
        // Could trigger satisfaction survey
        error_log("Request {$requestData['tracking_number']} has been completed");
    }
}
