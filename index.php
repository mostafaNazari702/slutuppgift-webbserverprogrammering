<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$page = 'home';
$pageTitle = 'Välkommen';

$upcoming = db()->query(
    "SELECT id, title, event_date, location
     FROM events
     WHERE event_date >= NOW()
     ORDER BY event_date ASC
     LIMIT 3"
)->fetchAll();

$top = db()->query(
    "SELECT u.username, COUNT(s.id) AS sends
     FROM sends s
     JOIN users u ON u.id = s.user_id
     WHERE s.send_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
     GROUP BY u.id
     ORDER BY sends DESC
     LIMIT 3"
)->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<?php $me = current_user(); ?>
<section class="hero">
    <?php if ($me): ?>
        <h1>Välkommen tillbaka, <?= e($me['username']) ?>.</h1>
        <p>Redo för fler sends? Logga in dina försök eller kika på dagens topplista.</p>
        <a href="<?= e(APP_URL) ?>/user/sends.php" class="cta">Logga ny send</a>
    <?php else: ?>
        <h1>Logga dina sends. Tävla. Klättra mer.</h1>
        <p>SkickaUpp är klubbens digitala loggbok för boulder och lead.
           Registrera dig och logga varje topp så ser du hur du klättrar fram i topplistan.</p>
        <a href="<?= e(APP_URL) ?>/auth/register.php" class="cta">Skapa konto</a>
    <?php endif; ?>
</section>

<section class="mt-3">
    <h2>Kommande hos oss</h2>
    <?php if (!$upcoming): ?>
        <p class="muted">Inga event planerade just nu, kolla tillbaka snart.</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($upcoming as $ev): ?>
                <article class="card">
                    <h3><?= e($ev['title']) ?></h3>
                    <p class="meta">
                        <?= e(date('d M Y H:i', strtotime($ev['event_date']))) ?><br>
                        <?= e($ev['location'] ?? '') ?>
                    </p>
                    <a class="btn btn-ghost mt-2" href="<?= e(APP_URL) ?>/events.php#event-<?= (int)$ev['id'] ?>">Mer info</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="mt-3">
    <h2>Topp 3 senaste månaden</h2>
    <?php if (!$top): ?>
        <p class="muted">Ingen har loggat sends ännu, var först!</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($top as $i => $row): ?>
                <article class="card stat">
                    <div class="big">#<?= $i + 1 ?></div>
                    <div class="label"><?= e($row['username']) ?></div>
                    <p class="muted mt-1"><?= (int) $row['sends'] ?> sends</p>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="mt-2"><a href="<?= e(APP_URL) ?>/topplista.php">Se hela topplistan</a></p>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
