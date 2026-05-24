<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mail.php';

$page = 'forgot';
$pageTitle = 'Glömt lösenord';
$done = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = clean_str($_POST['email'] ?? '', 120);

    if (!valid_email($email)) {
        $errors[] = 'Ange en giltig e-postadress.';
    } else {
        $stmt = db()->prepare("SELECT id, username FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);

            $up = db()->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $up->execute([$token, $expires, $user['id']]);

            $url = APP_URL . '/auth/reset.php?token=' . urlencode($token);
            send_mail(
                $email,
                'Återställ ditt lösenord',
                "<p>Hej " . e($user['username']) . "!</p>
                 <p>Klicka på länken nedan för att välja ett nytt lösenord.
                 Länken är giltig i 1 timme.</p>
                 <p><a href=\"$url\">$url</a></p>"
            );
        }
        $done = true;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<form class="form" method="post" novalidate>
    <h1>Glömt lösenord</h1>
    <?= csrf_field() ?>

    <?php foreach ($errors as $err): ?>
        <div class="alert error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <?php if ($done): ?>
        <div class="alert success">
            Om e-posten finns i vårt system har en återställningslänk skickats.
        </div>
    <?php endif; ?>

    <p class="muted">Fyll i e-posten du registrerade dig med så skickar vi en länk.</p>

    <div class="field">
        <label for="email">E-post</label>
        <input type="email" id="email" name="email" required maxlength="120">
    </div>

    <button class="btn btn-block">Skicka återställningslänk</button>

    <p class="muted text-center mt-2">
        <a href="<?= e(APP_URL) ?>/auth/login.php">Tillbaka till inloggning</a>
    </p>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
