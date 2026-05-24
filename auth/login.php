<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$page = 'login';
$pageTitle = 'Logga in';
$errors = [];

if (is_logged_in()) {
    redirect('/user/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $email = clean_str($_POST['email'] ?? '', 120);
    $pw = (string)($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    if (!valid_email($email) || $pw === '') {
        $errors[] = 'Fyll i e-post och lösenord.';
    }

    if (!$errors) {
        $stmt = db()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($pw, $row['password_hash'])) {
            $errors[] = 'Fel e-post eller lösenord.';
        } elseif (!$row['verified']) {
            $errors[] = 'Du måste verifiera din e-post först. Kolla din inkorg';
        } else {
            login_user($row);

            if ($remember) {
                setcookie('remember_user', $row['username'], [
                    'expires' => time() + 60*60*24*30,
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }

            flash('success', 'Välkommen tillbaka, ' . $row['username'] . '!');
            redirect(is_moderator() ? '/admin/index.php' : '/user/dashboard.php');
        }
    }
}

$rememberedUser = $_COOKIE['remember_user'] ?? '';

include __DIR__ . '/../includes/header.php';
?>

<form class="form" method="post" novalidate>
    <h1>Logga in</h1>
    <?= csrf_field() ?>

    <?php foreach ($errors as $err): ?>
        <div class="alert error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <?php if ($rememberedUser): ?>
        <p class="muted">Välkommen tillbaka, <strong><?= e($rememberedUser) ?></strong>!</p>
    <?php endif; ?>

    <div class="field">
        <label for="email">E-post</label>
        <input type="email" id="email" name="email" required maxlength="120"
               value="<?= e($_POST['email'] ?? '') ?>">
    </div>

    <div class="field">
        <label for="password">Lösenord</label>
        <input type="password" id="password" name="password" required>
    </div>

    <div class="field">
        <label style="display:flex; align-items:center; gap:.5rem; font-weight:400;">
            <input type="checkbox" name="remember" value="1" style="width:auto;">
            Kom ihåg mig
        </label>
    </div>

    <button type="submit" class="btn btn-block">Logga in</button>

    <p class="muted text-center mt-2">
        <a href="<?= e(APP_URL) ?>/auth/forgot.php">Glömt lösenord?</a> eller
        <a href="<?= e(APP_URL) ?>/auth/register.php">skapa konto</a>
    </p>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
