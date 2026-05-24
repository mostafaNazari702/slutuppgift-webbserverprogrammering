<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$page = 'reset';
$pageTitle = 'Nytt lösenord';
$errors = [];

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    flash('error', 'Ogiltig återställningslänk.');
    redirect('/auth/forgot.php');
}

$stmt = db()->prepare(
    "SELECT id, username FROM users
     WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1"
);
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    flash('error', 'Länken är ogiltig eller har gått ut.');
    redirect('/auth/forgot.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pw = (string)($_POST['password'] ?? '');
    $pw2 = (string)($_POST['password2'] ?? '');

    if ($pw !== $pw2) $errors[] = 'Lösenorden matchar inte.';
    foreach (password_issues($pw) as $issue) {
        $errors[] = 'Lösenordet saknar ' . $issue . '.';
    }

    if (!$errors) {
        $hash = password_hash($pw, PASSWORD_DEFAULT);
        $up = db()->prepare(
            "UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL
             WHERE id = ?"
        );
        $up->execute([$hash, $user['id']]);
        flash('success', 'Lösenordet är uppdaterat. Logga in med det nya.');
        redirect('/auth/login.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>

<form class="form" method="post" novalidate>
    <h1>Välj nytt lösenord</h1>
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <?= csrf_field() ?>

    <?php foreach ($errors as $err): ?>
        <div class="alert error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <p class="muted">Konto: <strong><?= e($user['username']) ?></strong></p>

    <div class="field">
        <label for="password">Nytt lösenord</label>
        <input type="password" id="password" name="password" required minlength="8">
        <div class="pw-meter"><span></span></div>
        <div class="pw-hints">Minst 8 tecken, blanda versaler, gemener, siffror och specialtecken.</div>
    </div>

    <div class="field">
        <label for="password2">Bekräfta lösenord</label>
        <input type="password" id="password2" name="password2" required minlength="8">
    </div>

    <button class="btn btn-block">Spara nytt lösenord</button>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
