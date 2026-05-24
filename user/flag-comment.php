<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/user/routes.php');
}
csrf_check();

$cid = (int)($_POST['comment_id'] ?? 0);
$back = (int)($_POST['back'] ?? 0);

if ($cid > 0) {
    $up = db()->prepare("UPDATE comments SET flagged = 1 WHERE id = ?");
    $up->execute([$cid]);
    flash('info', 'Kommentaren är rapporterad till moderatorerna.');
}

redirect('/user/route.php?id=' . $back);
