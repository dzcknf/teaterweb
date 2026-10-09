<?php
/**
 * config.php
 * File konfigurasi database - EDIT BAGIAN INI SESUAI HOSTING KAMU
 * ---------------------------------------------------------------
 * Untuk XAMPP lokal   : host = localhost, user = root, pass = ''
 * Untuk cPanel Hosting: sesuaikan dengan data dari cPanel > MySQL
 */

define('DB_HOST', 'localhost');       // Nama host database
define('DB_USER', 'root');            // Username database
define('DB_PASS', '');                // Password database
define('DB_NAME', 'cinema_bioskop'); // Nama database

define('SITE_NAME', 'CinemaXXI');    // Nama website
define('SITE_URL',  '');             // URL lengkap jika perlu (opsional)

// ---------------------------------------------------------------
// Buat koneksi menggunakan MySQLi
// ---------------------------------------------------------------
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek apakah koneksi berhasil
if ($conn->connect_error) {
    // Tampilkan pesan error yang ramah (sembunyikan detail teknis di produksi)
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;background:#111;color:#e63;">
            <h2>⚠️ Koneksi Database Gagal</h2>
            <p>Cek pengaturan database di <code>config.php</code></p>
         </div>');
}

// Set charset agar karakter Indonesia tampil dengan benar
$conn->set_charset('utf8mb4');

/**
 * Fungsi helper: escape string untuk mencegah SQL Injection
 */
function esc($conn, $str) {
    return $conn->real_escape_string(trim($str));
}

/**
 * Fungsi helper: format rupiah
 */
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Fungsi helper: format durasi menit → jam menit
 */
function formatDurasi($menit) {
    $jam  = floor($menit / 60);
    $sisa = $menit % 60;
    return ($jam > 0 ? $jam . ' jam ' : '') . ($sisa > 0 ? $sisa . ' menit' : '');
}
