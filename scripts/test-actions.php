<?php
require_once 'autoload.php';

use FixItMati\Models\ServiceRequest;

$model = new ServiceRequest();

echo "=== Testing Service Request Actions ===\n\n";

// Get a pending request
$requests = $model->getAll(['status' => 'pending']);
if (empty($requests)) {
    echo "No pending requests found\n";
    exit;
}

$request = $requests[0];
echo "Testing with request ID: {$request['id']}\n";
echo "Current status: {$request['status']}\n";
echo "Current assigned: " . ($request['assigned_technician_id'] ?? 'None') . "\n\n";

// Test 1: Assign technician (set status to in_progress)
echo "Test 1: Updating assigned_technician_id and status to in_progress...\n";
$updated = $model->update($request['id'], [
    'assigned_technician_id' => '2ee2cb19-1114-475e-9908-8c57aad4c82a', // admin user for testing
    'status' => 'in_progress'
]);

if ($updated) {
    $updatedRequest = $model->find($request['id']);
    echo "✅ Success!\n";
    echo "   New status: {$updatedRequest['status']}\n";
    echo "   Assigned to: {$updatedRequest['assigned_technician_id']}\n\n";
} else {
    echo "❌ Failed to update\n\n";
}

// Test 2: Mark as resolved
echo "Test 2: Updating status to resolved...\n";
$resolved = $model->update($request['id'], [
    'status' => 'resolved',
    'resolved_at' => date('Y-m-d H:i:s'),
    'resolved_by' => '2ee2cb19-1114-475e-9908-8c57aad4c82a'
]);

if ($resolved) {
    $resolvedRequest = $model->find($request['id']);
    echo "✅ Success!\n";
    echo "   New status: {$resolvedRequest['status']}\n";
    echo "   Resolved at: {$resolvedRequest['resolved_at']}\n\n";
} else {
    echo "❌ Failed to update\n\n";
}

// Reset back to pending for further testing
echo "Resetting to pending status...\n";
$model->update($request['id'], [
    'status' => 'pending',
    'assigned_technician_id' => null,
    'resolved_at' => null,
    'resolved_by' => null
]);
echo "✅ Reset complete\n";
