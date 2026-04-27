<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

requireUser();

$appLang = lang();
$title = t(['en' => 'Edit Trip Plan', 'si' => 'චාරිකා සැලසුම සංස්කරණය'], $appLang);
$currentUser = currentUser();
$error = '';
$tripId = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['trip_id'] ?? 0);

if ($tripId <= 0) {
    http_response_code(400);
    echo esc(t(['en' => 'Invalid trip plan.', 'si' => 'චාරිකා සැලසුම වලංගු නොවේ.'], $appLang));
    exit;
}

$planStmt = db()->prepare('SELECT * FROM trip_plans WHERE id = :id AND user_id = :user_id LIMIT 1');
$planStmt->execute([
    'id' => $tripId,
    'user_id' => (int) $currentUser['id'],
]);
$plan = $planStmt->fetch();

if (!$plan) {
    http_response_code(404);
    echo esc(t(['en' => 'Trip plan not found.', 'si' => 'චාරිකා සැලසුම හමු නොවීය.'], $appLang));
    exit;
}

if ((string) $plan['status'] !== 'planned') {
    setFlash('error', t(['en' => 'Completed trips cannot be edited. Create a new plan instead.', 'si' => 'සම්පූර්ණ චාරිකා සංස්කරණය කළ නොහැක. නව සැලසුමක් සාදන්න.'], $appLang));
    header('Location: ' . url('my-trips.php') . '?' . http_build_query(['lang' => $appLang]));
    exit;
}

$itemsStmt = db()->prepare('SELECT attraction_id FROM trip_plan_items WHERE trip_plan_id = :id ORDER BY visit_order ASC');
$itemsStmt->execute(['id' => $tripId]);
$existingIds = array_map(static fn(array $r): int => (int) $r['attraction_id'], $itemsStmt->fetchAll());
$existingSet = array_fill_keys($existingIds, true);

$planTitle = (string) $plan['title'];

$stmt = db()->query('SELECT id, name_en, category FROM attractions WHERE is_active = 1 ORDER BY name_en ASC');
$attractions = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $planTitle = trim((string) ($_POST['title'] ?? ''));
    $selected = $_POST['attraction_ids'] ?? [];
    $selectedIds = array_values(array_filter(array_map('intval', (array) $selected), static fn(int $id): bool => $id > 0));

    if ($planTitle === '') {
        $error = t(['en' => 'Trip title is required.', 'si' => 'චාරිකා මාතෘකාව අවශ්‍යයි.'], $appLang);
    } elseif (count($selectedIds) === 0) {
        $error = t(['en' => 'Select at least one attraction.', 'si' => 'අවම වශයෙන් ස්ථානයක් තෝරන්න.'], $appLang);
    } else {
        db()->beginTransaction();
        try {
            $updatePlan = db()->prepare('UPDATE trip_plans SET title = :title WHERE id = :id AND user_id = :user_id AND status = :status');
            $updatePlan->execute([
                'title' => $planTitle,
                'id' => $tripId,
                'user_id' => (int) $currentUser['id'],
                'status' => 'planned',
            ]);

            db()->prepare('DELETE FROM trip_plan_items WHERE trip_plan_id = :id')->execute(['id' => $tripId]);

            $insertItem = db()->prepare('INSERT INTO trip_plan_items (trip_plan_id, attraction_id, visit_order) VALUES (:trip_plan_id, :attraction_id, :visit_order)');
            foreach ($selectedIds as $idx => $attractionId) {
                $insertItem->execute([
                    'trip_plan_id' => $tripId,
                    'attraction_id' => $attractionId,
                    'visit_order' => $idx + 1,
                ]);
            }

            db()->commit();
            header('Location: ' . url('trip-plan.php') . '?' . http_build_query([
                'id' => $tripId,
                'lang' => $appLang,
                'lat' => currentLat(),
                'lng' => currentLng(),
            ]));
            exit;
        } catch (Throwable $e) {
            db()->rollBack();
            $error = t(['en' => 'Could not update trip plan. Please try again.', 'si' => 'චාරිකා සැලසුම යාවත්කාලීන කළ නොහැක. නැවත උත්සාහ කරන්න.'], $appLang);
        }
    }

    if ($error !== '') {
        $postedIds = array_values(array_filter(array_map('intval', (array) ($_POST['attraction_ids'] ?? [])), static fn(int $id): bool => $id > 0));
        $existingSet = $postedIds !== [] ? array_fill_keys($postedIds, true) : [];
    }
}

$navBackHref = url('my-trips.php') . '?' . http_build_query(['lang' => $appLang]);
$navBackText = t(['en' => 'My trips', 'si' => 'මගේ චාරිකා'], $appLang);
require_once __DIR__ . '/includes/header.php';
?>
<section class="surface-card mx-auto max-w-3xl border border-slate-100 p-6 md:p-10">
    <h1 class="font-display text-2xl font-bold text-slate-900 md:text-3xl"><?= esc(t(['en' => 'Edit your trip plan', 'si' => 'ඔබේ චාරිකා සැලසුම සංස්කරණය කරන්න'], $appLang)) ?></h1>
    <p class="mt-2 text-sm text-slate-600"><?= esc(t([
        'en' => 'Change the title or places. Visited progress will reset for this plan after you save.',
        'si' => 'මාතෘකාව හෝ ස්ථාන වෙනස් කරන්න. සුරැකීමෙන් පසු සංචාර ප්‍රගතිය නැවත සැකසේ.',
    ], $appLang)) ?></p>
    <?php if ($error): ?>
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><?= esc($error) ?></div>
    <?php endif; ?>
    <form method="post" class="mt-8 space-y-6">
        <input type="hidden" name="trip_id" value="<?= (int) $tripId ?>">
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700"><?= esc(t(['en' => 'Trip title', 'si' => 'චාරිකා මාතෘකාව'], $appLang)) ?></label>
            <input type="text" name="title" value="<?= esc($planTitle) ?>" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm" placeholder="<?= esc(t(['en' => 'My village weekend plan', 'si' => 'මගේ ගම්මාන සති අන්ත සැලසුම'], $appLang)) ?>">
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(t(['en' => 'Places to visit', 'si' => 'බලා ගැනීමට ස්ථාන'], $appLang)) ?></label>
            <div class="grid max-h-80 gap-2 overflow-auto rounded-2xl border border-slate-200 bg-slate-50/50 p-4 md:grid-cols-2">
                <?php foreach ($attractions as $place): ?>
                    <?php $pid = (int) $place['id']; ?>
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-transparent bg-white p-3 text-sm shadow-sm transition hover:border-village-200 hover:shadow-soft">
                        <input type="checkbox" name="attraction_ids[]" value="<?= $pid ?>" class="mt-0.5" <?= isset($existingSet[$pid]) ? 'checked' : '' ?>>
                        <span><span class="font-medium text-slate-900"><?= esc((string) $place['name_en']) ?></span> <span class="text-slate-500">(<?= esc((string) $place['category']) ?>)</span></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="inline-flex rounded-2xl bg-slate-900 px-6 py-3 text-sm font-bold text-white shadow-soft transition hover:bg-slate-800"><?= esc(t(['en' => 'Save changes', 'si' => 'වෙනස්කම් සුරකින්න'], $appLang)) ?></button>
            <a href="<?= esc(url('trip-plan.php')) ?>?<?= esc(http_build_query([
                'id' => $tripId,
                'lang' => $appLang,
                'lat' => currentLat(),
                'lng' => currentLng(),
            ])) ?>" class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-800 transition hover:border-village-300"><?= esc(t(['en' => 'Cancel', 'si' => 'අවලංගු'], $appLang)) ?></a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
