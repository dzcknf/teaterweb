<?php
/**
 * jadwal/hapus.php  –  Hapus Jadwal Tayang
 */

require_once '../includes/config.php';
require_once '../includes/auth_check.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirectWithMessage('list.php', 'error', 'ID jadwal tidak valid.');
}

// Cek apakah jadwal ini masih punya transaksi terkait (FK RESTRICT)
$stmtCheck = $conn->prepare("SELECT COUNT(*) AS total FROM transaksi WHERE id_jadwal = ?");
$stmtCheck->bind_param('i', $id);
$stmtCheck->execute();
$jumlahTrx = $stmtCheck->get_result()->fetch_assoc()['total'];
$stmtCheck->close();

if ($jumlahTrx > 0) {
    redirectWithMessage(
        'list.php',
        'error',
        'Jadwal ini tidak bisa dihapus karena sudah memiliki ' . $jumlahTrx . ' transaksi terkait.'
    );
}

$stmtDel = $conn->prepare("DELETE FROM jadwal WHERE id = ?");
$stmtDel->bind_param('i', $id);
$stmtDel->execute();
$stmtDel->close();

redirectWithMessage('list.php', 'success', 'Jadwal berhasil dihapus.');
