<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_moderator();

$page = 'admin';
$pageTitle = 'Hantera användare';
$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($id === $me['id']) {
        flash('error', 'Du kan inte ändra ditt eget konto härifrån.');
        redirect('/admin/users.php');
    }

    if ($action === 'promote') {
        db()->prepare("UPDATE users SET role = 'moderator' WHERE id = ?")->execute([$id]);
        flash('success', 'Användaren är nu moderator.');
    } elseif ($action === 'demote') {
        db()->prepare("UPDATE users SET role = 'user' WHERE id = ?")->execute([$id]);
        flash('info', 'Användaren är nu medlem.');
    } elseif ($action === 'delete') {
        // todo kolla fk-grejen efter lunchen, känns sus
        db()->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        flash('info', 'Användaren är borttagen.');
    } elseif ($action === 'verify') {
        db()->prepare("UPDATE users SET verified = 1, verify_token = NULL WHERE id = ?")->execute([$id]);
        flash('success', 'Användaren är manuellt verifierad.');
    }
    redirect('/admin/users.php');
}

$users = db()->query(
    "SELECT u.*, (SELECT COUNT(*) FROM sends WHERE user_id = u.id) AS sends
     FROM users u ORDER BY u.created_at DESC"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<p class="muted"><a href="<?= e(APP_URL) ?>/admin/index.php">Tillbaka till adminpanel</a></p>
<h1>Användare &amp; roller</h1>

<table class="table mt-2">
    <thead>
        <tr>
            <th>Användarnamn</th><th>E-post</th><th>Roll</th>
            <th>Verifierad</th><th>Sends</th><th>Skapad</th><th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $u): $self = $u['id'] === $me['id']; ?>
        <tr>
            <td><?= e($u['username']) ?><?= $self ? ' <small class="muted">(du)</small>' : '' ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['role']) ?></td>
            <td><?= $u['verified'] ? 'ja' : 'nej' ?></td>
            <td><?= (int)$u['sends'] ?></td>
            <td><?= e(date('Y-m-d', strtotime($u['created_at']))) ?></td>
            <td>
                <?php if (!$self): ?>
                    <?php if (!$u['verified']): ?>
                        <form method="post" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="verify">
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <button class="btn btn-ghost" style="padding:.3rem .6rem; font-size:.8rem;">Verifiera</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($u['role'] === 'user'): ?>
                        <form method="post" style="display:inline;" data-confirm="Befordra till moderator?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="promote">
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <button class="btn" style="padding:.3rem .6rem; font-size:.8rem;">Befordra</button>
                        </form>
                    <?php else: ?>
                        <form method="post" style="display:inline;" data-confirm="Degradera till vanlig medlem?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="demote">
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <button class="btn btn-ghost" style="padding:.3rem .6rem; font-size:.8rem;">Degradera</button>
                        </form>
                    <?php endif; ?>
                    <form method="post" style="display:inline;" data-confirm="Radera användaren permanent? Detta tar även bort deras sends och anmälningar.">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                        <button class="btn btn-danger" style="padding:.3rem .6rem; font-size:.8rem;">Radera</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>
