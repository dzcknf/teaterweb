<?php
/**
 * includes/sidebar.php
 * Komponen sidebar navigasi, dipakai di semua halaman admin setelah login.
 * Variabel yang bisa disetel sebelum include:
 *   $activePage – 'dashboard', 'film', 'jadwal'
 *   $basePath   – '' jika di root admin/, '../' jika di dalam subfolder
 */

$activePage = $activePage ?? '';
$basePath   = $basePath ?? '';
?>
<aside class="adm-sidebar" id="admSidebar">
  <div class="adm-sidebar-brand">
    CINEMA<span>XXI</span>
    <small>ADMIN PANEL</small>
  </div>

  <nav class="adm-nav">
    <div class="adm-nav-label">Menu Utama</div>

    <a href="<?= $basePath ?>dashboard.php" class="adm-nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
      <span class="icon">📊</span> Dashboard
    </a>

    <div class="adm-nav-label">Kelola Data</div>

    <a href="<?= $basePath ?>film/list.php" class="adm-nav-link <?= $activePage === 'film' ? 'active' : '' ?>">
      <span class="icon">🎬</span> Film
    </a>

    <a href="<?= $basePath ?>jadwal/list.php" class="adm-nav-link <?= $activePage === 'jadwal' ? 'active' : '' ?>">
      <span class="icon">🗓️</span> Jadwal Tayang
    </a>

    <div class="adm-nav-label">Akun</div>

    <a href="<?= $basePath ?>logout.php" class="adm-nav-link">
      <span class="icon">🚪</span> Logout
    </a>
  </nav>
</aside>
