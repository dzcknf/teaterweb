<?php
/**
 * includes/header.php
 * Komponen header yang dipakai di semua halaman.
 * Panggil dengan: require_once 'includes/header.php';
 *
 * Variabel opsional yang bisa disetel SEBELUM include:
 *   $pageTitle  – judul tab browser (default: SITE_NAME)
 *   $activeMenu – nama menu aktif: 'home', 'jadwal', dll.
 */

if (!defined('DB_HOST')) {
    // Auto-detect path ke config (2 level ke atas kalau di dalam includes/)
    $configPath = __DIR__ . '/../config.php';
    if (file_exists($configPath)) require_once $configPath;
}

$pageTitle  = $pageTitle  ?? SITE_NAME;
$activeMenu = $activeMenu ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> | <?= SITE_NAME ?></title>
  <meta name="description" content="Pesan tiket bioskop online dengan mudah di <?= SITE_NAME ?>">

  <!-- CSS Utama -->
  <link rel="stylesheet" href="<?= rtrim(SITE_URL, '/') ?>/assets/css/style.css">
</head>
<body>

<!-- ================================================================
     NAVBAR
     ================================================================ -->
<nav class="navbar">
  <!-- Logo -->
  <a href="<?= rtrim(SITE_URL, '/') ?>/index.php" class="navbar-brand">
    CINEMA<span>XXI</span>
  </a>

  <!-- Link navigasi -->
  <ul class="navbar-links" id="navLinks">
    <li><a href="index.php"  class="<?= $activeMenu === 'home'   ? 'active' : '' ?>">Beranda</a></li>
    <li><a href="jadwal.php" class="<?= $activeMenu === 'jadwal' ? 'active' : '' ?>">Jadwal</a></li>
  </ul>

  <!-- Hamburger (mobile) -->
  <button class="hamburger" id="hamburger" aria-label="Toggle menu">
    <span></span><span></span><span></span>
  </button>
</nav>
