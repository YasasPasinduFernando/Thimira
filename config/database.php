<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . (string) DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        error_log('Village Traveler DB connection failed: ' . $e->getMessage());
        if (!headers_sent()) {
            http_response_code(503);
            header('Content-Type: text/html; charset=UTF-8');
        }
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Database error</title></head><body style="font-family:sans-serif;max-width:36rem;margin:2rem auto;padding:1rem;">'
            . '<h1>Database connection failed</h1>'
            . '<p>The site could not connect to MySQL. Common causes:</p>'
            . '<ul><li><strong>Local XAMPP:</strong> Apache/MySQL running? Port in <code>config/config.php</code> matches MySQL (often <code>3306</code>, not <code>3307</code>)?</li>'
            . '<li><strong>InfinityFree:</strong> Database name, user, and password match the control panel? SQL imported?</li>'
            . '<li><strong>Wrong environment:</strong> Local / LAN (<code>localhost</code>, <code>127.0.0.1</code>, <code>192.168.x.x</code>) uses XAMPP MySQL; your <strong>public</strong> InfinityFree URL uses remote MySQL.</li>'
            . '<li><strong>Error 1044 / Access denied … 192.168.%:</strong> You were using InfinityFree DB credentials from a LAN IP. Use <code>http://localhost/...</code> for local dev, or open the live site URL from InfinityFree.</li></ul>'
            . '<p><small>Server message (for logs): ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</small></p>'
            . '</body></html>';
        exit;
    }

    return $pdo;
}
