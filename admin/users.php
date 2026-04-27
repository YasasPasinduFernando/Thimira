<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$adminSessionId = (int) ($_SESSION['admin_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');

    if ($id > 0 && $action === 'delete') {
        if ($id === $adminSessionId) {
            setFlash('error', 'You cannot delete your own account.');
        } else {
            $u = db()->prepare('SELECT id, role FROM users WHERE id = :id LIMIT 1');
            $u->execute(['id' => $id]);
            $row = $u->fetch();
            if (!$row) {
                setFlash('error', 'User not found.');
            } elseif ((string) $row['role'] === 'admin') {
                $ac = (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
                if ($ac <= 1) {
                    setFlash('error', 'Cannot delete the last administrator.');
                } else {
                    db()->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $id]);
                    setFlash('success', 'User deleted.');
                }
            } else {
                db()->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $id]);
                setFlash('success', 'User deleted.');
            }
        }
    }

    header('Location: ' . url('admin/users.php'));
    exit;
}

$q = trim((string) ($_GET['q'] ?? ''));
$roleFilter = (string) ($_GET['role'] ?? 'all');
if (!in_array($roleFilter, ['all', 'admin', 'user'], true)) {
    $roleFilter = 'all';
}

$sql = 'SELECT id, username, email, role, created_at FROM users WHERE 1=1';
$params = [];

if ($q !== '') {
    $sql .= ' AND (username LIKE :q OR email LIKE :q)';
    $params['q'] = '%' . $q . '%';
}

if ($roleFilter === 'admin') {
    $sql .= " AND role = 'admin'";
} elseif ($roleFilter === 'user') {
    $sql .= " AND role = 'user'";
}

$sql .= ' ORDER BY created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$userTotal = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$flash = getFlash();

$title = 'Manage users';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bg-white p-6 rounded-xl shadow space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-bold">Manage users</h1>
        <div class="flex flex-wrap gap-2">
            <a href="<?= esc(url('admin/dashboard.php')) ?>" class="px-3 py-2 rounded bg-slate-200 hover:bg-slate-300 text-sm">Dashboard</a>
            <a href="<?= esc(url('admin/user_form.php')) ?>" class="px-3 py-2 rounded bg-emerald-600 hover:bg-emerald-500 text-white text-sm">Add user</a>
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
            <input name="q" value="<?= esc($q) ?>" placeholder="Username or email" class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm mb-1">Role</label>
            <select name="role" class="w-full border border-slate-300 rounded px-3 py-2">
                <option value="all" <?= $roleFilter === 'all' ? 'selected' : '' ?>>All</option>
                <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="user" <?= $roleFilter === 'user' ? 'selected' : '' ?>>User</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800">Filter</button>
            <a href="<?= esc(url('admin/users.php')) ?>" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">Reset</a>
        </div>
    </form>

    <p class="text-sm text-slate-600">Results: <?= count($rows) ?> · Total users: <?= $userTotal ?></p>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border border-slate-200">
            <thead class="bg-slate-100">
                <tr>
                    <th class="p-2 text-left">Username</th>
                    <th class="p-2 text-left">Email</th>
                    <th class="p-2 text-left">Role</th>
                    <th class="p-2 text-left">Created</th>
                    <th class="p-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="5" class="p-3 text-slate-500">No matching users.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <tr class="border-t border-slate-200">
                        <td class="p-2 font-medium text-slate-900">
                            <?= esc((string) $row['username']) ?>
                            <?php if ((int) $row['id'] === $adminSessionId): ?>
                                <span class="ml-1 text-xs font-normal text-slate-500">(you)</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-2 text-slate-600"><?= esc((string) $row['email']) ?></td>
                        <td class="p-2">
                            <?php if ((string) $row['role'] === 'admin'): ?>
                                <span class="rounded-full bg-village-100 px-2 py-0.5 text-xs font-bold text-village-800">Admin</span>
                            <?php else: ?>
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">User</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-2 text-slate-500"><?= esc((string) $row['created_at']) ?></td>
                        <td class="p-2">
                            <a href="<?= esc(url('admin/user_form.php')) ?>?id=<?= (int) $row['id'] ?>" class="text-blue-700 hover:underline">Edit</a>
                            <?php if ((int) $row['id'] !== $adminSessionId): ?>
                                <form method="post" class="inline" onsubmit="return confirm('Delete this user? Their trip plans will be removed.')">
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="text-red-600 hover:underline ml-3">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
