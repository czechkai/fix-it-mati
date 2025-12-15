<?php
// Simple API test without authentication

$ch = curl_init('http://localhost:8000/api/admin/users');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status Code: $statusCode\n";
echo "Response:\n";
echo $response . "\n";
