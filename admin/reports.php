<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_moderator();

$page = 'admin';
$pageTitle = 'Rapporter';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $cid = (int)($_POST['id'] ?? 0);

    if ($action === 'delete') {
        db()->prepare("DELETE FROM comments WHERE id = ?")->execute([$cid]);
        flash('info', 'Kommentaren raderades.');
    } elseif ($action === 'unflag') {
        db()->prepare("UPDATE comments SET flagged = 0 WHERE id = ?")->execute([$cid]);
        flash('success', 'Kommentaren markerades som OK.');
    }
    redirect('/admin/reports.php');
}

$flagged = db()->query(
    "SELECT c.id, c.body, c.created_at, c.flagged,
            u.username, r.name AS route_name, r.id AS route_id
     FROM comments c
     JOIN users u  ON u.id = c.user_id
     JOIN routes r ON r.id = c.route_id
     WHERE c.flagged = 1
     ORDER BY c.created_at DESC"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<p class="muted"><a href="<?= e(APP_URL) ?>/admin/index.php">Tillbaka till adminpanel</a></p>
<h1>Rapporterade kommentarer</h1>

<?php if (!$flagged): ?>
    <p class="muted">Inga rapporter just nu.</p>
<?php else: ?>
    <?php foreach ($flagged as $c): ?>
        <article class="card mt-2">
            <div class="flex-between">
                <div>
                    <strong><?= e($c['username']) ?></strong>
                    på leden <em><?= e($c['route_name']) ?></em>
                </div>
                <small class="muted"><?= e($c['created_at']) ?></small>
            </div>
            <p class="mt-1" style="white-space:pre-wrap;"><?= e($c['body']) ?></p>

            <div class="flex mt-2">
                <form method="post" data-confirm="Markera som OK och visa igen?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="unflag">
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                    <button class="btn btn-ghost">Markera OK</button>
                </form>
                <form method="post" data-confirm="Radera kommentaren?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                    <button class="btn btn-danger">Radera</button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
