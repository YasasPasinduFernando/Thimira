<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$appLang = lang();
require_login_for_discovery_pages();

$lat = currentLat();
$lng = currentLng();
$title = t(['en' => 'Attractions Near You', 'si' => 'ඔබට ආසන්න ස්ථාන'], $appLang);

$stmt = db()->query('SELECT * FROM attractions WHERE is_active = 1');
$rows = $stmt->fetchAll();

$attractions = [];
foreach ($rows as $row) {
    $distance = distanceKm($lat, $lng, (float) $row['latitude'], (float) $row['longitude']);
    $row['distance_km'] = $distance;
    if ($distance <= MAX_RADIUS_KM) {
        $attractions[] = $row;
    }
}

usort($attractions, static fn(array $a, array $b): int => $a['distance_km'] <=> $b['distance_km']);

$navBackHref = url('index.php') . '?' . http_build_query(['lang' => $appLang]);
$navBackText = t(['en' => 'Home', 'si' => 'මුල් පිටුව'], $appLang);
require_once __DIR__ . '/includes/header.php';
?>
<section class="hero-gradient relative mb-8 overflow-hidden rounded-3xl p-6 text-white md:p-10">
    <h1 class="font-display text-2xl font-bold md:text-4xl"><?= esc($title) ?></h1>
    <p class="mt-3 max-w-xl text-slate-200"><?= esc(t([
        'en' => 'Discover places within 25 km of the reference location.',
        'si' => 'යොමු ස්ථානයෙන් කිලෝමීටර් 25 තුළ ස්ථාන සොයා ගන්න.',
    ], $appLang)) ?></p>
    <div class="mt-6 flex flex-wrap gap-3">
        <a href="https://maps.app.goo.gl/XpPW8HxJxcbdj1Zj6" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-2xl border border-white/25 bg-white/10 px-5 py-3 text-sm font-semibold backdrop-blur-sm transition hover:bg-white/20"><?= esc(t(['en' => 'Open reference on Maps', 'si' => 'සිතියමේ යොමුව අරින්න'], $appLang)) ?></a>
    </div>
</section>

<section class="grid gap-8 md:grid-cols-2">
    <div class="surface-card border border-slate-100 p-5 md:p-6">
        <h2 class="font-display text-lg font-bold text-slate-900"><?= esc(t(['en' => 'Map view', 'si' => 'සිතියම් දර්ශනය'], $appLang)) ?></h2>
        <div id="map" class="mt-4"></div>
        <p class="mt-4 text-sm text-slate-500"><?= esc(t(['en' => 'Reference:', 'si' => 'යොමුව:'], $appLang)) ?> <span class="font-mono text-slate-700"><?= esc(number_format($lat, 6)) ?>, <?= esc(number_format($lng, 6)) ?></span></p>
    </div>

    <div class="space-y-4">
        <?php if (!$attractions): ?>
            <div class="surface-card border border-dashed border-slate-200 p-8 text-center text-slate-600"><?= esc(t(['en' => 'No attractions found within 25 km.', 'si' => 'කිලෝමීටර් 25 තුළ ස්ථාන නොමැත.'], $appLang)) ?></div>
        <?php endif; ?>

        <?php foreach ($attractions as $place): ?>
            <?php
                $name = localized_text((string) $place['name_en'], (string) $place['name_si'], $appLang);
                $short = localized_text((string) $place['short_en'], (string) $place['short_si'], $appLang);
                $distance = number_format((float) $place['distance_km'], 2);
            ?>
            <article class="surface-card group border border-slate-100 p-5 transition hover:shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-display text-lg font-bold text-slate-900"><?= esc($name) ?></h3>
                        <p class="text-sm text-village-700"><?= esc($place['category']) ?></p>
                    </div>
                    <span class="shrink-0 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800"><?= esc($distance) ?> km</span>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-slate-600"><?= esc($short) ?></p>
                <a class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-village-700 transition group-hover:gap-2 hover:text-village-900" href="<?= esc(url('attraction.php')) ?>?id=<?= (int) $place['id'] ?>&lang=<?= esc($appLang) ?>&lat=<?= esc((string) $lat) ?>&lng=<?= esc((string) $lng) ?>"><?= esc(t(['en' => 'View details', 'si' => 'විස්තර බලන්න'], $appLang)) ?> <span aria-hidden="true">→</span></a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<script>
    const currentLat = <?= esc((string) $lat) ?>;
    const currentLng = <?= esc((string) $lng) ?>;
    const homeLat = <?= esc((string) DEFAULT_LAT) ?>;
    const homeLng = <?= esc((string) DEFAULT_LNG) ?>;

    const map = L.map('map').setView([currentLat, currentLng], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const redIcon = new L.Icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    const labelHomeRef = <?= json_encode(t(['en' => 'Home reference (red pin)', 'si' => 'නිවස යොමුව (රතු ඇණ)'], $appLang), JSON_UNESCAPED_UNICODE) ?>;
    const labelRefLoc = <?= json_encode(t(['en' => 'Reference location', 'si' => 'යොමු ස්ථානය'], $appLang), JSON_UNESCAPED_UNICODE) ?>;
    const labelDetails = <?= json_encode(t(['en' => 'Details', 'si' => 'විස්තර'], $appLang), JSON_UNESCAPED_UNICODE) ?>;

    L.marker([homeLat, homeLng], { icon: redIcon }).addTo(map).bindPopup(labelHomeRef);

    if (Math.abs(currentLat - homeLat) > 0.00001 || Math.abs(currentLng - homeLng) > 0.00001) {
        L.marker([currentLat, currentLng]).addTo(map).bindPopup(labelRefLoc);
    }

    const attractions = <?= json_encode(array_map(static function (array $row) use ($appLang): array {
        return [
            'name' => localized_text((string) $row['name_en'], (string) $row['name_si'], $appLang),
            'lat' => (float) $row['latitude'],
            'lng' => (float) $row['longitude'],
            'id' => (int) $row['id'],
        ];
    }, $attractions), JSON_UNESCAPED_UNICODE) ?>;

    attractions.forEach((item) => {
        L.marker([item.lat, item.lng]).addTo(map)
            .bindPopup(`<strong>${item.name}</strong><br><a href="<?= esc(url('attraction.php')) ?>?id=${item.id}&lang=<?= esc($appLang) ?>&lat=<?= esc((string) $lat) ?>&lng=<?= esc((string) $lng) ?>">${labelDetails}</a>`);
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
