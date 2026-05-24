<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$page = 'sends';
$pageTitle = 'Mina sends';
$me = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $route_id = (int)($_POST['route_id'] ?? 0);
    $attempts = max(1, (int)($_POST['attempts'] ?? 1));
    $date = clean_str($_POST['send_date'] ?? '', 10);
    $note = clean_str($_POST['note'] ?? '', 255);

    if ($route_id <= 0)                 $errors[] = 'Välj en led.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $errors[] = 'Ogiltigt datum.';

    if (!$errors) {
        $stmt = db()->prepare("SELECT 1 FROM routes WHERE id = ? AND active = 1");
        $stmt->execute([$route_id]);
        if (!$stmt->fetchColumn()) $errors[] = 'Leden finns inte.';
    }

    if (!$errors) {
        $ins = db()->prepare(
            "INSERT INTO sends (user_id, route_id, attempts, send_date, note)
             VALUES (?, ?, ?, ?, ?)"
        );
        $ins->execute([$me['id'], $route_id, $attempts, $date, $note ?: null]);
        flash('success', 'Send loggad!');
        redirect('/user/sends.php');
    }
}

$routes = db()->query(
    "SELECT id, name, grade, color FROM routes WHERE active = 1 ORDER BY grade, name"
)->fetchAll();

$mine = db()->prepare(
    "SELECT s.id, s.send_date, s.attempts, s.note, r.name, r.grade, r.color
     FROM sends s
     JOIN routes r ON r.id = s.route_id
     WHERE s.user_id = ?
     ORDER BY s.send_date DESC, s.id DESC
     LIMIT 50"
);
$mine->execute([$me['id']]);
$mine = $mine->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h1>Mina sends</h1>

<div class="grid mt-3" style="grid-template-columns: 1fr 2fr;">

    <article class="card">
        <h3>Logga en ny send</h3>
        <?php foreach ($errors as $err): ?>
            <div class="alert error"><?= e($err) ?></div>
        <?php endforeach; ?>
        <form method="post">
            <?= csrf_field() ?>

            <div class="field">
                <label for="route_id">Led</label>
                <select name="route_id" id="route_id" required>
                    <option value="">Välj led</option>
                    <?php foreach ($routes as $r): ?>
                        <option value="<?= (int)$r['id'] ?>">
                            <?= e($r['name']) ?> (<?= e($r['grade']) ?>, <?= e($r['color']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="attempts">Antal försök</label>
                <input type="number" id="attempts" name="attempts" min="1" max="999" value="1" required>
            </div>

            <div class="field">
                <label for="send_date">Datum</label>
                <input type="date" id="send_date" name="send_date" required
                       value="<?= e(date('Y-m-d')) ?>">
            </div>

            <div class="field">
                <label for="note">Notering (valfri)</label>
                <input type="text" id="note" name="note" maxlength="255"
                       placeholder="t.ex. Flash, ny PR, taggade!">
            </div>

            <button class="btn btn-block">Spara send</button>
        </form>
    </article>

    <article>
        <h3>Historik (senaste 50)</h3>
        <?php if (!$mine): ?>
            <p class="muted">Inga sends ännu, logga din första till vänster.</p>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Datum</th><th>Led</th><th>Grad</th><th>Försök</th><th>Notering</th></tr></thead>
                <tbody>
                    <?php foreach ($mine as $r): ?>
                    <tr>
                        <td><?= e($r['send_date']) ?></td>
                        <td><?= e($r['name']) ?> <span class="muted">(<?= e($r['color']) ?>)</span></td>
                        <td><strong><?= e($r['grade']) ?></strong></td>
                        <td><?= (int)$r['attempts'] ?></td>
                        <td><?= e($r['note'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </article>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
