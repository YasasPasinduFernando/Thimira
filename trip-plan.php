<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/email_layout.php';

requireUser();

$title = 'Trip Plan Details';
$currentUser = currentUser();
$tripId = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['trip_id'] ?? 0);
$error = '';
$message = '';

if ($tripId <= 0) {
    http_response_code(400);
    echo 'Invalid trip plan id.';
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
    echo 'Trip plan not found.';
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

    header('Location: ' . url('trip-plan.php') . '?id=' . $tripId);
    exit;
}

$itemsStmt = db()->prepare(
    'SELECT tpi.id, tpi.visit_order, tpi.is_visited, tpi.visited_at, a.name_en, a.category
     FROM trip_plan_items tpi
     INNER JOIN attractions a ON a.id = tpi.attraction_id
     WHERE tpi.trip_plan_id = :trip_id
     ORDER BY tpi.visit_order ASC'
);
$itemsStmt->execute(['trip_id' => $tripId]);
$items = $itemsStmt->fetchAll();

$totalItems = count($items);
$visitedCount = 0;
foreach ($items as $item) {
    if ((int) $item['is_visited'] === 1) {
        $visitedCount++;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_trip'])) {
    if ($totalItems === 0) {
        $error = 'This plan has no places.';
    } elseif ($visitedCount !== $totalItems) {
        $error = 'Mark all places as visited before completing the trip.';
    } elseif ($plan['status'] === 'completed') {
        $message = 'Trip is already completed.';
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

        $message = 'Trip completed successfully. Email sent with trip details.';
        $plan['status'] = 'completed';
        $plan['completed_at'] = $completedAt;
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="surface-card border border-slate-100 p-6 md:p-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-6">
        <div>
            <h1 class="font-display text-2xl font-bold text-slate-900 md:text-3xl"><?= esc((string) $plan['title']) ?></h1>
            <p class="mt-1 text-sm text-slate-600">Mark each stop, then complete to receive your trip summary email.</p>
        </div>
        <span class="rounded-full px-3 py-1.5 text-xs font-bold <?= $plan['status'] === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
            <?= esc(strtoupper((string) $plan['status'])) ?>
        </span>
    </div>

    <?php if ($error): ?>
        <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><?= esc($error) ?></div>
    <?php endif; ?>
    <?php if ($message): ?>
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900"><?= esc($message) ?></div>
    <?php endif; ?>

    <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-village-50 px-4 py-2 text-sm font-medium text-village-900 ring-1 ring-village-200">
        <span class="h-2 w-2 rounded-full bg-village-500"></span>
        Visited <?= $visitedCount ?>/<?= $totalItems ?> places
    </div>

    <div class="space-y-3">
        <?php foreach ($items as $item): ?>
            <form method="post" class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-100 bg-slate-50/50 p-4 transition hover:border-village-200 hover:bg-white hover:shadow-soft">
                <input type="hidden" name="trip_id" value="<?= (int) $tripId ?>">
                <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                <div>
                    <p class="font-display font-bold text-slate-900"><?= (int) $item['visit_order'] ?>. <?= esc((string) $item['name_en']) ?></p>
                    <p class="text-sm text-village-700"><?= esc((string) $item['category']) ?></p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="checkbox" name="is_visited" <?= (int) $item['is_visited'] === 1 ? 'checked' : '' ?> class="rounded border-slate-300">
                        Visited
                    </label>
                    <button type="submit" name="mark_visited" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Update</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>

    <form method="post" class="mt-8">
        <input type="hidden" name="trip_id" value="<?= (int) $tripId ?>">
        <button type="submit" name="complete_trip" class="inline-flex rounded-2xl bg-gradient-to-r from-village-600 to-village-700 px-6 py-3.5 text-sm font-bold text-white shadow-soft transition enabled:hover:from-village-700 enabled:hover:to-village-800 disabled:cursor-not-allowed disabled:opacity-50" <?= $plan['status'] === 'completed' ? 'disabled' : '' ?>>
            <?= $plan['status'] === 'completed' ? 'Trip completed' : 'Complete trip & send email' ?>
        </button>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
