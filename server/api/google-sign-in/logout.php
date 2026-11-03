<?php

require __DIR__ . '/../../bootstrap.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session and send a success response.
session_destroy();

success(200, [
    'authenticated' => false,
    'message' => 'Logged out.'
]);