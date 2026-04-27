<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

requireUser();

$appLang = lang();
$title = t(['en' => 'My Trip Plans', 'si' => 'මගේ චාරිකා සැලසුම්'], $appLang);
$currentUser = currentUser();

$stmt = db()->prepare(
    'SELECT tp.id, tp.title, tp.status, tp.created_at, tp.completed_at, COUNT(tpi.id) AS total_places,
            SUM(CASE WHEN tpi.is_visited = 1 THEN 1 ELSE 0 END) AS visited_places
     FROM trip_plans tp
     LEFT JOIN trip_plan_items tpi ON tpi.trip_plan_id = tp.id
     WHERE tp.user_id = :user_id
     GROUP BY tp.id, tp.title, tp.status, tp.created_at, tp.completed_at
     ORDER BY tp.created_at DESC'
);
$stmt->execute(['user_id' => (int) $currentUser['id']]);
$plans = $stmt->fetchAll();

$flash = getFlash();

$navBackHref = url('index.php') . '?' . http_build_query(['lang' => $appLang]);
$navBackText = t(['en' => 'Home', 'si' => 'මුල් පිටුව'], $appLang);
require_once __DIR__ . '/includes/header.php';
?>
<section class="surface-card border border-slate-100 p-6 md:p-8">
    <div class="mb-8 flex flex-col gap-4 border-b border-slate-100 pb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold text-slate-900 md:text-3xl"><?= esc(t(['en' => 'My trip plans', 'si' => 'මගේ චාරිකා සැලසුම්'], $appLang)) ?></h1>
            <p class="mt-1 text-sm text-slate-600"><?= esc(t([
                'en' => 'Track visits and complete trips for summary emails.',
                'si' => 'සංචාර සටහන් කර සම්පූර්ණ කර සාරාංශ විද්‍යුත් තැපැල් ලබා ගන්න.',
            ], $appLang)) ?></p>
        </div>
        <a href="<?= esc(url('trip-create.php')) ?>" class="inline-flex justify-center rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 px-5 py-3 text-sm font-bold text-white shadow-soft transition hover:from-slate-800 hover:to-slate-900"><?= esc(t(['en' => 'New plan', 'si' => 'නව සැලසුම'], $appLang)) ?></a>
    </div>

    <?php if ($flash): ?>
        <div class="mb-6 rounded-2xl border p-4 text-sm <?= $flash['type'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-red-200 bg-red-50 text-red-800' ?>">
            <?= esc((string) $flash['message']) ?>
        </div>
    <?php endif; ?>

    <?php if (!$plans): ?>
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-10 text-center text-slate-600"><?= esc(t(['en' => 'You have no trip plans yet.', 'si' => 'ඔබට තවම චාරිකා සැලසුම් නැත.'], $appLang)) ?></div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($plans as $plan): ?>
                <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft transition hover:border-village-200 hover:shadow-card">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="font-display text-lg font-bold text-slate-900"><?= esc((string) $plan['title']) ?></h2>
                        <span class="rounded-full px-3 py-1 text-xs font-bold <?= $plan['status'] === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                            <?= esc($plan['status'] === 'completed'
                                ? t(['en' => 'COMPLETED', 'si' => 'සම්පූර්ණ'], $appLang)
                                : t(['en' => 'PLANNED', 'si' => 'සැලසුම්'], $appLang)) ?>
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">
                        <?= esc(t(['en' => 'Progress:', 'si' => 'ප්‍රගතිය:'], $appLang)) ?> <span class="font-semibold text-slate-900"><?= (int) $plan['visited_places'] ?>/<?= (int) $plan['total_places'] ?></span> <?= esc(t(['en' => 'places visited', 'si' => 'ස්ථාන සංචාර'], $appLang)) ?>
                    </p>
                    <div class="mt-4 flex flex-wrap items-center gap-4">
                        <a class="inline-flex items-center gap-1 text-sm font-semibold text-village-700 hover:gap-2 hover:text-village-900" href="<?= esc(url('trip-plan.php')) ?>?<?= esc(http_build_query([
                            'id' => (int) $plan['id'],
                            'lang' => $appLang,
                            'lat' => currentLat(),
                            'lng' => currentLng(),
                        ])) ?>"><?= esc(t(['en' => 'Open plan', 'si' => 'සැලසුම අරින්න'], $appLang)) ?> <span aria-hidden="true">→</span></a>
                        <?php if ((string) $plan['status'] === 'planned'): ?>
                            <a class="inline-flex items-center gap-1 text-sm font-semibold text-slate-700 hover:text-slate-900" href="<?= esc(url('trip-edit.php')) ?>?<?= esc(http_build_query([
                                'id' => (int) $plan['id'],
                                'lang' => $appLang,
                            ])) ?>"><?= esc(t(['en' => 'Edit', 'si' => 'සංස්කරණය'], $appLang)) ?></a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
