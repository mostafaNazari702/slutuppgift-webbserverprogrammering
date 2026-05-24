<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Stockholm');

define('DB_HOST', 'localhost');
define('DB_NAME', 'skickaupp');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'SkickaUpp');

if (!defined('APP_URL')) {
    $scheme = 'http';
    if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')) {
        $scheme = 'https';
    }
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $projectRoot = realpath(__DIR__ . '/..');
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $basePath = '';
    if ($docRoot && $projectRoot && strpos($projectRoot, $docRoot) === 0) {
        $basePath = str_replace('\\', '/', substr($projectRoot, strlen($docRoot)));
    }
    define('APP_URL', $scheme . '://' . $host . $basePath);
}


// Här måste app-lösenord skapas, som i sig kräver two factor authentication
// Inte samma lösenord som själva kontot
// mellanslag ska tas bort i lösenordet man får från google när man skapar app-lösenordet
// exempel: bcd efgh ijkl mnop => abcdefghijklmnopbli

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'Mejl som används för att skicka mejl');
define('SMTP_PASS', 'app-lösenord');
define('SMTP_FROM', 'mejladress igen här som SMTP_USER');
define('SMTP_FROM_NAME', 'SkickaUpp');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
