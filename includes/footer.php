<?php
require_once __DIR__ . '/functions.php';
$footerLang = $appLang ?? lang();
$footerNavQ = http_build_query(['lang' => $footerLang]);
$footerAttrHref = isUserLoggedIn()
    ? esc(url('attractions.php')) . '?' . esc($footerNavQ)
    : esc(login_url_with_next('attractions.php?' . $footerNavQ));
$footerTripHref = isUserLoggedIn()
    ? esc(url('trip.php')) . '?' . esc($footerNavQ)
    : esc(login_url_with_next('trip.php?' . $footerNavQ));
?>
    </main>
    <footer class="relative mt-16 border-t border-slate-200/80 bg-slate-950 text-slate-400">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-teal-400/50 to-transparent"></div>
        <div class="max-w-6xl mx-auto px-4 py-12 sm:px-6">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <p class="font-display text-lg font-bold text-white">Village Traveler</p>
                    <p class="mt-3 max-w-md text-sm leading-relaxed text-slate-400">
                        <?= esc(t([
                            'en' => 'Discover nearby attractions, plan day trips, and explore Sri Lanka with bilingual content and maps.',
                            'si' => 'ආසන්න ස්ථාන, දින චාරිකා සැලසුම්, සහ ද්වි භාෂා අන්තර්ගතය සහ සිතියම් සමඟ ශ්‍රී ලංකාව ගවේෂණය කරන්න.',
                        ], $footerLang)) ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(t(['en' => 'Explore', 'si' => 'ගවේෂණය'], $footerLang)) ?></p>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= esc(url('index.php')) ?>?lang=<?= esc($footerLang) ?>"><?= esc(t(['en' => 'Home', 'si' => 'මුල් පිටුව'], $footerLang)) ?></a></li>
                        <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= $footerAttrHref ?>"><?= esc(t(['en' => 'Attractions', 'si' => 'ස්ථාන'], $footerLang)) ?></a></li>
                        <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= $footerTripHref ?>"><?= esc(t(['en' => 'One-Day Trip', 'si' => 'එක් දින චාරිකාව'], $footerLang)) ?></a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500"><?= esc(t(['en' => 'Account', 'si' => 'ගිණුම'], $footerLang)) ?></p>
                    <ul class="mt-4 space-y-2 text-sm">
                        <?php if (isUserLoggedIn()): ?>
                            <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= esc(url('my-trips.php')) ?>"><?= esc(t(['en' => 'My Trips', 'si' => 'මගේ චාරිකා'], $footerLang)) ?></a></li>
                            <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= esc(url('logout.php')) ?>"><?= esc(t(['en' => 'Logout', 'si' => 'ඉවත්වන්න'], $footerLang)) ?></a></li>
                        <?php else: ?>
                            <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= esc(url('login.php')) ?>"><?= esc(t(['en' => 'Login', 'si' => 'ඇතුල් වන්න'], $footerLang)) ?></a></li>
                            <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= esc(url('register.php')) ?>"><?= esc(t(['en' => 'Register', 'si' => 'ලියාපදිංචි'], $footerLang)) ?></a></li>
                        <?php endif; ?>
                        <?php if (show_public_admin_nav()): ?>
                            <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= esc(url('admin/login.php')) ?>"><?= esc(t(['en' => 'Admin', 'si' => 'පරිපාලක'], $footerLang)) ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-8 text-xs sm:flex-row">
                <p><?= esc(t([
                    'en' => 'Village Traveler · BIT SRS simulation · Wathugedara, Sri Lanka',
                    'si' => 'Village Traveler · BIT SRS සිමියුලේශන් · වතුගෙදර, ශ්‍රී ලංකාව',
                ], $footerLang)) ?></p>
                <p class="text-slate-500"><?= esc(date('Y')) ?> Village Traveler</p>
            </div>
        </div>
    </footer>

    <script src="<?= esc(url('assets/js/app.js')) ?>"></script>
</body>
</html>
