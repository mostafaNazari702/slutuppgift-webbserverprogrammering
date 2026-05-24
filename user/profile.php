<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$page = 'profile';
$pageTitle = 'Profil & inställningar';
$me = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'prefs') {
        $grade = $_POST['grade_system'] ?? 'font';
        $zone = $_POST['last_zone'] ?? '';
        $grade = in_array($grade, ['font','v'], true) ? $grade : 'font';

        // 1 år, borde räcka
        setcookie('grade_system', $grade, time() + 60*60*24*365, '/');
        setcookie('last_zone', $zone, time() + 60*60*24*365, '/');
        flash('success', 'Preferenser sparade');
        redirect('/user/profile.php');
    }

    if ($action === 'password') {
        $current = (string)($_POST['current'] ?? '');
        $new = (string)($_POST['new'] ?? '');
        $new2 = (string)($_POST['new2'] ?? '');

        $row = db()->prepare("SELECT password_hash FROM users WHERE id = ?");
        $row->execute([$me['id']]);
        $hash = $row->fetchColumn();

        if (!password_verify($current, $hash)) {
            $errors[] = 'Nuvarande lösenord stämmer inte.';
        }
        if ($new !== $new2) {
            $errors[] = 'De nya lösenorden matchar inte.';
        }
        foreach (password_issues($new) as $issue) {
            $errors[] = 'Nytt lösenord saknar ' . $issue . '.';
        }

        if (!$errors) {
            $up = db()->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $up->execute([password_hash($new, PASSWORD_DEFAULT), $me['id']]);
            flash('success', 'Lösenordet är uppdaterat.');
            redirect('/user/profile.php');
        }
    }
}

$zones = db()->query("SELECT id, name FROM zones ORDER BY name")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h1>Profil &amp; inställningar</h1>

<div class="grid mt-3" style="grid-template-columns: 1fr 1fr;">

    <article class="card">
        <h3>Kontoinformation</h3>
        <p><strong>Användarnamn:</strong> <?= e($me['username']) ?></p>
        <p><strong>E-post:</strong> <?= e($me['email']) ?></p>
        <p><strong>Roll:</strong>
            <?= is_moderator()
                ? '<span class="role-badge mod">moderator</span>'
                : '<span class="role-badge">medlem</span>' ?>
        </p>
    </article>

    <article class="card">
        <h3>Personliga preferenser</h3>
        <p class="muted">Dessa val sparas som cookies och följer dig på alla enheter där du loggar in</p>

        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="prefs">

            <div class="field">
                <label for="grade_system">Gradsystem</label>
                <select name="grade_system" id="grade_system">
                    <?php $g = $_COOKIE['grade_system'] ?? 'font'; ?>
                    <option value="font" <?= $g==='font'?'selected':'' ?>>Font (6a, 7a+, 8c)</option>
                    <option value="v"    <?= $g==='v'   ?'selected':'' ?>>V-skala (V0, V5, V10)</option>
                </select>
            </div>

            <div class="field">
                <label for="last_zone">Favoritzon</label>
                <select name="last_zone" id="last_zone">
                    <option value="">Ingen</option>
                    <?php $lz = $_COOKIE['last_zone'] ?? '';
                    foreach ($zones as $z): ?>
                        <option value="<?= (int)$z['id'] ?>"
                            <?= $lz == $z['id'] ? 'selected' : '' ?>>
                            <?= e($z['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label>Tema</label>
                <button type="button" id="themeToggle" class="btn btn-ghost">Växla mörkt/ljust läge</button>
            </div>

            <button type="submit" class="btn">Spara preferenser</button>
        </form>
    </article>

    <article class="card" style="grid-column: 1 / -1;">
        <h3>Byt lösenord</h3>
        <?php foreach ($errors as $err): ?>
            <div class="alert error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="post" style="max-width:420px;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="password">

            <div class="field">
                <label for="current">Nuvarande lösenord</label>
                <input type="password" id="current" name="current" required>
            </div>
            <div class="field">
                <label for="password">Nytt lösenord</label>
                <input type="password" id="password" name="new" required minlength="8">
                <div class="pw-meter"><span></span></div>
                <div class="pw-hints">Minst 8 tecken, blanda versaler, gemener, siffror och specialtecken.</div>
            </div>
            <div class="field">
                <label for="new2">Bekräfta nytt lösenord</label>
                <input type="password" id="new2" name="new2" required minlength="8">
            </div>
            <button class="btn">Uppdatera lösenord</button>
        </form>
    </article>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
