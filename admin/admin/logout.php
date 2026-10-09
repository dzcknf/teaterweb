<?php
/**
 * logout.php
 * Menghancurkan session admin dan kembali ke halaman login.
 */

require_once 'includes/config.php';

// Hapus semua data session
$_SESSION = [];

// Hancurkan cookie session jika ada
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

header('Location: index.php');
exit;
