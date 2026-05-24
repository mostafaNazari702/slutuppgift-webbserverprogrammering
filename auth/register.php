<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mail.php';

$page = 'register';
$pageTitle = 'Skapa konto';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $username = clean_str($_POST['username'] ?? '', 40);
    $email = clean_str($_POST['email'] ?? '', 120);
    $pw = (string)($_POST['password'] ?? '');
    $pw2 = (string)($_POST['password2'] ?? '');

    if (!preg_match('/^[A-Za-z0-9_åäöÅÄÖ]{3,40}$/u', $username)) {
        $errors[] = 'Användarnamn måste vara 3-40 tecken, bara bokstäver siffror och _';
    }
    if (!valid_email($email)) {
        $errors[] = 'Ange en giltig mailadress.';
    }
    if ($pw !== $pw2) {
        $errors[] = 'Lösenorden matchar inte';
    }
    foreach (password_issues($pw) as $issue) {
        $errors[] = 'Lösenordet saknar ' . $issue;
    }

    if (!$errors) {
        $stmt = db()->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ((int)$stmt->fetchColumn() > 0) {
            $errors[] = 'Användarnamnet eller e-posten är redan upptagen.';
        }
    }

    if (!$errors) {
        $hash = password_hash($pw, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));

        $stmt = db()->prepare(
            "INSERT INTO users (username, email, password_hash, role, verified, verify_token)
             VALUES (?, ?, ?, 'user', 0, ?)"
        );
        $stmt->execute([$username, $email, $hash, $token]);

        $verifyUrl = APP_URL . '/auth/verify.php?token=' . urlencode($token);
        send_mail(
            $email,
            'Verifiera din mail',
            "<p>Hej $username!</p>
             <p>Klicka på länken nedan för att aktivera ditt konto:</p>
             <p><a href=\"$verifyUrl\">$verifyUrl</a></p>"
        );

        flash('success', 'Konto skapat. Ett verifieringsmail har skickats');
        redirect('/auth/login.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>

<form class="form" method="post" novalidate>
    <h1>Skapa konto</h1>
    <?= csrf_field() ?>

    <?php foreach ($errors as $err): ?>
        <div class="alert error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <div class="field">
        <label for="username">Användarnamn</label>
        <input type="text" id="username" name="username" required minlength="3" maxlength="40"
               value="<?= e($_POST['username'] ?? '') ?>">
    </div>

    <div class="field">
        <label for="email">E-post</label>
        <input type="email" id="email" name="email" required maxlength="120"
               value="<?= e($_POST['email'] ?? '') ?>">
    </div>

    <div class="field">
        <label for="password">Lösenord</label>
        <input type="password" id="password" name="password" required minlength="8">
        <div class="pw-meter"><span></span></div>
        <div class="pw-hints">Minst 8 tecken, blanda versaler, gemener, siffror och specialtecken.</div>
    </div>

    <div class="field">
        <label for="password2">Bekräfta lösenord</label>
        <input type="password" id="password2" name="password2" required minlength="8">
    </div>

    <button type="submit" class="btn btn-block">Skapa konto</button>

    <p class="muted text-center mt-2">
        Har du redan ett konto? <a href="<?= e(APP_URL) ?>/auth/login.php">Logga in</a>
    </p>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
