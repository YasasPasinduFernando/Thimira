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
<section class="space-y-8">
    <div class="surface-card border border-slate-100 p-6 md:p-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-display text-2xl font-bold text-slate-900 md:text-3xl">Admin dashboard</h1>
                <p class="mt-1 text-sm text-slate-600">Welcome, <?= esc((string) ($_SESSION['admin_username'] ?? 'admin')) ?></p>
            </div>
            <a href="<?= esc(url('admin/logout.php')) ?>" class="rounded-xl bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 ring-1 ring-red-200 transition hover:bg-red-100">Logout</a>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-gradient-to-br from-slate-50 to-slate-100/80 p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total</p>
                <p class="mt-2 font-display text-3xl font-bold text-slate-900"><?= $total ?></p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50/80 p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Active</p>
                <p class="mt-2 font-display text-3xl font-bold text-emerald-900"><?= $active ?></p>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-orange-50/50 p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-800">Inactive</p>
                <p class="mt-2 font-display text-3xl font-bold text-amber-900"><?= $inactive ?></p>
            </div>
            <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50/50 p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-800">Avg fee</p>
                <p class="mt-2 font-display text-xl font-bold text-blue-900"><?= esc(money($avgFee)) ?></p>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-2">
            <a href="<?= esc(url('admin/users.php')) ?>" class="inline-flex rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-slate-800">Manage users</a>
            <a href="<?= esc(url('admin/user_form.php')) ?>" class="inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-soft transition hover:border-village-300">Add user</a>
            <a href="<?= esc(url('admin/attractions.php')) ?>" class="inline-flex rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-slate-800">Manage attractions</a>
            <a href="<?= esc(url('admin/attraction_form.php')) ?>" class="inline-flex rounded-xl bg-village-600 px-4 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-village-700">Add attraction</a>
            <a href="<?= esc(url('index.php')) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 transition hover:border-village-300">View public site</a>
        </div>
    </div>

    <div class="surface-card border border-slate-100 p-6 md:p-8">
        <h2 class="font-display text-lg font-bold text-slate-900">Recently updated</h2>
        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 shadow-soft">
            <table class="min-w-full text-sm">
                <thead class="bg-gradient-to-r from-slate-50 to-teal-50/40">
                    <tr>
                        <th class="p-4 text-left font-display font-semibold text-slate-700">Name</th>
                        <th class="p-4 text-left font-display font-semibold text-slate-700">Category</th>
                        <th class="p-4 text-left font-display font-semibold text-slate-700">Status</th>
                        <th class="p-4 text-left font-display font-semibold text-slate-700">Updated</th>
                        <th class="p-4 text-left font-display font-semibold text-slate-700">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if (!$recentRows): ?>
                        <tr><td colspan="5" class="p-6 text-center text-slate-500">No records found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recentRows as $row): ?>
                        <tr class="transition hover:bg-teal-50/20">
                            <td class="p-4 font-medium text-slate-900"><?= esc($row['name_en']) ?></td>
                            <td class="p-4 text-slate-600"><?= esc($row['category']) ?></td>
                            <td class="p-4"><?= (int) $row['is_active'] === 1 ? '<span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">Active</span>' : '<span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">Inactive</span>' ?></td>
                            <td class="p-4 text-slate-500"><?= esc((string) $row['updated_at']) ?></td>
                            <td class="p-4"><a class="font-semibold text-village-700 hover:text-village-900 hover:underline" href="<?= esc(url('admin/attraction_form.php')) ?>?id=<?= (int) $row['id'] ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>