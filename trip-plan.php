<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/email_layout.php';

requireUser();

$appLang = lang();
$title = t(['en' => 'Trip Plan Details', 'si' => 'චාරිකා සැලසුම'], $appLang);
$currentUser = currentUser();
$tripId = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['trip_id'] ?? 0);
$error = '';
$message = '';

if ($tripId <= 0) {
    http_response_code(400);
    echo esc(t(['en' => 'Invalid trip plan id.', 'si' => 'චාරිකා සැලසුම් හැඳුනුම් අංකය වලංගු නොවේ.'], $appLang));
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_visited'])) {
    $itemId = (int) ($_POST['item_id'] ?? 0);
    $visited = isset($_POST['is_visited']) ? 1 : 0;
    $visitedAt = $visited === 1 ? (new DateTimeImmutable())->format('Y-m-d H:i:s') : null;

    $updateItem = db()->prepare(
        'UPDATE trip_plan_items tpi
         INNER JOIN trip_plans tp ON tp.id = tpi.trip_plan_id
         SET tpi.is_visited = :is_visited, tpi.visited_at = :visited_at
         WHERE tpi.id = :item_id AND tp.user_id = :user_id'
    );
    $updateItem->execute([
        'is_visited' => $visited,
        'visited_at' => $visitedAt,
        'item_id' => $itemId,
        'user_id' => (int) $currentUser['id'],
    ]);

    $redirectQuery = $_GET;
    $redirectQuery['id'] = (string) $tripId;
    header('Location: ' . url('trip-plan.php') . '?' . http_build_query($redirectQuery));
    exit;
}

$itemsStmt = db()->prepare(
    'SELECT tpi.id, tpi.visit_order, tpi.is_visited, tpi.visited_at,
            a.id AS attraction_id, a.name_en, a.name_si, a.category,
            a.open_hours, a.entry_fee_lkr, a.latitude, a.longitude, a.short_en, a.short_si
     FROM trip_plan_items tpi
     INNER JOIN attractions a ON a.id = tpi.attraction_id
     WHERE tpi.trip_plan_id = :trip_id'
);
$itemsStmt->execute(['trip_id' => $tripId]);
$items = $itemsStmt->fetchAll();

$refLat = currentLat();
$refLng = currentLng();
foreach ($items as &$row) {
    $row['distance_km'] = distanceKm($refLat, $refLng, (float) $row['latitude'], (float) $row['longitude']);
}
unset($row);
usort($items, static fn(array $a, array $b): int => $a['distance_km'] <=> $b['distance_km']);

