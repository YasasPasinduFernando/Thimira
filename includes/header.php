<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

$appLang = lang();
$title = $title ?? 'Village Traveler';
$mainClass = $mainClass ?? '';

$currentScript = basename(str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '')));

$navQuery = $_GET;
unset($navQuery['lang']);
$enQuery = array_merge($navQuery, ['lang' => 'en']);
$siQuery = array_merge($navQuery, ['lang' => 'si']);
$enHref = esc($_SERVER['PHP_SELF']) . '?' . esc(http_build_query($enQuery));
$siHref = esc($_SERVER['PHP_SELF']) . '?' . esc(http_build_query($siQuery));

$navLangQ = http_build_query(['lang' => $appLang]);
$attractionsNavTarget = 'attractions.php?' . $navLangQ;
$tripNavTarget = 'trip.php?' . $navLangQ;
$attractionsNavHref = isUserLoggedIn()
    ? esc(url('attractions.php')) . '?' . esc($navLangQ)
    : esc(login_url_with_next($attractionsNavTarget));
$tripNavHref = isUserLoggedIn()
    ? esc(url('trip.php')) . '?' . esc($navLangQ)
    : esc(login_url_with_next($tripNavTarget));
?>
<!DOCTYPE html>
<html lang="<?= esc($appLang) ?>" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f766e">
    <meta name="application-name" content="Village Traveler">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="VillageTraveler">
    <meta name="app-base" content="<?= esc(APP_BASE_URL) ?>">
    <title><?= esc($title) ?> · Village Traveler</title>
    <link rel="manifest" href="<?= esc(url('manifest.php')) ?>">
    <link rel="icon" href="<?= esc(url('assets/icons/icon-32.png')) ?>" type="image/png" sizes="32x32">
    <link rel="icon" href="<?= esc(url('assets/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= esc(url('assets/icons/icon-180.png')) ?>" sizes="180x180">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                        display: ['"Outfit"', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        village: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        },
                    },
                    boxShadow: {
                        soft: '0 4px 24px -4px rgba(15, 23, 42, 0.07), 0 12px 32px -12px rgba(15, 23, 42, 0.12)',
                        card: '0 0 0 1px rgba(15, 23, 42, 0.06), 0 12px 40px -16px rgba(15, 23, 42, 0.16)',
                        glow: '0 0 48px -12px rgba(20, 184, 166, 0.45)',
                    },
                },
            },
        };
    </script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <link rel="stylesheet" href="<?= esc(url('assets/css/app.css')) ?>">
