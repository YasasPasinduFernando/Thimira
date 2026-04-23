<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$title = 'Reset Password';
$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$error = '';
$message = '';
$isValidToken = false;

if ($token !== '') {
    $tokenHash = hash('sha256', $token);
    $stmt = db()->prepare('SELECT id FROM users WHERE reset_token_hash = :token_hash AND reset_token_expires_at > NOW() LIMIT 1');
    $stmt->execute(['token_hash' => $tokenHash]);
    $isValidToken = (bool) $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if (!$isValidToken) {
        $error = 'This reset link is invalid or expired.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $tokenHash = hash('sha256', $token);
        $update = db()->prepare('UPDATE users SET password_hash = :password_hash, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE reset_token_hash = :token_hash');
        $update->execute([
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'token_hash' => $tokenHash,
        ]);

        $message = 'Password reset successful. You can now login.';
        $isValidToken = false;
    }
}

$mainClass = 'flex min-h-[calc(100vh-10rem)] items-center justify-center py-4';
require_once __DIR__ . '/includes/header.php';
?>
<section class="w-full max-w-md rounded-3xl border border-slate-200/80 bg-white/95 p-8 shadow-card backdrop-blur-sm sm:p-10">
    <h1 class="font-display text-2xl font-bold text-slate-900">Reset password</h1>
    <p class="mt-2 text-sm text-slate-600">Choose a new password for your account.</p>
    <?php if ($error): ?>
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><?= esc($error) ?></div>
    <?php endif; ?>
    <?php if ($message): ?>
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            <?= esc($message) ?> <a class="font-semibold underline hover:no-underline" href="<?= esc(url('login.php')) ?>">Login</a>
        </div>
    <?php endif; ?>

    <?php if ($isValidToken): ?>
        <form method="post" class="mt-6 space-y-5">
            <input type="hidden" name="token" value="<?= esc($token) ?>">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">New password</label>
                <input type="password" name="password" required autocomplete="new-password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Confirm password</label>
                <input type="password" name="confirm_password" required autocomplete="new-password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm">
            </div>
            <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-village-600 to-village-700 py-3.5 text-sm font-bold text-white shadow-soft transition hover:from-village-700 hover:to-village-800">Reset password</button>
        </form>
    <?php elseif ($message === ''): ?>
        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">This reset link is invalid or expired.</div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
