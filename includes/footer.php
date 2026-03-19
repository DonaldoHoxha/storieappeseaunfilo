<?php
// ============================================================
//  footer.php — Piè di pagina comune a tutte le pagine
//  Uso: require_once __DIR__ . '/includes/footer.php';
//  NB: chiude i tag <main> e <body> aperti da header.php
// ============================================================
?>
</main><!-- /.container (aperto in header.php) -->

<footer class="site-footer">
    <div class="footer-inner">
        <p class="footer-logo">🪡 StorieAppeseAUnFilo</p>
        <p class="footer-tagline">Ogni punto racconta una storia.</p>
        <nav class="footer-nav">
            <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
            <a href="#">Chi Sono</a>
            <a href="#">Contatti</a>
        </nav>
        <p class="footer-copy">
            &copy; <?php echo date('Y'); ?> StorieAppeseAUnFilo — Tutti i diritti riservati.
        </p>
    </div>
</footer>

<script src="<?php echo BASE_URL; ?>/assets/js/script.js"></script>
</body>

</html>