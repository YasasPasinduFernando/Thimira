<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/email_layout.php';

$title = 'Forgot Password';
$email = '';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } else {
        $stmt = db()->prepare('SELECT id, username, email FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

            $update = db()->prepare('UPDATE users SET reset_token_hash = :token_hash, reset_token_expires_at = :expires_at WHERE id = :id');
            $update->execute([
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt,
                'id' => (int) $user['id'],
            ]);

            $resetLink = url('reset-password.php') . '?token=' . urlencode($token);
            $subject = APP_NAME . ' - Password Reset';
            $inner = '<p style="margin:0 0 16px 0;">Hello <strong>' . esc((string) $user['username']) . '</strong>,</p>'
                . '<p style="margin:0 0 20px 0;">We received a request to reset your password. Use the button below (valid for <strong>1 hour</strong>).</p>'
                . '<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 20px 0;"><tr><td style="border-radius:12px;background:linear-gradient(135deg,#0d9488,#0f766e);">'
                . '<a href="' . esc($resetLink) . '" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:12px;">Reset my password</a>'
                . '</td></tr></table>'
                . '<p style="margin:0 0 12px 0;font-size:13px;color:#64748b;">If the button does not work, copy this link into your browser:</p>'
                . '<p style="margin:0 0 20px 0;word-break:break-all;font-size:13px;color:#0f766e;"><a href="' . esc($resetLink) . '" style="color:#0f766e;">' . esc($resetLink) . '</a></p>'
                . '<p style="margin:0;font-size:13px;color:#94a3b8;">If you did not request this, you can ignore this email.</p>';
            $html = email_layout_html('Security', 'Reset your password', $inner);
            $text = 'Reset your password (expires in 1 hour): ' . $resetLink;
            sendAppEmail((string) $user['email'], $subject, $html, $text);
        }

        $message = 'If an account exists for that email, a reset link has been sent.';
    }
}

$mainClass = 'flex min-h-[calc(100vh-10rem)] items-center justify-center py-4';
require_once __DIR__ . '/includes/header.php';
?>
<section class="w-full max-w-md rounded-3xl border border-slate-200/80 bg-white/95 p-8 shadow-card backdrop-blur-sm sm:p-10">
    <h1 class="font-display text-2xl font-bold text-slate-900">Forgot password</h1>
    <p class="mt-2 text-sm text-slate-600">We will email you a reset link if an account exists.</p>
    <?php if ($error): ?>
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><?= esc($error) ?></div>
    <?php endif; ?>
    <?php if ($message): ?>
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900"><?= esc($message) ?></div>
    <?php endif; ?>
    <form method="post" class="mt-6 space-y-5">
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
            <input type="email" name="email" value="<?= esc($email) ?>" required autocomplete="email" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm">
        </div>
        <button type="submit" class="w-full rounded-2xl bg-slate-900 py-3.5 text-sm font-bold text-white shadow-soft transition hover:bg-slate-800">Send reset link</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-600">Back to <a class="font-semibold text-village-700 hover:underline" href="<?= esc(url('login.php')) ?>">Login</a></p>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
