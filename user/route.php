<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$page = 'routes';
$pageTitle = 'Ledinfo';
$me = current_user();
$errors = [];

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    flash('error', 'Ingen led vald.');
    redirect('/user/routes.php');
}

$stmt = db()->prepare(
    "SELECT r.*, z.name AS zone_name
     FROM routes r LEFT JOIN zones z ON z.id = r.zone_id
     WHERE r.id = ? AND r.active = 1"
);
$stmt->execute([$id]);
$route = $stmt->fetch();
if (!$route) {
    flash('error', 'Leden finns inte.');
    redirect('/user/routes.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $body = clean_str($_POST['body'] ?? '', 1000);
    if (strlen($body) < 3) {
        $errors[] = 'Kommentaren är för kort.';
    } else {
        $ins = db()->prepare("INSERT INTO comments (user_id, route_id, body) VALUES (?, ?, ?)");
        $ins->execute([$me['id'], $id, $body]);
        flash('success', 'Kommentar publicerad.');
        redirect('/user/route.php?id=' . $id);
    }
}

// TODO paginering nån gång, lol
$cmts = db()->prepare(
    "SELECT c.id, c.body, c.created_at, c.flagged, u.username, u.id AS user_id
     FROM comments c JOIN users u ON u.id = c.user_id
     WHERE c.route_id = ?
     ORDER BY c.created_at DESC"
);
$cmts->execute([$id]);
$cmts = $cmts->fetchAll();

$senders = db()->prepare(
    "SELECT u.username, MIN(s.send_date) AS first_send, MIN(s.attempts) AS best_attempts
     FROM sends s JOIN users u ON u.id = s.user_id
     WHERE s.route_id = ?
     GROUP BY u.id
     ORDER BY first_send DESC
     LIMIT 20"
);
$senders->execute([$id]);
$senders = $senders->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<p class="muted"><a href="<?= e(APP_URL) ?>/user/routes.php">Tillbaka till alla leder</a></p>

<article class="card">
    <h1><?= e($route['name']) ?></h1>
    <p class="meta">
        Grad: <strong><?= e($route['grade']) ?></strong>,
        Färg: <?= e($route['color']) ?>,
        Zon: <?= e($route['zone_name'] ?? 'okänd') ?>
    </p>
    <?php if ($route['description']): ?>
        <p class="mt-2"><?= nl2br(e($route['description'])) ?></p>
    <?php endif; ?>
</article>

<div class="grid mt-3" style="grid-template-columns: 1fr 1fr;">

    <article class="card">
        <h3>Vilka har skickat?</h3>
        <?php if (!$senders): ?>
            <p class="muted">Ingen har loggat denna led ännu. Var först!</p>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Klättrare</th><th>Datum</th><th>Försök</th></tr></thead>
                <tbody>
                    <?php foreach ($senders as $s): ?>
                    <tr>
                        <td><?= e($s['username']) ?></td>
                        <td><?= e($s['first_send']) ?></td>
                        <td><?= (int)$s['best_attempts'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </article>

    <article class="card">
        <h3>Kommentarer &amp; beta</h3>

        <?php foreach ($errors as $err): ?>
            <div class="alert error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="post" class="mb-2">
            <?= csrf_field() ?>
            <textarea name="body" maxlength="1000" required minlength="3"
                placeholder="Dela din beta eller fråga andra…"></textarea>
            <button class="btn mt-1">Publicera</button>
        </form>

        <?php if (!$cmts): ?>
            <p class="muted">Inga kommentarer ännu.</p>
        <?php else: ?>
            <?php foreach ($cmts as $c): ?>
                <div style="border-top:1px solid var(--border); padding-top:.6rem; margin-top:.6rem;">
                    <div class="flex-between">
                        <strong><?= e($c['username']) ?></strong>
                        <small class="muted"><?= e($c['created_at']) ?></small>
                    </div>
                    <p style="margin:.3rem 0 0;"><?= nl2br(e($c['body'])) ?></p>
                    <?php if (!$c['flagged'] && $c['user_id'] !== $me['id']): ?>
                        <form method="post" action="<?= e(APP_URL) ?>/user/flag-comment.php" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="comment_id" value="<?= (int)$c['id'] ?>">
                            <input type="hidden" name="back" value="<?= (int)$id ?>">
                            <button type="submit" class="muted"
                                style="background:none; border:0; cursor:pointer; font-size:.8rem; padding:.2rem 0;">
                                Rapportera
                            </button>
                        </form>
                    <?php elseif ($c['flagged']): ?>
                        <small class="muted">Rapporterad</small>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </article>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
