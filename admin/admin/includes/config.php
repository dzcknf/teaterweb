<?php
/**
 * includes/config.php
 * Koneksi database untuk Panel Admin.
 * -------------------------------------------------------------
 * PENTING: isi data ini SAMA PERSIS dengan config.php di website utama,
 * karena admin panel ini mengakses database yang sama.
 */

// Mulai session di awal (dibutuhkan untuk login & proteksi halaman)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------------------------------------------------------
// EDIT BAGIAN INI SESUAI HOSTING KAMU
// ---------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_USER', 'root');            // ganti dengan username database
define('DB_PASS', '');                // ganti dengan password database
define('DB_NAME', 'cinema_bioskop'); // ganti dengan nama database

define('SITE_NAME', 'CinemaXXI Admin');

// Path folder upload poster (relatif dari file yang memanggil)
// dan path absolut untuk operasi hapus file
define('POSTER_UPLOAD_DIR', __DIR__ . '/../../assets/img/poster/');
define('POSTER_WEB_PATH', '/assets/img/poster/'); // sesuaikan jika folder berbeda

// ---------------------------------------------------------------
// Koneksi MySQLi
// ---------------------------------------------------------------
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;background:#1a1a1a;color:#ff6b6b;">
            <h2>⚠️ Koneksi Database Gagal</h2>
            <p>Periksa pengaturan di <code>includes/config.php</code></p>
         </div>');
}

$conn->set_charset('utf8mb4');

/**
 * Helper: escape string (untuk kasus tertentu di luar prepared statement)
 */
function esc($conn, $str) {
    return $conn->real_escape_string(trim($str));
}

/**
 * Helper: format rupiah
 */
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Helper: format durasi menit -> jam menit
 */
function formatDurasi($menit) {
    $jam  = floor($menit / 60);
    $sisa = $menit % 60;
    return ($jam > 0 ? $jam . ' jam ' : '') . ($sisa > 0 ? $sisa . ' menit' : '');
}

/**
 * Helper: redirect dengan pesan flash (disimpan di session)
 */
function redirectWithMessage($url, $type, $message) {
    $_SESSION['flash_type']    = $type;    // 'success' atau 'error'
    $_SESSION['flash_message'] = $message;
    header('Location: ' . $url);
    exit;
}

/**
 * Helper: tampilkan & hapus flash message (dipanggil di template)
 */
function showFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] === 'error' ? 'danger' : 'success';
        $icon = $_SESSION['flash_type'] === 'error' ? '⚠️' : '✅';
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">'
           . $icon . ' ' . htmlspecialchars($_SESSION['flash_message'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
           . '</div>';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    }
}
