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
    'latitude' => '6.2291414',
    'longitude' => '80.0573511',
    'image_url' => '',
    'is_active' => '1',
];

if ($isEdit) {
    $stmt = db()->prepare('SELECT * FROM attractions WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $found = $stmt->fetch();
    if ($found) {
        $data = array_merge($data, $found);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($data) as $key) {
        if (isset($_POST[$key])) {
            $data[$key] = trim((string) $_POST[$key]);
        }
    }
    $data['is_active'] = isset($_POST['is_active']) ? '1' : '0';

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
    }

    header('Location: ' . url('admin/attractions.php?saved=1'));
    exit;
}

$title = $isEdit ? 'Edit Attraction' : 'Add Attraction';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bg-white p-6 rounded-xl shadow">
    <h1 class="text-xl font-bold mb-4"><?= esc($title) ?></h1>
    <form method="post" class="grid md:grid-cols-2 gap-4">
        <div><label class="block text-sm mb-1">Category</label><input name="category" required value="<?= esc((string) $data['category']) ?>" class="w-full border border-slate-300 rounded px-3 py-2"></div>
        <div><label class="block text-sm mb-1">Open Hours</label><input name="open_hours" value="<?= esc((string) $data['open_hours']) ?>" class="w-full border border-slate-300 rounded px-3 py-2"></div>
        <div><label class="block text-sm mb-1">Name (EN)</label><input name="name_en" required value="<?= esc((string) $data['name_en']) ?>" class="w-full border border-slate-300 rounded px-3 py-2"></div>
        <div><label class="block text-sm mb-1">Name (SI)</label><input name="name_si" required value="<?= esc((string) $data['name_si']) ?>" class="w-full border border-slate-300 rounded px-3 py-2"></div>
        <div><label class="block text-sm mb-1">Short (EN)</label><input name="short_en" required value="<?= esc((string) $data['short_en']) ?>" class="w-full border border-slate-300 rounded px-3 py-2"></div>
        <div><label class="block text-sm mb-1">Short (SI)</label><input name="short_si" required value="<?= esc((string) $data['short_si']) ?>" class="w-full border border-slate-300 rounded px-3 py-2"></div>
        <div class="md:col-span-2"><label class="block text-sm mb-1">Description (EN)</label><textarea name="description_en" rows="3" required class="w-full border border-slate-300 rounded px-3 py-2"><?= esc((string) $data['description_en']) ?></textarea></div>
        <div class="md:col-span-2"><label class="block text-sm mb-1">Description (SI)</label><textarea name="description_si" rows="3" required class="w-full border border-slate-300 rounded px-3 py-2"><?= esc((string) $data['description_si']) ?></textarea></div>
        <div><label class="block text-sm mb-1">Entry Fee (LKR)</label><input type="number" step="0.01" name="entry_fee_lkr" value="<?= esc((string) $data['entry_fee_lkr']) ?>" class="w-full border border-slate-300 rounded px-3 py-2"></div>
        <div><label class="block text-sm mb-1">Image URL</label><input name="image_url" value="<?= esc((string) $data['image_url']) ?>" class="w-full border border-slate-300 rounded px-3 py-2"></div>
        <div><label class="block text-sm mb-1">Latitude</label><input type="number" step="0.0000001" name="latitude" value="<?= esc((string) $data['latitude']) ?>" required class="w-full border border-slate-300 rounded px-3 py-2"></div>
        <div><label class="block text-sm mb-1">Longitude</label><input type="number" step="0.0000001" name="longitude" value="<?= esc((string) $data['longitude']) ?>" required class="w-full border border-slate-300 rounded px-3 py-2"></div>
        <div class="md:col-span-2"><label class="inline-flex items-center gap-2"><input type="checkbox" name="is_active" value="1" <?= (string) $data['is_active'] === '1' ? 'checked' : '' ?>> Active</label></div>
        <div class="md:col-span-2 flex gap-2">
            <button class="bg-slate-900 text-white px-4 py-2 rounded hover:bg-slate-800">Save</button>
            <a href="<?= esc(url('admin/attractions.php')) ?>" class="bg-slate-200 px-4 py-2 rounded hover:bg-slate-300">Cancel</a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
