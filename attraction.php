<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$appLang = lang();
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
<article class="bg-white rounded-xl shadow overflow-hidden">
    <?php if (!empty($place['image_url'])): ?>
        <img src="<?= esc($place['image_url']) ?>" alt="<?= esc($name) ?>" class="w-full h-64 object-cover">
    <?php endif; ?>

    <div class="p-6">
        <h1 class="text-2xl font-bold mb-2"><?= esc($name) ?></h1>
        <p class="text-sm text-slate-500 mb-4"><?= esc($place['category']) ?></p>

        <div class="grid sm:grid-cols-2 gap-4 mb-5 text-sm">
            <div class="p-3 bg-slate-50 rounded-lg"><strong>Distance:</strong> <?= esc(number_format($distance, 2)) ?> km</div>
            <div class="p-3 bg-slate-50 rounded-lg"><strong>Estimated Travel Time:</strong> <?= esc((string) $minutes) ?> min</div>
            <div class="p-3 bg-slate-50 rounded-lg"><strong>Open Hours:</strong> <?= esc($place['open_hours']) ?></div>
            <div class="p-3 bg-slate-50 rounded-lg"><strong>Entry Fee:</strong> <?= esc(money((float) $place['entry_fee_lkr'])) ?></div>
        </div>

        <p class="text-slate-700 leading-7 mb-6"><?= esc($description) ?></p>

        <div class="flex flex-wrap gap-3">
            <a href="<?= esc($navigateUrl) ?>" target="_blank" class="px-4 py-2 rounded-lg bg-blue-700 text-white hover:bg-blue-600">Navigate</a>
            <a href="<?= esc(url('index.php')) ?>?lang=<?= esc($appLang) ?>&lat=<?= esc((string) $lat) ?>&lng=<?= esc((string) $lng) ?>" class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300">Back to List</a>
        </div>
    </div>
</article>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
