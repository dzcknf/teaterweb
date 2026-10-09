<?php
/**
 * jadwal.php  –  Halaman Jadwal Tayang
 * Menampilkan semua jadwal; bisa difilter per film.
 */

require_once 'config.php';

$pageTitle  = 'Jadwal Tayang';
$activeMenu = 'jadwal';

// ----------------------------------------------------------------
// Ambil film yang punya jadwal ke depan (untuk tombol filter)
// ----------------------------------------------------------------
$sqlFilm = "SELECT DISTINCT f.id, f.judul, f.genre
            FROM film f
            INNER JOIN jadwal j ON j.id_film = f.id
            WHERE j.tanggal >= CURDATE()
            ORDER BY f.judul ASC";
$films = $conn->query($sqlFilm)->fetch_all(MYSQLI_ASSOC);

// ----------------------------------------------------------------
// Filter berdasarkan ?film=ID jika ada
// ----------------------------------------------------------------
$filterFilmId = isset($_GET['film']) ? (int)$_GET['film'] : 0;

// ----------------------------------------------------------------
// Ambil semua jadwal ke depan (termasuk semua film)
// ----------------------------------------------------------------
$sqlJadwal = "SELECT j.*, f.judul, f.genre, f.durasi, f.rating, s.nama_studio
              FROM jadwal j
              INNER JOIN film   f ON f.id = j.id_film
              INNER JOIN studio s ON s.id = j.id_studio
              WHERE j.tanggal >= CURDATE()
              ORDER BY j.tanggal ASC, j.jam_tayang ASC";

$jadwals = $conn->query($sqlJadwal)->fetch_all(MYSQLI_ASSOC);

require_once 'includes/header.php';
?>

<!-- ================================================================
     PAGE HEADER
     ================================================================ -->
<div style="background:var(--dark);padding:36px 5%;border-bottom:1px solid var(--border);">
  <div class="container">
    <h1 class="section-title" style="margin-bottom:4px;">JADWAL <span>TAYANG</span></h1>
    <p style="color:var(--muted);font-size:.9rem;">Pilih film dan jadwal yang kamu inginkan</p>
  </div>
</div>

<!-- ================================================================
     FILTER FILM
     ================================================================ -->
<section class="section" style="padding-bottom:0;">
  <div class="container">
    <p style="font-size:.78rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:14px;">
      Filter Film
    </p>
    <div class="film-selector">
      <!-- Tombol "Semua Film" -->
      <button
        class="film-select-btn <?= $filterFilmId === 0 ? 'active' : '' ?>"
        data-film-id="all">
        🎬 Semua Film
      </button>

      <?php foreach ($films as $f): ?>
        <button
          class="film-select-btn <?= $filterFilmId === (int)$f['id'] ? 'active' : '' ?>"
          data-film-id="<?= (int)$f['id'] ?>">
          <?= htmlspecialchars($f['judul']) ?>
          <div class="fs-genre"><?= htmlspecialchars($f['genre']) ?></div>
        </button>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ================================================================
     TABEL JADWAL
     ================================================================ -->
<section class="section">
  <div class="container">

    <?php if (empty($jadwals)): ?>
      <div class="empty-state">
        <div class="empty-icon">🗓️</div>
        <h3>Tidak Ada Jadwal</h3>
        <p>Belum ada jadwal tayang yang tersedia untuk saat ini.</p>
      </div>
    <?php else: ?>
      <div class="jadwal-wrapper">
        <table class="jadwal-table">
          <thead>
            <tr>
              <th>Film</th>
              <th>Tanggal</th>
              <th>Jam</th>
              <th>Studio</th>
              <th>Durasi</th>
              <th>Rating</th>
              <th>Harga</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($jadwals as $j): ?>
              <!-- data-film-id dipakai oleh JS untuk filter -->
              <tr class="jadwal-row" data-film-id="<?= (int)$j['id_film'] ?>">
                <td>
                  <div style="font-weight:600;color:var(--white);"><?= htmlspecialchars($j['judul']) ?></div>
                  <div style="font-size:.75rem;color:var(--red);letter-spacing:1px;"><?= htmlspecialchars($j['genre']) ?></div>
                </td>
                <td data-tanggal="<?= $j['tanggal'] ?>">
                  <?= date('D, d M Y', strtotime($j['tanggal'])) ?>
                </td>
                <td><span class="time-chip"><?= substr($j['jam_tayang'], 0, 5) ?></span></td>
                <td><?= htmlspecialchars($j['nama_studio']) ?></td>
                <td><?= formatDurasi($j['durasi']) ?></td>
                <td><span class="badge-rating"><?= htmlspecialchars($j['rating']) ?></span></td>
                <td class="price-chip"><?= rupiah($j['harga']) ?></td>
                <td>
                  <a href="pesan.php?jadwal=<?= (int)$j['id'] ?>" class="btn btn-primary btn-sm">
                    🎟️ Pesan
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Keterangan total jadwal -->
      <p style="margin-top:16px;font-size:.82rem;color:var(--muted);">
        Menampilkan <?= count($jadwals) ?> jadwal tersedia
      </p>
    <?php endif; ?>
  </div>
</section>

<!-- Inisialisasi filter berdasarkan parameter URL -->
<?php if ($filterFilmId > 0): ?>
<script>
  // Aktifkan filter film dari URL saat halaman dimuat
  document.addEventListener('DOMContentLoaded', function () {
    const btn = document.querySelector('[data-film-id="<?= $filterFilmId ?>"]');
    if (btn) btn.click();
  });
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
