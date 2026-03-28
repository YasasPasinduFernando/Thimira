<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$appLang = lang();
$lat = currentLat();
$lng = currentLng();
$title = t(['en' => 'Attractions Near You', 'si' => 'Attractions Near You'], $appLang);

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

require_once __DIR__ . '/includes/header.php';
?>
<section class="hero-gradient text-white rounded-xl p-6 mb-6">
    <h1 class="text-2xl md:text-3xl font-bold mb-2"><?= esc($title) ?></h1>
    <p class="text-slate-100 mb-4">Discover places within 25 km of your location.</p>
    <div class="flex flex-wrap gap-3">
        <button id="use-my-location" class="bg-amber-400 hover:bg-amber-300 text-slate-900 font-semibold px-4 py-2 rounded-lg">Use My Live Location</button>
        <a href="https://maps.app.goo.gl/XpPW8HxJxcbdj1Zj6" target="_blank" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg">Open Given Location</a>
    </div>
</section>

<section class="grid md:grid-cols-2 gap-6">
    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-lg font-semibold mb-2">Map View</h2>
        <div id="map"></div>
        <p class="mt-3 text-sm text-slate-600">Reference location: <?= esc(number_format($lat, 6)) ?>, <?= esc(number_format($lng, 6)) ?></p>
    </div>

    <div class="space-y-4">
        <?php if (!$attractions): ?>
            <div class="bg-white p-4 rounded-xl shadow">No attractions found within 25 km.</div>
        <?php endif; ?>

        <?php foreach ($attractions as $place): ?>
            <?php
                $name = $appLang === 'si' ? $place['name_si'] : $place['name_en'];
                $short = $appLang === 'si' ? $place['short_si'] : $place['short_en'];
                $distance = number_format((float) $place['distance_km'], 2);
            ?>
            <article class="bg-white p-4 rounded-xl shadow">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold"><?= esc($name) ?></h3>
                        <p class="text-sm text-slate-500"><?= esc($place['category']) ?></p>
                    </div>
                    <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded-full"><?= esc($distance) ?> km</span>
                </div>
                <p class="mt-2 text-sm text-slate-700"><?= esc($short) ?></p>
                <a class="inline-block mt-3 text-blue-700 hover:underline" href="<?= esc(url('attraction.php')) ?>?id=<?= (int) $place['id'] ?>&lang=<?= esc($appLang) ?>&lat=<?= esc((string) $lat) ?>&lng=<?= esc((string) $lng) ?>">View Details</a>
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

    L.marker([homeLat, homeLng], { icon: redIcon }).addTo(map).bindPopup('Home Location (Red Pin)');

    if (Math.abs(currentLat - homeLat) > 0.00001 || Math.abs(currentLng - homeLng) > 0.00001) {
        L.marker([currentLat, currentLng]).addTo(map).bindPopup('Your Current Location');
    }

    const attractions = <?= json_encode(array_map(static function (array $row) use ($appLang): array {
        return [
            'name' => $appLang === 'si' ? $row['name_si'] : $row['name_en'],
            'lat' => (float) $row['latitude'],
            'lng' => (float) $row['longitude'],
            'id' => (int) $row['id'],
        ];
    }, $attractions), JSON_UNESCAPED_UNICODE) ?>;

    attractions.forEach((item) => {
        L.marker([item.lat, item.lng]).addTo(map)
            .bindPopup(`<strong>${item.name}</strong><br><a href="<?= esc(url('attraction.php')) ?>?id=${item.id}&lang=<?= esc($appLang) ?>&lat=<?= esc((string) $lat) ?>&lng=<?= esc((string) $lng) ?>">Details</a>`);
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>