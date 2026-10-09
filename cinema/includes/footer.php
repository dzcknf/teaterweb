<?php
/**
 * includes/footer.php
 * Komponen footer yang dipakai di semua halaman.
 */
?>

<!-- ================================================================
     FOOTER
     ================================================================ -->
<footer>
  <div class="footer-inner">
    <div class="footer-brand">CINEMA<span>XXI</span></div>
    <p class="footer-copy">
      &copy; <?= date('Y') ?> <?= SITE_NAME ?>.
      Semua hak cipta dilindungi.
    </p>
  </div>
</footer>

<!-- JavaScript Utama -->
<script src="<?= rtrim(SITE_URL, '/') ?>/assets/js/main.js"></script>
</body>
</html>
