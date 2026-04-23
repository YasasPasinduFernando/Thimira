<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isAdminLoggedIn()) {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT * FROM users WHERE username = :username AND role = :role LIMIT 1');
    $stmt->execute([
        'username' => $username,
        'role' => 'admin',
    ]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_id'] = (int) $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        header('Location: ' . url('admin/dashboard.php'));
        exit;
    }

    $error = 'Invalid username or password.';
}

$title = 'Admin Login';
$mainClass = 'flex min-h-[calc(100vh-10rem)] items-center justify-center py-4';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="w-full max-w-md rounded-3xl border border-slate-200/80 bg-white/95 p-8 shadow-card backdrop-blur-sm sm:p-10">
    <div class="mb-6 flex items-center gap-3">
        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-sm font-bold text-white">A</span>
        <div>
            <h1 class="font-display text-xl font-bold text-slate-900">Admin login</h1>
            <p class="text-xs text-slate-500">Restricted area</p>
        </div>
    </div>
    <?php if ($error): ?>
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><?= esc($error) ?></div>
    <?php endif; ?>
    <form method="post" class="space-y-5">
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Username</label>
            <input type="text" name="username" required autocomplete="username" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Password</label>
            <input type="password" name="password" required autocomplete="current-password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm">
        </div>
        <button type="submit" class="w-full rounded-2xl bg-slate-900 py-3.5 text-sm font-bold text-white shadow-soft transition hover:bg-slate-800">Login</button>
    </form>
    <p class="mt-6 text-center text-xs text-slate-500">Default: admin / admin123</p>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
