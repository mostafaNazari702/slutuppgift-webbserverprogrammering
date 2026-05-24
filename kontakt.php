<?php
require_once __DIR__ . '/includes/helpers.php';

$page = 'kontakt';
$pageTitle = 'Kontakt';
$sent = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $name = clean_str($_POST['name'] ?? '', 80);
    $email = clean_str($_POST['email'] ?? '', 120);
    $message = clean_str($_POST['message'] ?? '', 2000);

    if ($name === '') $errors[] = 'Ange ditt namn.';
    if (!valid_email($email)) $errors[] = 'Ange en giltig mailadress.';
    if (strlen($message) < 10) $errors[] = 'Skriv ett lite längre meddelande (minst 10 tecken)';

    if (!$errors) {
        $log = sprintf("[%s] %s <%s>: %s\n",
            date('Y-m-d H:i:s'), $name, $email, str_replace("\n", ' ', $message));
        file_put_contents(__DIR__ . '/contact-messages.log', $log, FILE_APPEND);
        $sent = true;
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1>Kontakta oss</h1>
<p class="muted">Frågor om medlemskap, kurser eller samarbeten? Skicka ett meddelande.</p>

<div class="grid mt-3" style="grid-template-columns: 1fr 1fr;">
    <div>
        <?php if ($sent): ?>
            <div class="alert success">Tack, vi hör av oss inom ett par dagar.</div>
        <?php endif; ?>
        <?php foreach ($errors as $err): ?>
            <div class="alert error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="post" class="form" style="max-width:none;margin:0;">
            <?= csrf_field() ?>
            <div class="field">
                <label for="name">Namn</label>
                <input type="text" id="name" name="name" required maxlength="80"
                       value="<?= e($_POST['name'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="email">E-post</label>
                <input type="email" id="email" name="email" required maxlength="120"
                       value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="message">Meddelande</label>
                <textarea id="message" name="message" required minlength="10" maxlength="2000"
                ><?= e($_POST['message'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-block">Skicka</button>
        </form>
    </div>

    <aside class="card">
        <h3>Hitta hit</h3>
        <p><strong>SkickaUpp Klätterhall</strong><br>
        Sven Eriksonsgatan 1<br>
        503 38 Borås</p>

        <h3 class="mt-2">Öppettider</h3>
        <p>dag-dag(vardagar) tid-tid(vardagar)<br>(icke-vardagae) tid-tid(icke-vardagar)</p>

        <h3 class="mt-2">Direktkontakt</h3>
        <p>Tel: nummer<br>Mail: mejladress</p>
    </aside>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
