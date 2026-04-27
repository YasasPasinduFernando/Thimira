<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$adminSessionId = (int) ($_SESSION['admin_id'] ?? 0);

$data = [
    'username' => '',
    'email' => '',
    'role' => 'user',
];
$errors = [];

if ($isEdit) {
    $stmt = db()->prepare('SELECT id, username, email, role FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $found = $stmt->fetch();
    if ($found) {
        $data['username'] = (string) $found['username'];
        $data['email'] = (string) $found['email'];
        $data['role'] = (string) $found['role'];
    } else {
        setFlash('error', 'User not found.');
        header('Location: ' . url('admin/users.php'));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['username'] = trim((string) ($_POST['username'] ?? ''));
    $data['email'] = trim((string) ($_POST['email'] ?? ''));
    $data['role'] = (string) ($_POST['role'] ?? 'user');
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if (!in_array($data['role'], ['admin', 'user'], true)) {
        $data['role'] = 'user';
    }

    if ($data['username'] === '' || $data['email'] === '') {
        $errors[] = 'Username and email are required.';
    } elseif (strlen($data['username']) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if (!$isEdit || $password !== '' || $confirm !== '') {
        if ($isEdit && $password === '' && $confirm === '') {
            // keep password — already skipped validation
        } elseif ($password === '' || $confirm === '') {
            $errors[] = 'Password and confirmation are required when changing the password.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }
    } elseif (!$isEdit) {
        $errors[] = 'Password is required for new users.';
    }

    if (!$errors && !$isEdit && $password === '') {
        $errors[] = 'Password is required for new users.';
    }

    if (!$errors) {
        $dup = db()->prepare('SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :id LIMIT 1');
        $dup->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'id' => $isEdit ? $id : 0,
        ]);
        if ($dup->fetch()) {
            $errors[] = 'Another account already uses this username or email.';
        }
    }

    if (!$errors && $isEdit) {
        $prevStmt = db()->prepare('SELECT role FROM users WHERE id = :id LIMIT 1');
        $prevStmt->execute(['id' => $id]);
        $prevRole = (string) ($prevStmt->fetch()['role'] ?? '');
        if ($prevRole === 'admin' && $data['role'] === 'user') {
            $keep = db()->prepare('SELECT COUNT(*) FROM users WHERE role = :role AND id != :id');
            $keep->execute(['role' => 'admin', 'id' => $id]);
            if ((int) $keep->fetchColumn() < 1) {
                $errors[] = 'Cannot remove the last administrator. Promote another user first.';
            }
        }
    }

    if (!$errors) {
        if ($isEdit) {
            if ($password !== '') {
                $upd = db()->prepare('UPDATE users SET username = :username, email = :email, role = :role, password_hash = :password_hash, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = :id');
                $upd->execute([
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'role' => $data['role'],
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'id' => $id,
                ]);
            } else {
                $upd = db()->prepare('UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id');
                $upd->execute([
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'role' => $data['role'],
                    'id' => $id,
                ]);
            }
            setFlash('success', 'User updated successfully.');
        } else {
            $ins = db()->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)');
            $ins->execute([
                'username' => $data['username'],
                'email' => $data['email'],
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $data['role'],
            ]);
            setFlash('success', 'User created successfully.');
        }
        header('Location: ' . url('admin/users.php'));
        exit;
    }
}

$title = $isEdit ? 'Edit user' : 'Add user';
$navBackHref = url('admin/users.php');
$navBackText = t(['en' => 'Users list', 'si' => 'පරිශීලක ලැයිස්තුව'], lang());
require_once __DIR__ . '/../includes/header.php';
?>
<section class="bg-white p-6 rounded-xl shadow space-y-4 max-w-xl">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-xl font-bold"><?= esc($title) ?></h1>
        <a href="<?= esc(url('admin/users.php')) ?>" class="px-3 py-2 rounded bg-slate-200 hover:bg-slate-300 text-sm">Back to list</a>
    </div>

    <?php if ($errors): ?>
        <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            <ul class="list-disc pl-5 space-y-1">
                <?php foreach ($errors as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
            <input name="username" required value="<?= esc($data['username']) ?>" autocomplete="username" class="w-full border border-slate-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input type="email" name="email" required value="<?= esc($data['email']) ?>" autocomplete="email" class="w-full border border-slate-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
            <select name="role" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                <option value="user" <?= $data['role'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $data['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <p class="mt-1 text-xs text-slate-500">Admins can sign in at the admin panel and manage content.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1"><?= $isEdit ? 'New password (optional)' : 'Password' ?></label>
            <input type="password" name="password" <?= $isEdit ? '' : 'required' ?> autocomplete="new-password" class="w-full border border-slate-300 rounded-lg px-3 py-2" placeholder="<?= $isEdit ? 'Leave blank to keep current' : '' ?>">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Confirm password</label>
            <input type="password" name="confirm_password" <?= $isEdit ? '' : 'required' ?> autocomplete="new-password" class="w-full border border-slate-300 rounded-lg px-3 py-2">
        </div>
        <div class="flex gap-2 pt-2">
            <button type="submit" class="rounded-lg bg-village-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-village-700"><?= $isEdit ? 'Save changes' : 'Create user' ?></button>
            <a href="<?= esc(url('admin/users.php')) ?>" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 hover:border-village-300">Cancel</a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
