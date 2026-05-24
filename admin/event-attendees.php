<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_moderator();

$page = 'admin';
$pageTitle = 'Deltagarlista';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();
if (!$event) {
    flash('error', 'Eventet finns inte.');
    redirect('/admin/events.php');
}

$rows = db()->prepare(
    "SELECT u.username, u.email, r.status, r.registered_at
     FROM registrations r JOIN users u ON u.id = r.user_id
     WHERE r.event_id = ?
     ORDER BY r.registered_at"
);
$rows->execute([$id]);
$rows = $rows->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<p class="muted"><a href="<?= e(APP_URL) ?>/admin/events.php">Tillbaka till events</a></p>
<h1><?= e($event['title']) ?></h1>
<p class="muted"><?= e(date('d M Y H:i', strtotime($event['event_date']))) ?>, <?= e($event['location']) ?></p>

<?php if (!$rows): ?>
    <p class="mt-3">Inga anmälda än.</p>
<?php else: ?>
    <table class="table mt-2">
        <thead><tr><th>Användarnamn</th><th>E-post</th><th>Status</th><th>Anmäld</th></tr></thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['username']) ?></td>
                <td><?= e($r['email']) ?></td>
                <td><?= e($r['status']) ?></td>
                <td><?= e($r['registered_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
