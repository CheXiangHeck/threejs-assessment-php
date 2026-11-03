<?php

require_once __DIR__ . '/../../bootstrap.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please sign in first.'
    ]);

    exit;
}

$spreadsheetId = '111_ENaxCo8Y3v3fAMWQuocQIrK53pE1lADEkqnrDBOU';
$sheetName = 'Data Template';
$apiKey = $_ENV['GS_API_KEY'];

$url = sprintf(
    'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s?%s',
    $spreadsheetId,
    rawurlencode($sheetName),
    http_build_query([
        'key' => $apiKey
    ])
);

$curl = curl_init($url);

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ($response === false) {
    $error = curl_error($curl);
    curl_close($curl);

    http_response_code(500);
    echo json_encode(['error' => $error]);
    exit;
}

curl_close($curl);

http_response_code($httpCode);
header('Content-Type: application/json');

echo $response;