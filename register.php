<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/email_layout.php';

if (isUserLoggedIn()) {
    header('Location: ' . url('index.php'));
    exit;
}

$error = '';
$title = 'Register';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $check = db()->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
        $check->execute([
            'username' => $username,
            'email' => $email,
        ]);

        if ($check->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            $stmt = db()->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)');
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'user',
            ]);

            $_SESSION['user_id'] = (int) db()->lastInsertId();
            $_SESSION['user_username'] = $username;

            $subject = APP_NAME . ' - Welcome';
            $inner = '<p style="margin:0 0 16px 0;">Hello <strong>' . esc($username) . '</strong>,</p>'
                . '<p style="margin:0 0 16px 0;">Your account was created successfully. You can sign in anytime to explore nearby attractions, plan day trips, and track your visits.</p>'
                . '<p style="margin:0 0 20px 0;">We are glad you joined <strong>' . esc(email_brand_display_name()) . '</strong>.</p>'
                . '<p style="margin:0;color:#64748b;font-size:14px;">Happy travels!</p>';
            $html = email_layout_html('Account', 'Welcome aboard', $inner);
            $text = 'Welcome to ' . APP_NAME . ". Your account was created successfully.\n\nHappy travels from Village Traveler.";
            sendAppEmail($email, $subject, $html, $text);

            header('Location: ' . url('index.php'));
            exit;
        }
    }
}

$mainClass = 'flex min-h-[calc(100vh-10rem)] items-center justify-center py-4';
require_once __DIR__ . '/includes/header.php';
?>
<section class="w-full max-w-md rounded-3xl border border-slate-200/80 bg-white/95 p-8 shadow-card backdrop-blur-sm sm:p-10">
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-slate-900 shadow-soft">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        </div>
        <h1 class="font-display text-2xl font-bold text-slate-900">Create account</h1>
        <p class="mt-2 text-sm text-slate-600">Join Village Traveler in a few steps</p>
    </div>
    <?php if ($error): ?>
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><?= esc($error) ?></div>
    <?php endif; ?>
    <form method="post" class="space-y-5">
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Username</label>
            <input type="text" name="username" value="<?= esc($username) ?>" required autocomplete="username" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
            <input type="email" name="email" value="<?= esc($email) ?>" required autocomplete="email" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Password</label>
            <input type="password" name="password" required autocomplete="new-password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Confirm password</label>
            <input type="password" name="confirm_password" required autocomplete="new-password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm">
        </div>
        <button type="submit" class="w-full rounded-2xl bg-slate-900 py-3.5 text-sm font-bold text-white shadow-soft transition hover:bg-slate-800">Register</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-600">Already have an account? <a class="font-semibold text-village-700 hover:underline" href="<?= esc(url('login.php')) ?>">Login</a></p>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
