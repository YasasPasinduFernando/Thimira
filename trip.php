<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$appLang = lang();
$lat = currentLat();
$lng = currentLng();
$title = 'One-Day Trip Planner';

$stmt = db()->query('SELECT * FROM attractions WHERE is_active = 1');
$rows = $stmt->fetchAll();

foreach ($rows as &$row) {
    $row['distance_km'] = distanceKm($lat, $lng, (float) $row['latitude'], (float) $row['longitude']);
}
unset($row);

usort($rows, static fn(array $a, array $b): int => $a['distance_km'] <=> $b['distance_km']);
$trip = array_slice(array_filter($rows, static fn(array $r): bool => $r['distance_km'] <= MAX_RADIUS_KM), 0, 4);

$startTime = new DateTimeImmutable('08:00');
require_once __DIR__ . '/includes/header.php';
?>
<section class="bg-white p-6 rounded-xl shadow">
    <h1 class="text-2xl font-bold mb-2"><?= esc($title) ?></h1>
    <p class="text-slate-600 mb-4">Auto-generated route using nearest attractions from your current location.</p>

    <button id="use-my-location" class="mb-5 bg-amber-400 hover:bg-amber-300 text-slate-900 font-semibold px-4 py-2 rounded-lg">Rebuild Using My Live Location</button>

    <?php if (!$trip): ?>
        <p>No route available in 25km radius.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border border-slate-200">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="p-2 text-left">#</th>
                        <th class="p-2 text-left">Place</th>
                        <th class="p-2 text-left">Distance</th>
                        <th class="p-2 text-left">Arrival</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trip as $i => $stop): ?>
                        <?php
                            $name = $appLang === 'si' ? $stop['name_si'] : $stop['name_en'];
                            $arrival = $startTime->modify('+' . (string) ($i * 90) . ' minutes')->format('H:i');
                        ?>
                        <tr class="border-t border-slate-200">
                            <td class="p-2"><?= (int) ($i + 1) ?></td>
                            <td class="p-2"><?= esc($name) ?></td>
                            <td class="p-2"><?= esc(number_format((float) $stop['distance_km'], 2)) ?> km</td>
                            <td class="p-2"><?= esc($arrival) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="map" class="mt-5"></div>

        <a class="inline-block mt-4 px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-600" target="_blank" href="https://www.google.com/maps/dir/?api=1&origin=<?= esc((string) $lat) ?>,<?= esc((string) $lng) ?>&destination=<?= esc((string) $trip[count($trip)-1]['latitude']) ?>,<?= esc((string) $trip[count($trip)-1]['longitude']) ?>&travelmode=driving">Open Route in Google Maps</a>

        <script>
            const map = L.map('map').setView([<?= esc((string) $lat) ?>, <?= esc((string) $lng) ?>], 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const routePoints = [[<?= esc((string) $lat) ?>, <?= esc((string) $lng) ?>]];
            L.marker([<?= esc((string) $lat) ?>, <?= esc((string) $lng) ?>]).addTo(map).bindPopup('Start');

            const tripStops = <?= json_encode(array_map(static function (array $item) use ($appLang): array {
                return [
                    'name' => $appLang === 'si' ? $item['name_si'] : $item['name_en'],
                    'lat' => (float) $item['latitude'],
                    'lng' => (float) $item['longitude'],
                ];
            }, $trip), JSON_UNESCAPED_UNICODE) ?>;

            tripStops.forEach((stop, idx) => {
                routePoints.push([stop.lat, stop.lng]);
                L.marker([stop.lat, stop.lng]).addTo(map).bindPopup(`${idx + 1}. ${stop.name}`);
            });

            L.polyline(routePoints, {color: 'blue'}).addTo(map);
        </script>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
