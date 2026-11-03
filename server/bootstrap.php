<?php

// Load Composer dependencies.
require __DIR__ . '/../vendor/autoload.php';

// Load environment variables from the project root.
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Frontend allowed to access this backend.
// Replace this with your actual Railway frontend domain.
const FRONTEND_ORIGIN = 'https://your-frontend.up.railway.app';

// Configure CORS.
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin === FRONTEND_ORIGIN) {
    header('Access-Control-Allow-Origin: ' . FRONTEND_ORIGIN);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Vary: Origin');
}

// Handle CORS preflight requests before starting a session.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Set JSON response content type.
header('Content-Type: application/json; charset=utf-8');

// Configure session lifetime.
$idleTimeout = 2592000; // 30 days

ini_set('session.gc_maxlifetime', (string) $idleTimeout);

$sessionPath = '/var/data/sessions';

if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0700, true);
}

if (!is_writable($sessionPath)) {
    throw new RuntimeException('PHP session directory is not writable.');
}

ini_set('session.save_path', $sessionPath);

// Configure session cookie.
session_set_cookie_params([
    'lifetime' => $idleTimeout,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None',
]);

// Start the session.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Send a JSON error response and terminate execution.
function fail(int $code, string $message, array $details = []): never
{
    http_response_code($code);

    echo json_encode(array_merge(
        ['error' => $message],
        $details
    ));

    exit;
}

// Send a JSON success response and terminate execution.
function success(int $code, array $data): never
{
    http_response_code($code);

    echo json_encode($data);

    exit;
}