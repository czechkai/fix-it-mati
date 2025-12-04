<?php

namespace FixItMati\DesignPatterns\Behavioral\State;

/**
 * Pending State
 * 
 * Initial state when a request is submitted.
 * Waiting for admin review.
 */
class PendingState extends AbstractRequestState
{
    public function __construct()
    {
        $this->name = 'pending';
        $this->description = 'Request submitted and waiting for admin review';
        $this->allowedTransitions = ['reviewed', 'cancelled'];
    }

    public function onEnter(array $requestData): void
    {
        // Log that request is pending
        // Could trigger notification to admins here
        error_log("Request {$requestData['tracking_number']} is now pending review");
    }
}
