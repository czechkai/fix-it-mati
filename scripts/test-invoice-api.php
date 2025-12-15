<?php
// Test invoice creation via API
$url = 'http://localhost:8000/api/admin/create-invoice';

$data = [
    'user_id' => 'd8f06a1d-a65b-488c-b327-3ddbde1237e1',
    'bill_type' => 'water',
    'amount' => 500.00,
    'due_date' => '2025-12-30',
    'description' => 'Test Water Bill'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer test'
]);

$response = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Status Code: $statusCode\n";
if ($error) {
    echo "Curl Error: $error\n";
}
echo "Response:\n";
echo $response . "\n";
