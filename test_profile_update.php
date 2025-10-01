<?php

// Simple test script to debug the profile update issue
$token = '22|rSrZBTCVjgFwrlqgrxeIAFmsuiLIX7Fl2acOMNI3bd3f943c';
$url = 'http://127.0.0.1:8000/api/onboarding/update-profile';

$postData = [
    'name' => 'Linn Lowe',
    'email' => 'prohaska.lilla@example.org', 
    'phone' => '+1234567890',
    'gender' => 'male',
    'dob' => '2007-10-04'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

echo "Testing profile update endpoint...\n";
echo "URL: $url\n";
echo "Data: " . json_encode($postData) . "\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($error) {
    echo "cURL Error: $error\n";
}
echo "Response: $response\n";