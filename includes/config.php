<?php
declare(strict_types=1);

const SITE_NAME = 'SMEConnect';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
// All root pages use absolute site paths; XAMPP will normally expose /SMEConnect.
define('SITE_URL', $scheme . '://' . $host . ($base === '/' ? '' : preg_replace('#/(pages|sme|admin)$#', '', $base)));
define('DEV_MODE', true);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    ]);
    session_start();
}
