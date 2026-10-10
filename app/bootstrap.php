<?php
declare(strict_types=1);

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/helpers/functions.php';

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('HOTSPOT_SESSID');
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
