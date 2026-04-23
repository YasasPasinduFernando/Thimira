<?php
declare(strict_types=1);

/*
 * Local vs hosting: XAMPP MySQL when the site is opened from this machine or LAN.
 * InfinityFree MySQL only when the public site hostname is used (e.g. yoursite.infinityfreeapp.com).
 *
 * Important: opening http://192.168.x.x/... from your phone/PC uses HTTP_HOST=192.168.x.x.
 * Without treating that as "local", PHP would use InfinityFree credentials; MySQL then
 * returns "Access denied ... 'user'@'192.168.%'" (error 1044) because remote DB is not for LAN clients.
 */
$serverName = strtolower((string) ($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? ''));
$hostOnly = preg_replace('/:\d+$/', '', $serverName);
$isLanOrLoopback = in_array($hostOnly, ['localhost', '127.0.0.1', '::1'], true)
    || (bool) preg_match('/\.local$/', $hostOnly)
    || (bool) preg_match('/^192\.168\.\d{1,3}\.\d{1,3}$/', $hostOnly)
    || (bool) preg_match('/^10\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $hostOnly)
    || (bool) preg_match('/^172\.(1[6-9]|2\d|3[0-1])\.\d{1,3}\.\d{1,3}$/', $hostOnly);
/** Touch empty file config/.force-local-mysql if your PC hostname is not detected as LAN. */
$isLocal = $isLanOrLoopback || is_readable(__DIR__ . '/.force-local-mysql');

if ($isLocal) {
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', 3307);
    define('DB_NAME', 'village_traveler');
    define('DB_USER', 'root');
    define('DB_PASS', 'yasas');
} else {
    /** InfinityFree — DB name must match the control panel exactly. */
    define('DB_HOST', 'sql113.infinityfree.com');
    define('DB_PORT', 3306);
    define('DB_NAME', 'if0_41737287_villagetraveler');
    define('DB_USER', 'if0_41737287');
    define('DB_PASS', '3rRuBCrXPjMB6yp');
}

const APP_NAME = 'village-traveler';
const APP_MAIL_FROM = 'info.itzone.sl@gmail.com';
const APP_MAIL_APP_PASSWORD = 'ihzh wrrx tzkt ltrc';
const APP_SMTP_HOST = 'smtp.gmail.com';
const APP_SMTP_PORT = 465;

const DEFAULT_LAT = 6.2291414;
const DEFAULT_LNG = 80.0573511;
const MAX_RADIUS_KM = 25;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$segments = array_values(array_filter(explode('/', trim($scriptName, '/'))));
$basePath = count($segments) > 1 ? '/' . $segments[0] : '';
define('APP_BASE_URL', $basePath);
