<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_moderator();

$page = 'admin';
$pageTitle = 'Adminpanel';

// todo: gör om till en query nån gång, blir slö här annars
$pdo = db();
$counts = [
    'users' => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'unverified' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE verified = 0")->fetchColumn(),
    'routes' => (int)$pdo->query("SELECT COUNT(*) FROM routes WHERE active = 1")->fetchColumn(),
    'sends' => (int)$pdo->query("SELECT COUNT(*) FROM sends")->fetchColumn(),
    'events' => (int)$pdo->query("SELECT COUNT(*) FROM events WHERE event_date >= NOW()")->fetchColumn(),
    'flagged' => (int)$pdo->query("SELECT COUNT(*) FROM comments WHERE flagged = 1")->fetchColumn(),
];

include __DIR__ . '/../includes/header.php';
?>

<h1>Adminpanel</h1>
<p class="muted">Inloggad som moderator, var snäll mot communityt.</p>

<div class="grid mt-3">
    <article class="card stat"><div class="big"><?= $counts['users'] ?></div><div class="label">Medlemmar</div></article>
    <article class="card stat"><div class="big"><?= $counts['unverified'] ?></div><div class="label">Ej verifierade</div></article>
    <article class="card stat"><div class="big"><?= $counts['routes'] ?></div><div class="label">Aktiva leder</div></article>
    <article class="card stat"><div class="big"><?= $counts['sends'] ?></div><div class="label">Sends totalt</div></article>
    <article class="card stat"><div class="big"><?= $counts['events'] ?></div><div class="label">Kommande event</div></article>
    <article class="card stat">
        <div class="big" style="color: <?= $counts['flagged'] ? 'var(--danger)' : 'var(--text-muted)' ?>;">
            <?= $counts['flagged'] ?>
        </div>
        <div class="label">Rapporterade kommentarer</div>
    </article>
</div>

<h2 class="mt-3">Snabbåtkomst</h2>
<div class="grid">
    <a class="card" href="<?= e(APP_URL) ?>/admin/routes.php" style="text-decoration:none; color:var(--text);">
        <h3>Hantera leder</h3>
        <p class="muted">Skapa, redigera och avaktivera leder i hallen</p>
    </a>
    <a class="card" href="<?= e(APP_URL) ?>/admin/events.php" style="text-decoration:none; color:var(--text);">
        <h3>Event</h3>
        <p class="muted">Planera kurser och tävlingar, se deltagarlistor</p>
    </a>
    <a class="card" href="<?= e(APP_URL) ?>/admin/users.php" style="text-decoration:none; color:var(--text);">
        <h3>Användare &amp; roller</h3>
        <p class="muted">Befordra medlemmar till moderatorer eller stäng av konton</p>
    </a>
    <a class="card" href="<?= e(APP_URL) ?>/admin/reports.php" style="text-decoration:none; color:var(--text);">
        <h3>Rapporter</h3>
        <p class="muted">Granska rapporterade kommentarer</p>
    </a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
