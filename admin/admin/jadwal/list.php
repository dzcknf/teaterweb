<?php
/**
 * jadwal/list.php  –  Daftar Semua Jadwal Tayang
 */

require_once '../includes/config.php';
require_once '../includes/auth_check.php';

$jadwals = $conn->query(
    "SELECT j.*, f.judul, s.nama_studio
     FROM jadwal j
     JOIN film f ON f.id = j.id_film
     JOIN studio s ON s.id = j.id_studio
     ORDER BY j.tanggal DESC, j.jam_tayang DESC"
)->fetch_all(MYSQLI_ASSOC);

$activePage = 'jadwal';
$basePath   = '../';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Jadwal | <?= SITE_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="adm-wrapper">

  <?php require_once '../includes/sidebar.php'; ?>

  <main class="adm-main">

    <div class="adm-topbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-outline-light d-md-none" id="sidebarToggle">☰</button>
        <h1>Kelola Jadwal Tayang</h1>
      </div>
      <a href="tambah.php" class="btn btn-adm-primary btn-sm">+ Tambah Jadwal</a>
    </div>

    <div class="adm-content">

      <?php showFlashMessage(); ?>

      <div class="adm-card">
        <?php if (empty($jadwals)): ?>
          <div class="text-center py-5" style="color:var(--adm-muted);">
            <div style="font-size:2.5rem;margin-bottom:10px;">🗓️</div>
            <p>Belum ada jadwal. Klik <strong>+ Tambah Jadwal</strong> untuk mulai.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-dark-custom mb-0">
              <thead>
                <tr>
                  <th>Film</th>
                  <th>Studio</th>
                  <th>Tanggal</th>
                  <th>Jam</th>
                  <th>Harga</th>
                  <th class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($jadwals as $j): ?>
                  <tr>
                    <td style="font-weight:600;color:#fff;"><?= htmlspecialchars($j['judul']) ?></td>
                    <td><?= htmlspecialchars($j['nama_studio']) ?></td>
                    <td><?= date('d M Y', strtotime($j['tanggal'])) ?></td>
                    <td><?= substr($j['jam_tayang'], 0, 5) ?></td>
                    <td><?= rupiah($j['harga']) ?></td>
                    <td class="text-end">
                      <a href="edit.php?id=<?= $j['id'] ?>" class="btn btn-sm btn-outline-light">✏️ Edit</a>
                      <a
                        href="hapus.php?id=<?= $j['id'] ?>"
                        class="btn btn-sm btn-outline-danger btn-delete"
                        data-name="jadwal <?= htmlspecialchars($j['judul']) ?> (<?= date('d M Y', strtotime($j['tanggal'])) ?>)">
                        🗑️ Hapus
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <p class="mt-3 mb-0" style="font-size:.8rem;color:var(--adm-muted);">
            Menampilkan <?= count($jadwals) ?> jadwal
          </p>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/admin.js"></script>
</body>
</html>
