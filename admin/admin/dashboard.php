<?php
/**
 * dashboard.php  –  Halaman Utama Setelah Login
 * Menampilkan ringkasan jumlah film, jadwal aktif, total transaksi.
 */

require_once 'includes/config.php';
require_once 'includes/auth_check.php';

// ----------------------------------------------------------------
// Ambil data ringkasan dari database
// ----------------------------------------------------------------

// Total film
$totalFilm = $conn->query("SELECT COUNT(*) AS total FROM film")->fetch_assoc()['total'];

// Jadwal aktif (tanggal >= hari ini)
$totalJadwalAktif = $conn->query("SELECT COUNT(*) AS total FROM jadwal WHERE tanggal >= CURDATE()")->fetch_assoc()['total'];

// Total transaksi
$totalTransaksi = $conn->query("SELECT COUNT(*) AS total FROM transaksi")->fetch_assoc()['total'];

// Total studio
$totalStudio = $conn->query("SELECT COUNT(*) AS total FROM studio")->fetch_assoc()['total'];

// Total pendapatan dari transaksi yang sudah lunas
$totalPendapatan = $conn->query("SELECT COALESCE(SUM(total_harga),0) AS total FROM transaksi WHERE status_pembayaran = 'lunas'")->fetch_assoc()['total'];

// 5 transaksi terbaru untuk ditampilkan di tabel
$transaksiTerbaru = $conn->query(
    "SELECT t.id, p.nama, f.judul, t.total_harga, t.status_pembayaran, t.waktu_pesan
     FROM transaksi t
     JOIN pelanggan p ON p.id = t.id_pelanggan
     JOIN jadwal j ON j.id = t.id_jadwal
     JOIN film f ON f.id = j.id_film
     ORDER BY t.waktu_pesan DESC
     LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);

$activePage = 'dashboard';
$basePath   = '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | <?= SITE_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>

<div class="adm-wrapper">

  <?php require_once 'includes/sidebar.php'; ?>

  <main class="adm-main">

    <!-- Topbar -->
    <div class="adm-topbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-outline-light d-md-none" id="sidebarToggle">☰</button>
        <h1>Dashboard</h1>
      </div>
      <div class="adm-user-chip">
        <div class="av"><?= strtoupper(substr($_SESSION['admin_nama'] ?? 'A', 0, 1)) ?></div>
        <span><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin') ?></span>
      </div>
    </div>

    <div class="adm-content">

      <?php showFlashMessage(); ?>

      <!-- ============================================================
           STAT CARDS
           ============================================================ -->
      <div class="row g-3 mb-4">

        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-icon bg-film">🎬</div>
            <div>
              <div class="stat-value"><?= $totalFilm ?></div>
              <div class="stat-label">Total Film</div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-icon bg-jadwal">🗓️</div>
            <div>
              <div class="stat-value"><?= $totalJadwalAktif ?></div>
              <div class="stat-label">Jadwal Aktif</div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-icon bg-trx">🎟️</div>
            <div>
              <div class="stat-value"><?= $totalTransaksi ?></div>
              <div class="stat-label">Total Transaksi</div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-icon bg-studio">🏢</div>
            <div>
              <div class="stat-value"><?= $totalStudio ?></div>
              <div class="stat-label">Total Studio</div>
            </div>
          </div>
        </div>

      </div>

      <!-- ============================================================
           PENDAPATAN + QUICK ACTIONS
           ============================================================ -->
      <div class="row g-3 mb-4">
        <div class="col-lg-4">
          <div class="adm-card h-100">
            <div class="adm-card-sub">Total Pendapatan (Lunas)</div>
            <div style="font-size:1.8rem;font-weight:800;color:var(--adm-gold);">
              <?= rupiah($totalPendapatan) ?>
            </div>
          </div>
        </div>

        <div class="col-lg-8">
          <div class="adm-card h-100">
            <div class="adm-card-sub mb-3">Aksi Cepat</div>
            <div class="d-flex gap-2 flex-wrap">
              <a href="film/tambah.php" class="btn btn-adm-primary btn-sm">+ Tambah Film</a>
              <a href="jadwal/tambah.php" class="btn btn-adm-gold btn-sm">+ Tambah Jadwal</a>
              <a href="film/list.php" class="btn btn-outline-light btn-sm">Kelola Film</a>
              <a href="jadwal/list.php" class="btn btn-outline-light btn-sm">Kelola Jadwal</a>
            </div>
          </div>
        </div>
      </div>

      <!-- ============================================================
           TRANSAKSI TERBARU
           ============================================================ -->
      <div class="adm-card">
        <div class="adm-card-title">Transaksi Terbaru</div>
        <div class="adm-card-sub">5 transaksi terakhir yang masuk</div>

        <?php if (empty($transaksiTerbaru)): ?>
          <p style="color:var(--adm-muted);">Belum ada transaksi.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-dark-custom mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Pelanggan</th>
                  <th>Film</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Waktu</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($transaksiTerbaru as $t): ?>
                  <tr>
                    <td>#<?= $t['id'] ?></td>
                    <td><?= htmlspecialchars($t['nama']) ?></td>
                    <td><?= htmlspecialchars($t['judul']) ?></td>
                    <td><?= rupiah($t['total_harga']) ?></td>
                    <td>
                      <?php
                        $badgeMap = ['lunas' => 'success', 'pending' => 'warning', 'dibatalkan' => 'danger'];
                        $badge = $badgeMap[$t['status_pembayaran']] ?? 'secondary';
                      ?>
                      <span class="badge bg-<?= $badge ?>"><?= ucfirst($t['status_pembayaran']) ?></span>
                    </td>
                    <td><?= date('d M Y, H:i', strtotime($t['waktu_pesan'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div><!-- end adm-content -->
  </main>
</div><!-- end adm-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/admin.js"></script>
</body>
</html>
