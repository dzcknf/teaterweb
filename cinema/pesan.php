<?php
/**
 * pesan.php  –  Halaman Pemesanan Tiket
 * Form isi data pelanggan + pilih kursi + konfirmasi.
 *
 * Alur:
 *   GET  pesan.php?jadwal=ID  → tampilkan form
 *   POST pesan.php?jadwal=ID  → proses pesanan, redirect ke konfirmasi.php
 */

require_once 'config.php';

// ----------------------------------------------------------------
// 1. Validasi jadwal
// ----------------------------------------------------------------
$jadwalId = isset($_GET['jadwal']) ? (int)$_GET['jadwal'] : 0;

if ($jadwalId <= 0) {
    header('Location: jadwal.php');
    exit;
}

// Ambil detail jadwal + film + studio
$stmt = $conn->prepare(
    "SELECT j.*, f.judul, f.genre, f.durasi, f.rating, s.nama_studio, s.id AS studio_id
     FROM jadwal j
     INNER JOIN film   f ON f.id = j.id_film
     INNER JOIN studio s ON s.id = j.id_studio
     WHERE j.id = ?"
);
$stmt->bind_param('i', $jadwalId);
$stmt->execute();
$jadwal = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$jadwal) {
    header('Location: jadwal.php');
    exit;
}

// ----------------------------------------------------------------
// 2. Ambil semua kursi di studio ini
// ----------------------------------------------------------------
$stmtK = $conn->prepare(
    "SELECT * FROM kursi WHERE id_studio = ? ORDER BY baris ASC, nomor_kursi ASC"
);
$stmtK->bind_param('i', $jadwal['studio_id']);
$stmtK->execute();
$allKursi = $stmtK->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtK->close();

// ----------------------------------------------------------------
// 3. Ambil kursi yang SUDAH dipesan untuk jadwal ini
// ----------------------------------------------------------------
$stmtT = $conn->prepare(
    "SELECT id_kursi FROM transaksi
     WHERE id_jadwal = ? AND status_pembayaran != 'dibatalkan'"
);
$stmtT->bind_param('i', $jadwalId);
$stmtT->execute();
$takenRows  = $stmtT->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtT->close();
$takenIds = array_column($takenRows, 'id_kursi');

// Kelompokkan kursi per baris
$kursiPerBaris = [];
foreach ($allKursi as $k) {
    $kursiPerBaris[$k['baris']][] = $k;
}

// ----------------------------------------------------------------
// 4. Proses POST (simpan transaksi)
// ----------------------------------------------------------------
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitasi input
    $nama     = esc($conn, $_POST['nama']     ?? '');
    $email    = esc($conn, $_POST['email']    ?? '');
    $telepon  = esc($conn, $_POST['telepon']  ?? '');
    $kursiId  = (int)($_POST['id_kursi']      ?? 0);
    $total    = (float)$jadwal['harga'];

    // Validasi
    if (!$nama)              $errors[] = 'Nama wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (!$telepon)           $errors[] = 'Nomor telepon wajib diisi.';
    if ($kursiId <= 0)       $errors[] = 'Pilih kursi terlebih dahulu.';

    // Cek kursi tidak sudah dipesan (double-check server-side)
    if (in_array($kursiId, $takenIds)) {
        $errors[] = 'Kursi yang kamu pilih sudah dipesan orang lain. Silakan pilih kursi lain.';
    }

    if (empty($errors)) {

        // ----- Cari atau buat pelanggan -----
        $stmtP = $conn->prepare("SELECT id FROM pelanggan WHERE email = ?");
        $stmtP->bind_param('s', $email);
        $stmtP->execute();
        $existingPel = $stmtP->get_result()->fetch_assoc();
        $stmtP->close();

        if ($existingPel) {
            $pelangganId = $existingPel['id'];
            // Update nama & telepon kalau berubah
            $stmtUp = $conn->prepare("UPDATE pelanggan SET nama=?, no_telepon=? WHERE id=?");
            $stmtUp->bind_param('ssi', $nama, $telepon, $pelangganId);
            $stmtUp->execute();
            $stmtUp->close();
        } else {
            $stmtIns = $conn->prepare("INSERT INTO pelanggan (nama, email, no_telepon) VALUES (?,?,?)");
            $stmtIns->bind_param('sss', $nama, $email, $telepon);
            $stmtIns->execute();
            $pelangganId = $conn->insert_id;
            $stmtIns->close();
        }

        // ----- Simpan transaksi -----
        $stmtTrx = $conn->prepare(
            "INSERT INTO transaksi (id_pelanggan, id_jadwal, id_kursi, total_harga, waktu_pesan, status_pembayaran)
             VALUES (?, ?, ?, ?, NOW(), 'pending')"
        );
        $stmtTrx->bind_param('iiid', $pelangganId, $jadwalId, $kursiId, $total);
        $stmtTrx->execute();
        $trxId = $conn->insert_id;
        $stmtTrx->close();

        // Redirect ke halaman konfirmasi
        header('Location: konfirmasi.php?id=' . $trxId);
        exit;
    }
}

// ----------------------------------------------------------------
// Nilai yang dipertahankan jika ada error
// ----------------------------------------------------------------
$oldNama    = htmlspecialchars($_POST['nama']    ?? '');
$oldEmail   = htmlspecialchars($_POST['email']   ?? '');
$oldTelepon = htmlspecialchars($_POST['telepon'] ?? '');

$pageTitle  = 'Pesan Tiket – ' . $jadwal['judul'];
$activeMenu = 'jadwal';

require_once 'includes/header.php';
?>

<!-- ================================================================
     BOOKING LAYOUT
     ================================================================ -->