$totalItems = count($items);
$visitedCount = 0;
foreach ($items as $item) {
    if ((int) $item['is_visited'] === 1) {
        $visitedCount++;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_trip'])) {
    if ($totalItems === 0) {
        $error = t(['en' => 'This plan has no places.', 'si' => 'මෙම සැලසුමේ ස්ථාන නැත.'], $appLang);
    } elseif ($visitedCount !== $totalItems) {
        $error = t(['en' => 'Mark all places as visited before completing the trip.', 'si' => 'චාරිකාව සම්පූර්ණ කිරීමට පෙර සියලු ස්ථාන සංචාර කළ බව සලකුණු කරන්න.'], $appLang);
    } elseif ($plan['status'] === 'completed') {
        $message = t(['en' => 'Trip is already completed.', 'si' => 'චාරිකාව දැනටමත් සම්පූර්ණයි.'], $appLang);
    } else {
        $completedAt = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $completeStmt = db()->prepare('UPDATE trip_plans SET status = :status, completed_at = :completed_at WHERE id = :id AND user_id = :user_id');
        $completeStmt->execute([
            'status' => 'completed',
            'completed_at' => $completedAt,
            'id' => $tripId,
            'user_id' => (int) $currentUser['id'],
        ]);

        $userStmt = db()->prepare('SELECT email, username FROM users WHERE id = :id LIMIT 1');
        $userStmt->execute(['id' => (int) $currentUser['id']]);
        $userData = $userStmt->fetch();

        if ($userData && !empty($userData['email'])) {
            $rowsTable = '';
            $rowsText = [];
            foreach ($items as $item) {
                $visitedAtText = $item['visited_at'] ? (string) $item['visited_at'] : 'N/A';
                $rowsTable .= '<tr>'
                    . '<td style="padding:12px 10px;border-bottom:1px solid #e2e8f0;font-weight:600;color:#0f172a;">' . esc((string) $item['name_en']) . '</td>'
                    . '<td style="padding:12px 10px;border-bottom:1px solid #e2e8f0;color:#64748b;font-size:14px;">' . esc((string) $item['category']) . '</td>'
                    . '<td style="padding:12px 10px;border-bottom:1px solid #e2e8f0;color:#0f766e;font-size:13px;white-space:nowrap;">' . esc($visitedAtText) . '</td>'
                    . '</tr>';
                $rowsText[] = '- ' . (string) $item['name_en'] . ' (' . (string) $item['category'] . ') - Visited at: ' . $visitedAtText;
            }

            $subject = APP_NAME . ' - Trip Completed';
            $summaryBox = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 24px 0;border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;">'
                . '<tr><td style="padding:14px 16px;background:#f8fafc;"><strong style="color:#0f172a;">Trip</strong></td><td style="padding:14px 16px;background:#ffffff;">' . esc((string) $plan['title']) . '</td></tr>'
                . '<tr><td style="padding:14px 16px;background:#f8fafc;border-top:1px solid #e2e8f0;"><strong style="color:#0f172a;">Places</strong></td><td style="padding:14px 16px;background:#ffffff;border-top:1px solid #e2e8f0;">' . (int) $totalItems . '</td></tr>'
                . '<tr><td style="padding:14px 16px;background:#f8fafc;border-top:1px solid #e2e8f0;"><strong style="color:#0f172a;">Completed</strong></td><td style="padding:14px 16px;background:#ffffff;border-top:1px solid #e2e8f0;">' . esc($completedAt) . '</td></tr>'
                . '</table>';

            $placesTable = '<p style="margin:0 0 10px 0;font-weight:700;color:#0f172a;">Places you visited</p>'
                . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;margin:0 0 24px 0;">'
                . '<thead><tr><th align="left" style="padding:10px;font-size:11px;text-transform:uppercase;letter-spacing:0.06em;color:#64748b;background:#f1f5f9;">Place</th>'
                . '<th align="left" style="padding:10px;font-size:11px;text-transform:uppercase;letter-spacing:0.06em;color:#64748b;background:#f1f5f9;">Category</th>'
                . '<th align="left" style="padding:10px;font-size:11px;text-transform:uppercase;letter-spacing:0.06em;color:#64748b;background:#f1f5f9;">Visited at</th></tr></thead>'
                . '<tbody>' . $rowsTable . '</tbody></table>';

            $inner = '<p style="margin:0 0 16px 0;">Hello <strong>' . esc((string) $userData['username']) . '</strong>,</p>'
                . '<p style="margin:0 0 20px 0;">Thank you — your trip is marked <strong style="color:#0f766e;">complete</strong>. Here is a summary you can keep for your records.</p>'
                . $summaryBox
                . $placesTable
                . '<p style="margin:0 0 8px 0;font-size:16px;font-weight:700;color:#0f172a;">Thank you again!</p>'
                . '<p style="margin:0;color:#64748b;font-size:14px;">Visit <strong>' . esc(email_brand_display_name()) . '</strong> again for your next adventure.</p>';

            $htmlBody = email_layout_html('Trips', 'Your trip is complete', $inner);
            $textBody = "Thank you for visiting " . APP_NAME . "!\n"
                . "Trip completed successfully.\n"
                . "Trip: " . (string) $plan['title'] . "\n"
                . "Total Places: " . $totalItems . "\n"
                . "Completed At: " . $completedAt . "\n"
                . "Visited Places:\n" . implode("\n", $rowsText) . "\n"
                . "Thank you again! Visit Village Traveler again.";
            sendAppEmail((string) $userData['email'], $subject, $htmlBody, $textBody);
        }

        $message = t(['en' => 'Trip completed successfully. Email sent with trip details.', 'si' => 'චාරිකාව සාර්ථකව සම්පූර්ණ විය. විස්තර සමඟ විද්‍යුත් තැපෑල යවන ලදී.'], $appLang);
        $plan['status'] = 'completed';
        $plan['completed_at'] = $completedAt;
    }
}

$navBackHref = url('my-trips.php') . '?' . http_build_query([
    'lang' => $appLang,
    'lat' => currentLat(),
    'lng' => currentLng(),
]);
$navBackText = t(['en' => 'My trips', 'si' => 'මගේ චාරිකා'], $appLang);
require_once __DIR__ . '/includes/header.php';
?>
<section class="surface-card border border-slate-100 p-6 md:p-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-6">
        <div>
            <h1 class="font-display text-2xl font-bold text-slate-900 md:text-3xl"><?= esc((string) $plan['title']) ?></h1>
            <p class="mt-1 text-sm text-slate-600"><?= esc(t([
                'en' => 'Mark each stop, then complete to receive your trip summary email. Stops are sorted by shortest distance from your reference location.',
                'si' => 'සෑම නැවතුමක් සලකුණු කර, සාරාංශ විද්‍යුත් තැපෑල ලබා ගැනීමට සම්පූර්ණ කරන්න. නැවතුම් ඔබේ යොමු ස්ථානයෙන් අඩුම දුර සිට පෙළගස්ව ඇත.',
            ], $appLang)) ?></p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full px-3 py-1.5 text-xs font-bold <?= $plan['status'] === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                <?= esc($plan['status'] === 'completed'
                    ? t(['en' => 'COMPLETED', 'si' => 'සම්පූර්ණ'], $appLang)
                    : t(['en' => 'PLANNED', 'si' => 'සැලසුම්'], $appLang)) ?>
            </span>
            <?php if ((string) $plan['status'] === 'planned'): ?>
                <a href="<?= esc(url('trip-edit.php')) ?>?<?= esc(http_build_query(['id' => $tripId, 'lang' => $appLang])) ?>" class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-800 ring-1 ring-slate-200 transition hover:bg-slate-200"><?= esc(t(['en' => 'Edit plan', 'si' => 'සැලසුම සංස්කරණය'], $appLang)) ?></a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><?= esc($error) ?></div>
    <?php endif; ?>
    <?php if ($message): ?>
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900"><?= esc($message) ?></div>
    <?php endif; ?>

    <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-village-50 px-4 py-2 text-sm font-medium text-village-900 ring-1 ring-village-200">
        <span class="h-2 w-2 rounded-full bg-village-500"></span>
        <?= esc(t(['en' => 'Visited', 'si' => 'සංචාර කළ'], $appLang)) ?> <?= $visitedCount ?>/<?= $totalItems ?> <?= esc(t(['en' => 'places', 'si' => 'ස්ථාන'], $appLang)) ?>
    </div>

    <?php if ($totalItems > 0): ?>
        <?php
            $lastStop = $items[$totalItems - 1];
            $fullTripUrl = 'https://www.google.com/maps/dir/?api=1&travelmode=driving'
                . '&origin=' . rawurlencode((string) $refLat . ',' . (string) $refLng)
                . '&destination=' . rawurlencode((string) $lastStop['latitude'] . ',' . (string) $lastStop['longitude']);
            if ($totalItems > 1) {
                $waypointChunks = [];
                for ($wi = 0; $wi < $totalItems - 1; $wi++) {
                    $waypointChunks[] = (string) $items[$wi]['latitude'] . ',' . (string) $items[$wi]['longitude'];
                }
                $fullTripUrl .= '&waypoints=' . rawurlencode(implode('|', $waypointChunks));
            }
        ?>
        <div class="mb-6">
            <a href="<?= esc($fullTripUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl border-2 border-village-500 bg-white px-5 py-3 text-sm font-bold text-village-800 shadow-soft transition hover:bg-village-50">
                <?= esc(t(['en' => 'Navigate full trip (all stops in order)', 'si' => 'සම්පූර්ණ චාරිකාව (සියලු නැවතුම් අනුපිළිවෙලට)'], $appLang)) ?>
                <span aria-hidden="true">↗</span>
            </a>
            <p class="mt-2 max-w-2xl text-xs text-slate-500"><?= esc(t([
                'en' => 'Opens Google Maps from your reference location, through each stop in the list order, to the last place.',
                'si' => 'ඔබේ යොමු ස්ථානයෙන් පටන්ගෙන ලැයිස්තුවේ අනුපිළිවෙලට සෑම නැවතුමක් ඔස්සේ අවසාන ස්ථානය දක්වා Google Maps යොමු කරයි.',
            ], $appLang)) ?></p>
        </div>
    <?php endif; ?>

    <div class="space-y-3">
        <?php foreach ($items as $stopIdx => $item): ?>
            <?php
                $displayNum = (int) $stopIdx + 1;
                $name = localized_text((string) $item['name_en'], (string) $item['name_si'], $appLang);
                $short = localized_text((string) $item['short_en'], (string) $item['short_si'], $appLang);
                if ($stopIdx === 0) {
                    $legOrigLat = $refLat;
                    $legOrigLng = $refLng;
                    $legHint = t(['en' => 'From reference / home', 'si' => 'යොමුව / නිවස සිට'], $appLang);
                } else {
                    $prevStop = $items[$stopIdx - 1];
                    $legOrigLat = (float) $prevStop['latitude'];
                    $legOrigLng = (float) $prevStop['longitude'];
                    $legHint = t(['en' => 'From previous stop', 'si' => 'පෙර නැවතුම සිට'], $appLang);
                }
                $navigateUrl = 'https://www.google.com/maps/dir/?api=1&travelmode=driving'
                    . '&origin=' . rawurlencode((string) $legOrigLat . ',' . (string) $legOrigLng)
                    . '&destination=' . rawurlencode((string) $item['latitude'] . ',' . (string) $item['longitude']);
                $detailHref = url('attraction.php') . '?' . http_build_query([
                    'id' => (int) $item['attraction_id'],
                    'lang' => $appLang,
                    'lat' => $refLat,
                    'lng' => $refLng,
                ]);
            ?>
            <form method="post" class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 transition hover:border-village-200 hover:bg-white hover:shadow-soft md:p-5">
                <input type="hidden" name="trip_id" value="<?= (int) $tripId ?>">
                <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1 space-y-2">
                        <p class="font-display text-lg font-bold text-slate-900"><?= $displayNum ?>. <?= esc($name) ?></p>
                        <p class="text-sm font-medium text-village-700"><?= esc((string) $item['category']) ?></p>
                        <?php if ($short !== ''): ?>
                            <p class="text-sm leading-relaxed text-slate-600"><?= esc($short) ?></p>
                        <?php endif; ?>
                        <div class="flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-600 sm:text-sm">
                            <span><span class="font-semibold text-slate-700"><?= esc(t(['en' => 'Open', 'si' => 'විවෘත'], $appLang)) ?>:</span> <?= esc((string) $item['open_hours']) ?></span>
                            <span><span class="font-semibold text-slate-700"><?= esc(t(['en' => 'Entry', 'si' => 'ඇතුල්වීම'], $appLang)) ?>:</span> <?= esc(money((float) $item['entry_fee_lkr'])) ?></span>
                        </div>
                        <a href="<?= esc($detailHref) ?>" class="inline-flex text-sm font-semibold text-village-700 hover:text-village-900 hover:underline"><?= esc(t(['en' => 'View full details', 'si' => 'සම්පූර්ණ විස්තර'], $appLang)) ?> →</a>
                    </div>
                    <div class="flex flex-shrink-0 flex-col gap-3 sm:flex-row sm:items-center lg:flex-col lg:items-stretch">
                        <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" name="is_visited" <?= (int) $item['is_visited'] === 1 ? 'checked' : '' ?> class="rounded border-slate-300">
                            <?= esc(t(['en' => 'Visited', 'si' => 'සංචාර කළ'], $appLang)) ?>
                        </label>
                        <div class="flex flex-col gap-1">
                            <div class="flex flex-wrap gap-2">
                                <button type="submit" name="mark_visited" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800"><?= esc(t(['en' => 'Update', 'si' => 'යාවත්කාලීන'], $appLang)) ?></button>
                                <a href="<?= esc($navigateUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl bg-village-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-village-700"><?= esc(t(['en' => 'Navigate leg', 'si' => 'මෙම කොටස'], $appLang)) ?></a>
                            </div>
                            <span class="text-[11px] font-medium text-slate-500"><?= esc($legHint) ?></span>
                        </div>
                    </div>
                </div>
            </form>
        <?php endforeach; ?>
    </div>

    <form method="post" class="mt-8">
        <input type="hidden" name="trip_id" value="<?= (int) $tripId ?>">
        <button type="submit" name="complete_trip" class="inline-flex rounded-2xl bg-gradient-to-r from-village-600 to-village-700 px-6 py-3.5 text-sm font-bold text-white shadow-soft transition enabled:hover:from-village-700 enabled:hover:to-village-800 disabled:cursor-not-allowed disabled:opacity-50" <?= $plan['status'] === 'completed' ? 'disabled' : '' ?>>
            <?= esc($plan['status'] === 'completed'
                ? t(['en' => 'Trip completed', 'si' => 'චාරිකාව සම්පූර්ණයි'], $appLang)
                : t(['en' => 'Complete trip & send email', 'si' => 'චාරිකාව සම්පූර්ණ කර විද්‍යුත් තැපෑල යවන්න'], $appLang)) ?>
        </button>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
