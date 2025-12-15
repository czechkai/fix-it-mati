<?php
// Test login and then invoice creation
$loginUrl = 'http://localhost:8000/api/auth/login';
$loginData = [
    'email' => 'jasme@gmail.com',
    'password' => 'Iloveyou123'
];

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login Status: $statusCode\n";
echo "Login Response:\n$response\n\n";

$loginResult = json_decode($response, true);

if (isset($loginResult['data']['token'])) {
    $token = $loginResult['data']['token'];
    echo "Token obtained: $token\n\n";
    
    // Now test invoice creation
    $invoiceUrl = 'http://localhost:8000/api/admin/billing/create-invoice';
    $invoiceData = [
        'user_id' => 'd8f06a1d-a65b-488c-b327-3ddbde1237e1',
        'bill_type' => 'water',
        'amount' => 500.00,
        'due_date' => '2025-12-30',
        'description' => 'Test Water Bill via API'
    ];
    
    $ch = curl_init($invoiceUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invoiceData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "Authorization: Bearer $token"
    ]);
    
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Invoice Creation Status: $statusCode\n";
    echo "Invoice Response:\n$response\n";
} else {
    echo "Failed to get token\n";
}
