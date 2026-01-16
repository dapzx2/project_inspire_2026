<?php
session_start();

// clear semua session data
$_SESSION = [];

// hapus session cookie
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

// destroy session
session_destroy();

// redirect ke login dengan pesan
header('Location: index.php?pesan=logout');
exit;
