<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_PORT = 3307;
const DB_NAME = 'village_traveler';
const DB_USER = 'root';
const DB_PASS = 'yasas';

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
