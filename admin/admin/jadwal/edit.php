<?php
/**
 * jadwal/edit.php  –  Form Edit Jadwal Tayang
 */

require_once '../includes/config.php';
require_once '../includes/auth_check.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirectWithMessage('list.php', 'error', 'ID jadwal tidak valid.');
}

// Ambil data jadwal yang akan diedit
$stmt = $conn->prepare("SELECT * FROM jadwal WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$jadwal = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$jadwal) {
    redirectWithMessage('list.php', 'error', 'Jadwal tidak ditemukan.');
}

// Dropdown data
$films   = $conn->query("SELECT id, judul FROM film ORDER BY judul ASC")->fetch_all(MYSQLI_ASSOC);
$studios = $conn->query("SELECT id, nama_studio, kapasitas FROM studio ORDER BY nama_studio ASC")->fetch_all(MYSQLI_ASSOC);

$errors = [];
$old = [
    'id_film'    => $jadwal['id_film'],
    'id_studio'  => $jadwal['id_studio'],
    'tanggal'    => $jadwal['tanggal'],
    'jam_tayang' => substr($jadwal['jam_tayang'], 0, 5),
    'harga'      => $jadwal['harga'],
];

// ----------------------------------------------------------------
// Proses form (POST)
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old['id_film']    = (int)($_POST['id_film'] ?? 0);
    $old['id_studio']  = (int)($_POST['id_studio'] ?? 0);
    $old['tanggal']    = trim($_POST['tanggal'] ?? '');
    $old['jam_tayang'] = trim($_POST['jam_tayang'] ?? '');
    $old['harga']      = trim($_POST['harga'] ?? '');

    if ($old['id_film'] <= 0)               $errors[] = 'Pilih film terlebih dahulu.';
    if ($old['id_studio'] <= 0)             $errors[] = 'Pilih studio terlebih dahulu.';
    if (!$old['tanggal'] || !strtotime($old['tanggal'])) $errors[] = 'Tanggal tidak valid.';
    if (!$old['jam_tayang'])                $errors[] = 'Jam tayang wajib diisi.';
    if (!is_numeric($old['harga']) || (float)$old['harga'] <= 0) {
        $errors[] = 'Harga harus berupa angka lebih dari 0.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "UPDATE jadwal SET id_film=?, id_studio=?, tanggal=?, jam_tayang=?, harga=? WHERE id=?"
        );
        $hargaFloat = (float)$old['harga'];
        $stmt->bind_param('iissdi', $old['id_film'], $old['id_studio'], $old['tanggal'], $old['jam_tayang'], $hargaFloat, $id);
        $stmt->execute();
        $stmt->close();

        redirectWithMessage('list.php', 'success', 'Jadwal berhasil diperbarui.');
    }
}

$activePage = 'jadwal';
$basePath   = '../';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Jadwal | <?= SITE_NAME ?></title>
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
        <h1>Edit Jadwal</h1>
      </div>
      <a href="list.php" class="btn btn-sm btn-outline-light">← Kembali</a>
    </div>

    <div class="adm-content">

      <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger">⚠️ <?= htmlspecialchars($err) ?></div>
      <?php endforeach; ?>

      <div class="adm-card" style="max-width:600px;">
        <div class="adm-card-title">Edit Jadwal Tayang</div>
        <div class="adm-card-sub">Mengedit jadwal #<?= $id ?></div>

        <form method="POST" action="edit.php?id=<?= $id ?>">

          <div class="row g-3">

            <div class="col-12">
              <label class="form-label" for="id_film">Film *</label>
              <select id="id_film" name="id_film" class="form-select" required>
                <option value="">-- Pilih Film --</option>
                <?php foreach ($films as $f): ?>
                  <option value="<?= $f['id'] ?>" <?= $old['id_film'] == $f['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['judul']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label" for="id_studio">Studio *</label>
              <select id="id_studio" name="id_studio" class="form-select" required>
                <option value="">-- Pilih Studio --</option>
                <?php foreach ($studios as $s): ?>
                  <option value="<?= $s['id'] ?>" <?= $old['id_studio'] == $s['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nama_studio']) ?> (<?= $s['kapasitas'] ?> kursi)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label" for="tanggal">Tanggal Tayang *</label>
              <input
                type="date" id="tanggal" name="tanggal"
                class="form-control"
                value="<?= htmlspecialchars($old['tanggal']) ?>"
                required>
            </div>

            <div class="col-md-6">
              <label class="form-label" for="jam_tayang">Jam Tayang *</label>
              <input
                type="time" id="jam_tayang" name="jam_tayang"
                class="form-control"
                value="<?= htmlspecialchars($old['jam_tayang']) ?>"
                required>
            </div>

            <div class="col-12">
              <label class="form-label" for="harga">Harga Tiket (Rp) *</label>
              <input
                type="number" id="harga" name="harga"
                class="form-control"
                min="1" step="500"
                value="<?= htmlspecialchars($old['harga']) ?>"
                required>
            </div>

          </div>

          <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-adm-primary">💾 Update Jadwal</button>
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
