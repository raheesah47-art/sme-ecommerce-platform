<?php
declare(strict_types=1);

const SITE_NAME = 'SMEConnect';
define('DEV_MODE', true);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$base = preg_replace('#/(pages|sme|admin)(/.*)?$#', '', $script);
$base = rtrim(str_replace('/index.php', '', $base), '/');
define('SITE_URL', $scheme . '://' . $host . $base);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    ]);
    session_start();
}
