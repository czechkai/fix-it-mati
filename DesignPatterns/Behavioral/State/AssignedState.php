<?php

namespace FixItMati\DesignPatterns\Behavioral\State;

/**
 * Assigned State
 * 
 * Request has been assigned to a technician.
 * Waiting for technician to start work.
 */
class AssignedState extends AbstractRequestState
{
    public function __construct()
    {
        $this->name = 'assigned';
        $this->description = 'Request assigned to technician';
        $this->allowedTransitions = ['in_progress', 'reviewed', 'cancelled'];
    }

    public function onEnter(array $requestData): void
    {
        // Notify customer and technician about assignment
        error_log("Request {$requestData['tracking_number']} assigned to technician ID: {$requestData['assigned_to']}");
    }
}
