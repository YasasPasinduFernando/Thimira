<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$total = (int) db()->query('SELECT COUNT(*) FROM attractions')->fetchColumn();
$active = (int) db()->query('SELECT COUNT(*) FROM attractions WHERE is_active = 1')->fetchColumn();
$inactive = $total - $active;
$avgFee = (float) db()->query('SELECT COALESCE(AVG(entry_fee_lkr), 0) FROM attractions WHERE is_active = 1')->fetchColumn();

$recentStmt = db()->query('SELECT id, name_en, category, is_active, updated_at FROM attractions ORDER BY updated_at DESC LIMIT 5');
$recentRows = $recentStmt->fetchAll();

$title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="space-y-6">
    <div class="bg-white p-6 rounded-xl shadow">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">Admin Dashboard</h1>
                <p class="text-slate-600 mt-1">Welcome, <?= esc((string) ($_SESSION['admin_username'] ?? 'admin')) ?></p>
            </div>
            <a href="<?= esc(url('admin/logout.php')) ?>" class="px-3 py-2 rounded bg-red-100 text-red-700 hover:bg-red-200">Logout</a>
        </div>

        <div class="grid md:grid-cols-4 sm:grid-cols-2 gap-4 mt-6">
            <div class="p-4 rounded-lg bg-slate-100">
                <p class="text-sm text-slate-600">Total Attractions</p>
                <p class="text-2xl font-bold"><?= $total ?></p>
            </div>
            <div class="p-4 rounded-lg bg-emerald-100">
                <p class="text-sm text-emerald-700">Active</p>
                <p class="text-2xl font-bold text-emerald-800"><?= $active ?></p>
            </div>
            <div class="p-4 rounded-lg bg-amber-100">
                <p class="text-sm text-amber-700">Inactive</p>
                <p class="text-2xl font-bold text-amber-800"><?= $inactive ?></p>
            </div>
            <div class="p-4 rounded-lg bg-blue-100">
                <p class="text-sm text-blue-700">Avg Fee (Active)</p>
                <p class="text-2xl font-bold text-blue-800"><?= esc(money($avgFee)) ?></p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 mt-6">
            <a href="<?= esc(url('admin/attractions.php')) ?>" class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800">Manage Attractions</a>
            <a href="<?= esc(url('admin/attraction_form.php')) ?>" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-500">Add New Attraction</a>
            <a href="<?= esc(url('index.php')) ?>" target="_blank" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">View Public Site</a>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-lg font-semibold mb-3">Recently Updated</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border border-slate-200">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-left">Category</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2 text-left">Updated</th>
                        <th class="p-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$recentRows): ?>
                        <tr><td colspan="5" class="p-3 text-slate-500">No records found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recentRows as $row): ?>
                        <tr class="border-t border-slate-200">
                            <td class="p-2"><?= esc($row['name_en']) ?></td>
                            <td class="p-2"><?= esc($row['category']) ?></td>
                            <td class="p-2"><?= (int) $row['is_active'] === 1 ? 'Active' : 'Inactive' ?></td>
                            <td class="p-2"><?= esc((string) $row['updated_at']) ?></td>
                            <td class="p-2"><a class="text-blue-700 hover:underline" href="<?= esc(url('admin/attraction_form.php')) ?>?id=<?= (int) $row['id'] ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>