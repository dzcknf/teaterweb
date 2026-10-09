<?php
/**
 * film/tambah.php  –  Form Tambah Film Baru
 * Termasuk upload poster dengan validasi tipe & ukuran file.
 */

require_once '../includes/config.php';
require_once '../includes/auth_check.php';

$errors = [];
$old = [
    'judul'         => '',
    'genre'         => '',
    'durasi'        => '',
    'rating'        => 'SU',
    'deskripsi'     => '',
    'tanggal_rilis' => '',
];

// Daftar rating yang valid (sesuai ENUM di database)
$ratingOptions = ['G','PG','PG-13','R','NC-17','SU','R13','R17','R21','D17'];

// ----------------------------------------------------------------
// Proses form (POST)
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old['judul']         = trim($_POST['judul'] ?? '');
    $old['genre']         = trim($_POST['genre'] ?? '');
    $old['durasi']        = trim($_POST['durasi'] ?? '');
    $old['rating']        = trim($_POST['rating'] ?? 'SU');
    $old['deskripsi']     = trim($_POST['deskripsi'] ?? '');
    $old['tanggal_rilis'] = trim($_POST['tanggal_rilis'] ?? '');

    // ---- Validasi server-side ----
    if ($old['judul'] === '') {
        $errors[] = 'Judul film wajib diisi.';
    }
    if ($old['genre'] === '') {
        $errors[] = 'Genre wajib diisi.';
    }
    if ($old['durasi'] === '' || !is_numeric($old['durasi']) || (int)$old['durasi'] <= 0) {
        $errors[] = 'Durasi harus berupa angka lebih dari 0.';
    }
    if (!in_array($old['rating'], $ratingOptions, true)) {
        $errors[] = 'Rating tidak valid.';
    }
    if ($old['tanggal_rilis'] === '' || !strtotime($old['tanggal_rilis'])) {
        $errors[] = 'Tanggal rilis wajib diisi dengan format yang benar.';
    }

    // ---- Validasi & proses upload poster ----
    $posterFilename = null;

    if (!empty($_FILES['poster']['name'])) {
        $file = $_FILES['poster'];

        // Cek error upload dasar
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Terjadi kesalahan saat upload poster.';
        } else {
            // Validasi tipe file (cek MIME asli, bukan hanya ekstensi)
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/jpg' => 'jpg'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!array_key_exists($mimeType, $allowedTypes)) {
                $errors[] = 'Poster harus berformat JPG atau PNG.';
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $errors[] = 'Ukuran poster maksimal 2MB.';
            } else {
                // Generate nama file unik agar tidak bentrok
                $ext = $allowedTypes[$mimeType];
                $posterFilename = 'film_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            }
        }
    }

    // ---- Jika tidak ada error, simpan ke database ----
    if (empty($errors)) {

        // Pastikan folder upload ada
        if (!is_dir(POSTER_UPLOAD_DIR)) {
            mkdir(POSTER_UPLOAD_DIR, 0755, true);
        }

        // Pindahkan file upload jika ada poster
        if ($posterFilename) {
            $targetPath = POSTER_UPLOAD_DIR . $posterFilename;
            if (!move_uploaded_file($_FILES['poster']['tmp_name'], $targetPath)) {
                $errors[] = 'Gagal menyimpan file poster ke server.';
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare(
                "INSERT INTO film (judul, genre, durasi, rating, deskripsi, tanggal_rilis, poster)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $durasiInt = (int)$old['durasi'];
            $stmt->bind_param(
                'ssissss',
                $old['judul'], $old['genre'], $durasiInt,
                $old['rating'], $old['deskripsi'], $old['tanggal_rilis'], $posterFilename
            );
            $stmt->execute();
            $stmt->close();

            redirectWithMessage('list.php', 'success', 'Film "' . $old['judul'] . '" berhasil ditambahkan.');
        }
    }
}

$activePage = 'film';
$basePath   = '../';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Film | <?= SITE_NAME ?></title>
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
        <h1>Tambah Film</h1>
      </div>
      <a href="list.php" class="btn btn-sm btn-outline-light">← Kembali</a>
    </div>

    <div class="adm-content">

      <!-- Tampilkan error validasi -->
      <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger">⚠️ <?= htmlspecialchars($err) ?></div>
      <?php endforeach; ?>

      <div class="adm-card" style="max-width:720px;">
        <div class="adm-card-title">Form Tambah Film</div>
        <div class="adm-card-sub">Lengkapi semua data film di bawah ini</div>

        <form id="filmForm" method="POST" action="tambah.php" enctype="multipart/form-data">

          <div class="row g-3">

            <div class="col-md-8">
              <label class="form-label" for="judul">Judul Film *</label>
              <input
                type="text" id="judul" name="judul"
                class="form-control"
                placeholder="Contoh: Garuda Perkasa"
                value="<?= htmlspecialchars($old['judul']) ?>"
                required>
            </div>

            <div class="col-md-4">
              <label class="form-label" for="genre">Genre *</label>
              <input
                type="text" id="genre" name="genre"
                class="form-control"
                placeholder="Action, Drama, dll"
                value="<?= htmlspecialchars($old['genre']) ?>"
                required>
            </div>

            <div class="col-md-4">
              <label class="form-label" for="durasi">Durasi (menit) *</label>
              <input
                type="number" id="durasi" name="durasi"
                class="form-control"
                placeholder="120"
                min="1"
                value="<?= htmlspecialchars($old['durasi']) ?>"
                required>
            </div>

            <div class="col-md-4">
              <label class="form-label" for="rating">Rating *</label>
              <select id="rating" name="rating" class="form-select" required>
                <?php foreach ($ratingOptions as $r): ?>
                  <option value="<?= $r ?>" <?= $old['rating'] === $r ? 'selected' : '' ?>><?= $r ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label" for="tanggal_rilis">Tanggal Rilis *</label>
              <input
                type="date" id="tanggal_rilis" name="tanggal_rilis"
                class="form-control"
                value="<?= htmlspecialchars($old['tanggal_rilis']) ?>"
                required>
            </div>

            <div class="col-12">
              <label class="form-label" for="deskripsi">Deskripsi</label>
              <textarea
                id="deskripsi" name="deskripsi"
                class="form-control" rows="4"
                placeholder="Sinopsis singkat film..."><?= htmlspecialchars($old['deskripsi']) ?></textarea>
            </div>

            <div class="col-12">
              <label class="form-label" for="poster">Poster Film</label>
              <input
                type="file" id="poster" name="poster"
                class="form-control"
                accept="image/jpeg,image/png">
              <div class="form-text">Format JPG/PNG, maksimal 2MB.</div>
              <img id="posterPreview" src="" alt="" class="current-poster-preview mt-2" style="display:none;">
            </div>

          </div><!-- end row -->

          <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-adm-primary">💾 Simpan Film</button>
            <a href="list.php" class="btn btn-outline-light">Batal</a>
          </div>

        </form>
      </div>
    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/admin.js"></script>
</body>
</html>
