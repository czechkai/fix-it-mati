<?php

namespace FixItMati\DesignPatterns\Behavioral\State;

/**
 * Reviewed State
 * 
 * Admin has reviewed the request and approved it.
 * Ready to be assigned to a technician.
 */
class ReviewedState extends AbstractRequestState
{
    public function __construct()
    {
        $this->name = 'reviewed';
        $this->description = 'Request reviewed and approved by admin';
        $this->allowedTransitions = ['assigned', 'cancelled'];
    }

    public function onEnter(array $requestData): void
    {
        // Notify customer that request was reviewed
        error_log("Request {$requestData['tracking_number']} has been reviewed and approved");
    }
}
