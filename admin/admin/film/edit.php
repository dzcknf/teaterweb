<?php
/**
 * film/edit.php  –  Form Edit Film
 * Form sama seperti tambah, tapi pre-filled dengan data lama.
 * Poster bisa diganti atau dibiarkan tetap.
 */

require_once '../includes/config.php';
require_once '../includes/auth_check.php';

$ratingOptions = ['G','PG','PG-13','R','NC-17','SU','R13','R17','R21','D17'];

// ----------------------------------------------------------------
// Ambil ID film dari URL
// ----------------------------------------------------------------
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirectWithMessage('list.php', 'error', 'ID film tidak valid.');
}

// Ambil data film yang akan diedit
$stmt = $conn->prepare("SELECT * FROM film WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$film = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$film) {
    redirectWithMessage('list.php', 'error', 'Film tidak ditemukan.');
}

$errors = [];

// Data form, default dari database
$old = [
    'judul'         => $film['judul'],
    'genre'         => $film['genre'],
    'durasi'        => $film['durasi'],
    'rating'        => $film['rating'],
    'deskripsi'     => $film['deskripsi'],
    'tanggal_rilis' => $film['tanggal_rilis'],
];

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
    if ($old['judul'] === '')  $errors[] = 'Judul film wajib diisi.';
    if ($old['genre'] === '')  $errors[] = 'Genre wajib diisi.';
    if ($old['durasi'] === '' || !is_numeric($old['durasi']) || (int)$old['durasi'] <= 0) {
        $errors[] = 'Durasi harus berupa angka lebih dari 0.';
    }
    if (!in_array($old['rating'], $ratingOptions, true)) {
        $errors[] = 'Rating tidak valid.';
    }
    if ($old['tanggal_rilis'] === '' || !strtotime($old['tanggal_rilis'])) {
        $errors[] = 'Tanggal rilis wajib diisi dengan format yang benar.';
    }

    // ---- Poster: ganti hanya jika ada file baru diupload ----
    $posterFilename = $film['poster']; // default: pertahankan poster lama
    $oldPosterToDelete = null;

    if (!empty($_FILES['poster']['name'])) {
        $file = $_FILES['poster'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Terjadi kesalahan saat upload poster.';
        } else {
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/jpg' => 'jpg'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!array_key_exists($mimeType, $allowedTypes)) {
                $errors[] = 'Poster harus berformat JPG atau PNG.';
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $errors[] = 'Ukuran poster maksimal 2MB.';
            } else {
                $ext = $allowedTypes[$mimeType];
                $newFilename = 'film_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

                if (empty($errors)) {
                    if (!is_dir(POSTER_UPLOAD_DIR)) {
                        mkdir(POSTER_UPLOAD_DIR, 0755, true);
                    }
                    $targetPath = POSTER_UPLOAD_DIR . $newFilename;

                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        // Tandai poster lama untuk dihapus setelah update berhasil
                        $oldPosterToDelete = $film['poster'];
                        $posterFilename = $newFilename;
                    } else {
                        $errors[] = 'Gagal menyimpan file poster baru ke server.';
                    }
                }
            }
        }
    }

    // ---- Update database jika tidak ada error ----
    if (empty($errors)) {
        $durasiInt = (int)$old['durasi'];

        $stmt = $conn->prepare(
            "UPDATE film SET judul=?, genre=?, durasi=?, rating=?, deskripsi=?, tanggal_rilis=?, poster=?
             WHERE id=?"
        );
        // Tipe parameter: s(judul) s(genre) i(durasi) s(rating) s(deskripsi) s(tanggal) s(poster) i(id)
        $stmt->bind_param(
            'ssissssi', // s,s,i,s,s,s,s,i = judul,genre,durasi,rating,deskripsi,tanggal,poster,id
            $old['judul'], $old['genre'], $durasiInt,
            $old['rating'], $old['deskripsi'], $old['tanggal_rilis'],
            $posterFilename, $id
        );
        $stmt->execute();
        $stmt->close();

        // Hapus file poster lama dari server jika diganti
        if ($oldPosterToDelete && file_exists(POSTER_UPLOAD_DIR . $oldPosterToDelete)) {
            @unlink(POSTER_UPLOAD_DIR . $oldPosterToDelete);
        }

        redirectWithMessage('list.php', 'success', 'Film "' . $old['judul'] . '" berhasil diperbarui.');
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
  <title>Edit Film | <?= SITE_NAME ?></title>
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
        <h1>Edit Film</h1>
      </div>
      <a href="list.php" class="btn btn-sm btn-outline-light">← Kembali</a>
    </div>

    <div class="adm-content">

      <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger">⚠️ <?= htmlspecialchars($err) ?></div>
      <?php endforeach; ?>

      <div class="adm-card" style="max-width:720px;">
        <div class="adm-card-title">Edit Data Film</div>
        <div class="adm-card-sub">Mengedit: <?= htmlspecialchars($film['judul']) ?></div>

        <form id="filmForm" method="POST" action="edit.php?id=<?= $id ?>" enctype="multipart/form-data">

          <div class="row g-3">

            <div class="col-md-8">
              <label class="form-label" for="judul">Judul Film *</label>
              <input
                type="text" id="judul" name="judul"
                class="form-control"
                value="<?= htmlspecialchars($old['judul']) ?>"
                required>
            </div>

            <div class="col-md-4">
              <label class="form-label" for="genre">Genre *</label>
              <input
                type="text" id="genre" name="genre"
                class="form-control"
                value="<?= htmlspecialchars($old['genre']) ?>"
                required>
            </div>

            <div class="col-md-4">
              <label class="form-label" for="durasi">Durasi (menit) *</label>
              <input
                type="number" id="durasi" name="durasi"
                class="form-control"
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
                class="form-control" rows="4"><?= htmlspecialchars($old['deskripsi']) ?></textarea>
            </div>

            <div class="col-12">
              <label class="form-label" for="poster">Poster Film</label>

              <!-- Tampilkan poster saat ini -->
              <?php if (!empty($film['poster'])): ?>
                <div class="mb-2">
                  <img
                    src="<?= POSTER_WEB_PATH . htmlspecialchars($film['poster']) ?>"
                    class="current-poster-preview"
                    alt="Poster saat ini">
                  <div class="form-text">Poster saat ini. Upload file baru untuk menggantinya.</div>
                </div>
              <?php else: ?>
                <div class="form-text mb-2">Belum ada poster.</div>
              <?php endif; ?>

              <input
                type="file" id="poster" name="poster"
                class="form-control"
                accept="image/jpeg,image/png">
              <div class="form-text">Format JPG/PNG, maksimal 2MB. Biarkan kosong jika tidak ingin mengganti.</div>
              <img id="posterPreview" src="" alt="" class="current-poster-preview mt-2" style="display:none;">
            </div>

          </div><!-- end row -->

          <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-adm-primary">💾 Update Film</button>
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
