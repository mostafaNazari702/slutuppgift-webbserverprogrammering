<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$page = 'stats';
$pageTitle = 'Min statistik';
$me = current_user();

$byMonth = db()->prepare(
    "SELECT DATE_FORMAT(send_date, '%Y-%m') AS ym, COUNT(*) AS n
     FROM sends
     WHERE user_id = ? AND send_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
     GROUP BY ym
     ORDER BY ym"
);
$byMonth->execute([$me['id']]);
$byMonth = $byMonth->fetchAll();

$byGrade = db()->prepare(
    "SELECT r.grade, COUNT(*) AS n
     FROM sends s JOIN routes r ON r.id = s.route_id
     WHERE s.user_id = ?
     GROUP BY r.grade
     ORDER BY r.grade"
);
$byGrade->execute([$me['id']]);
$byGrade = $byGrade->fetchAll();

// todo: bryta ut till helper imorn
$maxMonth = max(array_map(fn($r) => $r['n'], $byMonth ?: [['n'=>1]]));
$maxGrade = max(array_map(fn($r) => $r['n'], $byGrade ?: [['n'=>1]]));

include __DIR__ . '/../includes/header.php';
?>

<h1>Min statistik</h1>

<div class="grid mt-3" style="grid-template-columns: 1fr 1fr;">

    <article class="card">
        <h3>Sends per månad (senaste halvåret)</h3>
        <?php if (!$byMonth): ?>
            <p class="muted">Ingen data ännu.</p>
        <?php else: ?>
            <div style="display:flex; align-items:flex-end; gap:.5rem; height:180px; margin-top:1rem;">
                <?php foreach ($byMonth as $row):
                    $h = $maxMonth ? ($row['n'] / $maxMonth) * 100 : 0; ?>
                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:.3rem;">
                        <div style="width:100%; height:<?= $h ?>%; background:var(--primary); border-radius:6px 6px 0 0;"
                             title="<?= (int)$row['n'] ?> sends"></div>
                        <small class="muted"><?= e($row['ym']) ?></small>
                        <strong><?= (int)$row['n'] ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>

    <article class="card">
        <h3>Sends per grad</h3>
        <?php if (!$byGrade): ?>
            <p class="muted">Ingen data ännu.</p>
        <?php else: ?>
            <div style="margin-top:1rem;">
                <?php foreach ($byGrade as $row):
                    $w = $maxGrade ? ($row['n'] / $maxGrade) * 100 : 0; ?>
                    <div style="display:flex; align-items:center; gap:.6rem; margin-bottom:.4rem;">
                        <span style="width:55px;"><strong><?= e($row['grade']) ?></strong></span>
                        <div style="flex:1; background:var(--border); height:18px; border-radius:6px; overflow:hidden;">
                            <div style="width:<?= $w ?>%; height:100%; background:var(--accent);"></div>
                        </div>
                        <span><?= (int)$row['n'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
