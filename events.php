<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$page = 'events';
$pageTitle = 'Kurser & events';

$events = db()->query(
    "SELECT e.id, e.title, e.description, e.event_date, e.location, e.max_participants,
            (SELECT COUNT(*) FROM registrations r
             WHERE r.event_id = e.id AND r.status = 'registered') AS num_registered
     FROM events e
     WHERE e.event_date >= NOW()
     ORDER BY e.event_date ASC"
)->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<h1>Kurser &amp; events</h1>
<p class="muted">Anmäl dig till tävlingar, kurser och våra återkommande klätterkvällar</p>

<?php if (!$events): ?>
    <p class="mt-3">Inga kommande event just nu.</p>
<?php else: ?>
    <div class="grid mt-3">
        <?php foreach ($events as $ev):
            $full = $ev['max_participants'] !== null
                 && $ev['num_registered'] >= $ev['max_participants'];
        ?>
            <article class="card" id="event-<?= (int)$ev['id'] ?>">
                <h3><?= e($ev['title']) ?></h3>
                <p class="meta">
                    <?= e(date('d M Y H:i', strtotime($ev['event_date']))) ?><br>
                    <?= e($ev['location'] ?? '') ?><br>
                    <?= (int)$ev['num_registered'] ?>
                    <?= $ev['max_participants'] !== null ? '/' . (int)$ev['max_participants'] : '' ?>
                    anmälda
                </p>
                <p class="mt-2"><?= nl2br(e($ev['description'] ?? '')) ?></p>

                <?php if (is_logged_in()): ?>
                    <?php if ($full): ?>
                        <button class="btn" disabled>Fullt</button>
                    <?php else: ?>
                        <form method="post" action="<?= e(APP_URL) ?>/user/register-event.php" class="mt-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
                            <button class="btn" type="submit">Anmäl mig</button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="muted mt-2">
                        <a href="<?= e(APP_URL) ?>/auth/login.php">Logga in</a> för att anmäla dig.
                    </p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
