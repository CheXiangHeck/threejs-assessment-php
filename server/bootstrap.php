<?php

// The vendor autolad will load all the dependencies required for the application. 
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// This is a safe load, if the .env file is missing, it will not throw error.
$dotenv->safeLoad();

const FRONTEND_ORIGIN = 'https://threejs-assessment.vercel.app';

// Set headers for CORS and content type
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . FRONTEND_ORIGIN);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// 30-day session
$idleTimeout = 2592000;

ini_set('session.gc_maxlifetime', (string)$idleTimeout);

// The session cookie is a server side cookie. It will be sent to the client, but it will not be accessible via JavaScript.
// The HttpOnly flag is unnecessary to do but considering it may require Google API in the future that most likely will send Access Token and Refresh Token, it is better to set the cookie as HttpOnly.
session_set_cookie_params([
    'lifetime' => $idleTimeout,
    'httponly' => true,
    'secure' => true, // Change to true when using HTTPS
    'samesite' => 'Lax',
    'path' => '/',
]);

// This is preventing multiple session start errors.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// A void method that will send a JSON response with an error message and exit the script.
function fail(int $code, string $message, array $details = []): never
{
    http_response_code($code);

    echo json_encode(array_merge(
        ['error' => $message],
        $details
    ));

    exit;
}

// A void method that will send a JSON response with a success message and exit the script.
function success(int $code, array $data): never {
    http_response_code($code);
    echo json_encode($data);
    exit;
}