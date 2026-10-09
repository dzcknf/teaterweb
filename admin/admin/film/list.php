<?php
/**
 * film/list.php  –  Daftar Semua Film
 * Menampilkan tabel film dengan pencarian dan tombol edit/hapus.
 */

require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// ----------------------------------------------------------------
// Pencarian berdasarkan judul (jika ada parameter ?q=)
// ----------------------------------------------------------------
$keyword = trim($_GET['q'] ?? '');

if ($keyword !== '') {
    $stmt = $conn->prepare("SELECT * FROM film WHERE judul LIKE CONCAT('%', ?, '%') ORDER BY id DESC");
    $stmt->bind_param('s', $keyword);
    $stmt->execute();
    $films = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $films = $conn->query("SELECT * FROM film ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
}

$activePage = 'film';
$basePath   = '../';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Film | <?= SITE_NAME ?></title>
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
        <h1>Kelola Film</h1>
      </div>
      <a href="tambah.php" class="btn btn-adm-primary btn-sm">+ Tambah Film</a>
    </div>

    <div class="adm-content">

      <?php showFlashMessage(); ?>

      <div class="adm-card">

        <!-- ---- Search Form ---- -->
        <form method="GET" action="list.php" class="mb-4">
          <div class="input-group" style="max-width:400px;">
            <input
              type="text" name="q"
              class="form-control"
              placeholder="Cari judul film..."
              value="<?= htmlspecialchars($keyword) ?>">
            <button class="btn btn-outline-light" type="submit">🔍 Cari</button>
            <?php if ($keyword !== ''): ?>
              <a href="list.php" class="btn btn-outline-secondary">✕</a>
            <?php endif; ?>
          </div>
        </form>

        <!-- ---- Tabel Film ---- -->
        <?php if (empty($films)): ?>
          <div class="text-center py-5" style="color:var(--adm-muted);">
            <div style="font-size:2.5rem;margin-bottom:10px;">🎭</div>
            <?php if ($keyword !== ''): ?>
              <p>Tidak ada film dengan judul "<?= htmlspecialchars($keyword) ?>"</p>
            <?php else: ?>
              <p>Belum ada film. Klik <strong>+ Tambah Film</strong> untuk mulai.</p>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-dark-custom mb-0">
              <thead>
                <tr>
                  <th>Poster</th>
                  <th>Judul</th>
                  <th>Genre</th>
                  <th>Durasi</th>
                  <th>Rating</th>
                  <th>Tanggal Rilis</th>
                  <th class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($films as $film): ?>
                  <tr>
                    <td>
                      <?php if (!empty($film['poster'])): ?>
                        <img src="<?= POSTER_WEB_PATH . htmlspecialchars($film['poster']) ?>" class="poster-thumb" alt="">
                      <?php else: ?>
                        <div class="poster-thumb-empty">🎬</div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div style="font-weight:600;color:#fff;"><?= htmlspecialchars($film['judul']) ?></div>
                    </td>
                    <td><?= htmlspecialchars($film['genre']) ?></td>
                    <td><?= formatDurasi($film['durasi']) ?></td>
                    <td><span class="badge-rating-adm"><?= htmlspecialchars($film['rating']) ?></span></td>
                    <td><?= date('d M Y', strtotime($film['tanggal_rilis'])) ?></td>
                    <td class="text-end">
                      <a href="edit.php?id=<?= $film['id'] ?>" class="btn btn-sm btn-outline-light">✏️ Edit</a>
                      <a
                        href="hapus.php?id=<?= $film['id'] ?>"
                        class="btn btn-sm btn-outline-danger btn-delete"
                        data-name="<?= htmlspecialchars($film['judul']) ?>">
                        🗑️ Hapus
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <p class="mt-3 mb-0" style="font-size:.8rem;color:var(--adm-muted);">
            Menampilkan <?= count($films) ?> film
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
