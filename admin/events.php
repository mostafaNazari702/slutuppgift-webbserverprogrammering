<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_moderator();

$page = 'admin';
$pageTitle = 'Hantera event';
$me = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $title = clean_str($_POST['title'] ?? '', 120);
        $desc = clean_str($_POST['description'] ?? '', 5000);
        $date = clean_str($_POST['event_date'] ?? '', 30);
        $loc = clean_str($_POST['location'] ?? '', 120);
        $maxp = $_POST['max_participants'] !== '' ? (int)$_POST['max_participants'] : null;

        if ($title === '') $errors[] = 'Titel krävs';
        if (!strtotime($date)) $errors[] = 'Ogiltigt datum';

        if (!$errors) {
            $dt = date('Y-m-d H:i:s', strtotime($date));
            if ($action === 'create') {
                $ins = db()->prepare(
                    "INSERT INTO events (title, description, event_date, location, max_participants, created_by)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $ins->execute([$title, $desc ?: null, $dt, $loc ?: null, $maxp, $me['id']]);
                flash('success', 'Event skapat.');
            } else {
                $id = (int)$_POST['id'];
                $up = db()->prepare(
                    "UPDATE events SET title=?, description=?, event_date=?, location=?, max_participants=?
                     WHERE id=?"
                );
                $up->execute([$title, $desc ?: null, $dt, $loc ?: null, $maxp, $id]);
                flash('success', 'Event uppdaterat.');
            }
            redirect('/admin/events.php');
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        db()->prepare("DELETE FROM events WHERE id = ?")->execute([$id]);
        flash('info', 'Event borttaget.');
        redirect('/admin/events.php');
    }
}

$editId = (int)($_GET['edit'] ?? 0);
$editing = null;
if ($editId > 0) {
    $stmt = db()->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$editId]);
    $editing = $stmt->fetch();
}

$events = db()->query(
    "SELECT e.*, (SELECT COUNT(*) FROM registrations r
                  WHERE r.event_id = e.id AND r.status='registered') AS num
     FROM events e ORDER BY e.event_date DESC"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<p class="muted"><a href="<?= e(APP_URL) ?>/admin/index.php">Tillbaka till adminpanel</a></p>
<h1>Hantera event</h1>

<div class="grid mt-3" style="grid-template-columns: 1fr 2fr;">

    <article class="card">
        <h3><?= $editing ? 'Redigera event' : 'Skapa nytt event' ?></h3>
        <?php foreach ($errors as $err): ?>
            <div class="alert error"><?= e($err) ?></div>
        <?php endforeach; ?>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
            <?php endif; ?>

            <div class="field">
                <label for="title">Titel</label>
                <input type="text" id="title" name="title" required maxlength="120"
                       value="<?= e($editing['title'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="event_date">Datum &amp; tid</label>
                <input type="datetime-local" id="event_date" name="event_date" required
                       value="<?= e($editing ? date('Y-m-d\TH:i', strtotime($editing['event_date'])) : '') ?>">
            </div>
            <div class="field">
                <label for="location">Plats</label>
                <input type="text" id="location" name="location" maxlength="120"
                       value="<?= e($editing['location'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="max_participants">Max deltagare (tomt = obegränsat)</label>
                <input type="number" id="max_participants" name="max_participants" min="1"
                       value="<?= e($editing['max_participants'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="description">Beskrivning</label>
                <textarea id="description" name="description" maxlength="5000"
                ><?= e($editing['description'] ?? '') ?></textarea>
            </div>

            <button class="btn btn-block"><?= $editing ? 'Spara' : 'Skapa event' ?></button>
            <?php if ($editing): ?>
                <p class="text-center mt-2"><a href="<?= e(APP_URL) ?>/admin/events.php">Avbryt</a></p>
            <?php endif; ?>
        </form>
    </article>

    <article>
        <h3>Alla event</h3>
        <table class="table">
            <thead><tr><th>Titel</th><th>Datum</th><th>Anmälda</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($events as $ev): ?>
                <tr>
                    <td><?= e($ev['title']) ?></td>
                    <td><?= e(date('Y-m-d H:i', strtotime($ev['event_date']))) ?></td>
                    <td><?= (int)$ev['num'] ?><?= $ev['max_participants'] ? '/' . (int)$ev['max_participants'] : '' ?></td>
                    <td>
                        <a href="?edit=<?= (int)$ev['id'] ?>" class="btn btn-ghost" style="padding:.3rem .6rem; font-size:.85rem;">Ändra</a>
                        <a href="<?= e(APP_URL) ?>/admin/event-attendees.php?id=<?= (int)$ev['id'] ?>"
                           class="btn btn-ghost" style="padding:.3rem .6rem; font-size:.85rem;">Deltagare</a>
                        <form method="post" style="display:inline;" data-confirm="Ta bort eventet (och alla anmälningar)?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$ev['id'] ?>">
                            <button class="btn btn-danger" style="padding:.3rem .6rem; font-size:.85rem;">Ta bort</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </article>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