<div class="booking-layout">

  <!-- ============================================================
       KOLOM KIRI: Form + Seat Map
       ============================================================ -->
  <div>

    <!-- Breadcrumb -->
    <div style="display:flex;gap:8px;align-items:center;margin-bottom:24px;font-size:.82rem;color:var(--muted);">
      <a href="jadwal.php">Jadwal</a>
      <span>›</span>
      <a href="detail.php?id=<?= (int)$jadwal['id_film'] ?>"><?= htmlspecialchars($jadwal['judul']) ?></a>
      <span>›</span>
      <span style="color:var(--white);">Pesan Tiket</span>
    </div>

    <!-- Alert Error -->
    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        ⚠️ <?= implode(' &bull; ', $errors) ?>
      </div>
    <?php endif; ?>

    <!-- ---- Form Data Penonton ---- -->
    <form id="bookingForm" method="POST" action="pesan.php?jadwal=<?= $jadwalId ?>">

      <div class="form-card" style="margin-bottom:24px;">
        <div class="form-title">DATA PENONTON</div>

        <div class="form-group">
          <label class="form-label" for="nama">Nama Lengkap</label>
          <input
            id="nama" name="nama" type="text"
            class="form-control"
            placeholder="Contoh: Budi Santoso"
            value="<?= $oldNama ?>"
            required>
        </div>

        <div class="form-group">
          <label class="form-label" for="email">Alamat Email</label>
          <input
            id="email" name="email" type="email"
            class="form-control"
            placeholder="email@kamu.com"
            value="<?= $oldEmail ?>"
            required>
          <small style="color:var(--muted);font-size:.78rem;">
            Bukti tiket akan dikirim ke email ini.
          </small>
        </div>

        <div class="form-group">
          <label class="form-label" for="telepon">Nomor Telepon</label>
          <input
            id="telepon" name="telepon" type="tel"
            class="form-control"
            placeholder="08xxxxxxxxxx"
            value="<?= $oldTelepon ?>">
        </div>
      </div>

      <!-- ---- Seat Map ---- -->
      <div class="seat-map-wrap" style="margin-bottom:24px;">
        <div class="form-title" style="margin-bottom:16px;">PILIH KURSI</div>

        <div class="screen-bar"></div>
        <div class="screen-label">LAYAR</div>

        <!-- Legenda -->
        <div class="seat-legend">
          <span><span class="dot dot-free"></span> Tersedia</span>
          <span><span class="dot dot-taken"></span> Terisi</span>
          <span><span class="dot dot-selected"></span> Dipilih</span>
        </div>

        <!-- Baris kursi -->
        <?php foreach ($kursiPerBaris as $baris => $kursiList): ?>
          <div class="seat-row">
            <div class="row-label"><?= htmlspecialchars($baris) ?></div>
            <div class="seats">
              <?php foreach ($kursiList as $k):
                $isTaken = in_array($k['id'], $takenIds);
              ?>
                <div
                  class="seat <?= $isTaken ? 'taken' : '' ?>"
                  <?= !$isTaken ? 'data-id="' . $k['id'] . '" data-label="' . htmlspecialchars($k['nomor_kursi']) . '"' : '' ?>
                  title="<?= htmlspecialchars($k['nomor_kursi']) ?><?= $isTaken ? ' (Terisi)' : '' ?>">
                  <?= htmlspecialchars($k['nomor_kursi']) ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <!-- Input tersembunyi yang diisi oleh JS -->
        <input type="hidden" id="id_kursi"   name="id_kursi">
        <input type="hidden" id="label_kursi" name="label_kursi">
        <input type="hidden" id="total_harga" name="total_harga" value="0">

        <!-- Harga jadwal untuk dihitung JS -->
        <input type="hidden" id="harga_jadwal" value="<?= $jadwal['harga'] ?>">
      </div>

      <!-- Tombol submit – ada di sidebar sebelah kanan juga (JS scroll) -->
      <button type="submit" class="btn btn-primary btn-block" style="display:none;" id="submitBtn">
        ✅ Konfirmasi Pemesanan
      </button>
    </form>
  </div>

  <!-- ============================================================
       KOLOM KANAN: Ringkasan Pesanan
       ============================================================ -->
  <div>
    <div class="summary-card">
      <div class="summary-title">RINGKASAN</div>

      <div class="summary-row">
        <span>Film</span>
        <span class="val"><?= htmlspecialchars($jadwal['judul']) ?></span>
      </div>
      <div class="summary-row">
        <span>Genre</span>
        <span class="val"><?= htmlspecialchars($jadwal['genre']) ?></span>
      </div>
      <div class="summary-row">
        <span>Tanggal</span>
        <span class="val"><?= date('d M Y', strtotime($jadwal['tanggal'])) ?></span>
      </div>
      <div class="summary-row">
        <span>Jam Tayang</span>
        <span class="val"><?= substr($jadwal['jam_tayang'], 0, 5) ?> WIB</span>
      </div>
      <div class="summary-row">
        <span>Studio</span>
        <span class="val"><?= htmlspecialchars($jadwal['nama_studio']) ?></span>
      </div>
      <div class="summary-row">
        <span>Kursi</span>
        <span class="val" id="display_kursi" style="color:var(--gold);">Belum dipilih</span>
      </div>

      <div class="summary-total">
        <span class="label">Total</span>
        <span class="amount" id="total_display">–</span>
      </div>

      <div style="margin-top:20px;">
        <button
          type="submit"
          form="bookingForm"
          class="btn btn-primary btn-block">
          ✅ Konfirmasi Pesanan
        </button>
        <a href="jadwal.php" class="btn btn-outline btn-block" style="margin-top:10px;">
          ← Batal
        </a>
      </div>
    </div>
  </div>

</div><!-- end booking-layout -->

<?php require_once 'includes/footer.php'; ?>
