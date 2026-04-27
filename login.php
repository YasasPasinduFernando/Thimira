<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$nextRaw = (string) ($_GET['next'] ?? $_POST['next'] ?? '');
$nextSafe = safe_login_next($nextRaw) ?? '';

if (isUserLoggedIn()) {
    header('Location: ' . url($nextSafe !== '' ? $nextSafe : 'index.php'));
    exit;
}

$appLang = lang();
$error = '';
$title = t(['en' => 'Login', 'si' => 'ඇතුල් වන්න'], $appLang);
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $nextSafe = safe_login_next((string) ($_POST['next'] ?? '')) ?? '';

    $stmt = db()->prepare('SELECT id, username, password_hash FROM users WHERE username = :identity OR email = :identity LIMIT 1');
    $stmt->execute(['identity' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, (string) $user['password_hash'])) {
        adopt_frontend_user_session((int) $user['id'], (string) $user['username']);
        header('Location: ' . url($nextSafe !== '' ? $nextSafe : 'index.php'));
        exit;
    }

    $error = t(['en' => 'Invalid username or password.', 'si' => 'පරිශීලක නාමය හෝ මුරපදය වැරදියි.'], $appLang);
}

$mainClass = 'flex min-h-[calc(100vh-10rem)] items-center justify-center py-4';
require_once __DIR__ . '/includes/header.php';
?>
<section class="w-full max-w-md rounded-3xl border border-slate-200/80 bg-white/95 p-8 shadow-card backdrop-blur-sm sm:p-10">
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-village-400 to-village-700 text-white shadow-glow">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <h1 class="font-display text-2xl font-bold text-slate-900"><?= esc(t(['en' => 'Welcome back', 'si' => 'නැවත සාදරයෙන් පිළිගනිමු'], $appLang)) ?></h1>
        <p class="mt-2 text-sm text-slate-600"><?= esc($nextSafe !== ''
            ? t(['en' => 'Sign in to continue to the page you opened.', 'si' => 'ඔබ අරින ලද පිටුවට යාමට ඇතුල් වන්න.'], $appLang)
            : t(['en' => 'Sign in to continue your journey', 'si' => 'ඔබේ සංචාරය ඉදිරියට යාමට ඇතුල් වන්න'], $appLang)) ?></p>
    </div>
    <?php if ($error): ?>
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><?= esc($error) ?></div>
    <?php endif; ?>
    <form method="post" class="space-y-5">
        <?php if ($nextSafe !== ''): ?>
            <input type="hidden" name="next" value="<?= esc($nextSafe) ?>">
        <?php endif; ?>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700"><?= esc(t(['en' => 'Username or email', 'si' => 'පරිශීලක නාමය හෝ විද්‍යුත් තැපෑල'], $appLang)) ?></label>
            <input type="text" name="username" value="<?= esc($username) ?>" required autocomplete="username" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm placeholder:text-slate-400">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700"><?= esc(t(['en' => 'Password', 'si' => 'මුරපදය'], $appLang)) ?></label>
            <input type="password" name="password" required autocomplete="current-password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm placeholder:text-slate-400">
        </div>
        <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-village-600 to-village-700 py-3.5 text-sm font-bold text-white shadow-soft transition hover:from-village-700 hover:to-village-800"><?= esc(t(['en' => 'Login', 'si' => 'ඇතුල් වන්න'], $appLang)) ?></button>
    </form>
    <p class="mt-5 text-center text-sm"><a class="font-semibold text-village-700 hover:text-village-900 hover:underline" href="<?= esc(url('forgot-password.php')) ?>"><?= esc(t(['en' => 'Forgot password?', 'si' => 'මුරපදය අමතකද?'], $appLang)) ?></a></p>
    <p class="mt-4 text-center text-sm text-slate-600"><?= esc(t(['en' => 'No account?', 'si' => 'ගිණුමක් නැද්ද?'], $appLang)) ?> <a class="font-semibold text-village-700 hover:underline" href="<?= esc(url('register.php')) ?>"><?= esc(t(['en' => 'Register', 'si' => 'ලියාපදිංචි'], $appLang)) ?></a></p>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
