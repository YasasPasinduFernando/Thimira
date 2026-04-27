<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

requireUser();

$appLang = lang();
$title = t(['en' => 'Create Trip Plan', 'si' => 'චාරිකා සැලසුම සාදන්න'], $appLang);
$currentUser = currentUser();
$error = '';
$planTitle = '';

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
            $insertPlan = db()->prepare('INSERT INTO trip_plans (user_id, title, status) VALUES (:user_id, :title, :status)');
            $insertPlan->execute([
                'user_id' => (int) $currentUser['id'],
                'title' => $planTitle,
                'status' => 'planned',
            ]);
            $tripPlanId = (int) db()->lastInsertId();

            $insertItem = db()->prepare('INSERT INTO trip_plan_items (trip_plan_id, attraction_id, visit_order) VALUES (:trip_plan_id, :attraction_id, :visit_order)');
            foreach ($selectedIds as $idx => $attractionId) {
                $insertItem->execute([
                    'trip_plan_id' => $tripPlanId,
                    'attraction_id' => $attractionId,
                    'visit_order' => $idx + 1,
                ]);
            }

            db()->commit();
            header('Location: ' . url('trip-plan.php') . '?id=' . $tripPlanId);
            exit;
        } catch (Throwable $e) {
            db()->rollBack();
            $error = t(['en' => 'Could not create trip plan. Please try again.', 'si' => 'චාරිකා සැලසුම සාදිය නොහැක. නැවත උත්සාහ කරන්න.'], $appLang);
        }
    }
}

$navBackHref = url('my-trips.php') . '?' . http_build_query(['lang' => $appLang]);
$navBackText = t(['en' => 'My trips', 'si' => 'මගේ චාරිකා'], $appLang);
require_once __DIR__ . '/includes/header.php';
?>
<section class="surface-card mx-auto max-w-3xl border border-slate-100 p-6 md:p-10">
    <h1 class="font-display text-2xl font-bold text-slate-900 md:text-3xl"><?= esc(t(['en' => 'Create your trip plan', 'si' => 'ඔබේ චාරිකා සැලසුම සාදන්න'], $appLang)) ?></h1>
    <p class="mt-2 text-sm text-slate-600"><?= esc(t([
        'en' => 'Pick a title and select places in the order you plan to visit.',
        'si' => 'මාතෘකාවක් තෝරා, ඔබ සංචාරය කිරීමට සැලසුම් කරන අනුපිළිවෙලට ස්ථාන තෝරන්න.',
    ], $appLang)) ?></p>
    <?php if ($error): ?>
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><?= esc($error) ?></div>
    <?php endif; ?>
    <form method="post" class="mt-8 space-y-6">
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700"><?= esc(t(['en' => 'Trip title', 'si' => 'චාරිකා මාතෘකාව'], $appLang)) ?></label>
            <input type="text" name="title" value="<?= esc($planTitle) ?>" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm" placeholder="<?= esc(t(['en' => 'My village weekend plan', 'si' => 'මගේ ගම්මාන සති අන්ත සැලසුම'], $appLang)) ?>">
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(t(['en' => 'Places to visit', 'si' => 'බලා ගැනීමට ස්ථාන'], $appLang)) ?></label>
            <div class="grid max-h-80 gap-2 overflow-auto rounded-2xl border border-slate-200 bg-slate-50/50 p-4 md:grid-cols-2">
                <?php foreach ($attractions as $place): ?>
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-transparent bg-white p-3 text-sm shadow-sm transition hover:border-village-200 hover:shadow-soft">
                        <input type="checkbox" name="attraction_ids[]" value="<?= (int) $place['id'] ?>" class="mt-0.5">
                        <span><span class="font-medium text-slate-900"><?= esc((string) $place['name_en']) ?></span> <span class="text-slate-500">(<?= esc((string) $place['category']) ?>)</span></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="inline-flex rounded-2xl bg-slate-900 px-6 py-3 text-sm font-bold text-white shadow-soft transition hover:bg-slate-800"><?= esc(t(['en' => 'Create trip plan', 'si' => 'චාරිකා සැලසුම සාදන්න'], $appLang)) ?></button>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
