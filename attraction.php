<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$appLang = lang();
require_login_for_discovery_pages();

$lat = currentLat();
$lng = currentLng();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = db()->prepare('SELECT * FROM attractions WHERE id = :id AND is_active = 1');
$stmt->execute(['id' => $id]);
$place = $stmt->fetch();

if (!$place) {
    http_response_code(404);
    echo 'Attraction not found';
    exit;
}

$name = $appLang === 'si' ? $place['name_si'] : $place['name_en'];
$description = $appLang === 'si' ? $place['description_si'] : $place['description_en'];
$distance = distanceKm($lat, $lng, (float) $place['latitude'], (float) $place['longitude']);
$minutes = max(5, (int) round(($distance / 30) * 60));
$title = $name;

$navigateUrl = 'https://www.google.com/maps/dir/?api=1&origin=' . $lat . ',' . $lng
    . '&destination=' . $place['latitude'] . ',' . $place['longitude']
    . '&travelmode=driving';

require_once __DIR__ . '/includes/header.php';
?>
<article class="surface-card overflow-hidden border border-slate-100">
    <?php if (!empty($place['image_url'])): ?>
        <div class="relative h-64 w-full overflow-hidden md:h-80">
            <img src="<?= esc($place['image_url']) ?>" alt="<?= esc($name) ?>" class="h-full w-full object-cover transition duration-500 hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>
        </div>
    <?php endif; ?>

    <div class="p-6 md:p-10">
        <h1 class="font-display text-3xl font-bold text-slate-900 md:text-4xl"><?= esc($name) ?></h1>
        <p class="mt-2 text-sm font-semibold uppercase tracking-wide text-village-600"><?= esc($place['category']) ?></p>

        <div class="mt-8 grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-100 bg-gradient-to-br from-slate-50 to-teal-50/40 p-4 text-sm"><span class="font-semibold text-slate-700">Distance</span><p class="mt-1 text-lg font-bold text-slate-900"><?= esc(number_format($distance, 2)) ?> km</p></div>
            <div class="rounded-2xl border border-slate-100 bg-gradient-to-br from-slate-50 to-teal-50/40 p-4 text-sm"><span class="font-semibold text-slate-700">Est. travel time</span><p class="mt-1 text-lg font-bold text-slate-900"><?= esc((string) $minutes) ?> min</p></div>
            <div class="rounded-2xl border border-slate-100 bg-gradient-to-br from-slate-50 to-teal-50/40 p-4 text-sm"><span class="font-semibold text-slate-700">Open hours</span><p class="mt-1 font-medium text-slate-900"><?= esc($place['open_hours']) ?></p></div>
            <div class="rounded-2xl border border-slate-100 bg-gradient-to-br from-slate-50 to-teal-50/40 p-4 text-sm"><span class="font-semibold text-slate-700">Entry fee</span><p class="mt-1 font-medium text-slate-900"><?= esc(money((float) $place['entry_fee_lkr'])) ?></p></div>
        </div>

        <p class="mt-8 text-base leading-relaxed text-slate-700"><?= esc($description) ?></p>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="<?= esc($navigateUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex rounded-2xl bg-village-600 px-5 py-3 text-sm font-semibold text-white shadow-soft transition hover:bg-village-700">Navigate in Google Maps</a>
            <a href="<?= esc(url('attractions.php')) ?>?lang=<?= esc($appLang) ?>&lat=<?= esc((string) $lat) ?>&lng=<?= esc((string) $lng) ?>" class="inline-flex rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-800 transition hover:border-village-300">Back to list</a>
        </div>
    </div>
</article>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
