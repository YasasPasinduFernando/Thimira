<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function appBaseUrl(): string
{
    return APP_BASE_URL;
}

function url(string $path = ''): string
{
    $base = rtrim(appBaseUrl(), '/');
    $path = ltrim($path, '/');

    if ($path === '') {
        return $base === '' ? '/' : $base . '/';
    }

    return ($base === '' ? '' : $base) . '/' . $path;
}

function lang(): string
{
    $lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';
    if (!in_array($lang, ['en', 'si'], true)) {
        $lang = 'en';
    }
    $_SESSION['lang'] = $lang;
    return $lang;
}

function t(array $labels, string $lang): string
{
    return $labels[$lang] ?? $labels['en'] ?? '';
}

function currentLat(): float
{
    return isset($_GET['lat']) ? (float) $_GET['lat'] : DEFAULT_LAT;
}

function currentLng(): float
{
    return isset($_GET['lng']) ? (float) $_GET['lng'] : DEFAULT_LNG;
}

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $earthRadius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) * sin($dLat / 2)
        + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
        * sin($dLng / 2) * sin($dLng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_id']);
}

function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . url('admin/login.php'));
        exit;
    }
}

function money(float $amount): string
{
    return 'LKR ' . number_format($amount, 2);
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}