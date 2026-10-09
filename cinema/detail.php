<?php
/**
 * detail.php  –  Halaman Detail Film
 * Menampilkan poster, judul, genre, durasi, rating, deskripsi,
 * dan jadwal tayang ke depan untuk film yang dipilih.
 */

require_once 'config.php';

// ----------------------------------------------------------------
// Validasi & ambil ID film dari URL (?id=X)
// ----------------------------------------------------------------
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// Ambil data film
$stmt = $conn->prepare("SELECT * FROM film WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$film = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Jika film tidak ditemukan, kembali ke beranda
if (!$film) {
    header('Location: index.php');
    exit;
}

// ----------------------------------------------------------------
// Ambil jadwal ke depan untuk film ini
// ----------------------------------------------------------------
$stmtJ = $conn->prepare(
    "SELECT j.*, s.nama_studio, s.kapasitas
     FROM jadwal j
     INNER JOIN studio s ON s.id = j.id_studio
     WHERE j.id_film = ?
       AND j.tanggal >= CURDATE()
     ORDER BY j.tanggal ASC, j.jam_tayang ASC"
);
$stmtJ->bind_param('i', $id);
$stmtJ->execute();
$jadwals = $stmtJ->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtJ->close();

$pageTitle  = $film['judul'];
$activeMenu = 'home';

require_once 'includes/header.php';
?>

<!-- ================================================================
     DETAIL FILM HERO
     ================================================================ -->
<div class="detail-hero">

  <!-- Poster -->
  <div class="detail-poster">
    <div class="film-poster-placeholder" style="aspect-ratio:2/3;">
      <div class="icon">🎬</div>
      <div class="poster-title"><?= htmlspecialchars($film['judul']) ?></div>
      <div style="color:var(--gold);font-size:.75rem;margin-top:4px;">
        <?= htmlspecialchars($film['genre']) ?>
      </div>
    </div>
  </div>

  <!-- Info Detail -->
  <div class="detail-info">
    <div class="detail-genre"><?= htmlspecialchars($film['genre']) ?></div>
    <h1 class="detail-title"><?= htmlspecialchars($film['judul']) ?></h1>

    <!-- Meta data -->
    <div class="detail-metas">
      <div class="detail-meta-item">
        <span class="detail-meta-label">Durasi</span>
        <span class="detail-meta-value">⏱ <?= formatDurasi($film['durasi']) ?></span>
      </div>
      <div class="detail-meta-item">
        <span class="detail-meta-label">Rating</span>
        <span class="detail-meta-value"><span class="badge-rating"><?= htmlspecialchars($film['rating']) ?></span></span>
      </div>
      <div class="detail-meta-item">
        <span class="detail-meta-label">Tanggal Rilis</span>
        <span class="detail-meta-value">📅 <?= date('d F Y', strtotime($film['tanggal_rilis'])) ?></span>
      </div>
    </div>

    <!-- Deskripsi -->
    <p class="detail-desc"><?= nl2br(htmlspecialchars($film['deskripsi'] ?? '–')) ?></p>

    <!-- Tombol aksi -->
    <div class="hero-cta">
      <a href="jadwal.php?film=<?= $id ?>" class="btn btn-primary">🎟️ Pilih Jadwal & Pesan</a>
      <a href="index.php" class="btn btn-outline">← Kembali</a>
    </div>
  </div>
</div>

<!-- ================================================================
     JADWAL TAYANG
     ================================================================ -->
<section class="section" style="padding-top:0;">
  <div class="container">
    <h2 class="section-title">JADWAL <span>TAYANG</span></h2>
    <div class="divider"></div>

    <?php if (empty($jadwals)): ?>
      <div class="empty-state">
        <div class="empty-icon">📅</div>
        <h3>Belum Ada Jadwal</h3>
        <p>Jadwal untuk film ini belum tersedia. Silakan cek lagi nanti.</p>
      </div>
    <?php else: ?>
      <div class="jadwal-wrapper">
        <table class="jadwal-table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Jam Tayang</th>
              <th>Studio</th>
              <th>Kapasitas</th>
              <th>Harga</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($jadwals as $jadwal): ?>
              <tr>
                <td data-tanggal="<?= $jadwal['tanggal'] ?>">
                  <?= date('D, d M Y', strtotime($jadwal['tanggal'])) ?>
                </td>
                <td><span class="time-chip"><?= substr($jadwal['jam_tayang'], 0, 5) ?></span></td>
                <td><?= htmlspecialchars($jadwal['nama_studio']) ?></td>
                <td><?= (int)$jadwal['kapasitas'] ?> kursi</td>
                <td class="price-chip"><?= rupiah($jadwal['harga']) ?></td>
                <td>
                  <a href="pesan.php?jadwal=<?= (int)$jadwal['id'] ?>" class="btn btn-primary btn-sm">
                    Pesan
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
