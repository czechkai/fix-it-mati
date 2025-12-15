<?php
// Test the users API endpoint
$url = 'http://localhost:8000/api/users/all';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer test-token'
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
