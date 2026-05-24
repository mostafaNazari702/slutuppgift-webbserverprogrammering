<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$page = 'bookings';
$pageTitle = 'Mina bokningar';
$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $reg_id = (int)($_POST['registration_id'] ?? 0);
    if ($reg_id > 0) {
        $up = db()->prepare(
            "UPDATE registrations SET status = 'cancelled' WHERE id = ? AND user_id = ?"
        );
        $up->execute([$reg_id, $me['id']]);
        flash('info', 'Du har avbokat.');
        redirect('/user/bookings.php');
    }
}

$rows = db()->prepare(
    "SELECT r.id AS reg_id, r.status, r.registered_at,
            e.id AS event_id, e.title, e.event_date, e.location
     FROM registrations r
     JOIN events e ON e.id = r.event_id
     WHERE r.user_id = ?
     ORDER BY e.event_date DESC"
);
$rows->execute([$me['id']]);
$rows = $rows->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h1>Mina bokningar</h1>

<?php if (!$rows): ?>
    <p class="muted">Du har inga bokningar.
        <a href="<?= e(APP_URL) ?>/events.php">Se kommande event</a></p>
<?php else: ?>
    <table class="table mt-2">
        <thead><tr><th>Event</th><th>Datum</th><th>Plats</th><th>Status</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($rows as $r):
                $future = strtotime($r['event_date']) > time();
            ?>
                <tr>
                    <td><strong><?= e($r['title']) ?></strong></td>
                    <td><?= e(date('d M Y H:i', strtotime($r['event_date']))) ?></td>
                    <td><?= e($r['location']) ?></td>
                    <td><?= e($r['status']) ?></td>
                    <td>
                        <?php if ($future && $r['status'] === 'registered'): ?>
                            <form method="post" data-confirm="Avboka denna anmälan?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="registration_id" value="<?= (int)$r['reg_id'] ?>">
                                <button class="btn btn-danger" style="padding:.3rem .8rem; font-size:.85rem;">Avboka</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
