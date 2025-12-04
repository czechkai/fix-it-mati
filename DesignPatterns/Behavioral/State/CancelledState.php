<?php

namespace FixItMati\DesignPatterns\Behavioral\State;

/**
 * Cancelled State
 * 
 * Request was cancelled (by customer or admin).
 * This is a terminal state - no further transitions allowed.
 */
class CancelledState extends AbstractRequestState
{
    public function __construct()
    {
        $this->name = 'cancelled';
        $this->description = 'Request has been cancelled';
        $this->allowedTransitions = []; // Terminal state
    }

    public function onEnter(array $requestData): void
    {
        // Notify relevant parties of cancellation
        error_log("Request {$requestData['tracking_number']} has been cancelled");
    }
}
