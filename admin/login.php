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

    $stmt = db()->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
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
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-md mx-auto bg-white p-6 rounded-xl shadow">
    <h1 class="text-xl font-bold mb-4">Admin Login</h1>
    <?php if ($error): ?>
        <div class="mb-3 p-3 bg-red-100 text-red-700 rounded"><?= esc($error) ?></div>
    <?php endif; ?>
    <form method="post" class="space-y-3">
        <div>
            <label class="block text-sm mb-1">Username</label>
            <input type="text" name="username" required class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm mb-1">Password</label>
            <input type="password" name="password" required class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <button class="w-full bg-slate-900 text-white py-2 rounded hover:bg-slate-800">Login</button>
    </form>
    <p class="mt-4 text-xs text-slate-500">Default: admin / admin123</p>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