</head>
<body class="min-h-screen font-sans text-slate-800 antialiased bg-page-mesh">
    <header class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/80 backdrop-blur-xl supports-[backdrop-filter]:bg-slate-950/70">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex min-h-16 flex-wrap items-center justify-between gap-3 py-3">
                <a href="<?= esc(url('index.php')) ?>?lang=<?= esc($appLang) ?>" class="group flex items-center gap-3 font-display text-lg font-bold tracking-tight text-white">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-400 via-village-500 to-village-700 text-white shadow-glow ring-2 ring-white/20 transition group-hover:scale-105">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </span>
                    <span class="hidden sm:inline">Village Traveler</span>
                    <span class="sm:hidden">VT</span>
                </a>

                <nav class="flex flex-1 flex-wrap items-center justify-end gap-1 text-sm font-medium sm:gap-1.5" aria-label="<?= esc(t(['en' => 'Main', 'si' => 'ප්‍රධාන'], $appLang)) ?>">
                    <a class="nav-link <?= $currentScript === 'index.php' ? 'nav-link-active' : '' ?>" href="<?= esc(url('index.php')) ?>?lang=<?= esc($appLang) ?>"><?= esc(t(['en' => 'Home', 'si' => 'මුල් පිටුව'], $appLang)) ?></a>
                    <a class="nav-link <?= $currentScript === 'attractions.php' ? 'nav-link-active' : '' ?>" href="<?= $attractionsNavHref ?>"><?= esc(t(['en' => 'Attractions', 'si' => 'ස්ථාන'], $appLang)) ?><?php if (!isUserLoggedIn()): ?> <span class="text-[10px] font-normal opacity-70"><?= esc(t(['en' => '(login)', 'si' => '(ඇතුල් වන්න)'], $appLang)) ?></span><?php endif; ?></a>
                    <a class="nav-link <?= in_array($currentScript, ['trip.php', 'trip-create.php', 'trip-plan.php', 'my-trips.php'], true) ? 'nav-link-active' : '' ?>" href="<?= $tripNavHref ?>"><?= esc(t(['en' => 'One-Day Trip', 'si' => 'එක් දින චාරිකාව'], $appLang)) ?><?php if (!isUserLoggedIn()): ?> <span class="text-[10px] font-normal opacity-70"><?= esc(t(['en' => '(login)', 'si' => '(ඇතුල් වන්න)'], $appLang)) ?></span><?php endif; ?></a>

                    <?php if (isUserLoggedIn()): ?>
                        <a class="nav-link <?= $currentScript === 'trip-create.php' ? 'nav-link-active' : '' ?>" href="<?= esc(url('trip-create.php')) ?>"><?= esc(t(['en' => 'Create Trip', 'si' => 'සැලසුම සාදන්න'], $appLang)) ?></a>
                        <a class="nav-link <?= in_array($currentScript, ['my-trips.php', 'trip-plan.php'], true) ? 'nav-link-active' : '' ?>" href="<?= esc(url('my-trips.php')) ?>"><?= esc(t(['en' => 'My Trips', 'si' => 'මගේ චාරිකා'], $appLang)) ?></a>
                    <?php endif; ?>

                    <?php if (show_public_admin_nav()): ?>
                        <a class="nav-link opacity-80 hover:opacity-100" href="<?= esc(url('admin/login.php')) ?>"><?= esc(t(['en' => 'Admin', 'si' => 'පරිපාලක'], $appLang)) ?></a>
                    <?php endif; ?>

                    <span class="hidden h-5 w-px bg-white/15 sm:block" aria-hidden="true"></span>

                    <div class="inline-flex rounded-xl bg-slate-800/90 p-1 ring-1 ring-white/10" role="group" aria-label="<?= esc(t(['en' => 'Language', 'si' => 'භාෂාව'], $appLang)) ?>">
                        <a class="rounded-lg px-2.5 py-1 text-xs font-semibold transition <?= $appLang === 'en' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-white' ?>" href="<?= $enHref ?>">EN</a>
                        <a class="rounded-lg px-2.5 py-1 text-xs font-semibold transition <?= $appLang === 'si' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-white' ?>" href="<?= $siHref ?>">සිං</a>
                    </div>

                    <?php if (isUserLoggedIn()): ?>
                        <span class="hidden items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs text-slate-200 sm:inline-flex" title="<?= esc(t(['en' => 'Signed in', 'si' => 'ඇතුල් වී ඇත'], $appLang)) ?>">
                            <span class="h-1.5 w-1.5 rounded-full bg-teal-400 shadow-[0_0_8px_rgba(45,212,191,0.8)]"></span>
                            <?= esc((string) ($_SESSION['user_username'] ?? 'User')) ?>
                        </span>
                        <a class="inline-flex items-center rounded-xl px-3 py-2 text-xs font-semibold text-slate-300 ring-1 ring-white/15 transition hover:bg-white/10 hover:text-white" href="<?= esc(url('logout.php')) ?>"><?= esc(t(['en' => 'Logout', 'si' => 'ඉවත්වන්න'], $appLang)) ?></a>
                    <?php else: ?>
                        <a class="inline-flex items-center rounded-xl px-3 py-2 text-xs font-semibold text-slate-200 ring-1 ring-white/15 transition hover:bg-white/10 hover:text-white" href="<?= esc(url('login.php')) ?>"><?= esc(t(['en' => 'Login', 'si' => 'ඇතුල් වන්න'], $appLang)) ?></a>
                        <a class="inline-flex items-center rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 px-3 py-2 text-xs font-bold text-slate-900 shadow-soft transition hover:from-amber-300 hover:to-amber-400" href="<?= esc(url('register.php')) ?>"><?= esc(t(['en' => 'Register', 'si' => 'ලියාපදිංචි'], $appLang)) ?></a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>
    <main class="max-w-6xl mx-auto px-4 py-8 sm:px-6 sm:py-10 <?= esc($mainClass) ?>">
