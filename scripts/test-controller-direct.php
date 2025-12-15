<?php
// Directly test invoice creation bypassing authentication
require_once __DIR__ . '/../autoload.php';

use FixItMati\Models\Payment;
use FixItMati\Controllers\PaymentController;
use FixItMati\Core\Request;
use FixItMati\Core\Response;

// Create a mock request with invoice data
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

$invoiceData = [
    'user_id' => 'd8f06a1d-a65b-488c-b327-3ddbde1237e1',
    'bill_type' => 'water',
    'amount' => 500.00,
    'due_date' => '2025-12-30',
    'description' => 'Test Water Bill'
];

// Mock the request body
$GLOBALS['mock_input'] = json_encode($invoiceData);

// Create request
$request = new Request();

// Mock auth user
$reflection = new ReflectionClass($request);
$property = $reflection->getProperty('user');
$property->setAccessible(true);
$property->setValue($request, [
    'id' => '1fd8c666-47f2-49b8-be6f-f395ecb68c33',
    'email' => 'admin@fixitmati.com',
    'role' => 'admin'
]);

// Mock param method
$reflection = new ReflectionClass($request);
$method = $reflection->getMethod('param');
$method->setAccessible(true);

// Set body params
$bodyProperty = $reflection->getProperty('bodyParams');
$bodyProperty->setAccessible(true);
$bodyProperty->setValue($request, $invoiceData);

// Create controller and test
$controller = new PaymentController();

try {
    $response = $controller->createInvoice($request);
    
    echo "Response:\n";
    ob_start();
    $response->send();
    $output = ob_get_clean();
    echo $output . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
