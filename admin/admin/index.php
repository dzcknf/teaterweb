<?php
/**
 * index.php  –  Halaman Login Admin
 * Ini adalah pintu masuk utama panel admin.
 */

require_once 'includes/config.php';

// Jika sudah login, langsung lempar ke dashboard
if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];

// ----------------------------------------------------------------
// Proses form login (POST)
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'Username dan password wajib diisi.';
    } else {
        // Ambil data admin berdasarkan username (prepared statement)
        $stmt = $conn->prepare("SELECT id, username, password_hash, nama FROM admin WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Verifikasi password menggunakan password_verify (aman dari timing attack)
        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Login berhasil: simpan data ke session
            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_nama']     = $admin['nama'];

            // Redirect ke halaman yang dituju sebelumnya (jika ada), atau dashboard
            $redirect = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . ($redirect ?: 'dashboard.php'));
            exit;
        } else {
            // Pesan generik agar tidak bocorkan apakah username ada atau tidak
            $errors[] = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin | <?= SITE_NAME ?></title>

  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>

<div class="login-wrapper">
  <div class="login-card">

    <div class="login-brand">
      <div class="icon">🎬</div>
      <h1>CINEMA<span>XXI</span></h1>
      <p>Panel Admin</p>
    </div>

    <!-- Tampilkan error jika ada -->
    <?php foreach ($errors as $err): ?>
      <div class="alert alert-danger py-2 px-3" style="font-size:.85rem;">
        ⚠️ <?= htmlspecialchars($err) ?>
      </div>
    <?php endforeach; ?>

    <form method="POST" action="index.php">
      <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <input
          type="text" id="username" name="username"
          class="form-control"
          placeholder="admin"
          value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
          required autofocus>
      </div>

      <div class="mb-4">
        <label class="form-label" for="password">Password</label>
        <input
          type="password" id="password" name="password"
          class="form-control"
          placeholder="••••••••"
          required>
      </div>

      <button type="submit" class="btn btn-adm-primary w-100 py-2">
        Masuk
      </button>
    </form>

    <p class="text-center mt-4" style="font-size:.75rem;color:var(--adm-muted);">
      &copy; <?= date('Y') ?> CinemaXXI Admin Panel
    </p>
  </div>
</div>

</body>
</html>
