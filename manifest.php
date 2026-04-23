<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/manifest+json; charset=UTF-8');
header('Cache-Control: public, max-age=86400');

$data = [
    'name' => 'Village Traveler',
    'short_name' => 'VillageTraveler',
    'description' => 'Discover local attractions, plan day trips, and explore Sri Lanka.',
    'start_url' => url('index.php') . '?source=pwa',
    'scope' => url(''),
    'display' => 'standalone',
    'orientation' => 'portrait-primary',
    'background_color' => '#f4f7fb',
    'theme_color' => '#0f766e',
    'icons' => [
        [
            'src' => url('assets/icons/icon-192.png'),
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => url('assets/icons/icon-512.png'),
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => url('assets/icons/icon-512.png'),
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'maskable',
        ],
    ],
];

echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
