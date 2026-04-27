<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');

    if ($id > 0 && in_array($action, ['deactivate', 'activate'], true)) {
        $value = $action === 'activate' ? 1 : 0;
        $stmt = db()->prepare('UPDATE attractions SET is_active = :active WHERE id = :id');
        $stmt->execute(['active' => $value, 'id' => $id]);
        setFlash('success', $action === 'activate' ? 'Attraction activated.' : 'Attraction deactivated.');
    } elseif ($id > 0 && $action === 'delete') {
        $stmt = db()->prepare('DELETE FROM attractions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        setFlash('success', 'Attraction deleted permanently.');
    }

    header('Location: ' . url('admin/attractions.php'));
    exit;
}

$q = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['status'] ?? 'all');
$allowedStatus = ['all', 'active', 'inactive'];
if (!in_array($status, $allowedStatus, true)) {
    $status = 'all';
}

$sql = 'SELECT * FROM attractions WHERE 1=1';
$params = [];

if ($q !== '') {
    $sql .= ' AND (name_en LIKE :q OR name_si LIKE :q OR category LIKE :q)';
    $params['q'] = '%' . $q . '%';
}

if ($status === 'active') {
    $sql .= ' AND is_active = 1';
} elseif ($status === 'inactive') {
    $sql .= ' AND is_active = 0';
}

$sql .= ' ORDER BY updated_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$flash = getFlash();

$title = 'Manage Attractions';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bg-white p-6 rounded-xl shadow space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-bold">Manage Attractions</h1>
        <div class="flex gap-2">
            <a href="<?= esc(url('admin/dashboard.php')) ?>" class="px-3 py-2 rounded bg-slate-200 hover:bg-slate-300 text-sm">Dashboard</a>
            <a href="<?= esc(url('admin/attraction_form.php')) ?>" class="px-3 py-2 rounded bg-emerald-600 hover:bg-emerald-500 text-white text-sm">Add New</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="p-3 rounded <?= $flash['type'] === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-700' ?>">
            <?= esc((string) $flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="get" class="grid md:grid-cols-4 gap-3">
        <div class="md:col-span-2">
            <label class="block text-sm mb-1">Search</label>
            <input name="q" value="<?= esc($q) ?>" placeholder="Name or category" class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm mb-1">Status</label>
            <select name="status" class="w-full border border-slate-300 rounded px-3 py-2">
                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800">Filter</button>
            <a href="<?= esc(url('admin/attractions.php')) ?>" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">Reset</a>
        </div>
    </form>

    <p class="text-sm text-slate-600">Results: <?= count($rows) ?></p>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border border-slate-200">
            <thead class="bg-slate-100">
                <tr>
                    <th class="p-2 text-left">Name (EN)</th>
                    <th class="p-2 text-left">Category</th>
                    <th class="p-2 text-left">Coords</th>
                    <th class="p-2 text-left">Fee</th>
                    <th class="p-2 text-left">Status</th>
                    <th class="p-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="6" class="p-3 text-slate-500">No matching records.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <tr class="border-t border-slate-200">
                        <td class="p-2"><?= esc($row['name_en']) ?></td>
                        <td class="p-2"><?= esc($row['category']) ?></td>
                        <td class="p-2"><?= esc($row['latitude']) ?>, <?= esc($row['longitude']) ?></td>
                        <td class="p-2"><?= esc(money((float) $row['entry_fee_lkr'])) ?></td>
                        <td class="p-2"><?= (int) $row['is_active'] === 1 ? 'Active' : 'Inactive' ?></td>
                        <td class="p-2">
                            <a href="<?= esc(url('admin/attraction_form.php')) ?>?id=<?= (int) $row['id'] ?>" class="text-blue-700 hover:underline">Edit</a>
                            <form method="post" class="inline" onsubmit="return confirm('Change status for this attraction?')">
                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                <?php if ((int) $row['is_active'] === 1): ?>
                                    <input type="hidden" name="action" value="deactivate">
                                    <button type="submit" class="text-red-600 hover:underline ml-3">Deactivate</button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="activate">
                                    <button type="submit" class="text-emerald-700 hover:underline ml-3">Activate</button>
                                <?php endif; ?>
                            </form>
                            <form method="post" class="inline" onsubmit="return confirm('Permanently delete this attraction? Trip plans that reference it will lose those stops. This cannot be undone.')">
                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="text-slate-600 hover:text-red-700 hover:underline ml-3 font-medium">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>