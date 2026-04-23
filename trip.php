<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$appLang = lang();
require_login_for_discovery_pages();

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
<section class="surface-card border border-slate-100 p-6 md:p-8">
    <div class="mb-6 flex flex-col gap-4 border-b border-slate-100 pb-6 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold text-slate-900 md:text-3xl"><?= esc($title) ?></h1>
            <p class="mt-2 max-w-xl text-sm text-slate-600">Auto-generated route using the nearest attractions from your current location.</p>
        </div>
        <?php if (isUserLoggedIn()): ?>
            <div class="flex flex-wrap gap-2">
                <a href="<?= esc(url('trip-create.php')) ?>" class="inline-flex rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-slate-800">Personal trip plan</a>
                <a href="<?= esc(url('my-trips.php')) ?>" class="inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 transition hover:border-village-300">My trips</a>
            </div>
        <?php endif; ?>
    </div>

    <button type="button" id="use-my-location" class="mb-6 inline-flex rounded-2xl bg-gradient-to-r from-amber-400 to-amber-500 px-5 py-3 text-sm font-bold text-slate-900 shadow-lg shadow-amber-500/20 transition hover:from-amber-300 hover:to-amber-400">Rebuild using my live location</button>

    <?php if (!$trip): ?>
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-8 text-center text-slate-600">No route available within a 25 km radius.</div>
    <?php else: ?>
        <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-soft">
            <table class="min-w-full text-sm">
                <thead class="bg-gradient-to-r from-slate-50 to-teal-50/50">
                    <tr>
                        <th class="p-4 text-left font-display font-semibold text-slate-700">#</th>
                        <th class="p-4 text-left font-display font-semibold text-slate-700">Place</th>
                        <th class="p-4 text-left font-display font-semibold text-slate-700">Distance</th>
                        <th class="p-4 text-left font-display font-semibold text-slate-700">Arrival</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach ($trip as $i => $stop): ?>
                        <?php
                            $name = $appLang === 'si' ? $stop['name_si'] : $stop['name_en'];
                            $arrival = $startTime->modify('+' . (string) ($i * 90) . ' minutes')->format('H:i');
                        ?>
                        <tr class="transition hover:bg-teal-50/30">
                            <td class="p-4 font-medium text-slate-500"><?= (int) ($i + 1) ?></td>
                            <td class="p-4 font-semibold text-slate-900"><?= esc($name) ?></td>
                            <td class="p-4 text-slate-600"><?= esc(number_format((float) $stop['distance_km'], 2)) ?> km</td>
                            <td class="p-4"><span class="rounded-lg bg-village-100 px-2.5 py-1 text-xs font-bold text-village-800"><?= esc($arrival) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="map" class="mt-6"></div>

        <a class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-village-600 px-5 py-3 text-sm font-semibold text-white shadow-soft transition hover:bg-village-700" target="_blank" rel="noopener noreferrer" href="https://www.google.com/maps/dir/?api=1&origin=<?= esc((string) $lat) ?>,<?= esc((string) $lng) ?>&destination=<?= esc((string) $trip[count($trip)-1]['latitude']) ?>,<?= esc((string) $trip[count($trip)-1]['longitude']) ?>&travelmode=driving">Open route in Google Maps</a>

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

            L.polyline(routePoints, {color: '#0d9488', weight: 4, opacity: 0.85}).addTo(map);
        </script>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
