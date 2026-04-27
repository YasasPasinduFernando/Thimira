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

$appLang = lang();
$error = '';
$title = t(['en' => 'Register', 'si' => 'ලියාපදිංචි'], $appLang);
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $error = t(['en' => 'All fields are required.', 'si' => 'සියලු ක්ෂේත්‍ර අවශ්‍යයි.'], $appLang);
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = t(['en' => 'Enter a valid email address.', 'si' => 'වලංගු විද්‍යුත් තැපැල් ලිපිනයක් ඇතුළත් කරන්න.'], $appLang);
    } elseif (strlen($username) < 3) {
        $error = t(['en' => 'Username must be at least 3 characters.', 'si' => 'පරිශීලක නාමය අවම වශයෙන් අක්ෂර 3 ක් විය යුතුය.'], $appLang);
    } elseif (strlen($password) < 6) {
        $error = t(['en' => 'Password must be at least 6 characters.', 'si' => 'මුරපදය අවම වශයෙන් අක්ෂර 6 ක් විය යුතුය.'], $appLang);
    } elseif ($password !== $confirm) {
        $error = t(['en' => 'Passwords do not match.', 'si' => 'මුරපද නොගැලපේ.'], $appLang);
    } else {
        $check = db()->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
        $check->execute([
            'username' => $username,
            'email' => $email,
        ]);

        if ($check->fetch()) {
            $error = t(['en' => 'Username or email already exists.', 'si' => 'පරිශීලක නාමය හෝ විද්‍යුත් තැපෑල දැනටමත් භාවිතයේය.'], $appLang);
        } else {
            $stmt = db()->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)');
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'user',
            ]);

            adopt_frontend_user_session((int) db()->lastInsertId(), $username);

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
$navBackHref = url('index.php') . '?' . http_build_query(['lang' => $appLang]);
$navBackText = t(['en' => 'Home', 'si' => 'මුල් පිටුව'], $appLang);
require_once __DIR__ . '/includes/header.php';
?>
<section class="w-full max-w-md rounded-3xl border border-slate-200/80 bg-white/95 p-8 shadow-card backdrop-blur-sm sm:p-10">
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-slate-900 shadow-soft">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        </div>
        <h1 class="font-display text-2xl font-bold text-slate-900"><?= esc(t(['en' => 'Create account', 'si' => 'ගිණුම සාදන්න'], $appLang)) ?></h1>
        <p class="mt-2 text-sm text-slate-600"><?= esc(t(['en' => 'Join Village Traveler in a few steps', 'si' => 'කෙටි පියවර කිහිපයකින් Village Traveler සමඟ එකතු වන්න'], $appLang)) ?></p>
    </div>
    <?php if ($error): ?>
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><?= esc($error) ?></div>
    <?php endif; ?>
    <form method="post" class="space-y-5">
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700"><?= esc(t(['en' => 'Username', 'si' => 'පරිශීලක නාමය'], $appLang)) ?></label>
            <input type="text" name="username" value="<?= esc($username) ?>" required autocomplete="username" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700"><?= esc(t(['en' => 'Email', 'si' => 'විද්‍යුත් තැපෑල'], $appLang)) ?></label>
            <input type="email" name="email" value="<?= esc($email) ?>" required autocomplete="email" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700"><?= esc(t(['en' => 'Password', 'si' => 'මුරපදය'], $appLang)) ?></label>
            <input type="password" name="password" required autocomplete="new-password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700"><?= esc(t(['en' => 'Confirm password', 'si' => 'මුරපදය තහවුරු කරන්න'], $appLang)) ?></label>
            <input type="password" name="confirm_password" required autocomplete="new-password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm">
        </div>
        <button type="submit" class="w-full rounded-2xl bg-slate-900 py-3.5 text-sm font-bold text-white shadow-soft transition hover:bg-slate-800"><?= esc(t(['en' => 'Register', 'si' => 'ලියාපදිංචි'], $appLang)) ?></button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-600"><?= esc(t(['en' => 'Already have an account?', 'si' => 'දැනටමත් ගිණුමක් තිබේද?'], $appLang)) ?> <a class="font-semibold text-village-700 hover:underline" href="<?= esc(url('login.php')) ?>"><?= esc(t(['en' => 'Login', 'si' => 'ඇතුල් වන්න'], $appLang)) ?></a></p>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
