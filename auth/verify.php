<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$page = 'verify';
$pageTitle = 'Verifiera e-post';

$token = $_GET['token'] ?? '';
$ok = false;

if ($token !== '' && preg_match('/^[a-f0-9]{64}$/', $token)) {
    $stmt = db()->prepare("UPDATE users SET verified = 1, verify_token = NULL WHERE verify_token = ?");
    $stmt->execute([$token]);
    $ok = $stmt->rowCount() > 0;
}

include __DIR__ . '/../includes/header.php';
?>

<div class="form text-center">
    <?php if ($ok): ?>
        <h1>E-post verifierad</h1>
        <p>Ditt konto är nu aktivt. Logga in för att komma igång.</p>
        <a href="<?= e(APP_URL) ?>/auth/login.php" class="btn mt-2">Till inloggningen</a>
    <?php else: ?>
        <h1>Verifieringen misslyckades</h1>
        <p class="muted">Länken är ogiltig eller redan använd.</p>
        <a href="<?= e(APP_URL) ?>/auth/register.php" class="btn mt-2">Skapa nytt konto</a>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
