<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Use the logout button to end your session.');
}

if (!app_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Your session expired. Refresh the page and try again.');
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]
    );
}

session_destroy();
header('Location: login.php?message=Logged%20out%20successfully');
exit();
