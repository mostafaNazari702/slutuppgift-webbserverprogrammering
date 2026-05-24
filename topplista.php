<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$page = 'topplista';
$pageTitle = 'Topplista';

$period = $_GET['period'] ?? 'month';
$period = in_array($period, ['month', 'year', 'all'], true) ? $period : 'month';

$where = '';
switch ($period) {
    case 'month': $where = "WHERE s.send_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"; break;
    case 'year': $where = "WHERE s.send_date >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)"; break;
    default: $where = "";
}

$sql = "SELECT u.username, COUNT(s.id) AS num_sends,
               COALESCE(MAX(r.grade), '-') AS top_grade
        FROM users u
        JOIN sends s ON s.user_id = u.id
        JOIN routes r ON r.id = s.route_id
        $where
        GROUP BY u.id
        ORDER BY num_sends DESC, top_grade DESC
        LIMIT 25";

$rows = db()->query($sql)->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="flex-between mb-2">
    <h1>Topplista</h1>
    <div class="flex">
        <a href="?period=month" class="btn <?= $period==='month'?'':'btn-ghost' ?>">Senaste månaden</a>
        <a href="?period=year"  class="btn <?= $period==='year' ?'':'btn-ghost' ?>">Senaste året</a>
        <a href="?period=all"   class="btn <?= $period==='all'  ?'':'btn-ghost' ?>">All-time</a>
    </div>
</div>

<?php if (!$rows): ?>
    <p class="muted">Inga sends i den valda perioden ännu.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Klättrare</th>
                <th>Antal sends</th>
                <th>Topprad</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $i => $row): ?>
                <tr>
                    <td><strong>#<?= $i + 1 ?></strong></td>
                    <td><?= e($row['username']) ?></td>
                    <td><?= (int) $row['num_sends'] ?></td>
                    <td><?= e($row['top_grade']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
