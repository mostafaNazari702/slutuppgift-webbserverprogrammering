<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_moderator();

$page = 'admin';
$pageTitle = 'Hantera leder';
$me = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $name = clean_str($_POST['name'] ?? '', 80);
        $grade = clean_str($_POST['grade'] ?? '', 10);
        $color = clean_str($_POST['color'] ?? '', 20);
        $zone = (int)($_POST['zone_id'] ?? 0) ?: null;
        $desc = clean_str($_POST['description'] ?? '', 2000);

        if ($name === '')  $errors[] = 'Namn krävs.';
        if ($grade === '') $errors[] = 'Grad krävs.';
        if ($color === '') $errors[] = 'Färg krävs.';

        if (!$errors) {
            if ($action === 'create') {
                $ins = db()->prepare(
                    "INSERT INTO routes (name, grade, color, zone_id, description, created_by)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $ins->execute([$name, $grade, $color, $zone, $desc ?: null, $me['id']]);
                flash('success', 'Ny led skapad.');
            } else {
                $id = (int)$_POST['id'];
                $up = db()->prepare(
                    "UPDATE routes SET name=?, grade=?, color=?, zone_id=?, description=? WHERE id=?"
                );
                $up->execute([$name, $grade, $color, $zone, $desc ?: null, $id]);
                flash('success', 'Leden uppdaterad.');
            }
            redirect('/admin/routes.php');
        }
    }

    if ($action === 'toggle') {
        $id = (int)$_POST['id'];
        db()->prepare("UPDATE routes SET active = 1 - active WHERE id = ?")->execute([$id]);
        flash('info', 'Lederns status växlad.');
        redirect('/admin/routes.php');
    }
}

$editId = (int)($_GET['edit'] ?? 0);
$editing = null;
if ($editId > 0) {
    $stmt = db()->prepare("SELECT * FROM routes WHERE id = ?");
    $stmt->execute([$editId]);
    $editing = $stmt->fetch();
}

$zones = db()->query("SELECT id, name FROM zones ORDER BY name")->fetchAll();
$routes = db()->query(
    "SELECT r.*, z.name AS zone_name
     FROM routes r LEFT JOIN zones z ON z.id = r.zone_id
     ORDER BY r.active DESC, r.grade, r.name"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<p class="muted"><a href="<?= e(APP_URL) ?>/admin/index.php">Tillbaka till adminpanel</a></p>
<h1>Hantera leder</h1>

<div class="grid mt-3" style="grid-template-columns: 1fr 2fr;">

    <article class="card">
        <h3><?= $editing ? 'Redigera led' : 'Skapa ny led' ?></h3>
        <?php foreach ($errors as $err): ?>
            <div class="alert error"><?= e($err) ?></div>
        <?php endforeach; ?>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
            <?php endif; ?>

            <div class="field">
                <label for="name">Namn</label>
                <input type="text" id="name" name="name" required maxlength="80"
                       value="<?= e($editing['name'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="grade">Grad</label>
                <input type="text" id="grade" name="grade" required maxlength="10"
                       placeholder="V4 eller 6a+"
                       value="<?= e($editing['grade'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="color">Tejpfärg</label>
                <input type="text" id="color" name="color" required maxlength="20"
                       placeholder="blå / röd / gul"
                       value="<?= e($editing['color'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="zone_id">Zon</label>
                <select name="zone_id" id="zone_id">
                    <option value="0">Ingen</option>
                    <?php foreach ($zones as $z): ?>
                        <option value="<?= (int)$z['id'] ?>"
                            <?= ($editing['zone_id'] ?? 0) == $z['id'] ? 'selected' : '' ?>>
                            <?= e($z['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="description">Beskrivning</label>
                <textarea id="description" name="description" maxlength="2000"
                ><?= e($editing['description'] ?? '') ?></textarea>
            </div>

            <button class="btn btn-block"><?= $editing ? 'Spara ändringar' : 'Skapa led' ?></button>
            <?php if ($editing): ?>
                <p class="text-center mt-2"><a href="<?= e(APP_URL) ?>/admin/routes.php">Avbryt</a></p>
            <?php endif; ?>
        </form>
    </article>

    <article>
        <h3>Alla leder</h3>
        <table class="table">
            <thead><tr><th>Namn</th><th>Grad</th><th>Färg</th><th>Zon</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($routes as $r): ?>
                <tr style="<?= $r['active'] ? '' : 'opacity:.5;' ?>">
                    <td><?= e($r['name']) ?></td>
                    <td><strong><?= e($r['grade']) ?></strong></td>
                    <td><?= e($r['color']) ?></td>
                    <td><?= e($r['zone_name'] ?? '-') ?></td>
                    <td><?= $r['active'] ? 'Aktiv' : 'Inaktiv' ?></td>
                    <td>
                        <a href="?edit=<?= (int)$r['id'] ?>" class="btn btn-ghost" style="padding:.3rem .6rem; font-size:.85rem;">Ändra</a>
                        <form method="post" style="display:inline;" data-confirm="Växla status?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button class="btn btn-danger" style="padding:.3rem .6rem; font-size:.85rem;">
                                <?= $r['active'] ? 'Avaktivera' : 'Aktivera' ?>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </article>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
