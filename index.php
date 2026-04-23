<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$appLang = lang();
$title = 'Village Traveler - Home';
$isLoggedIn = isUserLoggedIn();
$username = (string) ($_SESSION['user_username'] ?? '');

$homeNavQ = http_build_query(['lang' => $appLang]);
$homeAttrHref = $isLoggedIn
    ? esc(url('attractions.php')) . '?' . esc($homeNavQ)
    : esc(login_url_with_next('attractions.php?' . $homeNavQ));
$homeTripHref = $isLoggedIn
    ? esc(url('trip.php')) . '?' . esc($homeNavQ)
    : esc(login_url_with_next('trip.php?' . $homeNavQ));

require_once __DIR__ . '/includes/header.php';
?>
<section class="hero-gradient relative mb-10 overflow-hidden rounded-3xl p-8 text-white md:p-14">
    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-teal-400/20 blur-3xl"></div>
    <div class="absolute -bottom-16 left-1/4 h-48 w-48 rounded-full bg-amber-400/15 blur-3xl"></div>
    <p class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-teal-100 ring-1 ring-white/20">
        <span class="h-1.5 w-1.5 rounded-full bg-teal-300"></span>
        <?= esc(t(['en' => 'Local discovery', 'si' => 'දේශීය සොයාගැනීම'], $appLang)) ?>
    </p>
    <h1 class="font-display text-3xl font-extrabold leading-tight tracking-tight md:text-5xl md:leading-tight">
        <?= esc(t(['en' => 'Explore local attractions, smarter', 'si' => 'ස්ථාන සොයන්න, වඩා හොඳින්'], $appLang)) ?>
    </h1>
    <p class="mt-5 max-w-2xl text-base leading-relaxed text-slate-200/95 md:text-lg">
        <?= esc(t([
            'en' => 'Plan one-day trips, discover nearby places within 25 km, and navigate with distance-aware guidance from your location.',
            'si' => 'එක් දින චාරිකා සැලසුම් කරන්න, කිලෝමීටර් 25 තුළ ස්ථාන සොයා ගන්න, සහ ඔබේ ස්ථානයෙන් දුර පදනම්ව මාර්ගොපදේශ ලබා ගන්න.',
        ], $appLang)) ?>
    </p>
    <div class="mt-8 flex flex-wrap gap-3">
        <a href="<?= $homeAttrHref ?>" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-amber-400 to-amber-500 px-6 py-3.5 text-sm font-bold text-slate-900 shadow-lg shadow-amber-500/25 transition hover:from-amber-300 hover:to-amber-400">
            <?= esc(t(['en' => 'Browse attractions', 'si' => 'ස්ථාන බලන්න'], $appLang)) ?>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
        <a href="<?= $homeTripHref ?>" class="inline-flex items-center rounded-2xl border border-white/25 bg-white/10 px-6 py-3.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">
            <?= esc(t(['en' => 'One-day trip planner', 'si' => 'එක් දින සැලසුම'], $appLang)) ?>
        </a>
    </div>
</section>

<?php if ($isLoggedIn): ?>
    <section class="surface-card mb-10 border border-emerald-200/60 bg-gradient-to-br from-emerald-50/90 to-teal-50/50 p-6 md:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-800"><?= esc(t(['en' => 'Welcome back', 'si' => 'නැවත සාදරයෙන් පිළිගනිමු'], $appLang)) ?></p>
                <p class="mt-1 font-display text-xl font-bold text-slate-900"><?= esc($username) ?></p>
                <p class="mt-2 text-sm text-slate-600"><?= esc(t(['en' => 'Continue from attractions or open your trip plans.', 'si' => 'ස්ථාන හෝ ඔබේ චාරිකා සැලසුම් වෙත යන්න.'], $appLang)) ?></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?= esc(url('attractions.php')) ?>?lang=<?= esc($appLang) ?>" class="inline-flex rounded-xl bg-village-600 px-4 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-village-700"><?= esc(t(['en' => 'Attractions', 'si' => 'ස්ථාන'], $appLang)) ?></a>
                <a href="<?= esc(url('my-trips.php')) ?>" class="inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition hover:border-village-300 hover:text-village-800">My Trips</a>
            </div>
        </div>
    </section>
<?php else: ?>
    <section class="mb-10 grid gap-6 md:grid-cols-2">
        <article class="surface-card group border border-slate-100 p-8 transition hover:shadow-card">
            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-village-400 to-village-600 text-white shadow-glow">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <h2 class="font-display text-xl font-bold text-slate-900">New here?</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">Create an account to save trip plans, track visits, and get trip summary emails.</p>
            <a href="<?= esc(url('register.php')) ?>" class="mt-6 inline-flex rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">Register</a>
        </article>
        <article class="surface-card group border border-slate-100 p-8 transition hover:shadow-card">
            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-village-700 text-white shadow-soft">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            </div>
            <h2 class="font-display text-xl font-bold text-slate-900">Already have an account?</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">Sign in to continue exploring attractions and managing your trips.</p>
            <a href="<?= esc(url('login.php')) ?>" class="mt-6 inline-flex rounded-xl bg-village-600 px-5 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-village-700">Login</a>
        </article>
    </section>
<?php endif; ?>

<section class="grid gap-6 md:grid-cols-3">
    <article class="surface-card border border-slate-100 p-7 transition hover:-translate-y-0.5 hover:shadow-card">
        <div class="mb-4 text-2xl" aria-hidden="true">📍</div>
        <h3 class="font-display text-lg font-bold text-slate-900">Location based</h3>
        <p class="mt-2 text-sm leading-relaxed text-slate-600">Find attractions within a 25 km radius from live or default coordinates.</p>
    </article>
    <article class="surface-card border border-slate-100 p-7 transition hover:-translate-y-0.5 hover:shadow-card">
        <div class="mb-4 text-2xl" aria-hidden="true">🌐</div>
        <h3 class="font-display text-lg font-bold text-slate-900">Bilingual</h3>
        <p class="mt-2 text-sm leading-relaxed text-slate-600">Switch English / Sinhala for comfortable reading on every page.</p>
    </article>
    <article class="surface-card border border-slate-100 p-7 transition hover:-translate-y-0.5 hover:shadow-card">
        <div class="mb-4 text-2xl" aria-hidden="true">🗺️</div>
        <h3 class="font-display text-lg font-bold text-slate-900">Trip planner</h3>
        <p class="mt-2 text-sm leading-relaxed text-slate-600">Auto-build a one-day route with map view and Google Maps directions.</p>
    </article>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
