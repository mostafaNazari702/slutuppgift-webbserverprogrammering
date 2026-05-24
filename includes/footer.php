    </div>
</main>

<footer class="site-footer">
    <div class="container">
        &copy; <?= date('Y') ?> <?= e(APP_NAME) ?>, klättercommunity.
        Serverns tid: <?= date('Y-m-d H:i') ?>
    </div>
</footer>

<?php if (!isset($_COOKIE['cookie_consent'])): ?>
<div class="cookie-banner" id="cookieBanner">
    <p>
        Vi använder cookies för att komma ihåg ditt tema, ditt val av gradsystem
        och att hålla dig inloggad. Inga spårningscookies, ingen tredjepart.
    </p>
    <button class="btn" id="acceptCookies">OK, jag förstår</button>
</div>
<?php endif; ?>

<script src="<?= e(APP_URL) ?>/assets/js/main.js"></script>
</body>
</html>
