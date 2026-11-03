<?php

require __DIR__ . '/../../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail(405, 'Method not allowed.');
}

// Read authorization code from frontend
$input = json_decode(file_get_contents('php://input'), true);
$code = $input['code'] ?? null;

if (!$code) {
    fail(400, 'Missing authorization code.');
}

// Exchange authorization code for access token
$curl = curl_init('https://oauth2.googleapis.com/token');

curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'code' => $code,
        'client_id' => $_ENV['GOOGLE_CLIENT_ID'],
        'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'],
        'redirect_uri' => 'postmessage',
        'grant_type' => 'authorization_code'
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15
]);

// Execute the cURL request to exchange the authorization code for an access token
$tokenResponse = curl_exec($curl);
$tokenStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ($tokenResponse === false) {
    curl_close($curl);
    fail(500, 'Unable to connect to Google token service.');
}

curl_close($curl);

// Decode the JSON response from Google to extract the access token
$token = json_decode($tokenResponse, true);

if ($tokenStatus !== 200 || empty($token['access_token'])) {
    fail(401, 'Google authentication failed.');
}

// Retrieve Google account email
$curl = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token['access_token']
    ],
    CURLOPT_TIMEOUT => 15
]);

// Execute the cURL request to retrieve user information
$userResponse = curl_exec($curl);
$userStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ($userResponse === false) {
    curl_close($curl);
    fail(500, 'Unable to retrieve Google account information.');
}

curl_close($curl);

$userInfo = json_decode($userResponse, true);

if ($tokenStatus !== 200 || empty($token['access_token'])) {
    fail(401, 'Google token exchange failed.', [
        'google_response' => $token
    ]);
}

// Store email in PHP session only
session_regenerate_id(true);

$_SESSION['email'] = $userInfo['email'];
$_SESSION['last_activity'] = time();

success(200, [
    'authenticated' => true,
    'email' => $userInfo['email']
]);