<?php
require_once __DIR__ . '/includes/auth.php';
$page = 'klubben';
$pageTitle = 'Om klubben';
include __DIR__ . '/includes/header.php';
?>

<h1>Om SkickaUpp</h1>
<p class="muted">En lokal community för dig som älskar att klättra.</p>

<div class="grid mt-3">
    <article class="card">
        <h3>Vår historia</h3>
        <p>SkickaUpp startades av en grupp boulderare som tröttnade på att fylla i pappersloggar
           i hallen. Idag är vi ett digitalt nav för klubbens medlemmar, från första försöket
           på V0 till den där efterlängtade 7c toppningen.</p>
    </article>

    <article class="card">
        <h3>Vad du kan göra här</h3>
        <ul>
            <li>Logga dina sends och se din egen progression.</li>
            <li>Bläddra leder i alla zoner med filter på grad, färg och vägg.</li>
            <li>Anmäl dig till tävlingar, kurser och Friday Send Sessions.</li>
            <li>Tävla med dina vänner på topplistan, månadsvis eller all-time.</li>
        </ul>
    </article>

    <article class="card">
        <h3>Två roller, två vyer</h3>
        <p><strong>Medlemmar</strong> loggar klättring och anmäler sig till event.
           <strong>Moderatorer</strong> (rutsättare och instruktörer) skapar nya leder
           planerar event och håller koll på communityt.</p>
    </article>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
