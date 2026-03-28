<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = db()->prepare('UPDATE attractions SET is_active = 0 WHERE id = :id');
    $stmt->execute(['id' => $id]);
    header('Location: ' . url('admin/attractions.php?deleted=1'));
    exit;
}

$rows = db()->query('SELECT * FROM attractions ORDER BY created_at DESC')->fetchAll();

$title = 'Manage Attractions';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bg-white p-6 rounded-xl shadow">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-xl font-bold">Manage Attractions</h1>
        <div class="flex gap-2">
            <a href="<?= esc(url('admin/dashboard.php')) ?>" class="px-3 py-2 rounded bg-slate-200 hover:bg-slate-300 text-sm">Dashboard</a>
            <a href="<?= esc(url('admin/attraction_form.php')) ?>" class="px-3 py-2 rounded bg-emerald-600 hover:bg-emerald-500 text-white text-sm">Add New</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border border-slate-200">
            <thead class="bg-slate-100">
                <tr>
                    <th class="p-2 text-left">Name (EN)</th>
                    <th class="p-2 text-left">Category</th>
                    <th class="p-2 text-left">Coords</th>
                    <th class="p-2 text-left">Status</th>
                    <th class="p-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr class="border-t border-slate-200">
                        <td class="p-2"><?= esc($row['name_en']) ?></td>
                        <td class="p-2"><?= esc($row['category']) ?></td>
                        <td class="p-2"><?= esc($row['latitude']) ?>, <?= esc($row['longitude']) ?></td>
                        <td class="p-2"><?= (int) $row['is_active'] === 1 ? 'Active' : 'Inactive' ?></td>
                        <td class="p-2">
                            <a href="<?= esc(url('admin/attraction_form.php')) ?>?id=<?= (int) $row['id'] ?>" class="text-blue-700 hover:underline">Edit</a>
                            <form method="post" class="inline" onsubmit="return confirm('Deactivate this attraction?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                <button type="submit" class="text-red-600 hover:underline ml-3">Deactivate</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
