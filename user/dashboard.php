<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$page = 'dashboard';
$pageTitle = 'Min sida';
$me = current_user();

$stats = db()->prepare(
    "SELECT
        (SELECT COUNT(*) FROM sends WHERE user_id = ?) AS total_sends,
        (SELECT COUNT(*) FROM sends WHERE user_id = ?
            AND send_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) AS sends_30d,
        (SELECT MAX(r.grade) FROM sends s JOIN routes r ON r.id = s.route_id
            WHERE s.user_id = ?) AS top_grade,
        (SELECT COUNT(*) FROM registrations WHERE user_id = ? AND status = 'registered') AS my_events"
);
$tmp = $me['id'];
$stats->execute([$tmp, $tmp, $tmp, $tmp]);
$s = $stats->fetch();

// $recentSends = []; // gammal variant, ta bort sen

$recent = db()->prepare(
    "SELECT s.send_date, s.attempts, s.note, r.name, r.grade, r.color
     FROM sends s
     JOIN routes r ON r.id = s.route_id
     WHERE s.user_id = ?
     ORDER BY s.send_date DESC, s.id DESC
     LIMIT 5"
);
$recent->execute([$me['id']]);
$recent = $recent->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h1>Hej <?= e($me['username']) ?>!</h1>
<p class="muted">Här är din översikt</p>

<div class="grid mt-3">
    <article class="card stat">
        <div class="big"><?= (int)$s['total_sends'] ?></div>
        <div class="label">Sends totalt</div>
    </article>
    <article class="card stat">
        <div class="big"><?= (int)$s['sends_30d'] ?></div>
        <div class="label">Sends senaste 30 dagarna</div>
    </article>
    <article class="card stat">
        <div class="big"><?= e($s['top_grade'] ?: '-') ?></div>
        <div class="label">Hårdaste grad</div>
    </article>
    <article class="card stat">
        <div class="big"><?= (int)$s['my_events'] ?></div>
        <div class="label">Anmälda event</div>
    </article>
</div>

<div class="flex mt-3" style="flex-wrap:wrap;">
    <a href="<?= e(APP_URL) ?>/user/sends.php"   class="btn">Logga ny send</a>
    <a href="<?= e(APP_URL) ?>/user/routes.php"  class="btn btn-ghost">Bläddra leder</a>
    <a href="<?= e(APP_URL) ?>/user/stats.php"   class="btn btn-ghost">Min statistik</a>
    <a href="<?= e(APP_URL) ?>/user/bookings.php" class="btn btn-ghost">Mina bokningar</a>
</div>

<h2 class="mt-3">Dina senaste sends</h2>
<?php if (!$recent): ?>
    <p class="muted">Du har inte loggat någon send ännu. <a href="<?= e(APP_URL) ?>/user/sends.php">Logga din första!</a></p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Datum</th><th>Led</th><th>Grad</th><th>Försök</th><th>Notering</th></tr></thead>
        <tbody>
            <?php foreach ($recent as $r): ?>
            <tr>
                <td><?= e($r['send_date']) ?></td>
                <td><?= e($r['name']) ?> <span class="muted">(<?= e($r['color']) ?>)</span></td>
                <td><strong><?= e($r['grade']) ?></strong></td>
                <td><?= (int)$r['attempts'] ?></td>
                <td><?= e($r['note'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
