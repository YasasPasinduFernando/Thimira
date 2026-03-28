<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

$appLang = lang();
$title = $title ?? 'Village Traveler';
?>
<!DOCTYPE html>
<html lang="<?= esc($appLang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <link rel="stylesheet" href="<?= esc(url('assets/css/app.css')) ?>">
</head>
<body class="bg-slate-100 text-slate-900">
    <header class="bg-slate-900 text-white">
        <div class="max-w-6xl mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-3">
            <a href="<?= esc(url('index.php')) ?>" class="text-xl font-bold tracking-wide">Village Traveler</a>
            <nav class="flex flex-wrap items-center gap-3 text-sm">
                <a class="hover:text-amber-300" href="<?= esc(url('index.php')) ?>?lang=<?= esc($appLang) ?>"><?= esc(t(['en' => 'Attractions', 'si' => 'ස්ථාන'], $appLang)) ?></a>
                <a class="hover:text-amber-300" href="<?= esc(url('trip.php')) ?>?lang=<?= esc($appLang) ?>"><?= esc(t(['en' => 'One-Day Trip', 'si' => 'එක් දින චාරිකාව'], $appLang)) ?></a>
                <a class="hover:text-amber-300" href="<?= esc(url('admin/login.php')) ?>"><?= esc(t(['en' => 'Admin', 'si' => 'පරිපාලක'], $appLang)) ?></a>
                <span class="mx-2 text-slate-400">|</span>
                <a class="hover:text-amber-300" href="<?= esc($_SERVER['PHP_SELF']) ?>?<?= esc(http_build_query(array_merge($_GET, ['lang' => 'en']))) ?>">EN</a>
                <a class="hover:text-amber-300" href="<?= esc($_SERVER['PHP_SELF']) ?>?<?= esc(http_build_query(array_merge($_GET, ['lang' => 'si']))) ?>">සිං</a>
            </nav>
        </div>
    </header>
    <main class="max-w-6xl mx-auto px-4 py-6">