<?php
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
                        Discover nearby attractions, plan day trips, and explore Sri Lanka with bilingual content and maps.
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Explore</p>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= esc(url('index.php')) ?>">Home</a></li>
                        <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= $footerAttrHref ?>">Attractions</a></li>
                        <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= $footerTripHref ?>">One-Day Trip</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Account</p>
                    <ul class="mt-4 space-y-2 text-sm">
                        <?php if (isUserLoggedIn()): ?>
                            <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= esc(url('my-trips.php')) ?>">My Trips</a></li>
                            <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= esc(url('logout.php')) ?>">Logout</a></li>
                        <?php else: ?>
                            <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= esc(url('login.php')) ?>">Login</a></li>
                            <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= esc(url('register.php')) ?>">Register</a></li>
                        <?php endif; ?>
                        <li><a class="text-slate-300 transition hover:text-teal-400" href="<?= esc(url('admin/login.php')) ?>">Admin</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-8 text-xs sm:flex-row">
                <p>Village Traveler · BIT SRS simulation · Wathugedara, Sri Lanka</p>
                <p class="text-slate-500"><?= esc(date('Y')) ?> Village Traveler</p>
            </div>
        </div>
    </footer>

    <script src="<?= esc(url('assets/js/app.js')) ?>"></script>
</body>
</html>
