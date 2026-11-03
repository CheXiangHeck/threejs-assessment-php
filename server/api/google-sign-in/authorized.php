<?php

/*
 * This file is used to check if the user is logged in and if the session is still valid.
 * It will return a JSON response with the user's email if the session is valid, or an error message if not.
 * 
 * This is used by the frontend when the user refreshes the page or navigates to a different page, to check if the user is still logged in.
 * If the future implementation needs to use Google API, please do not pass the access token and refresh token to the frontend.
 * The leak of access token will allow the attacker to impersonate the user and access Google API that the user gave permission to the application.
 * The leak of refresh token will allow the attacker to get a new access token and imperonate the user.
 */

require __DIR__ . '/../../bootstrap.php';

$idleTimeout = 2592000;

// Check if the user is logged in and if the session is still valid.
$email = $_SESSION['email'] ?? null;
$lastActivity = $_SESSION['last_activity'] ?? 0;

if (!$email || !$lastActivity) {
    fail(401, 'Not logged in.');
}

// Check session expiration.
if (time() - $lastActivity > $idleTimeout) {
    $_SESSION = [];
    session_destroy();

    fail(401, 'Session expired.');
}

// Update activity time.
$_SESSION['last_activity'] = time();

success(200, [
    'authenticated' => true,
    'email' => $email
]);