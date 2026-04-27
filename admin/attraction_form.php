<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;

$data = [
    'category' => '',
    'name_en' => '',
    'name_si' => '',
    'short_en' => '',
    'short_si' => '',
    'description_en' => '',
    'description_si' => '',
    'open_hours' => '08:00 - 18:00',
    'entry_fee_lkr' => '0',
    'latitude' => (string) DEFAULT_LAT,
    'longitude' => (string) DEFAULT_LNG,
    'image_url' => '',
    'is_active' => '1',
];
$errors = [];

if ($isEdit) {
    $stmt = db()->prepare('SELECT * FROM attractions WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $found = $stmt->fetch();
    if ($found) {
        $data = array_merge($data, $found);
        $data['is_active'] = (string) $data['is_active'];
    } else {
        setFlash('error', 'Attraction not found.');
        header('Location: ' . url('admin/attractions.php'));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($data) as $key) {
        if (isset($_POST[$key])) {
            $data[$key] = trim((string) $_POST[$key]);
        }
    }
    $data['is_active'] = isset($_POST['is_active']) ? '1' : '0';

    $required = ['category', 'name_en', 'name_si', 'short_en', 'short_si', 'description_en', 'description_si', 'latitude', 'longitude'];
    foreach ($required as $field) {
        if ($data[$field] === '') {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }

    if (!is_numeric($data['latitude']) || (float) $data['latitude'] < -90 || (float) $data['latitude'] > 90) {
        $errors[] = 'Latitude must be between -90 and 90.';
    }
    if (!is_numeric($data['longitude']) || (float) $data['longitude'] < -180 || (float) $data['longitude'] > 180) {
        $errors[] = 'Longitude must be between -180 and 180.';
    }
    if (!is_numeric($data['entry_fee_lkr']) || (float) $data['entry_fee_lkr'] < 0) {
        $errors[] = 'Entry fee must be a non-negative number.';
    }

    if (!$errors) {
        if ($isEdit) {
            $sql = 'UPDATE attractions SET
                category = :category,
                name_en = :name_en,
                name_si = :name_si,
                short_en = :short_en,
                short_si = :short_si,
                description_en = :description_en,
                description_si = :description_si,
                open_hours = :open_hours,
                entry_fee_lkr = :entry_fee_lkr,
                latitude = :latitude,
                longitude = :longitude,
                image_url = :image_url,
                is_active = :is_active
                WHERE id = :id';
            $stmt = db()->prepare($sql);
            $stmt->execute([
                'id' => $id,
                ...$data,
            ]);
            setFlash('success', 'Attraction updated successfully.');
        } else {
            $sql = 'INSERT INTO attractions (
                category, name_en, name_si, short_en, short_si,
                description_en, description_si, open_hours, entry_fee_lkr,
                latitude, longitude, image_url, is_active
            ) VALUES (
                :category, :name_en, :name_si, :short_en, :short_si,
                :description_en, :description_si, :open_hours, :entry_fee_lkr,
                :latitude, :longitude, :image_url, :is_active
            )';
            $stmt = db()->prepare($sql);
            $stmt->execute($data);
            setFlash('success', 'Attraction added successfully.');
        }

        header('Location: ' . url('admin/attractions.php'));
        exit;
    }
}

$title = $isEdit ? 'Edit Attraction' : 'Add Attraction';
$navBackHref = url('admin/attractions.php');
$navBackText = t(['en' => 'Attractions list', 'si' => 'ස්ථාන ලැයිස්තුව'], lang());
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bg-white p-6 rounded-xl shadow space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-xl font-bold"><?= esc($title) ?></h1>
        <a href="<?= esc(url('admin/attractions.php')) ?>" class="px-3 py-2 rounded bg-slate-200 hover:bg-slate-300 text-sm">Back to List</a>
    </div>

    <?php if ($errors): ?>
        <div class="p-3 rounded bg-red-100 text-red-700">
            <?php foreach ($errors as $error): ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" class="grid md:grid-cols-2 gap-4">
        <div><label class="block text-sm mb-1">Category</label><input name="category" required value="<?= esc((string) $data['category']) ?>" class="w-full border border-slate-300 rounded px-3 py-2"></div>
        <div><label class="block text-sm mb-1">Open Hours</label><input name="open_hours" value="<?= esc((string) $data['open_hours']) ?>" class="w-full border border-slate-300 rounded px-3 py-2"></div>

        <div><label class="block text-sm mb-1">Name (EN)</label><input name="name_en" required value="<?= esc((string) $data['name_en']) ?>" class="w-full border border-slate-300 rounded px-3 py-2"></div>
        <div><label class="block text-sm mb-1">Name (SI)</label><input name="name_si" required value="<?= esc((string) $data['name_si']) ?>" class="w-full border border-slate-300 rounded px-3 py-2"></div>

        <div><label class="block text-sm mb-1">Short (EN)</label><input name="short_en" required value="<?= esc((string) $data['short_en']) ?>" class="w-full border border-slate-300 rounded px-3 py-2"></div>
        <div><label class="block text-sm mb-1">Short (SI)</label><input name="short_si" required value="<?= esc((string) $data['short_si']) ?>" class="w-full border border-slate-300 rounded px-3 py-2"></div>

        <div class="md:col-span-2"><label class="block text-sm mb-1">Description (EN)</label><textarea name="description_en" rows="3" required class="w-full border border-slate-300 rounded px-3 py-2"><?= esc((string) $data['description_en']) ?></textarea></div>
        <div class="md:col-span-2"><label class="block text-sm mb-1">Description (SI)</label><textarea name="description_si" rows="3" required class="w-full border border-slate-300 rounded px-3 py-2"><?= esc((string) $data['description_si']) ?></textarea></div>

        <div><label class="block text-sm mb-1">Entry Fee (LKR)</label><input type="number" step="0.01" min="0" name="entry_fee_lkr" value="<?= esc((string) $data['entry_fee_lkr']) ?>" class="w-full border border-slate-300 rounded px-3 py-2"></div>
        <div>
            <label class="block text-sm mb-1">Image URL</label>
            <input id="image_url" name="image_url" value="<?= esc((string) $data['image_url']) ?>" class="w-full border border-slate-300 rounded px-3 py-2">
            <img id="image_preview" src="<?= esc((string) $data['image_url']) ?>" class="mt-2 rounded max-h-28 <?= $data['image_url'] === '' ? 'hidden' : '' ?>" alt="Preview">
        </div>

        <div><label class="block text-sm mb-1">Latitude</label><input id="latitude" type="number" step="0.0000001" name="latitude" value="<?= esc((string) $data['latitude']) ?>" required class="w-full border border-slate-300 rounded px-3 py-2"></div>
        <div><label class="block text-sm mb-1">Longitude</label><input id="longitude" type="number" step="0.0000001" name="longitude" value="<?= esc((string) $data['longitude']) ?>" required class="w-full border border-slate-300 rounded px-3 py-2"></div>

        <div class="md:col-span-2"><label class="inline-flex items-center gap-2"><input type="checkbox" name="is_active" value="1" <?= (string) $data['is_active'] === '1' ? 'checked' : '' ?>> Active</label></div>

        <div class="md:col-span-2">
            <label class="block text-sm mb-1">Location Preview</label>
            <div id="admin-map" class="h-64 rounded border border-slate-200"></div>
        </div>

        <div class="md:col-span-2 flex gap-2">
            <button class="bg-slate-900 text-white px-4 py-2 rounded hover:bg-slate-800">Save</button>
            <a href="<?= esc(url('admin/attractions.php')) ?>" class="bg-slate-200 px-4 py-2 rounded hover:bg-slate-300">Cancel</a>
        </div>
    </form>
</section>

<script>
    const imgInput = document.getElementById('image_url');
    const imgPreview = document.getElementById('image_preview');
    imgInput?.addEventListener('input', function () {
        const value = imgInput.value.trim();
        if (!value) {
            imgPreview.src = '';
            imgPreview.classList.add('hidden');
            return;
        }
        imgPreview.src = value;
        imgPreview.classList.remove('hidden');
    });

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    const map = L.map('admin-map').setView([
        parseFloat(latInput.value || '<?= esc((string) DEFAULT_LAT) ?>'),
        parseFloat(lngInput.value || '<?= esc((string) DEFAULT_LNG) ?>')
    ], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const marker = L.marker(map.getCenter()).addTo(map);

    function syncMarker() {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        if (Number.isFinite(lat) && Number.isFinite(lng)) {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], map.getZoom());
        }
    }

    latInput?.addEventListener('input', syncMarker);
    lngInput?.addEventListener('input', syncMarker);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>