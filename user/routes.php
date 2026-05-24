<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$page = 'routes';
$pageTitle = 'Bläddra leder';

$zoneFilter = isset($_GET['zone']) ? (int)$_GET['zone'] : 0;
$gradeFilter = clean_str($_GET['grade'] ?? '', 10);
$colorFilter = clean_str($_GET['color'] ?? '', 20);

$sql = "SELECT r.*, z.name AS zone_name,
               (SELECT COUNT(*) FROM sends WHERE route_id = r.id) AS total_sends
        FROM routes r
        LEFT JOIN zones z ON z.id = r.zone_id
        WHERE r.active = 1";
$params = [];

if ($zoneFilter) { $sql .= " AND r.zone_id = ?"; $params[] = $zoneFilter; }
if ($gradeFilter !== '') { $sql .= " AND r.grade = ?"; $params[] = $gradeFilter; }
if ($colorFilter !== '') { $sql .= " AND r.color = ?"; $params[] = $colorFilter; }

$sql .= " ORDER BY r.grade ASC, r.name ASC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$routes = $stmt->fetchAll();

$zones = db()->query("SELECT id, name FROM zones ORDER BY name")->fetchAll();
$grades = db()->query("SELECT DISTINCT grade FROM routes WHERE active = 1 ORDER BY grade")->fetchAll(PDO::FETCH_COLUMN);
$colors = db()->query("SELECT DISTINCT color FROM routes WHERE active = 1 ORDER BY color")->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/../includes/header.php';
?>

<h1>Bläddra leder</h1>

<form method="get" class="card mb-2">
    <div class="grid" style="grid-template-columns: repeat(4, 1fr);">
        <div class="field" style="margin:0;">
            <label for="zone">Zon</label>
            <select name="zone" id="zone">
                <option value="0">Alla zoner</option>
                <?php foreach ($zones as $z): ?>
                    <option value="<?= (int)$z['id'] ?>" <?= $zoneFilter===(int)$z['id']?'selected':'' ?>>
                        <?= e($z['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field" style="margin:0;">
            <label for="grade">Grad</label>
            <select name="grade" id="grade">
                <option value="">Alla</option>
                <?php foreach ($grades as $g): ?>
                    <option value="<?= e($g) ?>" <?= $gradeFilter===$g?'selected':'' ?>><?= e($g) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field" style="margin:0;">
            <label for="color">Färg</label>
            <select name="color" id="color">
                <option value="">Alla</option>
                <?php foreach ($colors as $c): ?>
                    <option value="<?= e($c) ?>" <?= $colorFilter===$c?'selected':'' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field" style="margin:0; align-self:end;">
            <button class="btn btn-block">Filtrera</button>
        </div>
    </div>
</form>

<?php if (!$routes): ?>
    <p class="muted">Inga leder matchade ditt filter.</p>
<?php else: ?>
    <div class="grid">
        <?php foreach ($routes as $r): ?>
            <article class="card">
                <h3><?= e($r['name']) ?></h3>
                <p class="meta">
                    Grad: <strong><?= e($r['grade']) ?></strong>,
                    Färg: <?= e($r['color']) ?><br>
                    Zon: <?= e($r['zone_name'] ?? 'okänd') ?>, <?= (int)$r['total_sends'] ?> sends
                </p>
                <?php if ($r['description']): ?>
                    <p class="mt-1"><?= nl2br(e($r['description'])) ?></p>
                <?php endif; ?>
                <a href="<?= e(APP_URL) ?>/user/route.php?id=<?= (int)$r['id'] ?>" class="btn btn-ghost mt-2">
                    Beta &amp; kommentarer
                </a>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
