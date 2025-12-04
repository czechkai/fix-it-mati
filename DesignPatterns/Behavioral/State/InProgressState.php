<?php

namespace FixItMati\DesignPatterns\Behavioral\State;

/**
 * InProgress State
 * 
 * Technician is actively working on the request.
 */
class InProgressState extends AbstractRequestState
{
    public function __construct()
    {
        $this->name = 'in_progress';
        $this->description = 'Technician is working on the request';
        $this->allowedTransitions = ['completed', 'assigned', 'cancelled'];
    }

    public function onEnter(array $requestData): void
    {
        // Notify customer that work has started
        error_log("Work started on request {$requestData['tracking_number']}");
    }
}
