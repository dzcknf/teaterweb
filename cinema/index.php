<?php
/**
 * index.php  –  Halaman Utama
 * Menampilkan daftar film yang sedang tayang diambil dari database.
 */

require_once 'config.php';

$pageTitle  = 'Beranda';
$activeMenu = 'home';

// ----------------------------------------------------------------
// Ambil semua film yang memiliki jadwal hari ini atau ke depan
// Diurutkan berdasarkan tanggal rilis terbaru
// ----------------------------------------------------------------
$sql = "SELECT DISTINCT f.*
        FROM film f
        INNER JOIN jadwal j ON j.id_film = f.id
        WHERE j.tanggal >= CURDATE()
        ORDER BY f.tanggal_rilis DESC";

$result = $conn->query($sql);
$films  = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// ----------------------------------------------------------------
// Ambil film yang tidak ada jadwal ke depan (sudah lewat)
// Tampilkan sebagai "Akan Datang" jika tanggal rilis belum lewat
// ----------------------------------------------------------------
$sqlUpcoming = "SELECT * FROM film
                WHERE id NOT IN (
                    SELECT DISTINCT id_film FROM jadwal WHERE tanggal >= CURDATE()
                )
                ORDER BY tanggal_rilis DESC
                LIMIT 4";
$resultUpcoming = $conn->query($sqlUpcoming);
$upcoming = $resultUpcoming ? $resultUpcoming->fetch_all(MYSQLI_ASSOC) : [];

require_once 'includes/header.php';
?>

<!-- ================================================================
     HERO BANNER
     ================================================================ -->
<section class="hero">
  <div class="hero-content">
    <p class="hero-eyebrow">Pengalaman Bioskop Terbaik</p>
    <h1 class="hero-title">
      NONTON<br>DENGAN <span class="accent">GAYA</span>
    </h1>
    <p class="hero-desc">
      Pilih film favoritmu, pesan kursi terbaik, dan nikmati pengalaman sinema yang tak terlupakan.
    </p>
    <div class="hero-cta">
      <a href="jadwal.php" class="btn btn-primary">🎬 Lihat Jadwal</a>
      <a href="#sedang-tayang" class="btn btn-outline">Daftar Film</a>
    </div>
  </div>
</section>

<!-- ================================================================
     SEDANG TAYANG
     ================================================================ -->
<section class="section" id="sedang-tayang">
  <div class="container">
    <h2 class="section-title">SEDANG <span>TAYANG</span></h2>
    <div class="divider"></div>
    <p class="section-sub">Film-film yang kini bisa kamu tonton di bioskop kami</p>

    <?php if (empty($films)): ?>
      <!-- Tampilan jika tidak ada film -->
      <div class="empty-state">
        <div class="empty-icon">🎭</div>
        <h3>Belum Ada Film</h3>
        <p>Jadwal film akan segera diperbarui. Nantikan!</p>
      </div>
    <?php else: ?>
      <div class="film-grid">
        <?php foreach ($films as $film): ?>
          <!-- Kartu Film -->
          <a href="detail.php?id=<?= (int)$film['id'] ?>" class="film-card" style="display:block;color:inherit;">
            <!-- Poster (gunakan placeholder karena tidak ada upload gambar) -->
            <div class="film-poster-placeholder">
              <div class="icon">🎬</div>
              <div class="poster-title"><?= htmlspecialchars($film['judul']) ?></div>
            </div>

            <!-- Info Film -->
            <div class="film-info">
              <div class="film-genre"><?= htmlspecialchars($film['genre']) ?></div>
              <div class="film-title"><?= htmlspecialchars($film['judul']) ?></div>
              <div class="film-meta">
                <span>⏱ <?= formatDurasi($film['durasi']) ?></span>
                <span><span class="badge-rating"><?= htmlspecialchars($film['rating']) ?></span></span>
              </div>
              <a href="jadwal.php?film=<?= (int)$film['id'] ?>" class="btn btn-primary btn-sm btn-block">
                Pesan Tiket
              </a>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php if (!empty($upcoming)): ?>
<!-- ================================================================
     AKAN DATANG
     ================================================================ -->
<section class="section" style="background:var(--dark);">
  <div class="container">
    <h2 class="section-title">AKAN <span>DATANG</span></h2>
    <div class="divider"></div>
    <p class="section-sub">Film-film yang akan segera hadir untuk kamu</p>

    <div class="film-grid">
      <?php foreach ($upcoming as $film): ?>
        <div class="film-card">
          <div class="film-poster-placeholder">
            <div class="icon">📽️</div>
            <div class="poster-title"><?= htmlspecialchars($film['judul']) ?></div>
            <div style="color:var(--gold);font-size:.7rem;margin-top:4px;">
              <?= date('d M Y', strtotime($film['tanggal_rilis'])) ?>
            </div>
          </div>
          <div class="film-info">
            <div class="film-genre"><?= htmlspecialchars($film['genre']) ?></div>
            <div class="film-title"><?= htmlspecialchars($film['judul']) ?></div>
            <div class="film-meta">
              <span>⏱ <?= formatDurasi($film['durasi']) ?></span>
              <span><span class="badge-rating"><?= htmlspecialchars($film['rating']) ?></span></span>
            </div>
            <a href="detail.php?id=<?= (int)$film['id'] ?>" class="btn btn-outline btn-sm btn-block">
              Detail Film
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
