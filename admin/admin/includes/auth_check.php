<?php
/**
 * includes/auth_check.php
 * Proteksi halaman admin: pastikan user sudah login.
 * Panggil file ini di BARIS PALING ATAS setiap halaman admin
 * (setelah config.php) dengan: require_once 'includes/auth_check.php';
 */

// config.php harus sudah di-include sebelumnya (untuk session_start & koneksi)
if (!defined('DB_HOST')) {
    die('config.php harus di-include sebelum auth_check.php');
}

// Jika belum login, lempar ke halaman login
if (empty($_SESSION['admin_id'])) {
    // Simpan halaman yang dituju supaya bisa redirect balik setelah login (opsional)
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
    header('Location: ' . getLoginUrl());
    exit;
}

/**
 * Helper: tentukan path relatif ke index.php (login) berdasarkan
 * kedalaman folder saat ini (root admin/ atau admin/film/, dll)
 */
function getLoginUrl() {
    // Jika file pemanggil ada di dalam subfolder (film/, jadwal/), naik 1 level
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $isSubfolder = (basename($scriptDir) === 'film' || basename($scriptDir) === 'jadwal');
    return $isSubfolder ? '../index.php' : 'index.php';
}
