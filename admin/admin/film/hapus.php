<?php
/**
 * film/hapus.php  –  Hapus Film
 * Menghapus record film dari database beserta file poster di server.
 * Konfirmasi sudah dilakukan di sisi client (JS confirm) sebelum link ini diakses.
 */

require_once '../includes/config.php';
require_once '../includes/auth_check.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirectWithMessage('list.php', 'error', 'ID film tidak valid.');
}

// Ambil dulu data film untuk tahu nama file poster & judul (untuk pesan)
$stmt = $conn->prepare("SELECT judul, poster FROM film WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$film = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$film) {
    redirectWithMessage('list.php', 'error', 'Film tidak ditemukan.');
}

// ----------------------------------------------------------------
// Cek apakah film ini masih punya jadwal terkait
// (FK akan menolak delete kalau masih dipakai jadwal, ON DELETE RESTRICT)
// ----------------------------------------------------------------
$stmtCheck = $conn->prepare("SELECT COUNT(*) AS total FROM jadwal WHERE id_film = ?");
$stmtCheck->bind_param('i', $id);
$stmtCheck->execute();
$jumlahJadwal = $stmtCheck->get_result()->fetch_assoc()['total'];
$stmtCheck->close();

if ($jumlahJadwal > 0) {
    redirectWithMessage(
        'list.php',
        'error',
        'Film "' . $film['judul'] . '" tidak bisa dihapus karena masih memiliki ' . $jumlahJadwal . ' jadwal tayang. Hapus jadwalnya terlebih dahulu.'
    );
}

// ----------------------------------------------------------------
// Hapus record film dari database
// ----------------------------------------------------------------
$stmtDel = $conn->prepare("DELETE FROM film WHERE id = ?");
$stmtDel->bind_param('i', $id);
$stmtDel->execute();
$stmtDel->close();

// ----------------------------------------------------------------
// Hapus file poster dari server (jika ada)
// ----------------------------------------------------------------
if (!empty($film['poster'])) {
    $posterPath = POSTER_UPLOAD_DIR . $film['poster'];
    if (file_exists($posterPath)) {
        @unlink($posterPath);
    }
}

redirectWithMessage('list.php', 'success', 'Film "' . $film['judul'] . '" berhasil dihapus.');
