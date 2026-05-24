<?php
require_once __DIR__ . '/auth.php';

$theme = $_COOKIE['theme'] ?? 'light';

$page = $page ?? '';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="sv" data-theme="<?= e($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? APP_NAME) ?> | <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container nav">
        <a href="<?= e(APP_URL) ?>/" class="brand">SkickaUpp<span class="dot">.</span></a>

        <button class="nav-toggle" aria-label="Visa meny" aria-expanded="false">&#9776;</button>

        <ul class="nav-links" id="navLinks">
            <li><a href="<?= e(APP_URL) ?>/index.php"            class="<?= $page==='home'?'active':'' ?>">Hem</a></li>
            <li><a href="<?= e(APP_URL) ?>/klubben.php"          class="<?= $page==='klubben'?'active':'' ?>">Klubben</a></li>
            <li><a href="<?= e(APP_URL) ?>/kontakt.php"          class="<?= $page==='kontakt'?'active':'' ?>">Kontakt</a></li>
            <li><a href="<?= e(APP_URL) ?>/events.php"           class="<?= $page==='events'?'active':'' ?>">Kurser &amp; events</a></li>
            <li><a href="<?= e(APP_URL) ?>/topplista.php"        class="<?= $page==='topplista'?'active':'' ?>">Topplista</a></li>

            <?php if ($user): ?>
                <li><a href="<?= e(APP_URL) ?>/user/dashboard.php" class="<?= $page==='dashboard'?'active':'' ?>">
                    Min sida
                    <?php if (is_moderator()): ?>
                        <span class="role-badge mod">mod</span>
                    <?php else: ?>
                        <span class="role-badge">medlem</span>
                    <?php endif; ?>
                </a></li>
                <?php if (is_moderator()): ?>
                    <li><a href="<?= e(APP_URL) ?>/admin/index.php" class="<?= $page==='admin'?'active':'' ?>">Admin</a></li>
                <?php endif; ?>
                <li><a href="<?= e(APP_URL) ?>/auth/logout.php" class="nav-cta">Logga ut</a></li>
            <?php else: ?>
                <li><a href="<?= e(APP_URL) ?>/auth/login.php"    class="<?= $page==='login'?'active':'' ?>">Logga in</a></li>
                <li><a href="<?= e(APP_URL) ?>/auth/register.php" class="nav-cta">Skapa konto</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>

<main>
    <div class="container">
        <?php if ($msg = flash('success')): ?>
            <div class="alert success"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
            <div class="alert error"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('info')): ?>
            <div class="alert info"><?= e($msg) ?></div>
        <?php endif; ?>
